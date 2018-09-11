<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");

class Crm_Util extends Crm_Controller{
    
    public function __construct(){
        parent::__construct();
    }

    /*下载 老师上传的文件*/
    function download_cloud_file($file_id){
        $this->load->model('cloud/cloud_model');
        $file_data = $this->cloud_model->file_info($file_id);
        $error_arr['errorcode'] = false;
        $error_arr['error'] = '文件错误';
        if($file_data){
            $error_arr['errorcode'] = true;
            $error_arr['error'] = '';
            $error_arr['fname'] = $file_data['file_name'];
            $error_arr['file_name'] = ($file_data['file_name'].'.'.$file_data['file_ext']);
            $error_arr['file_encode_name'] = urlencode($error_arr['file_name']);
            $error_arr['file_path'] = urlencode($file_data['file_path']);
            $error_arr['file_id'] = $file_id;
            $error_arr['file_type'] = $file_data['file_type'];
            if($file_data['file_type']!=Constant::CLOUD_FILETYPE_DOC){
                $this->load->helper('qiniu');
                $error_arr['url'] = qiniu_download($file_data['file_path'],$error_arr['file_encode_name']);
            }elseif(Constant::CLOUD_FILETYPE_DOC==$file_data['file_type']){
                $this->load->config('upload',true,true);
                $config = $this->config->item('upload');
                $base_url  = $config['domain_document'];
                $file_path = $base_url.$error_arr['file_path'];
                if(strpos($file_path, 'http://')===false){
                    $file_path='http://'.$file_path;
                }

                $error_arr['url'] =$file_path;
            }
        }
        echo json_encode($error_arr);
    }

}