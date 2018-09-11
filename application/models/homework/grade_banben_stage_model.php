<?php

class Grade_Banben_Stage_Model extends MY_Model {
    // protected $tab_gbs = "zuoye_grade_to_banben_stage";
    // protected $tab_s_banben = "common_edition";
    // protected $tab_s_stage = "common_stage";
    // protected $tab_s_unit = "common_unit";

    // function __construct(){
    //     parent::__construct();
    // }

    // /*年级下面的教材版本和stage(x年级上/下)*/
    // function get_banben_stage_from_grade($grade){
    //     $grade = intval($grade);
    //     if(!$grade){return null;}
    //     $select = "gbs.*,sb.name as banben_name,ss.name as stage_name, ss.semester";
    //     $sql = "select $select from {$this->tab_gbs} gbs left join {$this->tab_s_banben} sb on sb.id=gbs.banben_id left join {$this->tab_s_stage} ss on ss.id=gbs.zy_stage_id where gbs.grade_id = $grade and gbs.status=1";
    //     $order = " order by gbs.list_order";
    //     $res = $this->db->query($sql.$order)->result_array();

    //     //处理名字,  北师大版 和 一年级其  要拆开
    //     foreach($res as $key=>&$val){
    //         if($val['banben_id'] ==3 ){
    //             $find = array("（", "）");
    //             $replace = "";
    //             $val['banben_name'] = str_replace($find, $replace, $val['banben_name']);
    //             $n = $val['banben_name'];

    //             $val['banben_name'] = sub_str($val['banben_name'],0,12,'');
    //             $val['banben_down_name'] = substr($n,12,strlen($n));
    //         }
    //     }
    //     return $res;
    // }   
}

