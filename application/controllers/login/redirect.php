<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Redirect extends MY_Controller {

    function __construct()
    {
        parent::__construct();
    }

    function forgot()
    {
    	redirect(login_url('forgot'));
    }

    function register()
    {
    	redirect(login_url('register'));
    }

    function gk_shuati()
    {
        redirect(xue_url('training/mobile'));
    }

}	
/* End of file login.php */
/* Location: ./application/controllers/login/login.php */
