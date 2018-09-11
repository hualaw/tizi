<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');             
class Make_Swf_Api extends CI_Controller{

       
    public function __construct(){
       	parent::__construct();
        $this->load->model('user_data/user_document_model','teacher_doucment');
    }

    function set_swf_data()
    {
    	$swf_data = $this->input->post('response');
    	$swf_data_arr = json_decode($swf_data);
        $remote_key = isset($swf_data_arr->apisecret) ? $swf_data_arr->apisecret : '';
        self::api_verify($remote_key);
    	$error_code   = array();
    	if(isset($swf_data_arr->status) and isset($swf_data_arr->data)){

    		$re = $this->teacher_doucment->insert_swf_data($swf_data_arr->status,$swf_data_arr->data,$swf_data_arr->type);
            //$re = $this->teacher_doucment->insert_swf_data($swf_data_arr->status,$swf_data_arr->data);    //
    		if($re){
    			$error_code['status'] = true;
    			$error_code['code'] = 'success';
    		}else{
    			$error_code['status'] = false;
    			$error_code['code'] = 'faild';
    		}
    	}
    	echo json_encode($error_code);die;

    }

    protected function api_verify($remote_key)
    {
        $local_key = 'Njy!8%1MsJ&4';
        $api_key = sha1(md5($local_key).$remote_key);
        if($api_key !== Constant::MAKE_SWF_API_SECRET)
        {
            exit('Invalid key!');
        }   

    }

}

/* End of file make_swf_api.php */
