<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."Controller.php");

class Class_Controller extends Controller {

	protected $_smarty_dir = "teacher/class/";

	function __construct() {
		parent::__construct();
		self::i_class();
		self::smarty_grade();
	}
	
	protected function check($role = Constant::USER_TYPE_TEACHER){
		$user_id = $this->session->userdata("user_id");
		if ($user_id < 1){
			redirect('');
		}
		
		$user_type = $this->session->userdata("user_type");
		if ($user_type != $role){
			redirect('');
		}
	}
	
	protected function ajax_check($role = Constant::USER_TYPE_TEACHER){
		$this->load->helper("json");
		$user_type = $this->session->userdata("user_type");
		if ($user_type != $role){
			json_get(array("code" => -996, "msg" => "操作失败，您的身份不符。"));
		}
	}

	private function i_class(){
		$teacher_id = intval($this->session->userdata("user_id"));
		$this->load->model("class/classes_teacher");
		$this->load->model("class/classes");
		$this->load->model("class/classes_schools");
		$getall = $this->classes_teacher->get_bt($teacher_id);
		$member_total = count($getall);		//该老师加入班级的数量
		
		foreach ($getall as $key => $value){
			$getall[$key]["alpha_class_id"] = alpha_id_num($value["class_id"]);
			$class = $this->classes->get($value["class_id"], "classname,school_id,class_status,class_grade");
			if ($class["class_status"] == 0){
				$getall[$key]["classname"] = $class["classname"];
				$getall[$key]["class_grade"] = $class["class_grade"];
			} else {
				unset($getall[$key]);
			}
		}
		$this->smarty->assign("i_class", $getall);
	}
	
	protected function smarty_school_type(){
		$define = Constant::school_type();
		$this->smarty->assign("school_type", $define);
	}
	
	protected function smarty_grade(){
		$this->load->model("constant/grade_model");
		$grade = $this->grade_model->get_grade();
		$arr_grade = array();
		foreach ($grade as $gt){
			foreach ($gt as $key => $value){
				$key !== 0 ? $arr_grade[$key] = $value : "";
			}
		}
		$this->smarty->assign("igrade", $grade);
		$this->smarty->assign("arr_grade", $arr_grade);
	}
}	
/* End of file Class_Controller.php */
/* Location: ./application/controllers/class/Class_Controller.php */