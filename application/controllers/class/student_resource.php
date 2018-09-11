<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
//学生端 分享的文件

class Student_Resource extends MY_Controller{
    protected $_smarty_dir="student/class/share/";
    protected $_cloud_smarty_dir = "cloud/";
    protected $user_id ;
    public function __construct(){
        parent::__construct();
        $this->user_id = $this->session->userdata("user_id");
        $user_type = $this->session->userdata("user_type");
        $this->load->model('resource/res_dir_model');
        $this->load->model('resource/res_file_model');
        $this->load->model('cloud/cloud_model');
    }

    //学生下载 分享的文件
    public function download_share(){
        $this->load->model('cloud/cloud_model');
        $this->load->helper('download');
        $file_name = $this->input->get('file_name');
        if(!isset($file_name) || empty($file_name)) die();
        $file_id = intval($this->input->get('file_id'));
        $share_id = intval($this->input->get('share_id'));
        if(!$file_id)die;
        $file_path = $this->input->get('url');
        $file_path = $this->cloud_model->get_download_file_path($file_id,$file_path);
        $this->cloud_model->add_download_share($share_id,$this->tizi_uid);//下载+1
        $file_get_contents=tizi_get_contents($file_path,"student/homework/home");
        if(stripos($this->input->server('HTTP_USER_AGENT'), 'windows')) {
            force_download(iconv('utf-8', 'gbk//IGNORE', $file_name), $file_get_contents); 
        } else {
            force_download($file_name, $file_get_contents);
        }
    }

    public function flash_get_json(){
        $doc_id = $this->input->post('id');
        if(empty($doc_id)) {
            $referer_arr = explode('/', $this->input->server('HTTP_REFERER'));
            $doc_id = $referer_arr[count($referer_arr)-1];
        }
        $this->load->model('cloud/cloud_model');
        $preview_data = $this->cloud_model->get_single_doc_preview($doc_id,0,true,1);
        if(empty($preview_data)) {
            $files_info = array('status'=>1);
        } else {
            $uri_api    =  'http://tizi-zujuan-thumb.oss.aliyuncs.com/';
            $swf_list   = array();
            $i = 0;
            while ( $i < $preview_data->page_count) {
                $page = $i+1;
                $swf_list[$i] = $uri_api.$preview_data->swf_folder_path."/preview_{$page}.swf";
                $i++;
            }
            if($swf_list) {
                $files_info = array(
                'status'        =>99,
                'page_total'    =>$preview_data->page_count,
                'files_url'     =>$swf_list,
                'file_id'       =>$doc_id,
                'file_ext'      =>$preview_data->file_ext,
                'goto_next_num' =>Constant::PAGE_NUM_LOAD_NEXT
                );
            } else {
                $files_info = array('status'=>1);
            }
        }
        echo json_encode($files_info);exit;
    }

    //分享内容的详细页面  
    function share_detail($share_id){
        $share_id = intval($share_id);
        if(!$share_id){redirect('ban');}
        $is_file = true;
        $this->load->model('cloud/cloud_model');
        $share_info = $this->cloud_model->get_file_by_share_id($share_id);
        if(!isset($share_info[0])){//没有找到该分享
            redirect('ban');
        }
        $file = $share_info[0];//$this->cloud_model->file_info($id,'*',$class_id);
        if(empty($file)){
            redirect('ban');
        }
        $tpl_file = 'stu_share_preview';
        if($file['file_type']==Constant::CLOUD_FILETYPE_DOC){
            $tpl_file = 'stu_share_document_preview';
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_PIC){
            $this->load->helper('qiniu'); 
            $file['file_path'] = qiniu_img($file['file_path']);
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_VIDEO or $file['file_type']==Constant::CLOUD_FILETYPE_AUDIO){
            if($file['file_ext'] == 'swf'){
                $this->load->helper('qiniu'); 
                $file['file_path'] = qiniu_download($file['file_path'],'',10800,false);
                $this->smarty->assign('url',$file['file_path']);
            }else{ 
                $this->mediatype($file['file_id']);
            }
        }
        $this->cloud_model->add_hit_count($file['share_id']); //this is share_id
        $this->load->helper('number');
        $this->smarty->assign('file',$file);
        $this->smarty->assign('is_student',true);
        $this->smarty->display($this->_smarty_dir.$tpl_file.'.html');
    }

    /*预览 视频/音频 页面*/
    /*与res_file中的mediatype一样*/
    function mediatype($file_id){
        $this->load->model('cloud/cloud_model');
        $info = $this->cloud_model->file_info($file_id,'*',0,true);
        if(!$info){
            redirect('/student/home');
        }
        $this->load->model('resource/res_file_model');
        $url = $this->res_file_model->get_media_url($info);
        $this->smarty->assign('url',$url);
    }

    /*检查文件的*/
    function check_pfop($file_id){
        if(!$file_id){
            echo json_token(array('errorcode'=>false,'error'=>'文件获取失败'));
        }
        $json = $this->res_file_model->model_check_pfop($file_id);
        echo json_token($json);die;
    }

 
}