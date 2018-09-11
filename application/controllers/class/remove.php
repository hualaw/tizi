<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Remove extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 踢老师出班级
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
		
		$ctid = intval($this->input->post("ctid"));
		$this->load->model("class/classes_teacher");
		$ct = $this->classes_teacher->get($ctid);
		if (null !== $ct){
			if ($ct["teacher_id"] == $teacher_id){
				$json["code"] = -2;
				$json["msg"] = "不能把自己请离班级";
			} else {
				//管理员删除普通老师
				$this->load->model("class/classes");
				$class = $this->classes->get($ct["class_id"], "creator_id,classname");
				if ($class["creator_id"] == $teacher_id){
					$this->classes_teacher->remove($ctid, $ct["class_id"]);
					//add notice remove_from_class(teacher)
					$this->load->library("notice");
					$this->load->model("class/classes");
					$this->load->model("constant/grade_model");
					$arr_grade = $this->grade_model->arr_grade();
					$class_info = $this->classes->get($ct["class_id"], "classname,class_grade");
					$class_grade = $class_info["class_grade"];
					$grade_name = isset($arr_grade[$class_grade]) ? $arr_grade[$class_grade]["name"]: "";
					$data = array("classname" => $grade_name.$class_info["classname"]);
					$this->notice->add($ct["teacher_id"], "remove_from_class", $data);
					$json["code"] = 1;
					$json["msg"] = "请离成功,该老师已经被移除.";
				} else {
					$json["code"] = -3;
					$json["msg"] = "您没有权限请离老师";
				}
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "该处理已经更新,请尝试刷新页面.";
		}
		json_get($json);
	}
	
	public function student(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$csid = intval($this->input->post("csid"));
		$this->load->model("class/classes_student");
		$cs = $this->classes_student->get($csid);
		if (null !== $cs){
			$this->load->model("class/classes_teacher");
			$idct = $this->classes_teacher->get_idct($cs["class_id"], "teacher_id");
			if (in_array(array("teacher_id" => $teacher_id), $idct)){
				$this->classes_student->remove($csid, $cs["class_id"]);
				//add notice remove_from_class(student)
				$this->load->library("notice");
				$this->load->model("class/classes");
				$this->load->model("constant/grade_model");
				$arr_grade = $this->grade_model->arr_grade();
				$class_info = $this->classes->get($cs["class_id"], "classname,class_grade");
				$class_grade = $class_info["class_grade"];
				$grade_name = isset($arr_grade[$class_grade]) ? $arr_grade[$class_grade]["name"]: "";
				$data = array("classname" => $grade_name.$class_info["classname"]);
				$this->notice->add($cs["user_id"], "remove_from_class", $data);
				$json["code"] = 1;
				$json["msg"] = "请离成功,该学生已经被移除.";
			} else {
				$json["code"] = -2;
				$json["msg"] = "您没有权限请离学生";
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "该处理已经更新,请尝试刷新页面.";
		}
		json_get($json);
	}
}