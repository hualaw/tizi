<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appcenter extends MY_Controller {

    private $_smarty_dir="appcenter/";

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->smarty->display($this->_smarty_dir."app_index.html","yingyong");
    }
}