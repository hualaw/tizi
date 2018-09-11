<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."../Controller.php";

class Cloud_Base extends Controller {

    public function __construct(){
        parent::__construct();
        $this->user_id = $this->session->userdata("user_id");
        if ($this->user_id < 1){
            redirect('');
        }
        $this->user_type = $this->session->userdata("user_type");
        $this->load->model("login/register_model");
        $this->load->model('cloud/cloud_model');
    }

    //下载文件前，先验证
    public function download_verify(){
        if(!$this->input->is_ajax_request()) die;
        $error_arr = array();
        $file_id = $this->input->post('file_id',true);
        $class_id = $this->input->post('class_id',true);
        $source = $this->input->post('source',true,true,1);//1是上传的，2是收藏的
        $class_id = alpha_id_num($class_id,true);
        $_belong = false;
        if(!isset($file_id) || empty($file_id)){
            $error_arr['errorcode'] = false;
            $error_arr['error'] = '文件不存在';
        } else{
            // if($source==1){
                $file_data = $this->cloud_model->file_info($file_id,'*',0);
                if($file_data['user_id'] == $this->tizi_uid){
                    $_belong = true;
                }
            // }
            // elseif($source ==2){//收藏来的文档
            //     $this->load->model('lesson/document_model');
            //     $file_data = $this->document_model->get_single_doc_info($file_id);
            // }
            if(!$_belong){
                if($this->user_type == Constant::USER_TYPE_TEACHER){//同一个班级里的老师都能下载
                    $this->load->model('class/class_model');
                    $t = $this->class_model->g_class_teacher($class_id,'teacher_id');
                    if($t){
                        foreach($t as $k=>$val){
                            if($val['teacher_id'] == $this->tizi_uid){$_belong = true;}
                        }
                    }
                }elseif($this->user_type == Constant::USER_TYPE_STUDENT){
                    $this->load->model('class/classes_student');
                    $class_info = $this->classes_student->userid_get($this->user_id);
                    $class_id = $class_info[0]?$class_info[0]['class_id']:0;
                    if($class_id){
                        $this->load->model('class/classes_teacher');
                        $ts = $this->classes_teacher->get_idct($class_id,'teacher_id');
                        if($ts && is_array($ts)){
                            foreach($ts as $k=>$val){
                                if($this->cloud_model->check_belonging($val['teacher_id'],$file_id,true,1)){
                                    $_belong = true;
                                    break;
                                }           
                            }
                        }
                    }
                }
            }
            
            if($file_data && $_belong and $source==1){
                $error_arr['errorcode'] = true;
                $error_arr['error'] = 'Successed';
                $error_arr['fname'] = $file_data['file_name'];
                $error_arr['file_name'] = ($file_data['file_name'].'.'.$file_data['file_ext']);
                $error_arr['file_encode_name'] = urlencode($file_data['file_name'].'.'.$file_data['file_ext']);
                $error_arr['file_path'] = urlencode($file_data['file_path']);
                $error_arr['file_id'] = $file_id;
                $error_arr['file_type'] = $file_data['file_type'];
                if($file_data['file_type']!=Constant::CLOUD_FILETYPE_DOC){
                    $this->load->helper('qiniu');
                    $error_arr['url'] = qiniu_download($file_data['file_path'],$error_arr['file_encode_name']);
                }else{//文档类下载
                    $error_arr['type'] = $file_data['user_id']>0?'user':'lesson';
                    $error_arr['file_path'] = self::make_download_url($file_data['file_path'],urlencode($error_arr['file_name']));

                }
            }
            // elseif($source==2 and $file_data){
            //     $error_arr['errorcode'] = true;
            //     $error_arr['error'] = 'Successed';
            //     $error_arr['fname'] = $file_data->file_name;
            //     $error_arr['file_name'] = ($file_data->file_name.'.'.$file_data->file_ext);
            //     $error_arr['file_encode_name'] = urlencode($error_arr['file_name']);
            //     $error_arr['file_path'] = urldecode( $file_data->file_path);
            //     $error_arr['file_id'] = $file_id;
            //     $error_arr['file_type'] = Constant::CLOUD_FILETYPE_DOC;
            // }
             else{
                $error_arr['errorcode'] = false;
                $error_arr['error'] = '下载错误';
            }
            $error_arr['source'] = $source;
        }
        echo json_token($error_arr);
        exit;
    }

    protected function make_download_url($file_path,$file_name) {
        $this->load->config('upload',true,true);
        $this->load->helper('download');
        $this->load->config('download',true,true); //doc_download_service_uri
        $config = $this->config->item('upload');
        $base_url  = $config['domain_document']; 
        $file_path = $base_url.$file_path;
                 
        if(strpos($file_path, 'http://')===false)$file_path='http://'.$file_path;
        $file_path = urlencode($file_path);
        $salt = $this->config->item('encryption_key');
        $access_key = md5($this->tizi_uid);
        $time = time();
        $secret_key = sha1($access_key.$salt.$time);
        $download_url = $this->config->item('doc_download_service_uri');
        $return_url = $download_url.'?fileUrl='.$file_path.'&fileName='.$file_name;
        $return_url .= '&secretKey='.$secret_key.'&accessKey='.$access_key.'&time='.$time;
        return $return_url;
    }

    //下载文件，调用这个接口，+1
    public function add_download_count(){
        $this->load->model('cloud/cloud_model');
        $share_id = intval($this->input->post('share_id',true));
        $this->cloud_model->add_download_share($share_id,$this->tizi_uid);//下载+1
    }


}