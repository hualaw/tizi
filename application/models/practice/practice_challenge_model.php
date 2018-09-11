<?php
/**
 * @author saeed
 * @date   2013-10-16
 * @description 专项练习 - (小学数学)
 */
class Practice_Challenge_Model extends MY_Model{

    private $p_c_id;
    private $pre_pid_group = array();
    function __construct()
    {
        parent::__construct();
        $this->load->model('practice/practice_model');
    }
    /**
     * @info 获取问题
     */
    public function get_question($uid,$p_c_id,$num){
        $pre_practice_group = array();
        $question = array();
        $option = array();
        $pre_practice = $this->get_pre_question();
        $this->p_c_id = $p_c_id;
        $resources = $this->practice_model->get_resources($p_c_id);
        foreach($resources as $key=>$resources_val){
            $answer_group[] = $resources_val['answer'];
            if(!in_array($resources_val['id'],$this->pre_pid_group)){
                $resource_group[] = $key;
            }
        }
        $answer_group = array_unique($answer_group);
        if(count($resource_group)<10){
            return array();
        }
        if($num !=''){
            $resource_keys = array_rand($resource_group,$num);
            foreach($resource_keys as $resource_key){
                $key = $resource_group[$resource_key];
                $new_resouce_group[] = $key;
            }
            $resource_group = $new_resouce_group;
        }else{
            shuffle($resource_group);
        }
        foreach($resource_group as $key){
            $option = array();
            $current_answer_group = $answer_group;
            unset($current_answer_group[$key]);
            array_values($current_answer_group);
            $answer_key = array_rand($current_answer_group,2);
            foreach($answer_key as $answer_key_val){
                $option[] = $current_answer_group[$answer_key_val]; 
            }
            $option[] = $resources[$key]['answer'];
            shuffle($option);
            $question[] = array(
                'question'=>$resources[$key]['question'],
                'answer'=>$resources[$key]['answer'],
                'option'=>$option,
            );
        }
        return $question;

    }
    
    private function get_pre_question(){
        $serialize_group = array();
        $this->_redis = $this->practice_model->connect_redis('practice');
        $pre_practice = $this->_redis->keys("challenge_".$this->uid.'_'.$this->p_c_id."*");
        foreach($pre_practice as $pre_practice_val){

            $practice_info = $this->_redis->hgetall($pre_practice_val);
            if($practice_info['status']){
                $question_str = $practice_info['question'];
                $questions = json_decode($question_str,true);
                foreach($questions as $question){
                    $serialize_group[] = $question['question'];       
                    $this->pre_pid_group[] = $question['question']['id'];
                }
            }
        }
        return $serialize_group;
    }



}
