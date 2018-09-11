<?php
/**
 * @author saeed
 * @date   2013-10-16
 * @description 专项练习 - 练一练
 */
class Practice_Training_Model extends MY_Model{

    public $practices = array();
    function __construct()
    {
        parent::__construct();
        $this->load->model('practice/practice_model');
    }

    /** 
     * @info 获取每日题目
     * @param $p_c_id 类型id
     * @param $num 数量
     */
    public function get_question($uid,$p_c_id,$num=5,$category_id='', $practice_done = array()){

        $table_exercise = array();
        $table_question = array();
        $table_exercise_questions = array();
        $table_question_questions = array();
        $new_questions = array();
        $rand_group = array();
        $order = array(
            'start_time'=>'desc'
        );

        //获取已做过的练习题
        //读 mysql
        /*
        $pre_practice = $this->practice_model->fetch_today_practice_by_uid($uid,$p_c_id);

        if(!empty($pre_practice)) {
            foreach($pre_practice as $pre_practice_val){
                $current_data[] = explode(",",$pre_practice_val['practice_list']);
            }
        }
        if(!empty($current_data)){
            foreach($current_data as $current_data_val){
                foreach($current_data_val as $current_data_val_val){
                    $practice_done[] = $current_data_val_val;
                }
            }
        }
         */
        //读redis
        $practice_done = array();
		$redis_data = array();
        //$redis_data = $this->practice_statistics_model->get_question_done($uid,$p_c_id);
        
        if(!empty($redis_data)){
            $practice_done = $redis_data;
        }
        /**/
        if($category_id !=''){
            $this->practice_model->category_id = $category_id;
            $practice = $this->practice_model->fetch_practice_by_cid($p_c_id,2);
            extract($this->practice_model->getQuestionsFromMultiSource($practice));
            $questions = array_merge($table_exercise_questions,$table_question_questions);
            $practice = array();
            foreach($questions as $question){
                $practice[] = $question['practice_id'];
            }
            //处理练习题数据，去除已做过的
            if(!empty($practice_done)){
                foreach($practice as $key=>$practice_val){
                    if(in_array($practice_val,$practice_done)){
                        unset($practice[$key]);
                    }
                }
            }
            if(count($practice) <$num ){
                return array();   
            }
            $pid_group = array_rand($practice,$num);
            $new_pid_group = array();
            foreach($pid_group as $pid){
                $new_pid_group[] = $practice[$pid];
            }
            $this->practices= $new_pid_group;
            foreach($questions as $question){
                if(in_array($question['practice_id'],$new_pid_group)){
                    $new_questions[] = $question;
                }
            }
            return $new_questions;
        }else{
            $practice = $this->practice_model->fetch_practice_by_cid($p_c_id,2);
            //处理练习题数据，去除已做过的
            if(!empty($practice_done)){
                foreach($practice as $key=>$practice_val){
                    if(in_array($practice_val['id'],$practice_done)){
                        unset($practice[$key]);
                    }
                }
            }
            $practice = array_values($practice);
			if(count($practice)){
				if(count($practice) < $num)
					$num = count($practice);
			}else{
                return array();   
			}
            for($i = 0;$i<$num;$i++){
                $rand_val= rand(0,count($practice)-1);
                $practice_group[] = $practice[$rand_val]; 
                if(!empty($practice[$rand_val]['id'])){
                    $this->practices[]= $practice[$rand_val]['id'];
                }
                unset($practice[$rand_val]);
                $practice = array_values($practice);
            }
            extract($this->practice_model->getQuestionsFromMultiSource($practice_group));
            $new_questions = array_merge($table_exercise_questions,$table_question_questions);
        }
        return $new_questions;
        
    }
    






}
