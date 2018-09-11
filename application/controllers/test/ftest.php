<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ftest extends CI_Controller {

	/**
	 * Simpe Test for log4php
	 */
    public function __construct() {
        parent::__construct();
    }
	
    public function index()
    {
    	exit();
    }

    public function denglu()
    {
    	$this->load->model('login/session_model');
    	$this->session_model->generate_session(1000006,'8bd0765d65e0a7ffe051f5857477472e');
    	exit;
    }

	function check_session()
	{
		//$this->session->sess_destroy();
		//delete_cookie("username");
		//$this->session->keep_flashdata('errormsg');
		//$this->session->keep_flashdata('captcha_word');
		echo "<pre>";
		print_r($_COOKIE);
		echo "</pre>";
        echo "<pre>";
		print_r($this->session->all_userdata());				
		echo "</pre>";
		//$this->load->library("utility");
		echo get_remote_ip();
		echo "<br />";	
		$this->load->model('redis/redis_model');
		$this->load->driver('cache',array('adapter'=>'redis'));

		$this->cache->redis->select(0);
		$this->cache->save('test','123',100);
		echo $this->cache->get('test');
		//echo date("Y-m-d H:i:s", strtotime("last minute"));

		$this->load->library('thrift');	
		$phone='18601357927';
		$uid = $this->thrift->get_uid($phone);
		echo '<br/>'.$uid.'<br/>';
		//$this->thrift->change_phone($uid,'18601357923');
		//show_404();
		echo $this->input->server('HTTP_USER_AGENT');
		echo '<br/>500813213:'.alpha_id('999',false,2,'','ABCDEFGHIJKLMNOPQRSTUVWXYZ').alpha_id('813213',false,4,'','0123456789');
		//tizi_404('teacher/paper/preview');
		var_dump(tizi_get_contents('http://tizi.oss.aliyuncs.com/avatar/46755a15960d9c78dfa5acb2348a774d.jpg',false));

		echo long2ip('2093068738');
	}
	function clear_session()
	{
		$this->session->sess_destroy();
		//$this->load->helper("cookie");
		delete_cookie(Constant::COOKIE_TZUSERNAME);
	}
	function check_agent()
	{
		$agent = 'Agent:Unidentified User Agent';
		if ($this->agent->is_browser())
		{
		    $agent = 'Browser:'.$this->agent->browser().' '.$this->agent->version();
		}
		if ($this->agent->is_robot())
		{
		    $agent = 'Robot:'.$this->agent->robot();
		}
		if ($this->agent->is_mobile())
		{
		    $agent = 'Mobile:'.$this->agent->mobile();
		}

		echo $_SERVER['HTTP_USER_AGENT'];
		echo '<br/>';
		echo $agent;
		echo '<br/>';
		echo $this->agent->platform();
		echo '<br/>';
		echo user_agent();
	}
	function alphaid()
	{
		echo alpha_id($this->input->get('id'),true);
	}
	function s1()
	{
		echo sha1($this->input->get('p',true));
	}
	function s404()
	{
		show_404();
	}
	function testsmarty($tpl_id=0)
	{
		error_reporting(0);
		$tpl=array(
			'login/email_register.html',
			'login/email_bind.html',
			'teacher/class/student_sign.html'
		);
		$this->smarty->display($tpl[$tpl_id]);		
	}
	function fluent()
	{
		$this->load->library('fluent');
		var_dump($this->fluent->post(array('business'=>'tizi')));
	}
	function upload()
	{
		$this->smarty->display('test/uploadtest.html');
	}
	function phpinfo()
	{
		echo phpinfo();
	}
	function redis()
	{
		$this->load->model('redis/redis_model');
		if($this->redis_model->connect('statistics'))
		{
			$this->cache->save('redis_test',1);
		}
	}
	function credit()
	{
		$this->load->library('credit');
		$r = $this->credit->userlevel_privilege($this->session->userdata('user_id'));
		echo '<pre>';
		var_dump($r);
	}
	function rand()
	{
		$award = array(
            0 => array(array(10,50), array(-1,100)),
            1 => array(array(21,50), array(-1,100)),
            2 => array(array(-1,100)),
            3 => array(array(-1,100)),
            4 => array(array(-1,100)),
            5 => array(array(-1,100)),
            6 => array(array(-1,100)),
            7 => array(array(-1,100)),
            8 => array(array(-1,100)),
            9 => array(array(-1,100))
        );

        $statistics = array();
		for($i=0;$i<300;$i++)
		{
			$aid=tizi_rand($award);
			var_dump($aid);echo '<br>';
			//aid > 0;store;reduplicate
        	$statistics[$aid['aid']]=isset($statistics[$aid['aid']])?$statistics[$aid['aid']]+1:1;
		}
		var_dump($statistics);
	}
	function sqrt()
	{
		$n=(string)sqrt(25);
		if(strpos((string)sqrt(25),'.')===false) var_dump((string)sqrt(25));
	}
}

/* End of file fluent.php */
/* Location: ./library/libraries/fluent.php */
