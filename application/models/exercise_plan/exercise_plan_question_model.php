<?php
/**
 * @description 寒假作业计划-问题
 *
 */
class Exercise_Plan_Question_Model extends MY_Model{
    
    public function __construct(){
        
        parent::__construct();
    }

    //获取问题
    public function get_questions($uid,$plan_id){
        
        $pre_questions = $this->get_pre_questions();
        $plan_questions = $this->db
            ->query("select * from `exercise_plan_question` where `exercise_plan_id` = {$plan_id} ")
            ->result_array();
        foreach($plan_questions as $plan_question){
        
            //handle
        }
        

    }

    /**
     * @info 根据id 获取 exercise
     */
    public function getExerciseQuestionsById($id){
        $sql_ext ='';
        if(isset($this->category_id)&&!empty($this->category_id)){
            $sql_ext = " and b.`category_id`= ".$this->category_id;
        }
        if(is_array($id)){
            $ids = implode(",",$id);
            $result = $this->db->query("select a.*,b.`category_id` from `exercise` as a left join `exercise_category` as b on a.`id` = b.`question_id` where a.`id` in ( ".$ids." ) {$sql_ext}")->result_array();
        }else{
            $result = $this->db->query("select * from `exercise` where `id` =  {$id}")->row_array();

        }
        return $result;
    }

    /**
     * @info 根据id 获取 question 
     */
    public function getQestionQuestionsByIds($ids){
        $sql_ext ='';
        if(isset($this->category_id)&&!empty($this->category_id)){
            $sql_ext = " and b.`category_id`= ".$this->category_id;
        }
        if(is_array($ids)){
            $ids = implode(",",$ids);
            $result = $this->db->query("select a.*,b.`category_id` from `question` as a left join `question_category` as b on a.`id` = b.`question_id` where a.`id` in ( ".$ids." ) {$sql_ext}")->result_array();
        }else{
            $result = $this->db->query("select * from `question` where `id` =  {$id}")->row_array();

        }
        return $result;    
    }

    //获取已做过的题目
    private function get_pre_questions(){
               
        return array();
    }






}

