<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");

class Crm_School extends Crm_Controller{
	
	public function __construct(){
		parent::__construct();
	}
	
	public function api_create(){
		$schoolname = trim($this->input->get_post("schoolname"));
		$county_id = intval($this->input->get_post("county_id"));
		$sctype = intval($this->input->get_post("sctype"));
		
		if (!$schoolname || !$county_id || !$sctype){
			exit("-1");
		}
		
		$this->load->helper("pinyin");
		$arr_pinyin = utf82py($schoolname);
		$py = "";
		$first_py = "";
		foreach ($arr_pinyin as $value){
			$py .= $value;
			$first_py .= $value[0];
		}
		
		$this->load->model("class/classes_schools");
		echo $this->classes_schools->create($schoolname, $county_id, $sctype, $py, $first_py);
	}
	
	public function api_update(){
		$id = intval($this->input->get_post("id"));
		$schoolname = trim($this->input->get_post("schoolname"));
		$county_id = intval($this->input->get_post("county_id"));
		$sctype = intval($this->input->get_post("sctype"));
		
		if (!$id || !$schoolname || !$county_id || !$sctype){
			exit("-1");
		}
		
		$this->load->helper("pinyin");
		$arr_pinyin = utf82py($schoolname);
		$py = "";
		$first_py = "";
		foreach ($arr_pinyin as $value){
			$py .= $value;
			$first_py .= $value[0];
		}
		
		$this->load->model("class/classes_schools");
		echo $this->classes_schools->update($id, $schoolname, $county_id, $sctype, $py, $first_py);
	}
	
	public function api_delete(){
		$id = intval($this->input->get_post("id"));
		$this->load->model("class/classes_schools");
		$total = $this->classes_schools->class_count($id);
		if ($total == 0){
			echo $this->classes_schools->delete($id);
		} else {
			echo "无法删除，该学校已经存在班级.";
		}
	}
	
	public function move_delete(){
		$from_school_id = intval($this->input->get_post("from_school_id"));
		$to_school_id = intval($this->input->get_post("to_school_id"));
		if ($from_school_id > 0 && $to_school_id > 0){
			$this->load->model("class/classes_schools");
			$affected_rows = $this->classes_schools->class_move($from_school_id, $to_school_id);
			$this->classes_schools->delete($from_school_id);
			echo $affected_rows;
		} else {
			echo 0;
		}
	}
	
}
/* end of crm_school.php */