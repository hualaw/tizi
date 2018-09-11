<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crm_Controller extends CI_Controller {

   	public function __construct()
   	{
    	parent::__construct();
    	$allow_ip = array("127.0.0.1", "114.112.172.218");
		$remote_addr = get_remote_ip();
		if (!in_array($remote_addr, $allow_ip)&&strpos($remote_addr,'192.168.')===false) exit();
    }

}	
/* End of file crm_register.php */
/* Location: ./application/controllers/crm/crm_register.php */

