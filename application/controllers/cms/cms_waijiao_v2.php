<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("cms_controller.php");

class Cms_waijiao_v2 extends Cms_Controller {

   	public function __construct(){
    	parent::__construct();
    	$this->load->model("news/v9_waijiao_model");
    }
	
	public function add_unit(){
		$prefix = $this->input->post("prefix");
		$unit_name = $this->input->post("unit_name");
		$unit_number = $this->input->post("unit_number");
		$order_list = intval($this->input->post("order_list"));
		$edition_id = intval($this->input->post("edition_id"));
		$stage_id = intval($this->input->post("stage_id"));
		$category_id = intval($this->input->post('category_id'));
		$TZID = $this->v9_waijiao_model->add_unit($edition_id, $stage_id, $prefix, $unit_name, $unit_number, $order_list, $category_id);
		//$this->v9_waijiao_model->delete_unit($TZID);
		echo $TZID;
	}
	
	public function fls_video_lesson(){
		$TZID = intval($this->input->post("TZID"));
		$unit_id = intval($this->input->post("unit_id"));
		$en_title = trim($this->input->post("en_title"));
		$chs_title = trim($this->input->post("chs_title"));
		$length = $this->input->post("length");
		$thumb_uri = $this->input->post("thumb_uri");
		$online = intval($this->input->post("online"));
		$lesson_model = intval($this->input->post("lesson_model"));
		$order_list = intval($this->input->post('order_list'));
		$date = time();
		if ($TZID > 0){
			$this->v9_waijiao_model->update_video_lesson($TZID, $unit_id, $en_title, $chs_title, $length, $thumb_uri, $online, 
				$lesson_model, $order_list);
			echo $TZID;
		} else {
			echo $this->v9_waijiao_model->add_video_lesson($unit_id, $en_title, $chs_title, $length, $thumb_uri, $date, $online, 
				$lesson_model, $order_list);
		}
	}
	
	public function fls_lesson_resource(){
		$data = $this->input->post("data");
		$resource = json_decode($data, true);
		
		if (isset($resource[0]["lesson_id"])){
			$this->v9_waijiao_model->set_lesson_resource_online($resource[0]["lesson_id"]);
		}
		
		$arr_TZID = array();
		foreach ($resource as $value){
			$value["online"] = 1;
			if ($value["TZID"] > 0){
				$this->v9_waijiao_model->update_lesson_resource($value);
				$arr_TZID[] = $value["TZID"];
			} else {
				$TZID = $this->v9_waijiao_model->add_lesson_resource($value);
				$arr_TZID[] = $TZID;
			}
		}
		echo implode(",", $arr_TZID);
	}
	
	public function fls_subtitle(){
		$data = $this->input->post("data");
		$subtitle = json_decode($data, true);
		
		$group_res_arr = array();
		foreach ($subtitle as $value){
			!in_array($value["res_id"], $group_res_arr) && ($group_res_arr[] = $value["res_id"]);
		}
		if (!empty($group_res_arr)){
			$this->v9_waijiao_model->delete_subtitle_resids($group_res_arr);
		}
		
		$arr_TZID = array();
		foreach ($subtitle as $value){
			$arr_TZID[] = $this->v9_waijiao_model->insert_subtitle($value);
		}
		echo implode(",", $arr_TZID);
	}
	
	public function fls_words(){
		$data = $this->input->post("data");
		$words = json_decode($data, true);
	
		if (isset($words[0]["lesson_id"])){
			$this->v9_waijiao_model->delete_words($words[0]["lesson_id"]);
		}
		
		$arr_TZID = array();
		foreach ($words as $value){
			$arr_TZID[] = $this->v9_waijiao_model->insert_word($value);
		}
		echo implode(",", $arr_TZID);
	}
	
	public function fls_exercise(){
		$data = $this->input->post("data");
		$exercise = json_decode($data, true);
		if (isset($exercise[0]["lesson_id"])){
			$this->v9_waijiao_model->set_exercise_ol($exercise[0]["lesson_id"]);
		}
		
		$arr_TZID = array();
		foreach ($exercise as $value){
			$value["online"] = 1;
			if ($value["TZID"] > 0){
				$this->v9_waijiao_model->update_exercise($value);
				$arr_TZID[] = $value["TZID"];
				$this->v9_waijiao_model->ignore_video_exercise($value["TZID"], $value["lesson_id"]);
			} else {
				$TZID = $this->v9_waijiao_model->insert_exercise($value);
				$arr_TZID[] = $TZID;
				$this->v9_waijiao_model->ignore_video_exercise($TZID, $value["lesson_id"]);
			}
		}
		echo implode(",", $arr_TZID);
	}
	
	public function online_status(){
		$TZID = intval($this->input->post("TZID"));
		$online = intval($this->input->post("online"));
		$this->v9_waijiao_model->update_lesson_online($TZID, $online);
		echo $online;
	}




	/*******************edition manage code********************/
	public function add_edition(){
		$stage_id = intval($this->input->post('stage_id'));
		$edition_name = trim($this->input->post('edition_name'));
		$subject_id = intval($this->input->post('subject_id'));
		$category_id = intval($this->input->post('category_id'));
		$img_url = trim($this->input->post('img_url'));
		$all_edition = $this->v9_waijiao_model->get_all_edition();
		$edition_id = 0;
		$this->load->helper('json');

		foreach ($all_edition as $value) {
			if ($value['name'] == $edition_name && $value['subject_id'] == $subject_id){
				$edition_id = $value['id'];
			}
		}
		if ($edition_id === 0){
			$edition_id = $this->v9_waijiao_model->insert_edition($edition_name, $subject_id, $category_id);
		}
		if ($edition_id > 0){
			$stage_edition_id = $this->v9_waijiao_model->add_stage_edition($stage_id, $edition_id, $img_url);
			json_get(array(
				'code' => 1,
				'edition_id' => $edition_id,
				'stage_edition_id' => $stage_edition_id
			));
		} else {
			json_get(array('code' => -1));
		}
	}

	public function get_english_version(){
		$grade_type = intval($this->input->post('grade_type'));
		$data = $this->v9_waijiao_model->get_english_version($grade_type);
		$this->load->helper('json');
		json_get($data);
	}

	public function get_next_child(){
		$pid = intval($this->input->post('pid'));
		$data = $this->v9_waijiao_model->get_next_child($pid);
		$this->load->helper('json');
		json_get($data);
	}

	public function update_unit_listorders(){
		$listorders = $this->input->post('listorders');
		$order_list = json_decode($listorders, true);
		$this->v9_waijiao_model->update_unit_listorders($order_list);
		$this->load->helper('json');
		json_get(array('code' => 1));
	}

	public function set_unit_status(){
		$unit_id = intval($this->input->post('unit_id'));
		$status = intval($this->input->post('status'));
		$this->v9_waijiao_model->set_unit_status($unit_id, $status);
		echo 1;
	}

	public function set_edition_status(){
		$stage_id = intval($this->input->post('stage_id'));
		$edition_id = intval($this->input->post('edition_id'));
		$status = intval($this->input->post('status'));
		$this->v9_waijiao_model->set_edition_status($stage_id, $edition_id, $status);
		echo 1;
	}

	public function edition_update(){
		$stage_id = intval($this->input->post('stage_id'));
		$edition_id = intval($this->input->post('edition_id'));
		$img_url = $this->input->post('img_url');
		$category_id = intval($this->input->post('category_id'));
		$this->v9_waijiao_model->edition_update($stage_id, $edition_id, $img_url, $category_id);
		echo 1;
	}

	public function update_unit(){
		$unit_id = intval($this->input->post('unit_id'));
		$category_id = intval($this->input->post('category_id'));
		$unit_number = trim($this->input->post('unit_number'));
		$unit_name = trim($this->input->post('unit_name'));
		$prefix = trim($this->input->post('prefix'));

		$this->v9_waijiao_model->update_unit($unit_id, $category_id, $unit_number, $unit_name, $prefix);
		echo 1;
	}

	public function del_unit(){
		$unit_id = intval($this->input->post('unit_id'));
		$status = 0;
		$this->v9_waijiao_model->set_unit_status($unit_id, $status);
		echo 1;
	}

}