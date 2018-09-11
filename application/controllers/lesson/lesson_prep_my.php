<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once "lesson_prepare.php";

class Lesson_Prep_My extends lesson_prepare {

    private $_smarty_dir = 'teacher/lesson/';
    private $_islogin = true;
    public function __construct(){
        parent::__construct();
        $this->_user_id=$this->session->userdata('user_id');
        $this->_user_type=$this->session->userdata('user_type');
        $this->load->model('lesson/document_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('question/question_category_model');
        $this->load->model("lesson/document_type_model");
        $this->load->model("login/register_model");
        $this->load->library('search');
        $this->load->config('search');
        $this->load->model('redis/redis_model');
        if(!$this->_user_id) $this->_islogin = false;
        $this->smarty->assign('is_login',$this->_islogin);
    }

    public function mine($subject_id=0,$category_select=0){
        // 调用parent的 获取 年级 + 版本 + 章节 的 选中信息；
        $data = array();
        /*公共数据 no database,no cache*/
        parent::_init_param($subject_id,$category_select,$data);
        parent::_init_data($data);/*涉及较多数据库查询*/
        $sub_cat_id = $this->input->get('id',true,true,0);//sub_cat_id
        $this->beike_list($data['category_select'],$sub_cat_id);
        $classes = $this->get_my_classes();

        //移动文件 的 box的内容
        $move_box = $this->move_box($data['subject_id'],$data['category_select']);

        $this->smarty->assign('data1',$move_box);
        $this->smarty->assign('sj_url',site_url().'teacher/lesson/prepare/mine');
        $this->smarty->assign('data',$data);
        
        $this->smarty->assign('classes',$classes);
        $this->smarty->assign('subject_id',$data['subject_id']);
        $this->smarty->display($this->_smarty_dir.'lesson_prepare.html');
    }

    //获取移动文件的窗口内容
    function move_box($subject_id,$category_select){
        $move_box['subject_id'] = $subject_id;
        $move_box['subject_list'] = $this->question_subject_model->get_subjects_by_sid($subject_id);
        $subject_msg = $this->question_subject_model->get_grade_by_subject($subject_id);
        /*当前年级*/
        $move_box['grade_id'] = $subject_msg->grade;
        /*文档类型列表*/
        $move_box['type_list']=Constant::resource_type();
        /*版本列表*/
        $move_box['version_list'] = array();
        $category_root_arr=$this->question_category_model->get_root_id($subject_id);
        foreach ($category_root_arr as $key => $value) {
            if($value->type==0){
                $move_box['version_list'][] = $value;
            }
        }
        /*当前版本和当前年级*/
        $move_box['version_id'] = $move_box['stage_id'] = 0;
        $single_path=$this->question_category_model->get_single_path($category_select,'name,parent.id,parent.depth');
        if($single_path){
            foreach ($single_path as $val) {
                if($val->depth==1){$move_box['version_id']=$val->id;continue;}
                if($val->depth==2){$move_box['stage_id']=$val->id;continue;}
                if($move_box['version_id']>0 and $move_box['stage_id']>0) break;
            }
        }
        /*年级列表*/
        $move_box['stage_list'] = $this->question_category_model->get_subtree_node($move_box['version_id']);
        /*章节列表*/
        $move_box['node_list'] = array();
        parent::_get_category_tree($move_box['stage_id'],$move_box['node_list']);
        return $move_box;
    }

    public function beike_list($cat_id, $sub_cat_id=0){
        $sub_cat_id = $sub_cat_id==$cat_id?0:$sub_cat_id;
        $flip = $this->input->get('flip',true);//默认是false,没有就是false.
        $subject_id = $this->input->get('subject_id',true);//默认是false,没有就是false.
        $this->load->model('resource/res_file_model');
        $user_id = $this->_user_id;
        $page = $this->input->get('page',true,true,1);
        list($cat_id, $sub_cat_id, $page) = array(intval($cat_id),intval($sub_cat_id),intval($page));

        $param = array('user_id'=>$user_id,'dir_cat_id'=>$cat_id,'is_del'=>0);
        if($sub_cat_id){
            $param['sub_cat_id'] = $sub_cat_id;
        }
        $total = $this->res_file_model->file_sum($param);
        // $config['enable_query_strings'] = false;
        // $config['page_query_string'] = false;
        // $config['uri_segment'] = 6; //指定page参数是uri的第n个
        $config['per_page'] = Constant::RES_LIST_PAGESIZE;
        $pages = parent::get_pagination($page,$total,'prep_min_page',$config);
        $res = $this->res_file_model->get_list_by_cat($user_id,$cat_id,$sub_cat_id,$page,Constant::RES_LIST_PAGESIZE);

        if(!$res and $flip and $page>1){//是翻页，且刚才翻的那页没有数据,于是再往前翻一番
            $page -= 1;
            $res = $this->res_file_model->get_list_by_cat($user_id,$cat_id,$sub_cat_id,$page,Constant::RES_LIST_PAGESIZE);
        }
        //判断音视频是否转换好了  2014-08-01
        if($res){
            $this->res_file_model->is_pfop_done($res);              
        }

        /*lcc 新增 begin*/
        $json['tab_url']=$subject_id.'/'.$cat_id;
        if($cat_id!=$sub_cat_id)$json['tab_url'].='?id='.$sub_cat_id;
        /*lcc 新增 end*/
        $this->smarty->assign('list',$res);
        $this->smarty->assign('cur_page',$page);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('cat_id',$cat_id);
        $this->smarty->assign('tab_name','mine');
        $param = $this->input->get('id',true);//
        $this->smarty->assign('param',"?id=$param");
        $this->smarty->assign('subject_id',$subject_id);
        $this->smarty->assign('sub_cat_id',$sub_cat_id);
        if($flip){//是不是分页的请求
            $json['errorcode'] = true;
            $json['html'] = $this->smarty->fetch($this->_smarty_dir.'less_prep_mine_tpl.html');
            echo json_token($json);die;
        }
    }

    //删除我的备课文件
    function del_beike(){
        $ids = $this->input->post('ids',true);
        $this->load->helper('array');
        $file_arr = explode_to_distinct_and_notempty($ids);
        $json= array('errorcode'=>true,'error'=>'操作成功');
        if(!$file_arr){
            echo json_token($json);die;
        }
        $this->load->model('lesson/lesson_mine_model');
        $res = $this->lesson_mine_model->del_my_file($this->tizi_uid,$file_arr);
        if($res){
            echo json_token($json);die;   
        }
        $json = array('errorcode'=>false,'error'=>'删除失败');
        echo json_token($json);die;
    }

    function get_my_classes(){
        if($this->_user_type!=Constant::USER_TYPE_TEACHER){
            return null;
        }
        //获取所有班级
        $this->load->model('class/classes_teacher','ct');
        $this->load->model('class/classes_schools');
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $all_class_info = $this->ct->get_classes_by_tch($this->_user_id);
        foreach($all_class_info as $k=>$c){
            $name = $c['school_define_id']?$this->classes_schools->define_school_info($c['school_define_id'],true):$this->classes_schools->getsh_info($c['id']);//学校名字
            $c_name = $this->hcm->get_class_whole_name($c['id']);
            if($c_name[0]['class_year']){
                if(isset($name['schoolname']) and isset($name['classname'])){
                    $c['class_name'] =  $name['schoolname'].$c_name[0]['class_year'].'级'.$name['classname'];
                }elseif(isset($name['schoolname'])){
                    $c['class_name'] =  $name['schoolname'].$c_name[0]['class_name'];
                }else{
                    $c['class_name'] =  '';
                }
            }else{
                $c['class_name'] = $c['class_grade'].$c['classname'];
            }
            unset($c['classname']);
            $c['alpha_id'] = alpha_id_num($c['id']);
            unset($c['id']);
            $all_class_info[$k] = $c;
        }
        return $all_class_info;
    }

    //备课文件的详细页面  $id is file_id
    function file_detail($file_id){ 
        $this->load->model('cloud/cloud_model');
        $file_id = intval($file_id);
        $is_file = true;
        $unable_view = false;

        $file = $this->cloud_model->file_info($file_id,'*',0,true);
        if(empty($file)){   tizi_404('teacher/lesson/prepare/mine');exit; }//空数据就重定向
        $course_list = $this->question_category_model->get_single_path(intval($file['sub_cat_id']),'*');
        $ico_class = array(1=>'ico_doc',2=>'ico_pic',3=>'ico_video',4=>'ico_audio',5=>'ico_other');

        $share_status = $file['is_share_to_tizi'];//0:私有1:公开通过审核2:待审核3:公开未通过审核
        $obj = array();
        if($share_status==1){//不知道怎么处理 评星和评价
            $star_level = array(1,1,1,1,1);
            $assess_avg_score = 0.0;
            parent::prase_star_level($assess_avg_score,$star_level);
            $data1['star_level'] = $star_level;
            $data1['assess_count'] = 3;
            $obj = array('hits'=>444,'downloads'=>22,'file_size'=>trans_filesize($file['file_size']));
            $this->load->model('login/parent_model');
            $ui = $this->parent_model->get_info(intval($file['user_id']));
            $file['uploader_name'] = isset($ui[0]['name'])?$ui[0]['name']:'佚名';
            $this->load->helper("img_helper");
            $file['uploader_avatar'] = path2avatar($file['user_id']);
        }else{
            if($file['user_id']!=$this->tizi_uid){//非自己的文件不能看
                redirect(site_url('teacher/lesson/prepare/mine'));exit;
            }
            $data1['star_level'] = $data1['assess_count'] = null;
            $this->smarty->assign('data',$data1);
        }

        $file['extension'] = $file['file_ext'];
        $file['ico_class'] = $ico_class[$file['file_type']];
        $obj = array_merge($obj,$file);
        $obj['file_size'] = trans_filesize($file['file_size']);
        $data1['preview'] = (object)$obj;
        $data1['related_docs'] = null;

        //本月下载次数
        $this->load->library('credit');
        $privilege = $this->credit->userlevel_privilege($this->tizi_uid);
        $lesson_month_down_limit = $privilege['privilege']['lesson_permonth']['value'];
        $this->load->model('redis/redis_model');
        if($this->redis_model->connect('download'))
        {
            $lesson_doc_key=date('Y-m-d').'_lesson_doc_key_'.$this->tizi_uid;
            $month_down_key = date('Y-m').'_lesson_doc_key_'.$this->tizi_uid;
            $surplus_count = $lesson_month_down_limit-$this->cache->get($month_down_key);
            $data1['surplus_download_count'] = $surplus_count>=0?$surplus_count:0;
            $data1['download_doc_count'] = $this->cache->get($lesson_doc_key);
            $data1['download_doc_limit'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
        }
        else
        {
            $data1['surplus_download_count'] = $lesson_month_down_limit;
            $data1['download_doc_count'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
            $data1['download_doc_limit'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
        }

        if($file['file_type']==Constant::CLOUD_FILETYPE_DOC){
             
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_PIC){
            $this->load->helper('qiniu');
            $file['file_path'] = qiniu_img($file['file_path'],0,699);//最长边为699
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_VIDEO or $file['file_type']==Constant::CLOUD_FILETYPE_AUDIO){
            if($file['file_ext'] == 'swf'){
                $this->load->helper('qiniu'); 
                $file['file_path'] = qiniu_download($file['file_path'],'swf',10800,false);
                $this->smarty->assign('url',$file['file_path']);
            }else{ 
                $this->mediatype($file_id);
            }
        }else{//不支持预览
            $unable_view = true;
        }
        $this->load->helper('number');
        $file['file_id'] = $file_id;
        $this->smarty->assign('file',$file);
        $this->smarty->assign('share_status',$share_status);
        $this->smarty->assign('data',$data1);
        $this->smarty->assign('unable_view',$unable_view);//该文件不支持预览
        $this->smarty->assign('file_detail',true);
        $this->smarty->assign('breadcrumb',parent::_remove_first_node($course_list));
        $this->smarty->assign('sj_url',site_url().'teacher/lesson/prepare/mine');
        $this->smarty->display($this->_smarty_dir.'lesson_preview_mine.html');
    }

    //移动 备课中 我的文件
    function move(){
        $resource_ids = intval($this->input->post('move_ids_str',true));
        $sub_cat_id = $to_dir_id = intval($this->input->post('to_dir_id',true));
        $json = array('errorcode'=>false,'error'=>'操作失败');
        $dir_cat_id = intval($this->input->post('stage',true));//同步/知识点文件夹的cat_id
        if($dir_cat_id){
            $resource_type = intval($this->input->post('type',true));//文件的资源类型，1～8
            if(!in_array($resource_type,array_flip(Constant::resource_type()))){
                echo json_token(array('errorcode'=>false,'error'=>'资源类型不合法'));die;
            }
        }
        $this->load->model('cloud/cloud_model');
        $this->load->model('resource/res_file_model');
        $to_dir_id = $dir_cat_id;
        if(!$resource_ids){ echo json_token($json);die; }
        $is_file = true; 
        $this->load->helper('array');
        $resource_ids = explode_to_distinct_and_notempty($resource_ids);
        $file_to_cat_dir = true;
        foreach($resource_ids as $resource_id){
            $res = $this->cloud_model->move_dir_or_file($is_file,$resource_id,$to_dir_id,$this->_user_id,$dir_cat_id,$sub_cat_id,$resource_type);
            if(!$res){
                echo json_token($json);die;
            }
        }
        $json = array('errorcode'=>true,'error'=>'操作成功');
        echo json_token($json);die;
    }

    function t(){
        $sub_cat_id = 52380;
        $file_id = 2731;
        $uid = $this->tizi_uid;
        $ext = 'txt';
        $name= 'hostfile';
        $this->load->model('lesson/lesson_mine_model');
        $e = $this->lesson_mine_model->check_duplicate_name($uid,$sub_cat_id,$name,$ext,$file_id);
        var_dump($e);
    }

     

}
    
/* End of file lesson_prepare.php */
/* Location: ./application/controllers/lesson/lesson_prepare.php */
