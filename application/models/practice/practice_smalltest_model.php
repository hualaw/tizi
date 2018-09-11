<?php
/**
 * @author saeed
 * @date   2013-10-16
 * @description 专项练习 - 小试牛刀
 */
require_once(__DIR__."/practice_model.php");
class Practice_Smalltest_Model extends MY_Model{

	private $_redis=false;
    public $practices;

    const PROPORTION = 2;

    function __construct()
    {
        parent::__construct();
        $this->load->model('practice/practice_model');
    }
    
    public function get_question($uid,$p_c_id){

        $singal_time_1 = time();
        //语文简答题比例
        $chinese_proportion = array(
            8,9,10,12
        );
        $table_exercise = array();
        $table_question = array();
        $question_types = array();
        $table_exercise_questions = array();
        $table_question_questions = array();
        $new_questions_group = array();
        $current_data = array();
        $practice_done = array();
        $questions = array();
        $order = array(
            'start_time'=>'desc'
        );

        $practice_small_test = $this->get_small_test_info($p_c_id);
        //题型比例
        if(isset($practice_small_test['proportion']) && !empty($practice_small_test['proportion'])){
            $proportion = json_decode($practice_small_test['proportion'],true);
        }else{
            return array();
        }
        foreach($proportion as $proportion_val){
            $question_types[] = $proportion_val['question_type']; 
        }

        //获取已做过的练习题
        //$pre_practice = $this->practice_model->fetch_today_practice_by_uid($uid,$p_c_id);

        //读mysql
        /*
        foreach($pre_practice as $pre_practice_val){
            $current_data[] = explode(",",$pre_practice_val['practice_list']);
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
        $redis_data = $this->practice_statistics_model->get_today_done($uid,$p_c_id);
        
        if(!empty($redis_data)){
            $practice_done = $redis_data;
        }
        /**/
        $practice = $this->practice_model->fetch_practice_by_cid($p_c_id,1);
        if(!empty($pre_practice)){
            foreach($practice as $key=>$practice_val){
                if(in_array($practice_val['id'],$practice_done)){
                    unset($practice[$key]);
                }
            }
        }
        extract($this->practice_model->getQuestionsFromMultiSource($practice));
        //echo time()-$singal_time_1;
        //exit;       
        $new_questions = array_merge($table_exercise_questions,$table_question_questions);
        foreach($new_questions as $new_questions_val){
            $new_questions_group[$new_questions_val['qtype_id']][] = $new_questions_val;
        }
        //根据比例获取题目
        foreach($proportion as $proportion_val){
            $question_type = $proportion_val['question_type'];

            $question_num = $proportion_val['proportion'] * self::PROPORTION;
            //判断选择题是否小于5道
            if($question_type == 3){
                if(!isset($new_questions_group[3]) || count($new_questions_group[3]) < $question_num){
                    return array();
                }
            }
            $q_num_group = array();
            if(isset($new_questions_group[$question_type]) || $question_type ==0 ){
                $current_question = array();
                if($question_type == 0){
                    foreach($chinese_proportion as $chinese_proportion_val){
                        if(isset($new_questions[$chinese_proportion_val])){
                            $current_question = array_merge($current_question,$new_questions_group[$chinese_proportion_val]);
                        }
                    }
                }else{
                    $current_question = $new_questions_group[$question_type];
                }
                $current_question = array_values($current_question);
                $questions[$question_type] = array();
                if(!(count($current_question) < $question_num)){
                    for($i = 0;$i<$question_num;$i++){
                        $q_num = rand(0,count($current_question)-1);
                        if(!isset($questions[$question_type])){
                            $questions[$question_type] = array();
                        }
                        
                        $questions[$question_type][] = $current_question[$q_num]; 
                        if(!empty($current_question[$q_num]['practice_id'])){
                            $this->practices[]= $current_question[$q_num]['practice_id'];
                            unset($current_question[$q_num]);
                        }
                        $current_question = array_values($current_question);
                    }
                }else{
                    $questions[$question_type] = $current_question;
                }
            }else{
                $questions[$question_type] = array();      
            }
        }
        return $questions;
    }

    public function get_small_test_info($cid){
         $practice_small_test = $this->db
            ->query("select * from `practice_small_test` where `p_c_id` = {$cid}")
            ->row_array();   
         return $practice_small_test;
    }

    //获取问题类型
    public function get_question_type($ids){
        $ids_str = implode(",",$ids);
        $result = $this->db
            ->query("select `name` from `question_type` where `id` in ({$ids_str})")
            ->result_array();
        return $result;
    }



}
