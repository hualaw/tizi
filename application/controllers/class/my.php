<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class My extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function intro() {
		if($this->tizi_uid) {
			if($this->tizi_utype == Constant::USER_TYPE_TEACHER) {
				$this->index();
			} else {
				$this->class_create();
			}
		} else {
			$this->class_create();
		}
	}

	public function index(){
		$teacher_id = intval($this->session->userdata("user_id"));
		$this->load->model("class/classes_teacher");
		$this->load->model("class/classes_student_create");
		$getall = $this->classes_teacher->get_bt($teacher_id);
		$member_total = count($getall);		//该老师加入班级的数量
		if ($member_total > 0){
			$alpha_class_id = alpha_id_num($getall[0]["class_id"]);
			redirect($site_url."teacher/class/".$alpha_class_id);
		} else {
			$this->class_create();
		}
	}

	protected function class_create(){
		$template="teacher/class/my.html";
        $cache_id="banji_t".$this->tizi_utype;
        if(!$this->smarty->isCached($template, $cache_id))
        {
			$this->load->model("question/question_subject_model");
			$subject_type = $this->question_subject_model->get_subject_type(false, "class");
			//新增class_year
			$class_year = array();
			for ($i = 0; $i <= 9; ++$i){
				$class_year[] = date("Y") - $i;
			}
			parent::smarty_school_type();
			parent::smarty_grade();
			//self::recent_class();
			$this->smarty->assign("subject_type", $subject_type);
			$this->smarty->assign("class_year", $class_year);
		}
		$this->smarty->display($template, $cache_id);
	}
	
	private function recent_class(){
		$this->load->model("class/class_model");
		$class = $this->class_model->getnew();
		$this->smarty->assign("class", $class);
	}
}