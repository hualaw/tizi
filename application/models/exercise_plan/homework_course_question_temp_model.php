<?php

class Homework_Course_Question_Temp_Model extends MY_Model{
    private $_table = 'homework_course_question_temp';
    public function __construct(){
        parent::__construct();
        $this->load->model('exercise_plan/t_ex_plan_infrastructure_model','infra_model');
    }

    //通过paper_id来得到此作业的所有班级的所有信息
    function get_by_paper_id($paper_id){
        $sql = "select * from $this->_table where paper_id = $paper_id";
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    //插入临时表
    function insert_temp($p){
        // if($this->is_existed($p)){
        //     $this->del_temp($p);
        // }
        $sql = "insert into $this->_table(user_id,class_id,subject_id,course_id_depth3,course_id_depth4,online_q_ids,offline_q_ids,on_count,off_count,type_list,is_del,paper_id,start_time,end_time,remark) values({$p['user_id']},{$p['class_id']},{$p['subject_id']},{$p['course_id_depth3']},{$p['course_id_depth4']},'{$p['online_q_ids']}','{$p['offline_q_ids']}',{$p['on_count']},{$p['off_count']},'{$p['type_list']}',{$p['is_del']},{$p['paper_id']},{$p['start_time']},{$p['end_time']},'{$p['remark']}')";
        // echo $sql;die;
        $res = $this->db->query($sql);
        return $res;
    }

    //换一组online题目
    function update_temp_online_question($p){
        $sql = "update $this->_table set online_q_ids = '{$p['online_q_ids']}' , on_count = {$p['on_count']}  where class_id = {$p['class_id']} and course_id_depth4={$p['course_id_depth4']} and user_id={$p['user_id']}";
        $res = $this->db->query($sql);//echo $this->db->last_query();
        return $res;
    }
    //换一组offline题目
    function update_temp_offline_question($p){
        $sql = "update $this->_table set offline_q_ids='{$p['offline_q_ids']}' , off_count = {$p['off_count']} where class_id = {$p['class_id']} and course_id_depth4={$p['course_id_depth4']} and user_id={$p['user_id']}";
        $res = $this->db->query($sql);//echo $this->db->last_query();
        return $res;
    }

    //select question ids
    function get_q_ids($p){
        $sql = "select * from $this->_table  where class_id = {$p['class_id']} ";
        if(isset($p['course_id_depth3']) && $p['course_id_depth3']){
            $sql.=" and course_id_depth3={$p['course_id_depth3']} ";
        }
        if(isset($p['course_id_depth4']) && $p['course_id_depth4']){
            $sql.=" and course_id_depth4={$p['course_id_depth4']} ";
        }
        $sql.=" and user_id={$p['user_id']}";
        // echo $sql;
        $res = $this->db->query($sql);//echo $this->db->last_query();
        if($res->result_array()){
            $r = $res->result_array();
            return $r;
        }
        return null;
    }

    function info_by_cid_uid($p){
        $sql = "select * from $this->_table  where class_id = {$p['class_id']} ";
        if(isset($p['course_id_depth3']) && $p['course_id_depth3']){
            $sql.=" and course_id_depth3={$p['course_id_depth3']} ";
        }
        if(isset($p['course_id_depth4']) && $p['course_id_depth4']){
            $sql.=" and course_id_depth4={$p['course_id_depth4']} ";
        }
        $sql.=" and user_id={$p['user_id']}";
        // echo $sql;
        $res = $this->db->query($sql);//echo $this->db->last_query();
        if($res->result_array()){
            $r = $res->result_array();
            return $r;
        }
        return null;
    }

    //生成paper后，就把这些数据删掉
    function del_temp($p,$depth = false){
        $sql = "delete from $this->_table  where 1=1 ";
        if(isset($p['class_id'])){
            $sql.=" and class_id = {$p['class_id']} ";
        }
        if($depth){
            if(isset($p['course_id_depth3']) && $p['course_id_depth3']){
                $sql.=" and course_id_depth3={$p['course_id_depth3']} ";
            }
            if(isset($p['course_id_depth4']) && $p['course_id_depth4']){
                $sql.=" and course_id_depth4={$p['course_id_depth4']} ";
            }
        }
        $sql.=" and user_id={$p['user_id']}";
        if(isset($p['all_this_subject_id'])){
            $sql.=" and subject_id={$p['subject_id']}";
        }
        // echo $sql;die;
        $res = $this->db->query($sql);//echo $this->db->last_query();
        return $res;
    }

    function is_existed($p,$depth = false){
        $sql = "select count(1) as num from $this->_table where class_id = {$p['class_id']} ";
        if($depth){
            if(isset($p['course_id_depth3']) && $p['course_id_depth3']){
                $sql.=" and course_id_depth3={$p['course_id_depth3']} ";
            }
            if(isset($p['course_id_depth4']) && $p['course_id_depth4']){
                $sql.=" and course_id_depth4={$p['course_id_depth4']} ";
            }
        }
        $sql.=" and user_id={$p['user_id']}";
        $res = $this->db->query($sql)->row(0)->num;//echo $this->db->last_query();die;
        return $res;
    }


}