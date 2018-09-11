<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class testsms extends CI_Controller {

        /**
         * Simpe Test for log4php
         */
    public function __construct() {
        parent::__construct();
    }

        /*log info will append to /var/log/127.0.0.1/tizi.log on rsyslog config server*/
        public function index()
        {
                $mb = $this->input->get('mb');
                $this->load->library('sms');
                $this->sms->setPhoneNums($mb);
                //$this->sms->setPhoneNums('18600364806');
                $this->sms->setContent('www.tizi.com[梯子网]');
                $sms_error=$this->sms->send();
                var_dump($sms_error);
        }
}

/* End of file log4php.php 123*/
/* Location: ./application/controllers/test/log4php.php */
