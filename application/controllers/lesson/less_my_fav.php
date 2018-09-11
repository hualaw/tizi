<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once "lesson_prep_my.php";

class Less_My_Fav extends lesson_prep_my {

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
        $this->load->model('lesson/document_favorite_model');
        $this->load->config('search');
        $this->load->model('redis/redis_model');
        if(!$this->_user_id) $this->_islogin = false;
        $this->smarty->assign('is_login',$this->_islogin);
    }

    public function mine($subject_id=0,$category_select=0){

        $data = array();
        /*公共数据 no database,no cache*/
        parent::_init_param($subject_id,$category_select,$data);
        parent::_init_data($data);/*涉及较多数据库查询*/
        $sub_cat_id = $this->input->get('id',true,true,0);//sub_cat_id
        $this->fav_list($data['category_select'],$sub_cat_id);
        $classes = parent::get_my_classes();

        //获取移动文件的窗口内容
        $move_box = parent::move_box($data['subject_id'],$data['category_select']);

        $this->smarty->assign('data1',$move_box);
        $this->smarty->assign('sj_url',site_url().'teacher/lesson/prepare/fav');
        $this->smarty->assign('data',$data);
        $this->smarty->assign('classes',$classes);
        $this->smarty->assign('subject_id',$data['subject_id']);
        $this->smarty->display($this->_smarty_dir.'lesson_prepare.html');
    }

    public function fav_list($cat_id, $sub_cat_id=0){
        $sub_cat_id = $sub_cat_id==$cat_id?0:$sub_cat_id;
        $flip = $this->input->get('flip',true);//默认是false,没有就是false.
        $subject_id = $this->input->get('subject_id',true);//默认是false,没有就是false.
        $this->load->model('resource/res_file_model');
        $user_id = $this->_user_id;
        $page = $this->input->get('page',true,true,1);
        list($cat_id, $sub_cat_id, $page) = array(intval($cat_id),intval($sub_cat_id),intval($page));
        $total = $this->document_favorite_model->get_data($user_id,$cat_id,$sub_cat_id,$page,true);
        $config['per_page'] = Constant::RES_LIST_PAGESIZE;
        $pages = parent::get_pagination($page,$total,'prep_fav_page',$config);
        $res = $this->document_favorite_model->get_data($user_id,$cat_id,$sub_cat_id,$page);
        if(!$res and $flip and $page>1){//是翻页，且刚才翻的那页没有数据,于是再往前翻一番
            $page -= 1;
            $res = $this->document_favorite_model->get_data($user_id,$cat_id,$sub_cat_id,$page);
        }
        $this->smarty->assign('list',$res);
        $this->smarty->assign('cur_page',$page);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('tab_name','fav');
        $this->smarty->assign('cat_id',$cat_id);
        $this->smarty->assign('subject_id',$subject_id);
        $this->smarty->assign('sub_cat_id',$sub_cat_id);
        //选中sub_cat_id的情况下，切换 3个大tab
        $param = $this->input->get('id',true);//for lesson_prepare
        $this->smarty->assign('param',"?id=$param");
        //选中sub_cat_id的情况下，切换 3个大tab
        $json['tab_url']=$subject_id.'/'.$cat_id;
        if($cat_id!=$sub_cat_id)$json['tab_url'].='?id='.$sub_cat_id;

        if($flip){//是不是分页的请求
            $json['errorcode'] = true;
            $json['html'] = $this->smarty->fetch($this->_smarty_dir.'less_prep_mine_tpl.html');
            echo json_token($json);die;
        }
    }

    //删除我收藏的备课文件
    function del_fav(){
        $ids = $this->input->post('ids',true);
        $this->load->helper('array');
        $file_arr = explode_to_distinct_and_notempty($ids);
        $json= array('errorcode'=>true,'error'=>'操作成功');
        if(!$file_arr){
            echo json_token($json);die;
        }
        foreach($file_arr as $key=>$val){
            $res = $this->document_favorite_model->delete($this->tizi_uid,$val);
        }
        if($res){
            echo json_token($json);die;   
        }
        $json = array('errorcode'=>false,'error'=>'删除失败');
        echo json_token($json);die;
    }

    // function get_my_classes(){
    //     parent::get_my_classes();
    // }
 

    //移动 收藏的文件
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
        $this->load->helper('array');
        $resource_ids = explode_to_distinct_and_notempty($resource_ids);
        $update_data = array('res_type'=>$resource_type,'dir_cat_id'=>$dir_cat_id,'sub_cat_id'=>$sub_cat_id);
        foreach($resource_ids as $resource_id){
            $res = $this->document_favorite_model->update($this->_user_id,$resource_id,$update_data);
            if(!$res){
                echo json_token($json);die;
            }
        }
        $json = array('errorcode'=>true,'error'=>'操作成功');
        echo json_token($json);die;
    }

     
     

}
 