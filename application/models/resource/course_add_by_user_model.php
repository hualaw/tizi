<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Course_Add_By_User_Model extends MY_Model {
    private $_table = 'feedback_category_lack';
    public function __construct(){
        parent::__construct();
    }
    
    function insert($param){
        return $this->db->insert($this->_table,$param);
    }
}