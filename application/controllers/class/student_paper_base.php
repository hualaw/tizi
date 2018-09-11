<?php

require(dirname(__DIR__).DIRECTORY_SEPARATOR.'Controller.php');

class Student_Paper_Base extends Controller{

	protected $question_option = array('A','B','C','D');

	protected $online_questions = array();

	protected $online_question_num;

	protected $offline_question_ids = array();

	protected $online_question_ids = array();

	protected $online_questions_answer = array();

	protected $asw_none = array();//无asw

    protected $_zuoye;


	public function __construct(){

		parent::__construct();   

		$this->load->model("exercise_plan/student_homework_model");
		$this->load->model("exercise_plan/student_paper_model");
        $this->load->model('homework/student_zuoye_model');

	}

	protected function _getQuestionsByPid($paper_id){

		$question_info = $this->get_paper($paper_id);
		//print_r($question_info);
		$online_question = $question_info['paper_question'][1];
		$offline_question = $question_info['paper_question'][2];
		$questions = array();

		$question_origin = $question_info['question_origin'];
		$s_questions = array();
		foreach($question_info['question'] as $question_info_val){
			if(!empty($question_info_val) && is_array($question_info_val)){
				foreach($question_info_val as $key => $question){
					$s_questions[$key] =  $question;
				}
			}
		}
        if($online_question){
            foreach($online_question as $online_question_val){
                if(empty($online_question_val)) continue;
                foreach($online_question_val as $p_q_key=>$paper_question_id){
                    if(isset($s_questions[$paper_question_id])){
                        $question = $s_questions[$paper_question_id];
                        /*
                         * user upload
                         */
                        if($question_origin[$p_q_key] == 1){
                            $question->body_text = $question->body;
                            $question->asw = $question->answer;
                            $question->analysis_text = $question->analysis;
                        }
                        if(!in_array(trim($question->asw),array('A','B','C','D'))){
                            $this->asw_none[] = $question;
                            continue;
                        }
                        //$question->body = $question->body_text;//text to img
                        #$question->analysis = $question->analysis_text;//text to img
                        #if(preg_match("/.*解析.*/",$question->analysis)){
                        #    $question->analysis_tip = false;
                        #}else{
                        #    $question->analysis_tip = true;
                        #}
                        $this->online_questions_answer[$paper_question_id] =$question->asw;
                        $question = (array)$question;
                        $question['is_online'] = 1;
                        //$question['answer'] = $question['asw'];//text to img
                        //$questions[$paper_question_id] = $this->student_homework_model->separateQuestion($question);
                        $questions[$paper_question_id] = $question;
                    }
                }
            }
        }
        
		$this->online_question_num = count($questions);
        if($offline_question){
            foreach($offline_question as $offline_question_val){
                if(!empty($offline_question_val) && is_array($offline_question_val)){
                    foreach($offline_question_val as $p_k_key=>$paper_question_id){

                        if(isset($s_questions[$paper_question_id])){
                            $question = $s_questions[$paper_question_id];
                            if($question_origin[$p_k_key] == 1){
                                //$question->body_text = $question->body;
                                $question->asw = $question->answer;
                                $question->analysis_text = $question->analysis;
                            }
                            $question = (array)$question;
                            //$question['body'] = $question['body_text'];//text to img
                            //$question['answer'] = $question['asw'];//text to img
                            $question['is_online'] = 0;
                            //$question = $this->student_homework_model->paperQuestionHandle($question);//text to img
                            $questions[$paper_question_id] = $question; 
                            $this->offline_question_ids[] = $paper_question_id;
                        }
                    }
                }
            }
        }
		

		foreach($this->asw_none as $question){
			$question = (array)$question;
			//$question['body'] = $question['body_text']; //text to img
			$question['is_online'] = 0;
			//$question = $this->student_homework_model->paperQuestionHandle($question); //text to img
			$questions[$question['id']] = $question; 
			$this->offline_question_ids[] = $paper_question_id;
		}
		return $questions;

	}

    protected function _get_question_order($random_num, $question_num){
        
        if(!$question_num) return array();
        if(!$random_num) return range(0, $question_num-1);
        $question_list = range(0, $question_num-1);
        $enc_string = substr(md5($random_num),0,10);
        if(!($random_num % 2))
            $enc_string = strrev($enc_string);
        $rand_string = (String)hexdec(substr($enc_string, 0, 5).
            substr($enc_string, strlen($enc_string)-6,5));
        $question_order = array();
        for($i = 0; $i < ceil($question_num / 10); $i++){
            $slice_area = array_slice($question_list, $i * 10, 10);      
            if(empty($slice_area)) continue;
            for($j = 0; $j < intval(strlen($rand_string)/2); $j++){
                if(isset($slice_area[$rand_string[$j]])){
                    $l_index = $rand_string[$j];
                }else{
                    $l_index = 0;
                }
                if(isset($slice_area[$rand_string[strlen($rand_string)-1-$j]])){
                    $r_index = $rand_string[strlen($rand_string)-1-$j];
                }else{
                    $r_index = count($slice_area)-1;
                }
                list($slice_area[$l_index], $slice_area[$r_index]) =
                    array($slice_area[$r_index], $slice_area[$l_index]);
            }
            $question_order = array_merge($question_order, $slice_area);
        }
        return $question_order;

    }

    protected function _question_restructing($questions, $question_order){

        $questions = array_values($questions);
        $questions_new = array();
        foreach($question_order as $order_val){
            $questions_new[$questions[$order_val]['id']] = $questions[$order_val];
        }
        return $questions_new;
    }

	protected function _info_handle($status, $msg){

		$text = json_token(array('status'=>$status,'msg'=>$msg));
		exit($text);

	}

    protected function _get_assign_id($zuoye_id, $paper_index) {

        $data = $this->student_zuoye_model->get(array('zuoye_student.id'=>$zuoye_id));
        $student_zuoye = array();
        if (isset($data[0]) && !empty($data[0])){
            $student_zuoye = $data[0];
            $this->_zuoye = $student_zuoye;
        }else{
            return false;
        }
        $paper_ids = $student_zuoye['paper_ids'];
        if (empty($paper_ids)) {
            return false;
        }
        $papers = json_decode($paper_ids, true);
        if (isset($papers[$paper_index-1]) && !empty($papers[$paper_index-1])) {
            return $papers[$paper_index-1]['assignment_id'];
        }

        return false;


    }

    protected function _get_zuoye_assign_id($zuoye_id, $paper_index) {
        $data = $this->student_zuoye_model->get(array('zuoye_student.id'=>$zuoye_id));
        if($data){
            return $data[0];
        }
        return null;
    }




}

