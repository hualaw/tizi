<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Apply extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 梯子3.0教师拥有班级号直接加入班级
	 */ 
	public function dosubmit(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$alpha_class_id = strtoupper($this->input->post("class_id"));
		$alpha_class_id = str_replace(" ", "", $alpha_class_id);
		$class_id = alpha_id_num($alpha_class_id, true);
		$subject_id = self::subject_type();
		
		$this->load->model("class/classes");
		$class_info = $this->classes->get($class_id, "class_status,creator_id");
		if (null !== $class_info){
			if ($class_info["class_status"] == 1){
				$json["code"] = -2;
				$json["msg"] = "该班级已经被班级管理员解散.";
			} else {
				$this->load->model("class/classes_teacher");
				$bt = $this->classes_teacher->get_bt($teacher_id);	//get classes_teacher by teacher_id
				foreach ($bt as $value){
					if ($value["class_id"] == $class_id){
						$json["code"] = -4;
						$json["msg"] = "您已经在该班级里面了.";
						json_get($json);
					}
				}
				$this->load->model("class/class_model");
				$res = $this->class_model->i_join_class($class_id, $teacher_id, $subject_id, time());
				if ($res === 1){
					$json["code"] = 1;
					$json["msg"] = "加入成功，您将跳转到该班级首页";
					$json["redirect"] = site_url()."teacher/class/".$alpha_class_id."/teacher";
				} else {
					$json["code"] = -5;
					$json["msg"] = "系统忙，加入失败，请稍后再试";
				}
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "班级编号不存在.";
		}
		json_get($json);
	}
	
	protected function subject_type(){
		$user_id = $this->tizi_uid;
		$this->load->model("login/register_model");
		$info = $this->register_model->get_user_info($user_id);
		if ($info["user"]->register_subject > 0){
			$this->load->model("question/question_subject_model");
			$subject_id = $info["user"]->register_subject;
			$subject_type = $this->question_subject_model->get_subject_type_by_id($subject_id);
			if ($subject_type > 0){
				return $subject_type;
			}
		}
		return Constant::DEFAULT_SUBJECT_TYPE;
	}
	
}