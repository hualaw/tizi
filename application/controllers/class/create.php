<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Create extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 创建班级action
	 * code -999	统一为未登录
	 */ 
	public function index(){
		$this->ajax_check();
		$user_id = intval($this->session->userdata("user_id"));
		
		
		$classname = trim($this->input->post("classname"));
		$classname = strip_tags($classname);
		$school_id = intval($this->input->post("school_id"));
		$school_type = intval($this->input->post("school_type"));
		$schoolname = strip_tags($this->input->post("schoolname"));
		$county_id = intval($this->input->post("area_county_id"));
		$sctype = $school_type;
		$class_grade = intval($this->input->post("class_grade"));
		$subject_id = self::subject_type();
		
		$classname === "" ? self::_stream(-1, "班级名称不能为空.") : "";
		(!$school_id && (!$school_type && !$schoolname)) ? self::_stream(-4, "请选择学校.") : "";
		($class_grade < 1 or $class_grade > 14) ? self::_stream(-7, "年级选择不正确.") : "";
		
		$this->load->model("class/classes");
		$classes = $this->classes->creator_get($user_id, "id");
		//count($classes) >= Constant::TEACHER_CLASS_MAX_NUM ? self::_stream(-3, "您创建的班级已超过限制.") : "";
		count($classes) >= self::class_number() && self::_stream(-3, "您创建的班级已超过限制.");
		
		//扩展数据
		if ($school_id > 0){
			$this->load->model("class/classes_schools");
			$classes_school = $this->classes_schools->get($school_id);
			$extension_loaded = array(
				"class_grade"	=> $class_grade,
				"school_id"		=> $school_id,
				"province_id"	=> $classes_school["province_id"],
				"city_id"		=> $classes_school["city_id"],
				"county_id"		=> $classes_school["county_id"]
			);
		} else {
			if ($school_type > 0 && $schoolname){
				$this->load->model("class/classes_schools");
				$city_id = $this->classes_schools->parentid($county_id);
				$province_id = $this->classes_schools->parentid($city_id);
				$school_define_id = $this->classes_schools->define_create($schoolname, $province_id, $city_id, 
					$county_id, $sctype, $school_type);
				if ($school_define_id > 0){
					$extension_loaded = array(
						"class_grade"	=> $class_grade,
						"school_id"		=> 0,
						"school_define_id" => $school_define_id,
						"province_id"	=> $province_id,
						"city_id"		=> $city_id,
						"county_id"		=> $county_id
					);
				} else {
					self::_stream(-6, "系统繁忙，请稍后再试.errorcode:60");
				}
			} else {
				self::_stream(-4, "请选择学校.");
			}
		}
		
		$classname .= "班";		//3.3班级管理加“班”字
		$status = $this->classes->create($classname, $user_id, time(), $subject_id, $extension_loaded);
		if ($status > 0){
			$json["code"] = 1;
			$json["msg"] = "班级创建成功！您可以：<br/>";
			$json["msg"] .= "1、添加班级学生名单，为学生创建帐号；<br/>";
			$json["msg"] .= "2、给班级内的学生分享学习资源；<br/>";
			$json["msg"] .= "3、在“留作业”栏目中给班级学生布置作业，并在班级空间中查看作业完成情况。";
			$json["redirect"] = site_url()."teacher/class/".alpha_id_num($status);
			
			//积分系统_首次创建班级
			$this->load->library("credit");
			$earn = $this->credit->exec($this->tizi_uid, "class_first_create", $this->tizi_cert);
			if ($earn > 0){
				$json["redirect"] .= "?vs=gfs";
			}
			
			//任务系统_首次创建班级
			$this->load->library("task");
			$this->task->exec($user_id, "use_class");
			
			json_get($json);
		}
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
	
	//获取我的最近的一个班级的信息
	public function last_info(){
		$user_id = $this->session->userdata("user_id");
		$this->load->helper("json");
		$this->load->model("class/classes");
		$class_info = $this->classes->last_info($user_id);
		if (isset($class_info["id"]) && $class_info["id"] > 0){
			$this->load->model("class/classes_teacher");
			$_t = $this->classes_teacher->get_teacher_class_info($user_id, $class_info["id"]);
			if (isset($_t[0]["subject_id"])){
				$class_info["subject_type_id"] = $_t[0]["subject_id"];
			}
		}
		if (isset($class_info["school_id"]) && $class_info["school_id"] > 0){
			$this->load->model("classes/classes_schools");
			$school_info = $this->classes_schools->school_info($class_info["school_id"]);
			$class_info["school_info"] = $school_info;
		} else if (isset($class_info["school_define_id"]) && $class_info["school_define_id"] > 0){
			$this->load->model("classes/classes_schools");
			$school_info = $this->classes_schools->define_school_info($class_info["school_define_id"], true);
			$class_info["school_info"] = $school_info;
		}
		json_get($class_info);
	}
	
	private function class_number(){
		return Constant::TEACHER_CLASS_MAX_NUM;
		/**
		$user_id = intval($this->session->userdata("user_id"));
		$this->load->library("credit");
		$privilege = $this->credit->userlevel_privilege($user_id);
		return isset($privilege["privilege"]["class_number"]["value"]) ? 
			$privilege["privilege"]["class_number"]["value"] : Constant::TEACHER_CLASS_MAX_NUM;*/
	}
	
	private function _stream($code, $msg){
		$this->load->helper("json");
		$data = array(
			"code" => $code,
			"msg" => $msg
		);
		json_get($data);
	}
}