<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");

class Crm_Agents extends Crm_Controller{
	
	private $arr_name = null;
	
	public function __construct(){
		parent::__construct();
		$this->load->model("class/classes_agents_model");
		$this->load->model("class/classes_schools");
	}
	
	public function build(){
		$t_name = $this->input->get_post("t_name");
		$s_name = $this->input->get_post("s_name");
		$school_id = intval($this->input->get_post("school_id"));
		$classname = $this->input->get_post("classname");
		$class_grade = intval($this->input->get_post("class_grade"));
		$subject_id = intval($this->input->get_post("subject_id"));
		
		if (!$t_name or !$s_name or !$school_id or !$classname){
			exit("must parameters less.");
		}
		self::check_repeat($t_name, $school_id);
		self::check_repeat(explode("\n", $s_name), $school_id);
		
		$school = $this->classes_schools->get($school_id);
		$school === null && exit("school does not exists.");
		
		$data = array();
		
		$sign_t_info = self::sign_t($t_name, $subject_id);
		$sign_t_info["user_id"] > 0 ? $this->classes_agents_model->abs_sign($sign_t_info["user_id"], $school_id, $t_name) : exit("register teacher error.");
		$data["teacher"] = $sign_t_info;
		
		/*创建班级*/
		$this->load->model("class/classes");
		$ext = array(
			"class_grade" => $class_grade, 
			"school_id" => $school_id, 
			"province_id" => $school["province_id"], 
			"city_id" => $school["city_id"],
			"county_id" => $school["county_id"]
		);
		$class_id = $this->classes->create($classname, $sign_t_info["user_id"], time(), $subject_id, $ext);
		!$class_id && exit("create class failure.");
		$data["class_id"] = $class_id;
		
		/*创建学生*/
		$s_name = explode("\n", $s_name);
		$data["student"] = $this->classes_agents_model->create($class_id, $school_id, $s_name);
		
		/*创建学校*/
		$this->classes_agents_model->add_agents($school["province_id"], $school["city_id"], $school["county_id"], $school_id);
		echo json_encode($data);
	}
	
	public function t_add(){
		$user_id = intval($this->input->get_post("user_id"));
		$classname = $this->input->get_post("classname");
		$s_name = $this->input->post("s_name");
		$class_grade = intval($this->input->get_post("class_grade"));
		if (!$user_id or !$classname or !$s_name or !$class_grade){
			exit("must parameters less.");
		}
		$this->load->model("class/classes_agents_model");
		
		$is_agents_user = $this->classes_agents_model->is_agents_user($user_id);
		if ($is_agents_user){
			$this->load->model("class/classes");
			$class_info = $this->classes->first_info($user_id);
			if (isset($class_info["id"])){
				self::check_repeat(explode("\n", $s_name), $class_info["school_id"]);
				
				$s_name = explode("\n", $s_name);
				
				/*创建班级*/
				$ext = array(
					"class_grade" => $class_grade,
					"school_id" => $class_info["school_id"],
					"province_id" => $class_info["province_id"],
					"city_id" => $class_info["city_id"],
					"county_id" => $class_info["county_id"]
				);
				$this->load->model("class/classes_teacher");
				$subject_id = $this->classes_teacher->teachsubj_inclass($class_info["id"], $user_id);
				$class_id = $this->classes->create($classname, $user_id, time(), $subject_id, $ext);
				!$class_id && exit("create class failure.");
				$data["class_id"] = $class_id;
				
				/*创建学生*/
				$data["student"] = $this->classes_agents_model->create($class_id, $class_info["school_id"], $s_name);
				echo json_encode($data);
			} else {
				exit("该老师还没创建过班级");
			}
		} else {
			exit("用户不满足crm的添加条件");
		}
	}

	public function del_realname(){
		$school_id = intval($this->input->get_post('school_id'));
		$names = $this->input->get_post('names');
		$arr_data = explode(',', $names);
		$bsr = $this->classes_agents_model->get_info_bsr($school_id, $arr_data);
		$affected_rows = 0;
		foreach ($bsr as $value){
			$aff = $this->classes_agents_model->delete($value['id']);
			$this->classes_agents_model->delete_create($value['create_id']);
			$aff > 0 && $affected_rows++;
		}
		if ($affected_rows > 0){
			$action = 'DEL';
			$data = $names.'|'.$affected_rows;
			$this->classes_agents_model->insert_log($action, $data);
		}
		echo $affected_rows;
	}
	
	protected function sign_t($t_name, $register_subject){
		$username = "tza".time()."_".mt_rand(10000, 99999);
		//$password = rand6pwd(time());
		//$password = "123456";
		$password = "999999";
		$this->load->model("login/register_model");
		$md5pwd = md5("ti".$password."zi");
		$res = $this->register_model->insert_register($username, $md5pwd, $t_name,
					Constant::INSERT_REGISTER_UNAME, Constant::USER_TYPE_TEACHER, array("register_subject" => $register_subject, 
					"register_origin" => Constant::REG_ORIGIN_SCHOOL_LOGIN));
		return $res["user_id"] > 0 ? array("user_id" => $res["user_id"], "password" => $password) : -1;
	}
	
	//检查姓名重复
	protected function check_repeat($username, $school_id){
		if ($this->arr_name === null){
			$this->load->model("class/classes_agents_model");
			$this->arr_name = $this->classes_agents_model->name_school($school_id);
		}
		
		if (is_array($username)){
			$arr_values = array_count_values($username);
			foreach ($arr_values as $key => $value){
				$value > 1 && exit("学生“".$key."”姓名重复");
				in_array($key, $this->arr_name) && exit("学生“".$key."”在该学校已经存在");
			}
		} else {
			in_array($username, $this->arr_name) && exit("老师“".$username."”在该学校已经存在");
		}
	}
	
}
/* end of crm_agents.php */