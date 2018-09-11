<?php

class Zy_Allow_Edition_Model extends MY_Model {
    protected $tab = "zuoye_allow_edition";

    function __construct(){
        parent::__construct();
    }

    function get_by_sid($subject_id){
        $sql= "select * from {$this->tab} where subject_id=$subject_id";
        return $this->db->query($sql)->result_array();
    }

    
}

