<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Error extends MY_Controller {

    private $_smarty_dir="header/";

    function __construct()
    {
        parent::__construct();
    }

    function index($redirect='')
    {
        set_status_header('404');
        if($redirect&&urldecode($redirect)) $redirect=urldecode($redirect);
        if(strpos($redirect,'http://') === false) $redirect='';     
        if(!$redirect) $redirect=redirect_url($this->tizi_utype,'tizi');
        $this->smarty->assign('redirect',$redirect);
        $this->smarty->display($this->_smarty_dir.'404.html');
    }

    function quit()
    {
        exit('quit');
    }

}