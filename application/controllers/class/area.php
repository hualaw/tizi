<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Area extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		$parent_id = intval($this->input->get("id"));
		$this->load->model("class/classes_area");
		$result = $this->classes_area->id_children($parent_id);
		$this->load->helper("json");
		json_get($result);
	}
	
	public function sctype(){
		$define = Constant::school_type();
		$sctype = array();
		foreach ($define as $key => $value){
			$sctype[] = array("id" => $key, "name" => $value);
		}
		$this->load->helper("json");
		json_get($sctype);
	}
}