<?php

class Zuoye_Report_Model extends MY_Model {
    protected $tab = "zuoye_assign";
    protected $tab_z_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
    }

    //一堆作业，每个学生分别完成了多少个
    function zuoye_done_by_student($start,$end,$class_id,$sub_ids=0){
        $zuoyes = $this->get_class_month_zy($start,$end,$class_id,$sub_ids);
        if(!$zuoyes){
            return array('stu'=>null,'month_zy_sum'=>0);
        }
        $month_sum = count($zuoyes);
        $zuoyes = implode(',', $zuoyes);
        $sql = "select user_id,sum(is_complete=2) as finish_num from {$this->tab_z_stu} zs where zs.zy_assign_id in ($zuoyes) group by user_id ";
        $res = $this->db->query($sql)->result_array();
        if(!$res){
            return array('stu'=>null,'month_zy_sum'=>0);
        }
        $result = array();
        foreach($res as $key=>$val){
            $result[$val['user_id']] = $val['finish_num'];
        }
        return array('stu'=>$result,'month_zy_sum'=>$month_sum);
    }


    //这个时间段内总过，这个班有多少作业
    function get_class_month_zy($first_day,$last_day,$class_id,$sub_ids){
        $sql = "select id from {$this->tab} where start_time >= $first_day and start_time<=$last_day and class_id=$class_id and status=1 ";
        if($sub_ids){
            $sql.= " and subject_id in ($sub_ids) " ;
        }
        $rs = $this->db->query($sql)->result_array();
        if(!$rs)return null;
        $return = array();
        foreach ($rs as $key => $value) {
            $return[] = $value['id'];
        }
        return $return;
    }

    
}

