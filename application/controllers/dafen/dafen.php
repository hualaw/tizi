<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dafen extends MY_Controller {

    private $_smarty_dir="teacher/dafen/";

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->smarty->display($this->_smarty_dir."dafen_index.html","pigai");
    }
}