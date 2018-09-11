<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."resource_base.php";

class Res_File extends Resource_Base {
    protected $_smarty_dir = "teacher/resource/";
    protected $_cloud_smarty_dir = "cloud/";
    protected $user_id ;
    public function __construct(){
        parent::__construct();
        $this->user_id = $this->session->userdata("user_id");
        if (!$this->user_id){                                                       
            $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
            redirect('login');
        }      
        $user_type = $this->session->userdata("user_type");
        if ($user_type != Constant::USER_TYPE_TEACHER){
            redirect('login');
        }
        $this->load->model('resource/res_dir_model');
        $this->load->model('resource/res_file_model');
        $this->load->model('cloud/cloud_model');
    }

    // 网盘的文件的详细页面  $id is file_id
    function file_detail($file_id){ 
        $file_id = intval($file_id);
        $is_file = true;
        $belonging = $this->cloud_model->check_belonging($this->user_id,$file_id,$is_file);
        if(!$belonging || !$file_id){redirect('/teacher/cloud');}
        $file = $this->cloud_model->file_info($file_id,'*',0,true);
        $tpl_file = 'cloud/share_preview';
        if($file['file_type']==Constant::CLOUD_FILETYPE_DOC){
            $tpl_file = 'cloud/share_document_preview';
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_PIC){
            $this->load->helper('qiniu');
            $file['file_path'] = qiniu_img($file['file_path']);
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_VIDEO or $file['file_type']==Constant::CLOUD_FILETYPE_AUDIO){
            if($file['file_ext'] == 'swf'){
                $this->load->helper('qiniu'); 
                $file['file_path'] = qiniu_download($file['file_path'],'swf',10800,false);
                $this->smarty->assign('url',$file['file_path']);
            }else{ 
                $this->mediatype($file_id);
            }
        }
        $this->load->helper('number');
        $file['file_id'] = $file_id;
        $this->smarty->assign('file',$file);
        $this->smarty->assign('file_detail',true);
        $this->smarty->display($tpl_file.'.html');
    }

    function share_detail($share_id){ 
        $share_id = intval($share_id);
        if(!$share_id){redirect("/teacher/cloud");}
        $is_file = true;
        $share_info = $this->cloud_model->get_file_by_share_id($share_id);
        if(!isset($share_info[0])){//没有找到该分享
            redirect("/teacher/cloud");;
        }
        $class_id = $share_info[0]['class_id'];
        $_belong = false;
        if($share_info[0]['user_id'] == $this->tizi_uid){
            $_belong = true;
        }else{
            $this->load->model('class/class_model');
            $t = $this->class_model->g_class_teacher($class_id,'teacher_id');
            if($t){
                foreach($t as $k=>$val){
                    if($val['teacher_id'] == $this->tizi_uid){$_belong = true;}
                }
            }
        }
        //有必要检测是否是班级的老师
        $id = $share_info[0]['file_id'];
        if(!$_belong || !$id){redirect('/teacher/cloud');}
        $file = $share_info[0];
        $tpl_file = 'share_preview';
        if($file['file_type']==Constant::CLOUD_FILETYPE_DOC){
            $tpl_file = 'share_document_preview';
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_PIC){
            $this->load->helper('qiniu'); 
            $file['file_path'] = qiniu_img($file['file_path']);
        }elseif($file['file_type']==Constant::CLOUD_FILETYPE_VIDEO or $file['file_type']==Constant::CLOUD_FILETYPE_AUDIO){
            if($file['file_ext'] == 'swf'){
                $this->load->helper('qiniu'); 
                $file['file_path'] = qiniu_download($file['file_path'],'swf',10800,false);
                $this->smarty->assign('url',$file['file_path']);
            }else{ 
                $this->mediatype($id);
            }
        }
        $this->load->helper('number');
        $this->cloud_model->add_hit_count($file['id']);//谁来访问都加1
        $this->smarty->assign('file',$file);
        $this->smarty->assign('class_id',alpha_id_num($class_id));
        $this->smarty->assign('file_detail',false);
        $this->smarty->display($this->_cloud_smarty_dir.$tpl_file.'.html');
    }

    /*预览 视频/音频 页面*/
    function mediatype($file_id){
        $this->load->model('cloud/cloud_model');
        $info = $this->cloud_model->file_info($file_id,'*',0);
        if(!$info){
            redirect('/teacher/cloud');
        }
        $this->load->model('resource/res_file_model');
        $url = $this->res_file_model->get_media_url($info);
        $this->smarty->assign('url',$url);
    }

    /*检查文件的*/
    function check_pfop($file_id){
        if(!$file_id){
            echo json_token(array('errorcode'=>false,'error'=>'文件获取失败'));die;
        }
        $json = $this->res_file_model->model_check_pfop($file_id);
        echo json_token($json);die;
    }


     
 
}

