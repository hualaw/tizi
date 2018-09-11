<?php

class Zuoye_Model extends MY_Model {
    protected $tab = "zuoye_assign";
    protected $tab_z_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
    }

    function get_assignment($ass_id){
        $sql = "select * from {$this->tab} where id=$ass_id ";
        $res = $this->db->query($sql)->row_array();
        return $res;
    }

    //布置作业，插入zuoye_assign and zuoye_student 
    function insert_zuoye($data){
        $has_assigned_today = $this->has_assigned_today($data['class_id'],$data['user_id']);
        if($has_assigned_today>=Constant::ZUOYE_LIMIT_IN_A_DAY){//今天的超过10次，就不允许布置作业；
            return false;
        }
        $this->db->trans_start();
        $this->db->insert($this->tab, $data);
        $id = $this->db->insert_id();
        if($id and $data['class_id']){ // insert zuoye_student
            $this->load->model("exercise_plan/hw_classes_model");
            $student_ids = $this->hw_classes_model->get_students($data['class_id']);
            $param = array();
            if($student_ids){
                foreach ($student_ids as $stu_id){
                    $param[] = array('zy_assign_id' => $id,'user_id' => $stu_id,'id'=>0);
                }
                $result = $this->db->insert_batch($this->tab_z_stu,$param);
                //if($result){//通知//$this->hw_notice($student_ids,$param,$subject_id,$assignment_id);//}
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status() === false? false:$id;
    }

    //班级作业列表
    function class_zy($class_id,$user_id, $page=1,$pagesize=10){
        if($page<1)return null;
        $start = ($page-1)*$pagesize;
        $class_id = intval($class_id);
        $sql = "select * from {$this->tab} where class_id=$class_id and user_id = $user_id and status=1 order by assign_time desc limit $start, $pagesize";
        $l = $this->db->query($sql)->result_array();
        return $l;
    }

    //班级作业总数
    function class_zy_sum($class_id,$user_id){
        $class_id = intval($class_id);
        $sql = "select count(1) as num from {$this->tab} where class_id=$class_id and user_id=$user_id and status=1 ";
        $num = $this->db->query($sql)->row(0)->num;
        return $num;
    }

    //作业首页的各班级，搜出最新的一个作业
    function intro_zy_list($user_id){
        $sql="select * from (select * from {$this->tab} where user_id={$user_id} and status=1 and has_checked=0 order by assign_time desc ) as c GROUP BY class_id "; //assign_time
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    function del_zuoye($ass_id,$teacher_id){
        $sql = "update {$this->tab} set status=0 where id = $ass_id and user_id=$teacher_id "; 
        $this->db->query($sql);
        return  ($this->db->affected_rows() === 1)? true:false;
    }

    //检查今天是否已经布置过了作业   返回次数
    function has_assigned_today($class_id,$teacher_id){
        $today = strtotime(date('y-m-d',time()));
        $tmr = strtotime(date('y-m-d',time()))+86400;
        $sql="select count(1) as num from {$this->tab} where class_id=$class_id and user_id=$teacher_id and status=1 and start_time>=$today and start_time<$tmr";
        $num = $this->db->query($sql)->row(0)->num;
        return $num;
    }    

    //检查作业是否是该老师的，比如在 写评语前会调用下
    function is_belong($ass_id,$teacher_id){
        $sql="select count(1) as num from {$this->tab} where id=$ass_id and user_id=$teacher_id and status=1 ";
        $num = $this->db->query($sql)->row(0)->num;
        return $num;
    }

    //获取单元对应的视频 重点词汇
    function get_video_word_by_unit($unit_id){
        $sql = "select fw.* from common_unit cu right join fls_video fv on fv.unit_id=cu.id right join fls_words fw on fw.video_id=fv.id where cu.id=$unit_id";
        $res = $this->db->query($sql)->result_array();
        return $res;
    }



    
}

