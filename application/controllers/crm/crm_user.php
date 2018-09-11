<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");

class crm_user extends Crm_Controller {

   	public function __construct(){
    	parent::__construct();
    	$this->load->model("login/register_model");
    }
	
	public function unbind_phone(){
		$user_id = intval($this->input->get_post("user_id"));
		$this->load->library("thrift");
		$data = $this->thrift->get_phone($user_id);
		if ($data > 0){
			$res = $this->thrift->change_phone($user_id, 0 - $user_id);
			if ($res = 1){
				$this->register_model->unbind_phone($user_id);
				exit("成功解除绑定");
			} else {
				exit("解除绑定失败，errorcode:".$res);
			}
		} else {
			exit("用户没有绑定手机");
		}
	}
	
	public function unbind_email(){
		$user_id = intval($this->input->get_post("user_id"));
		if (1 === $this->register_model->unbind_email($user_id)){
			exit("成功解除绑定");
		} else {
			exit("解除绑定失败，用户可能没有绑定状态");
		}
	}
	
	public function lock_user(){
		$user_id = intval($this->input->get_post("user_id"));
		
		$this->load->library("thrift");
		$this->register_model->unbind_email($user_id);
		$res = $this->thrift->change_phone($user_id, 0 - $user_id);
		$this->register_model->unbind_phone($user_id);
		
		if (1 === $this->register_model->lock_user($user_id)){
			exit("成功注销帐号");
		} else {
			exit("已经锁定过了或用户不存在");
		}
	}
	
}