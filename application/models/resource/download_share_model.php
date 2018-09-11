<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Download_Share_Model extends MY_Model {
    private $_table = 'activity_download_record';
    public function __construct(){
        parent::__construct();
    }

    function add($share_id,$student_id){
        $this->load->model('cloud/cloud_model');
        $share_info = $this->cloud_model->get_file_by_share_id($share_id);

        $param['file_id'] = $share_info[0]['file_id']; 
        $param['teacher_id'] = $share_info[0]['user_id']; 
        $param['student_id'] = $student_id; 
        $param['share_time'] =$share_info[0]['create_time']; 
        $param['download_time'] =time();  
        $param['download_ip'] = get_remote_ip();    
        $param['cookie_info'] = $this->input->cookie('uid');  
        $this->db->insert($this->_table,$param);
    }
     
}