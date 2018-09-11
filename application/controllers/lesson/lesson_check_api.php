<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');             
class Lesson_Check_Api extends CI_Controller{

       
    public function __construct(){
       	parent::__construct();
        $this->load->model('resource/share_to_tizi_model','share_doucment');
        $this->load->model('cloud/cloud_model');
    }

    function check()
    {
    	$file_id = $this->input->post('file_id',true);
        $remote_key = $this->input->post('apisecret');
        $status = $this->input->post('status',true);
        self::api_verify($remote_key);
    	$error_code   = array();
    	if(intval($status)==1){
    		$re = $this->share_doucment->user_file_online($file_id);
    		if($re){
                $this->cloud_model->update_file_table(array('is_share_to_tizi'=>1,'lesson_res_id'=>$re[0]),array('id'=>$file_id));
                $this->load->library("credit");
                $this->credit->exec($re[1], "devote_lesson_share", false, "", array($re[0]));
    			$error_code['status'] = true;
    			$error_code['code'] = 'success';
    		}else{
    			$error_code['status'] = false;
    			$error_code['code'] = 'faild';
    		}
    	}else{

            $this->cloud_model->update_file_table(array('is_share_to_tizi'=>3),array('id'=>$file_id));
            $error_code['status'] = true;
            $error_code['code'] = 'success';
        }
    	echo json_encode($error_code);die;
    }

    protected function api_verify($remote_key)
    {
        $local_key = 'Njy!8%1MsJ&4';
        $api_key = sha1(md5($local_key).$remote_key);
        if($api_key !== Constant::MAKE_SWF_API_SECRET)
        {
            echo json_encode(array('status'=>false,'code'=>'Invalid key!'));die;
        }   
    }
}

/* End of file lesson_check_api.php */
