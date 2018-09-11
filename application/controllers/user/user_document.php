<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');             
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'user_upload_controller.php'); 
                                                                                 
class User_Document extends User_Upload_Controller{

    private $_smarty_dir = 'teacher/user/';
       
    public function __construct(){
       	parent::__construct();
        $this->load->model('user_data/user_document_model','teacher_document');
        $this->load->model('user_data/user_category_model');
        $this->load->model('question/question_course_model');
              
    }

    function index($subject_id = 0)
    {
        $sj_url = site_url().'teacher/user/mydocument';
        $template_path = $this->_smarty_dir.'user_document_index.html';
        self::_base_index($subject_id,$sj_url,$template_path,false);
    }

    function my_document_index($subject_id = 0)
    {
        $sj_url = site_url().'teacher/lesson/mydocument';
        $template_path = 'teacher/lesson/my_document_index.html';
        self::_base_index($subject_id,$sj_url,$template_path,true);
    }
    protected function _base_index($subject_id,$sj_url,$template_path,$status_where)
    {
        $data['subject_id']=$this->input->get('sid');
        $data['doctype']=$this->input->get('doctype');
        if(!$data['subject_id']) $data['subject_id']=$subject_id;
        if(!$data['subject_id']) $data['subject_id']=$this->register_model->my_subject($this->_uid,'doc');
        if(!$this->question_subject_model->check_subject($data['subject_id'])) $data['subject_id']=Constant::DEFAULT_SUBJECT_ID;
        $this->register_model->set_favorate_subject($data['subject_id'],'doc');
        $data['subject_name']=$this->question_subject_model->get_subject_name($data['subject_id']);
        if($data['doctype']=="") $data['doctype']=0;
        $data['type_list'] = array((object)array('id'=>2,'name'=>'课件'),(object)array('id'=>3,'name'=>'试题'),(object)array('id'=>8,'name'=>'其他'));
        list($data['all_total'],$data['groups']) = self::_get_group($data['subject_id'],$status_where);
        $data['filter_show'] = $data['all_total'] > 0 ? true : false;
        $this->smarty->assign('sj_url',$sj_url);
        $this->smarty->assign('data',$data);
        $this->smarty->display($template_path);
    }

    public function get_teacher_document()
    {
        $doc_type=$this->input->get("doctype");
        $group_select=$this->input->get("gid");
        $page_select=$this->input->get("page");
        $subject_id=$this->input->get("subject"); 
        $pattern = $this->input->get("pattern")=='lesson'?true:false;  
        $documents = array();
        if($page_select<=0) $page_select=1;
        if($doc_type<0) $doc_type=0;
        if($group_select=='') $group_select = false;
        $data['total']       = self::_get_document($group_select,$page_select,$doc_type,$subject_id,true,$pattern);
        $data['page_total']  = ceil($data['total']/Constant::LESSON_PER_PAGE);
        $data['page_num']    = $page_select;
        $pages = self::get_pagination($data['page_num'],$data['total'],'Teacher.UserCenter.docLib.page');

        $data['document'] = self::_get_document($group_select,$page_select,$doc_type,$subject_id,false,$pattern);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('data',$data);
        $referer = $this->input->server('HTTP_REFERER');
        if(strpos($referer, 'teacher/user')!==false)
        {
            $documents['html'] = $this->smarty->fetch($this->_smarty_dir.'user_document_tpl.html');
        }
        if(strpos($referer, 'teacher/lesson')!==false)
        {
            $documents['html'] = $this->smarty->fetch('teacher/lesson/my_document_tpl.html');
        }
        
        $documents['errorcode'] = true;
        $documents['total'] = $data['total'];
        echo json_encode($documents);
        exit();
    }

    public function preview($doc_id = NULL)
    {
        if(!is_null($doc_id))
        {
            $data = $this->teacher_document->get_single_doc_preview($doc_id,$this->_uid);
            if(empty($data))
            {
                tizi_404('teacher/lesson/mydocument');
                exit;
            }
            $data->upload_time = date("Y-m-d",$data->upload_time);
            $data->file_size = prase_file_size($data->file_size);
            $this->smarty->assign('data',$data);
            $this->smarty->display('teacher/lesson/my_lesson_preview.html');
        }
        else
        {
            tizi_404('teacher/lesson/mydocument');
            exit;
        }
    }

     public function flash_get_json(){
        $doc_id = $this->input->post('id');
        if(empty($doc_id))
        {
            $referer_arr = explode('/', $this->input->server('HTTP_REFERER'));
            $doc_id = $referer_arr[count($referer_arr)-1];
        }
        $preview_data = $this->teacher_document->get_single_doc_preview($doc_id,$this->_uid,true);
        if(empty($preview_data))  
        {
            $files_info = array('status'=>1);
        }
        else
        {
            $uri_api    =  'http://tizi-zujuan-thumb.oss.aliyuncs.com/';
            $swf_list   = array();
            $i = 0;
            while ( $i < $preview_data->page_count) {
                $page = $i+1;
                $swf_list[$i] = $uri_api.$preview_data->swf_folder_path."/preview_{$page}.swf";
                $i++;
            }
            if($swf_list)
            {
                $files_info = array(
                'status'        =>99,
                'page_total'    =>$preview_data->page_count,
                'files_url'     =>$swf_list,
                'file_id'       =>$doc_id,
                'file_ext'      =>$preview_data->file_ext,
                'goto_next_num' =>Constant::PAGE_NUM_LOAD_NEXT
                );
            }
            else
            {
                $files_info = array('status'=>1);
            }
        }
        echo json_encode($files_info);exit;
    }

    protected function _get_group( $subject_id ,$status_where=false)
    {
        $ungroup = new stdClass();
        $ungroup->id=0;
        $ungroup->name='未选分组';
        $group_arr = $this->group->get_list($subject_id,$this->_uid,Constant::DOCUMENT_GROUP);
        array_unshift($group_arr, $ungroup); 
        $all_total = 0;
        if($group_arr){
            foreach ($group_arr as &$value) {
               $value->total = $this->group->group_data_statistics($subject_id,$this->_uid,$value->id,'document','get',$status_where);
               $all_total +=$value->total;
            }
        }
        return array($all_total,$group_arr);
    }

    public function ajax_get_doc_groups()
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

    protected function _get_document($group_id,$select_page,$doc_type,$subject_id,$is_total,$status_where=false)
    {
        if($is_total){
            $total_num = $this->teacher_document->get_docs_list($group_id,$select_page,$doc_type,$subject_id,$is_total,$status_where);
            return $total_num;
        }else{
            $documents = array();
            $document_list = $this->teacher_document->get_docs_list($group_id,$select_page,$doc_type,$subject_id,false,$status_where);
            $i=0;
            foreach($document_list as $key=>$val)
            {
                $documents[$i]['id']=$val->id;
                $documents[$i]['file_name']=$val->file_name;
                $documents[$i]['file_ext']=$val->file_ext;
                $documents[$i]['upload_time']=date("Y-m-d",$val->upload_time);
                $documents[$i]['file_size']=prase_file_size($val->file_size);
                $documents[$i]['group_id']=$val->group_id;
                $documents[$i]['queue_status']=$val->queue_status;
                if($val->course_id){
                    $course_list    = $this->question_course_model->get_single_path($val->course_id,'*');
                    if(empty($course_list)){
                        $documents[$i]['course_str'] = $documents[$i]['version_str'] =
                        $documents[$i]['grade_str']  = '--'; 
                    }else{
                        list($version_str,$grade_str,$course_prase_list) = self::_remove_first_node($course_list,'list');
                        $documents[$i]['course_str']  = self::_build_category_string($course_prase_list,'list');
                        $documents[$i]['version_str']  =  $version_str;
                        $documents[$i]['grade_str']  =  $grade_str;
                    }
                }
                else{
                    $documents[$i]['course_str'] = $documents[$i]['version_str'] =
                    $documents[$i]['grade_str']  = '--';  

                }
                $i++;
            }
            return $documents;
        }
    }

    protected function get_pagination($page_num,$total,$func)
    {
        $this->load->library('pagination'); 
        $config['total_rows']       = $total; //为页总数
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        $config['per_page']       = Constant::LESSON_PER_PAGE;
        //获取分页
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_ajax_links();
        return $pages;
    }

    public function new_group()
    {
        if($this->input->is_ajax_request())
        {
            $subject_id = $this->input->post('sid');
            $group_name = $this->input->post('group_name');
            if(empty($group_name)){
                $error['error_code'] = false;
                $error['error'] = '分组名称不能为空';
            }else{
                $insert_data = array(
                'name'=>trim($group_name),
                'user_id'=>$this->_uid,
                'subject_id'=>$subject_id,
                'type'=>Constant::DOCUMENT_GROUP
                );
                $status = $this->group->insert_group($insert_data);
                if($status){
                    $error['error_code'] = true;
                    $error['error'] = $status;
                    $error['new_name'] = trim($group_name);
                }else{
                    $error['error_code'] = false;
                    $error['error'] = '添加失败';
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
            $op_type = $this->input->post('op_type');
            $error = array();
            switch ($op_type) {
                case 'delete':
                    $group_id = $this->input->post('gid');
                    $subject_id = $this->input->post('sid');
                    $update_data = array('status'=>-1);
                    $status = $this->group->update($group_id,$this->_uid,$update_data);
                    /*删除reids统计*/
                    $this->group->group_data_statistics($subject_id,$this->_uid,$group_id,'document','delete');
                    /*更新为未分组*/
                    if($status){
                        $no_group_count = $this->teacher_document->remove_no_group($group_id,$this->_uid,$subject_id);
                        $error['error_code'] = true;
                        $error['error'] = '删除成功';
                        $error['count'] = $no_group_count;
                    }else{
                        $error['error_code'] = false;
                        $error['error'] = '删除失败';
                    }
                    break;
                case 'update':
                    $group_id = $this->input->post('gid');
                    $new_group_name = $this->input->post('new_name');
                    $update_data = array('name'=>$new_group_name);
                    $status = $this->group->update($group_id,$this->_uid,$update_data);
                    if($status){
                        $error['error_code'] = true;
                        $error['error'] = '更新成功';
                    }else{
                        $error['error_code'] = false;
                        $error['error'] = '更新失败';
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

    function file_upload()
    {
        ini_set("max_execution_time", "3600");
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(isset($_FILES['documents']['error'])&&$_FILES['documents']['error'] == 0)
            {
                $this->load->helper("upload");
                $status = user_doc_file_upload('documents');
                if(strpos($status,'teacher_doc')!==false){
                    $file_info = pathinfo($_FILES['documents']['name']);
                    $insert['user_id'] = $this->_uid;
                    $insert['file_name'] = str_replace(strrchr($_FILES['documents']['name'], '.'),'',$_FILES['documents']['name']);
                    $insert['file_path'] = $status;
                    $insert['file_ext'] = $file_info['extension'];
                    $insert['file_size'] = $_FILES['documents']['size'];
                    $insert['upload_ip'] = ip2long(get_remote_ip());
                    $result = $this->teacher_document->add_file($insert);
                    if($result) echo '1';
                    else echo '上传失败';
                }
            }
        }
        else{
            $this->smarty->display($this->_smarty_dir.'user_document_upload.html');
        }

    }

    function do_edit()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $file_id        = $this->input->post('doc_id');
            $file_name      = $this->input->post('file_name');
            $group_id       = $this->input->post('group');
            $doc_type       = $this->input->post('doc_type');
            $subject_type   = $this->input->post('subject_type');
            $category_id    = $this->input->post('category_id');
            $old_group      = $this->input->post('old_group');
            $old_subject    = $this->input->post('old_subject');
                $update_data = array(
                    'file_name' =>$file_name,
                    'doc_type'  =>!empty($doc_type)?$doc_type:0,
                    'subject_id'=>$subject_type,
                    'group_id'  =>!empty($group_id)?$group_id:0
                    );
                //编辑更新分组统计
                if($group_id !== $old_group){
                    $this->group->group_data_statistics($subject_type,$this->_uid,$group_id,'document','delete');
                    $this->group->group_data_statistics($old_subject,$this->_uid,$old_group,'document','delete');
                }
                $category_id_list = splitId($category_id, '|', 0);
                $res = $this->teacher_document->update_file_info($file_id,$this->_uid,$update_data);
                if($res){
                    /*添加教材同步信息*/
                    $this->user_category_model->make_category_rela($file_id,$category_id_list,'edit');
                    $error=array('error_code'=>true,'redirect'=>'/teacher/user/mydocument','isequal'=>true,'subject_id'=>$subject_type,'error'=>'');
                }else{
                    $error=array('error_code'=>false,'error'=>'服务器繁忙');
                }
          
            echo json_encode($error);die;

        }
    }
    function file_edit($doc_id=NULL)
    {
        if(!is_null($doc_id))
        {
            $this->load->model('lesson/document_type_model');
            $file_detail = $this->teacher_document->get_single_file($doc_id,$this->_uid);
            if(empty($file_detail)){
                tizi_404('teacher/user/mydocument');
            }
            $subject_msg = $this->question_subject_model->get_grade_by_subject($file_detail->subject_id);
            if($file_detail->course_id){
                $course_list    = $this->question_course_model->get_single_path($file_detail->course_id,'*');
                if(empty($course_list)){
                    $data['subject_name']   = $subject_msg->name;
                    $data['category_id']    = $data['category_str'] = '';
                }else{
                    list($first, $course_prase_list) = self::_remove_first_node($course_list);
                    $subject_name   = mb_substr($first->name, 0, 4);
                    $first->name    = str_replace(mb_substr($first->name, 0, 4),'',$first->name); 
                    list($esid, $edu_str)   = self::_build_category_string($course_prase_list);
                    $data['subject_name']   = $subject_name;
                    $data['category_id']    = $esid;
                    $data['category_str']   = $edu_str;
                }
            }
            else{
                $data['subject_name']   = $subject_msg->name;
                $data['category_id']    = $data['category_str'] = '';
            }
            $data['file_detail']        = $file_detail;
            $data['type_list']          = array((object)array('id'=>2,'name'=>'课件'),(object)array('id'=>3,'name'=>'试题'),(object)array('id'=>8,'name'=>'其他'));
            $data['version_list']       = parent::edit_category_band($file_detail->subject_id);
            $data['grade_id']           = $subject_msg->grade;
            $sel_subject                = $this->register_model->my_subject($this->_uid,'doc');
            $data['groups']             = $this->group->get_list($sel_subject,$this->_uid,Constant::DOCUMENT_GROUP);
            $this->smarty->assign('data',$data);
            $this->smarty->display($this->_smarty_dir.'user_document_edit.html');
           
        }
        else
        {
            tizi_404('teacher/user/mydocument');
            exit;
        }
    }

    protected function _remove_first_node($arr,$type='single'){
        $new_arr = array();
        if($type == 'single'){
            $new_arr = array();
            $first = null;
            if (is_array($arr) and !empty($arr))  {
                $first = $arr[0];
                $new_arr = $arr;
            } else {
                return $arr;
            }
            return array($first, $new_arr);
        }else{
            $version_str = str_replace(mb_substr($arr[0]->name, 0, 4),'',$arr[0]->name); 
            $grade_str   = $arr[1]->name;
            unset($arr[0],$arr[1]);
            $new_arr = $arr;
            return array($version_str,$grade_str,$new_arr);
        }
        
    }

    protected function _build_category_string($arr,$type='single', $namesep = '--', $idsep='-', $idsep1='|'){

        if($type == 'single')
        {
            $id_str = $sid = $name = $id='';
            foreach($arr as $i => $j){
                if(is_object($j)){
                    $name .= $j->name . $namesep;
                    $id .= $j->id . $idsep;
                }else{
                    return false;
                }
            } 
            $sid .= $id . $idsep1;
            $id_str .='<span id="'. $id . '">' . $name . '<a href="javascript:void(0);" onclick="Teacher.UserCenter.ajax_select.delSelect(\'' 
                . $id . '\',this);" class="del_message">删除</a></span>';
            return array($sid, $id_str);
        }else{
            $name = '';
            foreach($arr as $i => $j){
                if(is_object($j)){
                    $name .= $j->name . $namesep;
                }else{
                    return false;
                }
            } 
            return trim($name,'--');
        }
        
    }

    function file_perfect()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $file_id_arr        = $this->input->post('doc_id');
            $file_name_arr      = $this->input->post('file_name');
            $group_id_arr       = $this->input->post('group');
            $doc_type_arr       = $this->input->post('doc_type');
            $subject_type_arr   = $this->input->post('subject_type');
            $category_id_arr    = $this->input->post('category_id');
            if(empty($file_id_arr)){
                echo json_encode(array('error_code'=>false,'error'=>'您没有要完成上传的文档！'));die;
            }
            $isEqual = $isJudge = true;
            $firstEle = $subject_type_arr[0];
            foreach ($file_id_arr as $key => $value) {
                if($isJudge){
                    $isEqual = $isJudge = $firstEle !== $subject_type_arr[$key] ? false : true;
                }
                $update_data = array(
                    'file_name' =>$file_name_arr[$key],
                    'doc_type'  =>!empty($doc_type_arr[$key])?$doc_type_arr[$key]:0,
                    'subject_id'=>$subject_type_arr[$key],
                    'group_id'  =>!empty($group_id_arr[$key])?$group_id_arr[$key]:0,
                    'status'    =>1,
                    'upload_time' => time(),
                    'queue_status'=>2
                    );
                $category_id_list = splitId($category_id_arr[$key], '|', 0);
                $res = $this->teacher_document->update_file_info($value,$this->_uid,$update_data);
                if($res){
                    //添加make_swf_task_queue
                    $this->teacher_document->task_lpush_queue($value);
                    /*删除reids统计*/
                    $this->group->group_data_statistics($update_data['subject_id'],$this->_uid,$update_data['group_id'],'document','delete');
                    if(!empty($category_id_list)){
                        /*添加教材同步信息*/
                        $this->user_category_model->make_category_rela($value,$category_id_list);
                    }
                }else{
                    continue;
                }
            }
            echo json_encode(array('error_code'=>true,'redirect'=>'/teacher/user/mydocument','isequal'=>$isEqual,'subject_id'=>$firstEle,'error'=>''));die;
        }
        else{
            $this->load->model('lesson/document_type_model');
            $files_list = $this->teacher_document->get_uploaded_files($this->_uid);
            $data['type_list']=array((object)array('id'=>2,'name'=>'课件'),(object)array('id'=>3,'name'=>'试题'),(object)array('id'=>8,'name'=>'其他'));
            //$this->document_type_model->get_type_list();
            $data['files_list'] = $files_list;
            $data['total'] = count($files_list);
            if($data['total'] == 0)redirect('teacher/user/mydocument');
            $sel_subject = $this->register_model->my_subject($this->_uid,'doc');
            $data['groups'] = $this->group->get_list($sel_subject,$this->_uid,Constant::DOCUMENT_GROUP);
            $this->smarty->assign('data',$data);
            $this->smarty->display($this->_smarty_dir.'user_document_perfect.html');
        }
    }

    function del_doc()
    {
        if($this->input->is_ajax_request()){
            $is_prefected = false;
            $get_data= $this->input->get('doc_id');
            if(strpos($get_data,'-')!==false){
                $get_data= explode('-', $get_data);
                $doc_id = $get_data[0];
                $group_id = $get_data[1];
                $subject_id = $this->input->get('sid');
                $is_prefected = true;
            }else{
                $doc_id = $get_data;
            }
            $status = $this->teacher_document->update_file_info($doc_id,$this->_uid,array('status'=>2));
            if($status){
                /*删除reids统计*/
                if($is_prefected){
                    $this->group->group_data_statistics($subject_id,$this->_uid,$group_id,'document','delete');
                }
                $error['error_code'] = true;
            }
            else{
                $error['error_code'] = false;
                $error['error'] = '服务器繁忙！';
            }
            echo json_token($error);exit();
        }
    }

    public function download_verify()
    {
        if(!$this->input->is_ajax_request()) die;
        $error_arr = array();
        $file_id = $this->input->post('file_id');
        if(!isset($file_id) || empty($file_id))
        {
            $error_arr['errorcode'] = false;
            $error_arr['error'] = '文件不存在';
        }
        else
        {
            $file_data = $this->teacher_document->get_single_file($file_id,$this->_uid,false);
            if($file_data)
            {
                $error_arr['errorcode'] = true;
                $error_arr['error'] = 'Successed';
                $error_arr['fname'] = $file_data->file_name;
                $error_arr['file_name'] = urlencode($file_data->file_name.'.'.$file_data->file_ext);
                $error_arr['file_path'] = urlencode($file_data->file_path);
            }
            else
            {
                $error_arr['errorcode'] = false;
                $error_arr['error'] = '下载错误';
            }
        }
        echo json_encode($error_arr);
        exit;
    }

    public function download()
    {
        $this->load->helper('download');
        $this->load->config('upload');
        $file_name = $this->input->get('file_name');
        $file_path = $this->input->get('url');
        $base_url  = $this->config->item('domain_document');
        $file_path = $base_url.urldecode($file_path);
        if(strpos($file_path, 'http://')===false){
            $file_path='http://'.$file_path;
        }
        if(!isset($file_name) || empty($file_name)) die();
        $file_get_contents=tizi_get_contents($file_path,"teacher/user/mydocument");
        if(stripos($this->input->server('HTTP_USER_AGENT'), 'windows'))
        {
            force_download(iconv('utf-8', 'gbk//IGNORE', $file_name), $file_get_contents); 
        }
        else
        {
            force_download($file_name, $file_get_contents);
        }

    }


}

/* End of file user_document.php */
