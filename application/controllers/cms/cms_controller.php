<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class Cms_Controller extends CI_Controller {

   	public function __construct(){
    	parent::__construct();
    	$this->auth_verify();
    }

	/**
	 * 通过user_agent和盐安全加密，在cookie中携带加密来达到安全api
	 */
	protected function auth_verify(){
		$auth_info = $this->input->server("HTTP_USER_AGENT");
		$auth_salt = sha1(date("Y-m-d"));
		$auth = md5($auth_info.$auth_salt);
		if ($auth !== $this->input->cookie("auth")){
			exit("服务器验证失败");
		}
	}
}
/* End of file crm_register.php */
/* Location: ./application/controllers/crm/crm_register.php */

