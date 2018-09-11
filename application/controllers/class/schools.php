<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Schools extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		$parent_id = intval($this->input->get("id"));
		$sctype = intval($this->input->get("sctype"));
		$this->load->model("class/classes_schools");
		$result = $this->classes_schools->get_unuserdefine($parent_id, $sctype, "id,schoolname");
		$this->load->helper("json");
		json_get($result);
	}

	public function county_sch(){
		$county_id = intval($this->input->post("id"));
		$sctype = intval($this->input->post("sctype"));
		$this->load->model('class/classes_schools');
		$r = $this->classes_schools->county_schools($county_id, $sctype,'id,schoolname,py,first_py');
		$school = array();
		foreach ($r as $value){
			if (isset($value['first_py'][0])){
				$school[$value['first_py'][0]][] = $value;
			}
		}
		ksort ($school);
		$this->load->helper('json');
		json_out($school);
	}
	
	public function convert(){
		$chinese = trim($this->input->get('chinese'));
		$chinese = urldecode($chinese);
		$this->load->helper('pinyin');
		$this->load->helper('json');
		$pinyin = utf82py($chinese);
		$convert = '';
		foreach ($pinyin as $value){
			$convert .= $value;
		}
		$data = array("py" => $convert);
		json_get($data);
	}

}
