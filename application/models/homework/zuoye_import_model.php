<?php

class Zuoye_Import_Model extends MY_Model {
    protected $tab = "game_important";
    protected $tab_z_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
    }

    //获取单元对应的 重点词汇 、 句子等
    function get_import_word($unit_id,$prac_type=0,$important_type=0){
        $sql = "select * from {$this->tab} where unit_id = $unit_id ";
        if($prac_type){
            $sql .= " and prac_type = $prac_type ";
        }
        if($important_type){
            $sql .= " and important_type = $important_type ";
        }
        $res = $this->db->query($sql)->result_array();
        return $res;
    }



    
}

