<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("cms_controller.php");

class Cms_Zhuangyuan extends Cms_Controller {

   	public function __construct(){
    	parent::__construct();
    }
	
	public function update(){
		$this->load->model("champion/user_model");
		$this->load->model("champion/video_model");
		$main = $this->input->post("main");
		$video = $this->input->post("video");
		$this->user_model->replace($main);
		foreach ($video as $value){
			$this->video_model->replace($value);
		}
		echo 1;
	}
	
	public function setol(){
		$id = intval($this->input->post("id"));
		$status = intval($this->input->post("status"));
		$this->load->model("champion/user_model");
		echo $this->user_model->status($id, $status);
	}
	
	public function vsetol(){
		$id = intval($this->input->post("id"));
		$status = intval($this->input->post("status"));
		$this->load->model("champion/video_model");
		echo $this->video_model->status($id, $status);
	}
	
}