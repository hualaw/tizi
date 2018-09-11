<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class Agents_school extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}

	public function get_city(){
		$province_id = intval($this->input->get("province_id"));
		$this->load->model("class/classes_agents_model");
		$data = $this->classes_agents_model->get_city($province_id);
		$cities = array();
		foreach ($data as $key => $value){
			$cities[] = array("city_id" => $key, "city_name" => $value);
		}
		$this->load->helper("json");
		json_get($cities);
	}
	
	public function get_school(){
		$city_id = intval($this->input->get("city_id"));
		$this->load->model("class/classes_area");
		$this->load->model("class/classes_agents_model");
		$area = $this->classes_area->get($city_id, "parentid");
		$this->load->helper("area");
		
		$data = ismunicipality($area["parentid"]) ? $this->classes_agents_model->get_school_county($city_id) : $this->classes_agents_model->get_school($city_id);

		$this->load->helper("json");
		json_get($data);
	}
	
	public function multi(){
		$data = array();
		$school_id = intval($this->input->get("school_id"));
		
		$this->load->model("class/classes_agents_model");
		$data["my"] = $this->classes_agents_model->get_by_school_id($school_id, "province_id,city_id,school_id");
		$data["province"] = $this->classes_agents_model->get_province();
		$data["province"] = $data["province"]["data"];
		$city = $this->classes_agents_model->get_city($data["my"]["province_id"]);
		foreach ($city as $key => $value){
			$data["city"][] = array("city_id" => $key, "city_name" => $value);
		}
		$data["school"] = $this->classes_agents_model->get_school($data["my"]["city_id"]);
		$this->load->helper("json");
		json_get($data);
	}
	
}