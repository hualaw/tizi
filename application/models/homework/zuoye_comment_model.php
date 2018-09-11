<?php

class Zuoye_Comment_Model extends MY_Model {
    protected $tab = "zuoye_comment";

    function __construct(){
        parent::__construct();
    }

    //老师写 作业 评语
    function insert($param){
        $this->load->model('homework/zuoye_model');
        if($this->zuoye_model->is_belong($param['zy_assign_id'],$param['teacher_id'])){
            $res = $this->db->insert($this->tab,$param);
            return $res;
        }
        return false;
    }


    function get_cmt($zy_assign_id,$stu_user_id){
        $sql = "select * from {$this->tab} where zy_assign_id=$zy_assign_id and user_id=$stu_user_id ";
        $com = $this->db->query($sql)->row_array();
        return isset($com['content'])?$com['content']:'';
    }

    
}

