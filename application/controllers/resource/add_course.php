<?php
/*用户反馈  tizi没有的教材*/
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__) . "/../Controller.php";
class Add_Course extends Controller {
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
        $this->load->model('resource/course_add_by_user_model');
    }
    
    function add(){
        $param['phone'] = trim(strip_tags($this->input->post('phone',true)));
        $param['qq'] = trim(strip_tags($this->input->post('qq_code',true)));
        $param['course_name'] = trim(strip_tags($this->input->post('course_name',true)));
        $param['subject_id'] =  (intval($this->input->post('add_subject_id',true)));
        $param['grade'] = (intval($this->input->post('add_grade',true)));
        if(!($param['phone'] or $param['qq']) or !$param['course_name']){
            echo json_token(array('errorcode'=>false,'error'=>'请完善资料'));die;
        }
        // $this->load->helper('common');
        // if(!preg_phone($param['phone'])){
        //     echo json_token(array('errorcode'=>false,'error'=>'手机号码填写不正确'));die;   
        // }
        $param['user_id'] = $this->user_id;
        $param['create_time'] = time();
        $param['user_name'] = $this->tizi_urname;
        $res = $this->course_add_by_user_model->insert($param);
        echo json_token(array('errorcode'=>true,'error'=>'您的反馈已提交成功，我们将尽快处理。'));die;
    
    }
 
}

