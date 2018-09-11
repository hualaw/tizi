<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

require(dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php");
class Leave extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 教师自己主动离开某班级
	 */
	public function teacher(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$alpha_class_id = $this->input->post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		
		$this->load->model("class/classes");
		$class = $this->classes->get($class_id, "creator_id,classname,class_grade");
		if (null !== $class && $class["creator_id"] != $teacher_id){
			$this->load->model("class/classes_teacher");
			$status = $this->classes_teacher->tc_remove($class_id, $teacher_id);
			if (true === $status){
				//add notice quit_class(teacher)
				$this->load->library("notice");
				$this->load->model("class/classes");
				$this->load->model("constant/grade_model");
				$arr_grade = $this->grade_model->arr_grade();
				$class_grade = $class["class_grade"];
				$grade_name = isset($arr_grade[$class_grade]) ? $arr_grade[$class_grade]["name"]: "";
				$data = array("classname" => $grade_name.$class["classname"]);
				$this->notice->add($teacher_id, "quit_class", $data);
				
				$json["code"] = 1;
				$json["msg"] = "操作成功,您已经退出了该班级.";
			} else {
				$json["code"] = -2;
				$json["msg"] = "退出失败,请尝试刷新页面.";
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "班级管理员不能自己退出班级,您可以尝试解散班级.";
		}
		json_get($json);
	}
	
	public function disband(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$password = $this->input->post("password");
		$alpha_class_id = $this->input->post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		
		$this->load->model("class/classes");
		$this->load->model("class/classes_teacher");
		$this->load->model("class/classes_student");
		$classes_teacher = $this->classes_teacher->get_idct($class_id, "teacher_id");
		$classes_student = $this->classes_student->get_user_ids($class_id, "user_id");
		$class = $this->classes->get($class_id, "creator_id,classname,class_grade");
		if ($teacher_id == $class["creator_id"]){
			$this->load->model("login/register_model");
			$verify_pwd = $this->register_model->verify_password($teacher_id, $password);
			if (false === $verify_pwd["errorcode"]){
				$json["code"] = -2;
				$json["msg"] = "解散班级失败,登录密码不正确.";
				json_get($json);
			}
			
			$disband = $this->classes->disband($class_id);
			if (true === $disband){
				//add notice class_disband(all)
				$this->load->library("notice");
				$this->load->model("constant/grade_model");
				$arr_grade = $this->grade_model->arr_grade();
				$class_grade = $class["class_grade"];
				$grade_name = isset($arr_grade[$class_grade]) ? $arr_grade[$class_grade]["name"]: "";
				$data = array("classname" => $grade_name.$class["classname"]);
				$user_ids = array();
				foreach ($classes_teacher as $value){
					$user_ids[] = $value["teacher_id"];
				}
				foreach ($classes_student as $value){
					$user_ids[] = $value["user_id"];
				}
				foreach ($user_ids as $_user_id){
					$this->notice->add($_user_id, "class_disband", $data);
				}
				
				$json["code"] = 1;
				$json["msg"] = "解散班级成功.";
				$json["redirect"] = site_url()."teacher/class/my";
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "您不是该班级的管理员,没有权限解散该班级.";
		}
		json_get($json);
	}
}

/* end of leave.php */