<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');             
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'user_upload_controller.php'); 
                                                                                 
class User_Question extends User_Upload_Controller{

    private $_smarty_dir = 'teacher/user/';
       
    public function __construct(){
       	parent::__construct();
        $this->load->model('user_data/user_question_model','teacher_question');
        $this->load->model('user_data/user_category_model');
        $this->load->model('question/question_course_model');
        $this->load->model('question/question_category_model');
        $this->load->model("question/question_level_model");  
        $this->smarty->assign('sj_url',site_url().'teacher/user/myquestion'); 
    }

    function index($subject_id = 0)
    {   
        $data['subject_id']=$this->input->get('sid');
        $data['qtype']=$this->input->get('qtype');
        $data['qlevel']=$this->input->get('qlevel');
        if(!$data['subject_id']) $data['subject_id']=$subject_id;
        if(!$data['subject_id']) $data['subject_id']=$this->register_model->my_subject($this->_uid,'paper');
        if(!$this->question_subject_model->check_subject($data['subject_id'])) $data['subject_id']=Constant::DEFAULT_SUBJECT_ID;
        $this->register_model->set_favorate_subject($data['subject_id'],'paper');
        $data['subject_name']=$this->question_subject_model->get_subject_name($data['subject_id']);
        list($data['all_total'],$data['groups']) = self::_get_group($data['subject_id']);
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'user_question_index.html');
    }


    protected function assign_groups_by_sid($subject_id)
    {
        $groups = $this->group->get_list($subject_id,$this->_uid,Constant::QUESTION_GROUP);
        $this->smarty->assign('groups',$groups);
    }

    public function myquestion_index($group_id = 0)
    {
        $question_type=$question_level=false;
        $data['subject_id']=$this->register_model->my_subject($this->_uid,'paper');
        $data['subject_name']=$this->question_subject_model->get_subject_name($data['subject_id']);
        if($group_id > 0){
            $data['group_info'] = $this->group->get_single_group($this->_uid,$group_id);
            if(empty($data['group_info'])){
                tizi_404('teacher/user/myquestion');
                exit();
            }
        }else{
            $data['group_info'] = new stdClass();
            $data['group_info']->id=0;
            $data['group_info']->name='未分组的题目';
        }
        $questions = array();
        $data['total']       = self::_get_question($group_id,1,$question_type,$question_level,$data['subject_id'],true);
        $pages = self::get_pagination(1,$data['total'],'teacher_ques_page');
        $data['easy_level'] = Constant::level_name(null,true);//难度级别数组(常量函数)
        $data['question'] = self::_get_question($group_id,1,$question_type,$question_level,$data['subject_id'],false);
        self::assign_groups_by_sid($data['subject_id']);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'user_question_list_index.html');
    }

    public function ajax_get_teacher_question()
    {
        $question_type = $question_level = false;
        $group_select=$this->input->get("gid");
        $page_select=$this->input->get("page");
        $subject_id=$this->input->get("subject"); 
        
        $questions = array();
        if($page_select<=0) $page_select=1;
        if($group_select=='') $group_select = '0';
        if($group_select=='0'){
            $data['group_info'] = new stdClass();
            $data['group_info']->id=0;
            $data['group_info']->name='未分组的题目';
        }else{
            $data['group_info'] = $this->group->get_single_group($this->_uid,$group_select);
        }
        $data['total']       = self::_get_question($group_select,$page_select,$question_type,$question_level,$subject_id,true);
        $data['page_total']  = ceil($data['total']/Constant::LESSON_PER_PAGE);
        $data['page_num']    = $page_select;
        $pages = self::get_pagination($data['page_num'],$data['total'],'teacher_ques_page');
        $data['easy_level'] = Constant::level_name(null,true);//难度级别数组(常量函数)
        $data['question'] = self::_get_question($group_select,$page_select,$question_type,$question_level,$subject_id,false);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('data',$data);
        $questions['html'] = $this->smarty->fetch($this->_smarty_dir.'user_question_list_tpl.html');
        $questions['errorcode'] = true;
        $questions['total'] = $data['total'];
        echo json_encode($questions);
        exit();
    }

    protected function _get_group( $subject_id )
    {
        $ungroup = new stdClass();
        $ungroup->id=0;
        $ungroup->name='未分组的题目';
        $group_arr = $this->group->get_list($subject_id,$this->_uid,Constant::QUESTION_GROUP);
        array_unshift($group_arr, $ungroup); 
        $all_total = 0;
        if($group_arr){
            foreach ($group_arr as &$value) {
               $value->total = $this->group->group_data_statistics($subject_id,$this->_uid,$value->id,'question','get');
               $all_total +=$value->total;
            }
        }
        return array($all_total,$group_arr);
    }
    
    public function ajax_get_ques_groups()
    {
        $subject_id = $this->input->get('sid');
        $gid = $this->input->get('gid');
        list($data['all_total'],$data['groups']) = self::_get_group($subject_id);
        $data['groups'][0]->id = '0';
        $data['sel_gid'] = $gid===''?'all':$gid;
        $this->smarty->assign('data',$data);
        $re_content['html'] = $this->smarty->fetch($this->_smarty_dir.'user_groups_tpl.html');
        $re_content['error_code'] = true;
        echo json_encode($re_content);
        exit();
    }

    protected function _get_question($group_id,$select_page,$question_type,$question_level,$subject_id,$is_total)
    {
        if($is_total){
            $total_num = $this->teacher_question->get_question_list($group_id,$select_page,$question_type,$question_level,$subject_id,$is_total);
            return $total_num;
        }else{
            $questions = array();
            $question_list = $this->teacher_question->get_question_list($group_id,$select_page,$question_type,$question_level,$subject_id);
            $i=0;
            foreach($question_list as $key=>$val)
            {
                $questions[$i]['id']=$val->id;
                $questions[$i]['title']=$val->title;
                $questions[$i]['date']=date("Y-m-d",strtotime($val->date));
                $questions[$i]['qtype_id']=$val->qtype_id;
                $questions[$i]['qtype_name']=$val->qtype_name;
                $questions[$i]['level_id']=$val->level_id;
                $questions[$i]['body']=$val->body;
                $questions[$i]['answer']=$val->answer;
                $questions[$i]['analysis']=$val->analysis;
                $questions[$i]['group_id']=$val->group_id;
                $course_info = $category_info = '';
                $edu_list = $knowledge_list = array();
                $course_list = $this->teacher_question->get_question_category($val->id,'course'); 
                $category_list = $this->teacher_question->get_question_category($val->id,'category');
                
                if(!empty($course_list)){
                    foreach ($course_list as $k => $v){
                        $edu_list[] = $this->question_category_model->get_single_path($v->category_id,'*');
                    }
                    $course_info = self::_build_category_string($edu_list,'course',false);
                }
                if(!empty($category_list)){
                    foreach ($category_list as $k => $v){
                        $knowledge_list[] = $this->question_category_model->get_single_path($v->category_id, "*");
                    }
                    $category_info = self::_build_category_string($knowledge_list,'category',false);
                }
                $questions[$i]['course_info']=$course_info;
                $questions[$i]['category_info']=$category_info;
                $i++;
            }
            return $questions;
        }
    }

    function new_question(){

        $data['subject_id']=$this->register_model->my_subject($this->_uid,'paper');
        $data['easy_level'] = Constant::level_name(null,true);//难度级别数组(常量函数)
        $sel_subject = $this->register_model->my_subject($this->_uid,'paper');
        $data['groups'] = $this->group->get_list($sel_subject,$this->_uid,Constant::QUESTION_GROUP);
        $data['referer'] = $this->input->server('HTTP_REFERER');
        $question_info = $this->teacher_question->get_last_modified_question($this->_uid,$sel_subject);
        $is_use_cache = false;
        if($question_info){

            $subject_list = $this->question_subject_model->get_subjects_by_sid($question_info->subject_id);

            $subject_msg = $this->question_subject_model->get_grade_by_subject($question_info->subject_id);
            $knowledge_list = $edu_list = array();
            $course_list = $this->teacher_question->get_question_category($question_info->id,'course'); 
            $category_list = $this->teacher_question->get_question_category($question_info->id,'category'); 
            foreach ($course_list as $k => $v){
                $edu_list[] = $this->question_category_model->get_single_path($v->category_id,'*');
            }
            foreach ($category_list as $k => $v){
                $knowledge_list[] = $this->question_category_model->get_single_path($v->category_id, "*");
            }

            list($first_k, $knowledge_list) = self::_remove_first_node($knowledge_list);
            list($first_edu, $edu_list) = self::_remove_first_node($edu_list);
            list($ksid, $knowledge_str) =  self::_build_category_string($knowledge_list);
            list($esid, $edu_str) =  self::_build_category_string($edu_list,'course');
            $subject_name = $subject_msg->name;
            $show_answer_html=false;
            if(!in_array($question_info->qtype_id, array(2,3,4,5,6,7)))
            {
                $show_answer_html=true;
            }
            $qtype_arr = $this->question_type_model->get_subject_question_type($question_info->subject_id,false);
            $version_list = parent::edit_category_band($question_info->subject_id);
            $data_cache = array(
                'subject_id'=>$question_info->subject_id,
                'questions'=>$question_info,
                'knowledge_list'=> isset($knowledge_list) ? $knowledge_list : null,
                'category_list'=> isset($edu_list) ? $edu_list : null,
                'edu_str' => isset($edu_str) ? $edu_str : null,
                'knowledge_str' => isset($knowledge_str) ? $knowledge_str: null,
                'ksid' => isset($ksid) ? $ksid: null,
                'esid' => isset($esid) ? $esid: null,
                'show_answer_html'=>$show_answer_html,
                'grade_id'=>$subject_msg->grade,
                'qtype_arr'=>$qtype_arr,
                'version_list'=>$version_list,
                'subject_list'=>$subject_list,
                'subject_name'=>$subject_name);
            $data = array_merge($data,$data_cache);
            $is_use_cache=true;
        }
        $this->smarty->assign('data',$data);
        if($is_use_cache)
        $this->smarty->display($this->_smarty_dir.'user_question_new_cache.html');
        else
        $this->smarty->display($this->_smarty_dir.'user_question_new.html');    
    }

     function edit_question($question_id=null){
        if(!is_null($question_id))
        {
            $subject_id=$this->register_model->my_subject($this->_uid,'paper');
            $question_info = $this->teacher_question->get_single_question($question_id,$this->_uid);
            if(empty($question_info)){
                tizi_404('teacher/user/myquestion');
            }
            $subject_msg = $this->question_subject_model->get_grade_by_subject($question_info->subject_id);
            $sel_subject = $this->register_model->my_subject($this->_uid,'paper');
            $groups = $this->group->get_list($sel_subject,$this->_uid,Constant::QUESTION_GROUP);
            $knowledge_list = $edu_list = array();
            $course_list = $this->teacher_question->get_question_category($question_id,'course'); 
            $category_list = $this->teacher_question->get_question_category($question_id,'category'); 
            foreach ($course_list as $k => $v){
                $edu_list[] = $this->question_category_model->get_single_path($v->category_id,'*');
            }
            foreach ($category_list as $k => $v){
                $knowledge_list[] = $this->question_category_model->get_single_path($v->category_id, "*");
            }

            list($first_k, $knowledge_list) = self::_remove_first_node($knowledge_list);
            list($first_edu, $edu_list) = self::_remove_first_node($edu_list);
            list($ksid, $knowledge_str) =  self::_build_category_string($knowledge_list);
            list($esid, $edu_str) =  self::_build_category_string($edu_list,'course');
            $subject_name = $subject_msg->name;
            $question_info->options = str_split($question_info->options);
            $question_info->last_option = end($question_info->options);
            $show_answer_html=false;
            if(!in_array($question_info->qtype_id, array(2,3,4,5,6,7)))
            {
                $show_answer_html=true;
            }
            $qtype_arr = $this->question_type_model->get_subject_question_type($question_info->subject_id,false);
            $version_list = parent::edit_category_band($question_info->subject_id);
            $data = array(
                'subject_id'=>$subject_id,
                'questions'=>$question_info,
                'knowledge_list'=> isset($knowledge_list) ? $knowledge_list : null,
                'category_list'=> isset($edu_list) ? $edu_list : null,
                'question_id'=>isset($question_id) ? $question_id : null,
                'edu_str' => isset($edu_str) ? $edu_str : null,
                'knowledge_str' => isset($knowledge_str) ? $knowledge_str: null,
                'ksid' => isset($ksid) ? $ksid: null,
                'esid' => isset($esid) ? $esid: null,
                'easy_level'=>Constant::level_name(null,true),
                'show_answer_html'=>$show_answer_html,
                'grade_id'=>$subject_msg->grade,
                'qtype_arr'=>$qtype_arr,
                'groups'=>$groups,
                'referer'=>$this->input->server('HTTP_REFERER'),
                'version_list'=>$version_list,
                'subject_name'=>$subject_name);
            $this->smarty->assign('data',$data);
            $this->smarty->display($this->_smarty_dir.'user_question_edit.html');
        }
        else
        {
            tizi_404('teacher/user/myquestion');
            exit;
        }
    }

    protected function _remove_first_node($arr){
        $new_arr = array();
        $first = null;
        foreach($arr as $k=>$v){
            if (is_array($v) and !empty($v))  {
                $first = $v[0];
                $new_arr[$k] = $v;
            } else {
                return $arr;
            }
        }
        return array($first, $new_arr);
    }

    protected function _build_category_string($arr, $type='category',$is_single=true,$namesep = '--', $idsep='-', $idsep1='|'){
        if($is_single){

            $js_func = $type=='category'?'delCategorySelect':'delCourseSelect';
            $id_str = "";
            $sid = '';
            foreach($arr as $k => $v){
                $name = '';$id='';
                foreach($v as $i => $j){
                    if(is_object($j)){
                        $name .= $j->name . $namesep;
                        $id .= $j->id . $idsep;
                    }else {
                        return false;
                    }
                }
                $sid .= $id . $idsep1;
                $id_str .= '<p id="'. $id . '">' . $name . '<a href="javascript:void(0);" onclick="Teacher.UserCenter.my_question_ajax.'.$js_func.'(\'' 
                    . $id . '\',this);" class="del_message">删除</a></p>';
            }
            return array($sid, $id_str);
        }else{
            $name_str = '';
            foreach($arr as $k => $v){
                $name = '';
                foreach($v as $i => $j){
                    if(is_object($j)){
                        $name .= $j->name . $namesep;
                    }else {
                        return false;
                    }
                }
                $name_str .= $name . $idsep1;
            }
            return trim($name_str,'--|');
        }
        
    }

    function submit(){

        $insert_data=array();
        $question_id = $this->input->post('question_id',true);
        $insert_data['qtype_id'] = $this->input->post('qtype',true);
        $insert_data['level_id'] = $this->input->post('level_id',true);
        $insert_data['subject_id'] = $this->input->post('subject_type',true);
        $insert_data['group_id'] = $this->input->post('group',true);
        $insert_data['source'] = $this->input->post('source',true);
        $insert_data['title'] = $this->input->post('source',true);
        $insert_data['body'] = $this->input->post('question_content',true);
        $insert_data['answer'] = $this->input->post('answer_content',true);
        $insert_data['option_answer'] = $this->input->post('option_answer',true);
        $insert_data['analysis'] = $this->input->post('question_analysis',true);
        $insert_data['date'] = date('Y-m-d H:i:s', time());
        $last_option = $this->input->post('last_option',true);
        $category_id    = $this->input->post('category_id',true);
        $knowledge_id   = $this->input->post('knowledge_id',true);
        $insert_data['options'] ="";
        $knowledge_id_list  = splitId($knowledge_id, '|', 1);
        $category_id_list = splitId($category_id, '|', 0);

        self::check_post($insert_data);
        if(in_array(intval($insert_data['qtype_id']), array(2,3,4,5,6,7)))
        {
            $insert_data['options'] = range_options($last_option);
            $insert_data['answer'] = implode(' ', $insert_data['option_answer']);
        }
        unset($insert_data['option_answer']);
        /*添加试题*/
        if (empty($question_id)) {
            $insert_data['user_id'] = $this->_uid;
            $ret = $this->teacher_question->insert_new_question($insert_data);
            /*删除reids统计*/
            $this->group->group_data_statistics($insert_data['subject_id'],$this->_uid,$insert_data['group_id'],'question','delete');
            /*添加知识点信息*/
            if($ret && !empty($knowledge_id_list)){
                $this->user_category_model->make_category_rela($ret,$knowledge_id_list,'add','question','category');
            }
             /*添加章节信息*/
            if($ret && !empty($category_id_list)){
                $this->user_category_model->make_category_rela($ret,$category_id_list,'add','question');
            }
            if($ret)$error=array('error_code'=>true,'error'=>'试题创建成功','type'=>'insert','new_id'=>$ret);
            else $error=array('error_code'=>false,'error'=>'服务器繁忙');

        }else{
            $old_group      = $this->input->post('old_group',true);
            $old_subject    = $this->input->post('old_subject',true);
            //编辑更新分组统计
            if($insert_data['group_id'] !== $old_group){
                $this->group->group_data_statistics($insert_data['subject_id'],$this->_uid,$insert_data['group_id'],'question','delete');
                $this->group->group_data_statistics($old_subject,$this->_uid,$old_group,'question','delete');
            }
            /*编辑试题*/
            $ret = $this->teacher_question->update_question($question_id,$this->_uid,$insert_data);
            /*编辑知识点信息*/
            if($ret){
                $this->user_category_model->del_all_relation($question_id);
                $this->user_category_model->make_category_rela($question_id,$knowledge_id_list,'add','question','category');
                $this->user_category_model->make_category_rela($question_id,$category_id_list,'add','question');
            }
            if($ret)$error=array('error_code'=>true,'error'=>'试题编辑成功','type'=>'edit');
            else $error=array('error_code'=>false,'error'=>'服务器繁忙');
        }
        echo json_token($error);die;
       
    }

    private function check_post(&$post_data)
    {
       
        if($post_data['subject_id']=='' || $post_data['qtype_id']==''){
            echo json_encode(array('success'=>false, 'error'=>'请选择所属学科和题型!')); die;
        }
        if(empty($post_data['body']) || (empty($post_data['answer']) && empty($post_data['option_answer']))){
            echo json_encode(array('success'=>false, 'error'=>'请完善题目和答案信息!')); die;
        }

    }

    public function word_img_upload()
    {
        $this->load->helper("upload");
        if ($_FILES["file"]["error"] > 0)
        {
            echo 'error';
        }else
        {
            $path = word_img_upload('file');
            if($path<0){
                echo 'error';
            }else{
                echo $path;
            }
        }
    }

    protected function get_pagination($page_num,$total,$func)
    {
        $this->load->library('pagination'); 
        $config['total_rows']       = $total; //为页总数
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        $config['per_page']         = Constant::QUESTION_PER_PAGE;
        //获取分页
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_ajax_links();
        return $pages;
    }

    public function new_group()
    {
        if($this->input->is_ajax_request())
        {
            $get_sid_input = $this->input->post('sid',true);
            $get_sid_select = $this->input->post('sel_sid',true);
            $subject_id = $get_sid_input?$get_sid_input:$get_sid_select;
            $group_name = $this->input->post('group_name',true);
            $group_name = filter_file_name(trim($group_name));
            if(strlen($group_name)<1){
                $error['error_code'] = false;
                $error['error'] = '请输入有效的分组名称';
            }else{
                $insert_data = array(
                'name'=>trim($group_name),
                'user_id'=>$this->_uid,
                'subject_id'=>$subject_id,
                'type'=>Constant::QUESTION_GROUP
                );
                $status = $this->group->insert_group($insert_data);
                
                if($status>0){
                    $error['error_code'] = true;
                    $error['error'] = $status;
                    $error['new_name'] = trim($group_name);
                }else{
                    switch ($status) {
                        case -1:
                            $error['error_code'] = false;
                            $error['error'] = '服务器繁忙,请稍后重试';
                            break;
                        case -2:
                            $error['error_code'] = false;
                            $error['error'] = '无效的操作';
                            break;
                        case -3:
                            $error['error_code'] = false;
                            $error['error'] = '每个学科只能创建50个分组哦';
                            break;    
                        default:
                            $error['error_code'] = false;
                            $error['error'] = '服务器繁忙,请稍后重试';
                            break;
                    }
                }
            }
            echo json_token($error);
            exit();
        }
    }

    public function update_group()
    {
        if($this->input->is_ajax_request())
        {
            $op_type = $this->input->post('op_type',true);
            $error = array();
            switch ($op_type) {
                case 'delete':
                    $group_id = $this->input->post('gid',true);
                    $subject_id = $this->input->post('sid',true);
                    $update_data = array('status'=>-1);
                    $status = $this->group->update($group_id,$this->_uid,$update_data);
                    /*删除reids统计*/
                    $this->group->group_data_statistics($subject_id,$this->_uid,$group_id,'question','delete');
                    /*更新为未分组*/
                    if($status){
                        $no_group_count = $this->teacher_question->remove_no_group($group_id,$this->_uid,$subject_id);
                        $error['error_code'] = true;
                        $error['error'] = '删除成功';
                        $error['count'] = $no_group_count;
                    }else{
                        $error['error_code'] = false;
                        $error['error'] = '删除失败';
                    }
                    break;
                case 'update':
                    $group_id = $this->input->post('gid',true);
                    $new_group_name = $this->input->post('new_name',true);
                    $new_group_name = filter_file_name(trim($new_group_name));
                    if(strlen($new_group_name)<1){
                        $error['error_code'] = false;
                        $error['error'] = '请输入有效的分组名称';
                    }else{
                        $update_data = array('name'=>$new_group_name);
                        $status = $this->group->update($group_id,$this->_uid,$update_data);
                        if($status){
                            $error['error_code'] = true;
                            $error['new_group_name'] = $new_group_name;
                            $error['error'] = '更新成功';
                        }else{
                            $error['error_code'] = false;
                            $error['error'] = '更新失败';
                        }
                    }
                    
                    break;    
                default:
                    exit();
                    break;
            }
            echo json_token($error);
            exit();
        }
    }

    function move_group()
    {
        if($this->input->is_ajax_request()){
            $subject_id = $this->input->post('c_sid',true);
            $old_subject_id = $this->input->post('old_sid',true);
            $get_data= $this->input->post('c_qid',true);
            $get_data= explode('-', $get_data);
            $ques_id = $get_data[0];
            $old_group = $get_data[1];
            $new_group = $this->input->post('c_group',true);
            if($old_group==$new_group){
                $error['error_code'] = true;
                $error['error'] = '操作成功！';
            }else{
                $status = $this->teacher_question->update_question($ques_id,$this->_uid,
                    array('group_id'=>$new_group,'subject_id'=>$subject_id));
                if($status){
                    /*删除reids统计*/
                    $this->group->group_data_statistics($subject_id,$this->_uid,$new_group,'question','delete');
                    $this->group->group_data_statistics($old_subject_id,$this->_uid,$old_group,'question','delete');
                    $error['error_code'] = true;
                    $error['error'] = '操作成功！';
                }else{
                    $error['error_code'] = false;
                    $error['error'] = '服务器繁忙！';
                }
            }
            echo json_token($error);exit();
        }
    }

    function del_question()
    {
        if($this->input->is_ajax_request()){
            $get_data= $this->input->get('qid');
            $subject_id = $this->input->get('sid');
            $get_data= explode('-', $get_data);
            $ques_id = $get_data[0];
            $group_id = $get_data[1];
            $status = $this->teacher_question->update_question($ques_id,$this->_uid,array('status'=>2));
            if($status){
                /*删除reids统计*/
                $this->group->group_data_statistics($subject_id,$this->_uid,$group_id,'question','delete');
                $error['error_code'] = true;
            }
            else{
                $error['error_code'] = false;
                $error['error'] = '服务器繁忙！';
            }
            echo json_token($error);exit();
        }
    }
}

/* End of file user_question.php */
