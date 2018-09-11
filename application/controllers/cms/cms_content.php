<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("cms_controller.php");

class Cms_Content extends Cms_Controller {

   	public function __construct(){
    	parent::__construct();
    }
	
	public function publish(){
		$data = $_POST;
		$this->load->model("news/v9_news_model");
		$res = $this->v9_news_model->replace($data);
		echo "ok";
	}
	
	public function virtual_delete(){
		$delete_ids = array();
		$id_array = explode(",", $this->input->post("delete_ids"));
		foreach ($id_array as $value){
			if (intval($value) > 0){
				$delete_ids[] = $value;
			}
		}
		$this->load->model("news/v9_news_model");
		$delete_ids = implode($delete_ids, ",");
		$res = $this->v9_news_model->virtual_delete($delete_ids);
		if (false === $res){
			echo "error";
		} else {
			echo "ok";
		}
	}
	
	public function position(){
		$data = $_POST;
		$this->load->model("news/v9_position_model");
		$res = $this->v9_position_model->add($data);
		echo "ok";
	}
	
	public function position_delete(){
		$data = $_POST;
		$this->load->model("news/v9_position_model");
		$res = $this->v9_position_model->position_delete($data);
		echo "ok";
	}
	
	public function listorders(){
		$data = $_POST;
		$this->load->model("news/v9_news_model");
		$this->v9_news_model->update_listorders($data);
		echo "ok";
	}
}