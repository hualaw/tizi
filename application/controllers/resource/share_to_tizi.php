<?php
/*用户将文件共享给 tizi*/
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__) . "/../Controller.php";
class Share_To_Tizi extends Controller {

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
        $this->load->model('resource/share_to_tizi_model');
    }
    
    function add(){
        $file_ids = ($this->input->post('file_ids',true));
        if(!$file_ids){
            echo json_token(array('errorcode'=>false,'error'=>'文件获取失败，请重试'));die;    
        }
        $this->load->helper('array');
        $file_ids = explode_to_distinct_and_notempty($file_ids);
        if($file_ids){
            foreach($file_ids as $key=>$val){
                $this->load->model('cloud/cloud_model');//判断文件是不是这个user的；
                $belonging = $this->cloud_model->check_belonging($this->user_id,$val,true);
                if(!$belonging){
                    echo json_token(array('errorcode'=>false,'error'=>'不能共享不是您上传的文件'));die;    
                }
                $param['user_id'] = $this->user_id;
                $param['create_time'] = time();
                $param['source'] = 1;
                $param['file_id'] = $val;
                $res = $this->share_to_tizi_model->insert($param);
            }
        }
        echo json_token(array('errorcode'=>true,'error'=>'感谢您的共享'));die;
    }
 
}

