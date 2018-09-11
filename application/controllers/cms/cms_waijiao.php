<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("cms_controller.php");

class Cms_Waijiao extends Cms_Controller {

   	public function __construct(){
    	parent::__construct();
    	$this->load->model("news/v9_waijiao_model");
    }
	
	public function addunits(){
		$name = $this->input->post("name");
		$stage_id = $this->input->post("stage_id");
		$TZID = $this->v9_waijiao_model->addunits($name, $stage_id);
		$this->v9_waijiao_model->delete_unit($TZID);
		echo $TZID;
	}
	
	public function update_unit_name(){
		$id = intval($this->input->post("id"));
		$name = $this->input->post("name");
		$this->v9_waijiao_model->update_units(array("name" => $name), $id);
		echo "ok";
	}
	
	public function update_unit_listorder(){
		$id = intval($this->input->post("id"));
		$order_list = $this->input->post("order_list");
		$this->v9_waijiao_model->update_units(array("order_list" => $order_list), $id);
		echo "ok";
	}
	
	public function delete_unit(){
		$id = intval($this->input->post("id"));
		$this->v9_waijiao_model->delete_unit($id);
		echo "ok";
	}
	
	public function rsync_unit(){
		$id = intval($this->input->post("id"));
		$name = $this->input->post("name");
		$stage_id = intval($this->input->post("stage_id"));
		$order_list = intval($this->input->post("order_list"));
		$this->v9_waijiao_model->replace_units($id, $name, $stage_id, $order_list);
		echo "ok";
	}
	
	public function addvideo(){
		$data = $_POST;
		if (isset($data["id"]) && $data["id"] > 0){
			$this->v9_waijiao_model->updatevideo($data);
			echo $data["id"];
		} else {
			echo $this->v9_waijiao_model->addvideo($data);
		}
	}
	
	public function addwords(){
		$data = $_POST;
		$print = "";
		foreach ($data as $value){
			if (isset($value["id"]) && $value["id"] > 0){
				$this->v9_waijiao_model->update_word($value);
			} else {
				$_TZID = $this->v9_waijiao_model->add_word($value);
				$print .= $print === "" ? $_TZID : ",".$_TZID;
			}
		}
		echo $print;
	}
	
	public function addexercise(){
		$data = $_POST;
		$print = "";
		foreach ($data as $value){
			if (isset($value["id"]) && $value["id"] > 0){
				$id = intval($value["id"]);
				unset($value["id"]);
				unset($value["video_id"]);
				$this->v9_waijiao_model->update_exercise($value, $id);
			} else {
				$video_id = $value["video_id"];
				$value["date"] = date("Y-m-d H:i:s");
				unset($value["video_id"]);
				unset($value["id"]);
				$_TZID = $this->v9_waijiao_model->add_exercise($value);
				$this->v9_waijiao_model->add_video_exercise($_TZID, $video_id);
				$print .= $print === "" ? $_TZID : ",".$_TZID;
			}
		}
		echo $print;
	}
	
	public function vol(){
		$id = intval($this->input->post("id"));
		$ol = intval($this->input->post("ol"));
		$this->v9_waijiao_model->setol($ol, $id);
		echo "ok";
	}
	
	public function add_student_video(){
		$grade_id = intval($this->input->post("grade_id"));
		$title = $this->input->post("title");
		$thumb50_uri = $this->input->post("thumb50_uri");
		$thumb_uri = $this->input->post("thumb_uri");
		$video_uri = $this->input->post("video_uri");
		$date = $this->input->post("date");
		$data = array(
			"grade_id" => $grade_id,
			"title" => $title,
			"thumb50_uri" => $thumb50_uri,
			"thumb_uri" => $thumb_uri,
			"video_uri" => $video_uri,
			"date" => $date
		);
		echo $this->v9_waijiao_model->add_student_video($data);
	}
	
	public function del_student_video(){
		$id = intval($this->input->post("id"));
		echo $this->v9_waijiao_model->del_student_video($id);
	}

	public function update_thumb(){
		$id = intval($this->input->post("id"));
		$name = $this->input->post("name");
		$uri = $this->input->post("uri");
		$this->v9_waijiao_model->update_thumb($id, $name, $uri);
		echo 1;
	}
	
	public function update_captions(){
		$video_id = intval($this->input->post("video_id"));
		$captions = json_decode($this->input->post("captions"), true);
		$this->v9_waijiao_model->del_waijiao_video_captions($video_id);
		$ids = "";
		foreach ($captions as $value){
			if (isset($value["TZID"]) && $value["TZID"] > 0){
				$data = array(
					"id" => $value["TZID"],
					"video_id" => $video_id,
					"begin_time" => $value["time"]["start"],
					"end_time" => $value["time"]["over"],
					"en_str" => $value["english"],
					"chs_str" => $value["chinese"]
				);
				$this->v9_waijiao_model->replace_captions($data);
				$ids .= $ids === "" ? "" : ",";
				$ids .= $value["TZID"];
			} else {
				$data = array(
					"video_id" => $video_id,
					"begin_time" => $value["time"]["start"],
					"end_time" => $value["time"]["over"],
					"en_str" => $value["english"],
					"chs_str" => $value["chinese"]
				);
				$TZID = $this->v9_waijiao_model->insert_captions($data);
				$ids .= $ids === "" ? "" : ",";
				$ids .= $TZID;
			}
		}
		echo $ids;
	}
	
}