<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class log4php extends CI_Controller {

	/**
	 * Simpe Test for log4php
	 */
    public function __construct() {
        parent::__construct();
        $this->_log = Logger::getLogger(__CLASS__);
    }
	
	/*log info will append to /var/log/127.0.0.1/tizi.log on rsyslog config server*/
	public function index()
	{
		/* record error log */
        $this->_log->error('Catch error log from '. $this->input->ip_address().'!');
		/* record info log */
        $this->_log->info('Catch info log from '. $this->input->ip_address().'!');
		/* record debug log */
        $this->_log->debug('Catch debug log from '. $this->input->ip_address().'!');
		echo 'Look 192.168.11.12\'s /var/log/your_ip_addr/tizi.log file to find your log';
	}
}

/* End of file log4php.php 123*/
/* Location: ./application/controllers/test/log4php.php */
