<?php

class Practice_Type_Model extends MY_Model {
    protected $tab_zpt = "zuoye_practice_type";

    function __construct(){
        parent::__construct();
    }
         
    function get_practice_type(){
        $sql = "select * from {$this->tab_zpt}";
        return $this->db->query($sql)->result_array();
    } 
}

