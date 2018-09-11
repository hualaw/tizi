<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";

class Teacher_Class_Paper extends Class_Controller {
    private $user_id ;
    public function __construct(){
        parent::__construct();
        $this->user_id = $this->tizi_uid;
        $this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
        $this->check(Constant::USER_TYPE_TEACHER);
    }

    //评语入库
    function insert_cmt(){
        $this->load->helper('array');
        $stu_ids = $this->input->post('stu_ids',true);
        $stu_ids = explode_to_distinct_and_notempty($stu_ids);
        if(empty($stu_ids)){
            echo json_token(array('errorcode'=>false,'error'=>'没有找到学生'));die;
        }

        $cmt = $this->input->post('cmt',true);
        $cmt = strip_tags(trim($cmt));
        if(strlen($cmt)<1){
            echo json_token(array('errorcode'=>false,'error'=>'评语内容不能为空'));die;
        }
        $cmt = addslashes($cmt); //防注入
        $teacher_id = $this->user_id;
        $assignment_id = intval($this->input->post('ass_id',true));
        if(!$assignment_id){
            echo json_token(array('errorcode'=>false,'error'=>'非法请求'));die;
        }
        $this->load->model('exercise_plan/homework_assign_model');
        $belong = $this->homework_assign_model->is_hw_belong($teacher_id,$assignment_id);
        if(!$belong){// 判断是否自己的作业
            echo json_token(array('errorcode'=>false,'error'=>'只能对自己布置的作业给评语'));die;
        }
        //获取作业的科目名称，作业时间
        $hw_info = $this->homework_assign_model->get_assigned_homework_info_by_id($assignment_id);
        $hw_title = date('Y-m-d',$hw_info['start_time']);
        $this->load->model('class/classes_teacher');
        $subject = $this->classes_teacher->get_teacher_class_info($teacher_id,$hw_info['class_id']);
        if($subject){
            $this->load->model('question/question_subject_model');
            $subject_name = $this->question_subject_model->get_subject_name($subject[0]['subject_id']);
            $subject_name = mb_substr($subject_name,2);
        }else{
            $subject_name = '';
        }
        $this->load->library("notice");
        $msg_data = array("subject_name"=>$subject_name,
                         "t_name"=>$this->tizi_urname,
                         'hw_title'=>$hw_title,
                         'cmt'=>sub_str($cmt,0,60));
        foreach($stu_ids as $s){
            $s = alpha_id_num($s,true);
            $data = array('student_id'=>$s,'assignment_id'=>$assignment_id,'content'=>$cmt,'teacher_id'=>$teacher_id);
            $res = $this->tepm->insert_comment($data);
            if($res){
                $this->notice->add($s, "hw_cmt", $msg_data);
            }
        }
        echo 1;die;
    }

    //给评语的弹窗
    function give_cmt_preview(){
        $teacher_id = $this->user_id;
        $assignment_id = intval($this->input->get('ass_id',true));
        if(!$assignment_id){
            echo json_token(array('errorcode'=>false,'error'=>'非法请求'));die;
        }
        $this->load->model('exercise_plan/homework_assign_model');
        $belong = $this->homework_assign_model->is_hw_belong($teacher_id,$assignment_id);
        if(!$belong){// 判断是否自己的作业
            echo json_token(array('errorcode'=>false,'error'=>'只能对自己布置的作业给评语'));die;
        }
        $this->load->model('exercise_plan/student_homework_model');
        $stu_ids = $this->student_homework_model->get_all_stu_homework($assignment_id);

        $this->load->model('exercise_plan/homework_assign_model');
        $hw_info = $this->homework_assign_model->get_assigned_homework_info_by_id($assignment_id);
        if(!$hw_info){
            echo json_token(array('errorcode'=>false,'error'=>'非法请求'));die;   
        }
        $this->load->model('class/classes_student');
        $student_ids = $this->classes_student->get_user_ids($hw_info['class_id'], 'user_id'); 
        $available_stu_ids = array();
        foreach($student_ids as $v){
            $available_stu_ids[] = $v['user_id'];//没有被清理的班级学生
        }
        if($available_stu_ids && $stu_ids){
            foreach($stu_ids as $k=>$s){
                if(!in_array($s['user_id'],$available_stu_ids)){ // 要过滤掉已经不在本班的同学
                    unset($stu_ids[$k]);    
                }
            }
        }

        if(empty($stu_ids)){
            echo json_token(array('errorcode'=>false,'error'=>'没有找到学生'));die;
        }
        foreach($stu_ids as $k=>$s){
            $has_cmt = $this->tepm->has_comment($assignment_id,$s['user_id']);
            if($has_cmt){
                unset($stu_ids[$k]);
            }else{
                $s['name'] = sub_str($s['name'], 0,18);
                $stu_ids[$k] = $s;
            }
        }
        $stu_ids = array_values($stu_ids);//让index从0开始
        foreach($stu_ids as $k=>&$s){
            $s['user_id'] = alpha_id_num($s['user_id']);//加密后外面看不到uid
        }
        echo json_token(array('errorcode'=>true,'stu'=>$stu_ids));die;   
    }

    //tizi 4.0 删除已经布置的作业
    function del($assignment_id){
        $assignment_id = intval($assignment_id);
        if(!$assignment_id){
            echo json_token(array('errorcode'=>false,'error'=>'无权删除该作业'));die;
        }
        $teacher_id = $this->tizi_uid;
        $this->load->model('exercise_plan/homework_assign_model');
        $belong = $this->homework_assign_model->is_hw_belong($teacher_id,$assignment_id);
        if(!$belong){// 判断是否自己的作业
            echo json_token(array('errorcode'=>false,'error'=>'无权删除该作业'));die;
        }
        
        $this->load->model('exercise_plan/student_homework_model');
        $this->load->model('exercise_plan/student_task_model');
        $r = $this->tepm->del_assignment($assignment_id);
        if($r){
            $this->student_task_model->deleteHomework($assignment_id);
            // $this->tepm->teacher_ex_total($teacher_id,-1);//redis里统计数据减一
            echo json_token(array('errorcode'=>true,'error'=>'删除成功'));die;
        }
        echo json_token(array('errorcode'=>false,'error'=>'系统繁忙，请稍后再试'));die;
    }
    
    
}