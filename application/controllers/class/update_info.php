<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Update_Info extends Class_Controller {
	public function __construct(){
		parent::__construct();
	}
	
	public function main(){
		$this->ajax_check();
		$user_id = $this->session->userdata("user_id");
		$alpha_class_id = $this->input->post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		$classname = trim($this->input->post("classname"));
		$classname = strip_tags($classname);
		$classname .= "班";
		$class_grade = intval($this->input->post("class_grade"));
		if ($classname === ""){
			$json["code"] = -1;
			$json["msg"] = "班级名称不能为空.";
			json_get($json);
		}
		$status = $this->classes->update_clsname($class_id, $classname, $user_id, $class_grade);
		$json["code"] = $status;
		if ($status === 1){
			$json["msg"] = "修改成功.";
			$json["name"] = $classname;
			$json["class_grade"] = $class_grade;
		} else if ($status === 0){
			$json["msg"] = "未作任何修改.";
		}
		json_get($json);
	}
	
	public function classname(){
		$this->ajax_check();
		
		$alpha_class_id = $this->input->post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		$classname = trim($this->input->post("classname"));
		$classname = strip_tags($classname);
		$classname .= "班";
		$class_grade = intval($this->input->post("class_grade"));
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		if ($classname === ""){
			$json["code"] = -1;
			$json["msg"] = "班级名称不能为空.";
			json_get($json);
		}
		
		$this->load->model("class/classes");
		$status = $this->classes->update_clsname($class_id, $classname, $teacher_id, $class_grade);
		$json["code"] = $status;
		if ($status === 1){
			$json["msg"] = "班级名称修改成功.";
			$json["name"] = $classname;
			$json["class_grade"] = $class_grade;
		} else if ($status === 0){
			$json["msg"] = "班级名称未修改.";
		}
		json_get($json);
	}
	
	public function year(){
		$this->ajax_check();
		
		$year = intval($this->input->post("year"));
		$alpha_class_id = $this->input->post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		if ($year > date("Y") || $year < date("Y") - 6){
			$json["code"] = -1;
			$json["msg"] = "修改失败,入学年份范围设置有误.";
			json_get($json);
		}
		
		$this->load->model("class/classes");
		$status = $this->classes->update_year($class_id, $year, $teacher_id);
		$json["code"] = $status;
		if ($status === 1){;
			$json["msg"] = "班级入学年份修改成功.";
		} else if ($status === 0){;
			$json["msg"] = "班级入学年份修改失败.";
		}
		json_get($json);
	}
	
	public function school(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$alpha_class_id = $this->input->post("class_id");
		$school_id = intval($this->input->post("school_id"));
		$class_id = alpha_id_num($alpha_class_id, true);
		$this->load->model("class/classes_schools");
		$school_info = $this->classes_schools->get($school_id, "province_id,city_id,county_id");
		if (!$school_info){
			$json["code"] = -1;
			$json["msg"] = "没有找到该学校的信息.";
			json_get($json);
		}
		
		$this->load->model("class/classes_teacher");
		$idct = $this->classes_teacher->get_idct($class_id, "teacher_id");
		if (!in_array(array("teacher_id" => $teacher_id), $idct)){
			$json["code"] = -2;
			$json["msg"] = "您没有权限设置.";
			json_get($json);
		}
		
		$this->load->model("class/classes");
		$status = $this->classes->update_school($class_id, $school_id, $school_info);
		if ($status === 1){
			$school_info = $this->classes_schools->school_info($school_id);
			$json["code"] = 1;
			$json["msg"] = "学校更新成功.";
			$json["fullname"] = implode("", $school_info);
			$json["school"] = $school_info["school"];
		} else {
			$json["code"] = -3;
			$json["msg"] = "学校未更新.";
		}
		json_get($json);
	}
	
	public function reset_password(){;
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
		$alpha_user_id = $this->input->post("user_id");
		$user_id = alpha_id_num($alpha_user_id, true);
		$rand6pwd = rand6pwd($class_id);
		$this->load->model("class/classes_student");
		$classes_student = $this->classes_student->userid_get($user_id, "class_id");
		if (in_array(array("class_id" => $class_id), $classes_student)){
			$this->load->model("class/classes_teacher");
			$bt = $this->classes_teacher->get_bt($teacher_id, "class_id");
			if (in_array(array("class_id" => $class_id), $bt)){
				$this->load->model("login/register_model");
				$md5_rand6pwd = md5("ti".$rand6pwd."zi");
				$this->register_model->update_password($user_id, $md5_rand6pwd);
				$json["code"] = 1;
				$json["msg"] = "修改密码成功，请尝试用密码<b>".$rand6pwd."</b>登录";
				$json["password"] = $rand6pwd;
			} else {
				$json["code"] = -2;
				$json["msg"] = "修改失败,该数据也能改变,请尝试更新页面.";
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "修改失败,该数据也能改变,请尝试更新页面.";
		}
		json_get($json);
	}
	
	public function class_subject(){
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
		$subject_id = intval($this->input->post("subject_id"));
		
		$this->load->model("question/question_subject_model");
		$subject_type = $this->question_subject_model->get_subject_type();
		if (false === array_key_exists($subject_id, $subject_type)){
			$json["code"] = -1;
			$json["msg"] = "科目不存在";
		} else {
			$this->load->model("class/classes_teacher");
			$res = $this->classes_teacher->update_class_subject($class_id, $teacher_id, $subject_id);
			if (1 === $res){
				$json["code"] = 1;
				$json["msg"] = "修改成功";
			} else {
				$json["code"] = -2;
				$json["msg"] = "修改失败";
			}
		}
		json_get($json);
	}
}