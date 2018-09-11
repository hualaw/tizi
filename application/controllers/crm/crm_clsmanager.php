<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");

class Crm_Clsmanager extends Crm_Controller {

   	public function __construct(){
    	parent::__construct();
    }
	
	/**
	 * 创建班级
	 */ 
	public function create_cls(){
		$classname = trim($this->input->get_post("classname"));
		$creator_id = intval($this->input->get_post("creator_id"));
		$subject_id = intval($this->input->get_post("subject_id"));
		$class_grade = intval($this->input->get_post("class_grade"));
		$school_id = intval($this->input->get_post("school_id"));
		
		$this->load->model("class/classes_schools");
		$this->load->model("class/classes");
		$area = $this->classes_schools->get($school_id, "province_id,city_id,county_id");
		
		if ("" === $classname || !$creator_id || !$subject_id ){
			exit("parameters less.");
		}
		
		/*推算*/
		if ($class_grade > 0 && $class_grade < 7){
			$grade_level = $this->grade_level();
			$class_year = $grade_level[$class_grade]['class_year'];
		} else if ($class_grade > 6 && $class_grade <13) {
			$grade_level = $this->grade_level(1);
			$class_year = $grade_level[$class_grade]['class_year'];
		} else {
			$class_year = 0;
		}	
		
		$data = array(
			"classname"		=> $classname,
			"creator_id"	=> $creator_id,
			"province_id"	=> intval($area["province_id"]),
			"city_id"		=> intval($area["city_id"]),
			"county_id"		=> intval($area["county_id"]),
			"school_id"		=> $school_id,
			"class_grade"	=> $class_grade,
			"class_year"	=> $class_year,
			"create_date"	=> time(),
			"subject_id"	=> $subject_id
		);
		$status = $this->classes->create_class($data);
		echo $status;
	}
	
	/**
	 * 班级绑定老师
	 */
	public function classes_teacher(){
		$class_id = intval($this->input->get_post("class_id"));
		$teacher_id = intval($this->input->get_post("teacher_id"));
		$subject_id = intval($this->input->get_post("subject_id"));
		
		if (!$class_id || !$teacher_id || !$subject_id){
			exit("parameters less.");
		}
		
		$this->load->model("class/classes_teacher");
		$status = $this->classes_teacher->create($class_id, $teacher_id, $subject_id, time());
		echo $status;
	}
	
	/**
	 * 传入一个99以内的数字，指定数量的学生
	 */ 
	public function crts_i(){
		$total = intval($this->input->get_post("total"));
		$class_id = intval($this->input->get_post("class_id"));
		if ($total > 200 || $total < 1 || !$class_id){
			exit("parameters less.");
		}
		
		$empty_name = array();
		for ($i = 0; $i < $total; ++$i){
			$empty_name[] = "";
		}
		$this->load->helper("json");
		$this->load->model("class/classes_student_create");
		$create = $this->classes_student_create->create($class_id, $empty_name);
		json_get($create);
	}
	
	public function crts_name(){
		$student_names = $this->input->get_post("student_names");
		$class_id = intval($this->input->get_post("class_id"));
		if (!$student_names || !$class_id){
			exit("parameters less.");
		}
		
		$create_name = array();
		$names = explode(",", $student_names);
		foreach ($names as $value){
			if ("" !== trim($value)){
				$create_name[] = trim($value);
			}
		}
		$this->load->helper("json");
		$this->load->model("class/classes_student_create");
		$create = $this->classes_student_create->create($class_id, $create_name);
		
		foreach ($create as $value){
			$this->eslogin($value["student_id"]);
		}
		
		json_get($create);
	}
	
	private function eslogin($student_id){
		$result = $this->db->query("select * from classes_student_create where student_id=?", 
			array($student_id))->result_array();
		if (isset($result[0])){
			$result = $result[0];
			
			$password = md5("ti".$result["password"]."zi");
			$this->load->model("login/register_model");
			$user = $this->register_model->insert_register($student_id, $password, 
				$result["student_name"], Constant::INSERT_REGISTER_STUID, Constant::USER_TYPE_STUDENT);
			
			//update origin
			$this->db->query("update user set register_origin=? where id=?", 
				array(Constant::REG_ORIGIN_CRM_STUID, $user["user_id"]));
			
			/* 标记中间表 */
			$this->db->query("update classes_student_create set user_id=? where id=?", 
				array($user["user_id"], $result["id"]));
				
			$this->load->model("class/classes_student");
			$this->classes_student->add($result["class_id"], $user["user_id"],
				$_SERVER["REQUEST_TIME"], Classes_student::JOIN_METHOD_TCREATE);
		}
	}

	/**
	 * 计算获取入学年份和年级
	 * sctype（学校类型）=1.小学，2.中学
	 */
	private function grade_level($sctype = 2){
		$const_grades = Constant::grade();
		if ($sctype === 2){
			foreach ($const_grades as $key => $value){
				if ($key <= 6){
					$grades[$key] = $value;
				}
			}
		} else {
			foreach ($const_grades as $key => $value){
				if ($key > 6){
					$grades[$key] = $value;
				}
			}
		}
		$grade_level = array();
		$year = date('Y');
		$month = date('n');
		$first_year = $month >= 9 ? $year : $year - 1;
		foreach ($grades as $key => $value){
			$grade_level[$key]['name'] = $value;
			$key === 1 || $key === 4 ? ($grade_level[$key]['class_year'] = $first_year) : '';
			$key === 2 || $key === 5 ? ($grade_level[$key]['class_year'] = $first_year - 1) : '';
			$key === 3 || $key === 6 ? ($grade_level[$key]['class_year'] = $first_year - 2) : '';
			$key > 6 ? ($grade_level[$key]["class_year"] = $first_year - ($key - 7)) : "";
		}
		return $grade_level;
	}
}