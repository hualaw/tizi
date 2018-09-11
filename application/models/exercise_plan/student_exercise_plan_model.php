<?php
/**
 * @description 寒假作业计划
 */
class Student_Exercise_Plan_Model extends MY_Model{
    
    public function __construct(){
        
        parent::__construct();
    
    }
    
    /**
     * @info 获取学生所有计划
     */
    public function getStudentAllPlans($user_id){
        return $this->db
            ->query("select * from `student_exercise_plan` where `user_id` = {$user_id}")
            ->result_array();
    }

    /**
     */
    public function getStudentPlanById($user_id,$plan_id){
        
        return $this->db
            ->query("select * from `student_exercise_plan` where `user_id` = {$user_id} and `exercise_plan_id` = {$plan_id}")
            ->row_array();
    }

    /**
     * @info 添加学生计划
     */
    public function addPlan($user_id,$plans,$assign_by){
        $created_time = time();
        if(is_array($plans)){
            $this->db->trans_start();
            foreach($plans as $plan_id){
                $status = $this->db->query("insert into `student_exercise_plan` (`user_id`,`exercise_plan_id`,`assign_by`,`created_time`) values($user_id,$plan_id,$assign_by,$created_time)");   
            }
            $this->db->trans_complete();

            if($this->db->trans_status()){
                return true;
            }
            return false;
        }else{
            return $this->db->query("insert into `student_exercise_plan` (`user_id`,`exercise_plan_id`,`assign_by`) values($user_id,$plan_id,$assign_by)");   
        }
    }

    /**
     * @info 删除学生计划
     */
    public function delPlan($user_id,$plan_id){
        return $this->db->query("update `student_exercise_plan` set `id_del` = 1 where `user_id` = {$user_id} and `exercise_plan_id` = {$plan_id}");
    }

    



}
