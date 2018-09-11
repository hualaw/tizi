<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."../lesson/lesson_prepare.php";

class Resource_Base extends Lesson_Prepare {

    public function __construct(){
        parent::__construct();
        $this->user_id = $this->session->userdata("user_id");
        if ($this->user_id < 1){
            redirect('');
        }
        $this->user_type = $this->session->userdata("user_type");
    }

    function get_my_classes(){
        if($this->user_type!=Constant::USER_TYPE_TEACHER){
            return null;
        }
        //获取所有班级
        $this->load->model('class/classes_teacher','ct');
        $this->load->model('class/classes_schools');
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $all_class_info = $this->ct->get_classes_by_tch($this->user_id);
        foreach($all_class_info as $k=>$c){
            $name = $c['school_define_id']?$this->classes_schools->define_school_info($c['school_define_id'],true):$this->classes_schools->getsh_info($c['id']);//学校名字
            $c_name = $this->hcm->get_class_whole_name($c['id']);
            if($c_name[0]['class_year']){
                if(isset($name['schoolname']) and isset($name['classname'])){
                    $c['class_name'] =  $name['schoolname'].$c_name[0]['class_year'].'级'.$name['classname'];
                }elseif(isset($name['schoolname'])){
                    $c['class_name'] =  $name['schoolname'].$c_name[0]['class_name'];
                }else{
                    $c['class_name'] =  '';
                }
            }else{
                $c['class_name'] = $c['class_grade'].$c['classname'];
            }
            unset($c['classname']);
            $c['alpha_id'] = alpha_id_num($c['id']);
            unset($c['id']);
            $all_class_info[$k] = $c;
        }
        return $all_class_info;
    }
     

}