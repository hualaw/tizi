<?php

/**
 * @author saeed
 * @date   2013-8-30
 * @description 专项自主练习-练一练
 */

require(dirname(__FILE__).DIRECTORY_SEPARATOR.'practice_base.php');
class Practice_training extends Practice_Base{

    public function __construct(){
        parent::__construct();
        $this->load->model('practice/practice_training_model');
        $this->_practice_redis = $this->practice_model->connect_redis('practice');
    }

    /**
     * @info 首页 
     */
    public function index($p_c_id=1){

		$this->set_basic_info($p_c_id);
		$this->ranking_list($p_c_id);
        $this->smarty->display($this->_smarty_dir.'practice_training_home.html');

    }

    public function entry($p_c_id=''){

        if(!$this->_practice_redis){
           $this->session->set_flashdata("errormsg",$this->lang->line('sh_redis_connect_faild'));
            redirect("practice_test/$p_c_id");
        }
        $practice_info = array();

        if(isset($p_c_id) && !empty($p_c_id)){
            $p_c_id = (int)$p_c_id;
        }else{
            redirect('practice/1');
        }

		$id = $this->generate_id($p_c_id);

		$temporary_homework_info['questions'] = json_encode(array());
		$temporary_homework_info['question_index'] = 1;
		$temporary_homework_info['status'] = 1;

        $this->_practice_redis->hmset($id,$temporary_homework_info);
		if($this->tizi_uid){
			$this->_practice_redis->expire($id, 24*60*60);
		}else{
			$this->_practice_redis->expire($id, 1*60*60);
		}
		$questions = $this->generate_question($id, $p_c_id);
		if(count($questions) < 5) { 

			$this->session->set_flashdata("errormsg", 
				$this->lang->line('sp_no_question'));
			redirect('practice/training/'.$p_c_id);

		}

		if($this->tizi_role == 'student'){
			$this->update_participants_stats($p_c_id);
		}
		
        redirect("practice/training/do/{$id}");
        $this->smarty->display($this->_smarty_dir."practice_do.html");

    }

    public function test($training_id){

        if(!isset($training_id) || empty($training_id)){
            redirect();
        }
		$p_c_id = $this->p_c_id($training_id);
		$this->set_basic_info($p_c_id);
		$this->ranking_list($p_c_id);

        $this->practice_model->connect_redis('practice');
		$training_record = $this->_practice_redis->hgetall($training_id);
		if (empty($training_record) || !$training_record['status']) redirect(); 

		$questions = json_decode($training_record['questions'], true);
		$question = $questions[$training_record['question_index']-1];
		
        $this->smarty->assign('question',$question);
        $this->smarty->assign('correct_num',$training_record['question_index']-1);
        $this->smarty->assign('training_id', $training_id);
        $this->smarty->display($this->_smarty_dir."practice_do.html");

    }

	public function next_question(){
		
		$id = $this->input->post('id');
		$option = $this->input->post('option');
		if(!$id || !$option) $this->_info_handle(1, 'faild');
		$p_c_id = $this->p_c_id($id);

		$p_c_info = $this->practice_model->get_category_info($p_c_id);
		if(empty($p_c_info)) $this->_info_handle(1, 'faild');
		$training_record = $this->_practice_redis->hgetall($id);

		//if(!$training_record['status']) $this->_info_handle(4, 'over');
		$questions = json_decode($training_record['questions'], true);
		$question = $questions[$training_record['question_index']-1];
		if(strtolower(trim($question['asw'])) != strtolower(trim($option))){
			$this->_practice_redis->hset($id, 'status', 0);
			if($this->tizi_uid && $this->tizi_role == 'student'){
				$this->update_ranking_list($p_c_id, $training_record['question_index'] -1 );
			}
			$this->_info_handle(2, 'over');
		}
		
		if(count($questions) == $training_record['question_index']){
			$questions = $this->generate_question($id, $p_c_id);
			if(!empty($questions)){
				$question = $questions[0];
			}else{
				$question = array();
			}
		}else{
			$question = $questions[$training_record['question_index']];
		}
		
		if(!empty($question)){
			$question_index = $training_record['question_index'] + 1;
			$this->_practice_redis->hset($id, 'question_index', $question_index);
			$this->_info_handle(99, $question['title']);

		}else{

			$this->_practice_redis->hset($id, 'status', 0);
			if($this->tizi_uid && $this->tizi_role == 'student'){
				$this->update_ranking_list($p_c_id, $training_record['question_index'] -1 );
			}
			$this->_info_handle(3, 'success');

		}
		
	}

	public function complete($training_id){
		
		$training_record = $this->_practice_redis->hgetall($training_id);
		$p_c_id = $this->p_c_id($training_id);
		$this->set_basic_info($p_c_id);
		$this->ranking_list($p_c_id);
		$this->smarty->assign('correct_num', $training_record['question_index']-1);
		$this->smarty->assign('p_c_id', $p_c_id);
		$this->smarty->assign('training_id', $training_id);
		$this->smarty->display($this->_smarty_dir."complete.html");

	}

    private function _get_questions($pids){
        $questions = $this->practice_model->getQuestionsByPids($pids);
        return $questions[3];
    }

	private function generate_question($id = '', $p_c_id = ''){
		$this->practice_model->connect_redis('practice');
		$training_record = $this->_practice_redis->hgetall($id);
		$pids = array();
		if (!empty($training_record)){
		
			$redis_questions = json_decode($training_record['questions'], true);
			foreach($redis_questions as $question){
				$pids[] = $question['practice_id'];
			}
			$this->practice_training_model->get_question($this->tizi_uid,$p_c_id, 5, '', $pids);
			$pids = $this->practice_training_model->practices;
			$questions = $this->_get_questions($pids);
			if (empty($redis_questions)){
				$redis_questions = $questions;
			}else{
				$redis_questions = array_merge($redis_questions, $questions);
			}
			$this->practice_model->connect_redis('practice');
			$this->_practice_redis->hset($id, 'questions', json_encode($redis_questions));

			return $questions;

		}

	}

	private function p_c_id($id){

		return hexdec(array_pop(explode('-', $id)));

	}

	private function update_ranking_list($p_c_id, $correct_num){
		
		$redis = $this->practice_model->connect_redis('practice_statistics');
		if($redis){
			$key = 'special_stats_'.$p_c_id;
			$score = $redis->zscore($key,$this->tizi_uid);
			if($correct_num > $score){
				$redis->zrem($key,$this->tizi_uid);
				$redis->zadd($key, $correct_num, $this->tizi_uid);
			}
		}

	}

	private function generate_id($p_c_id){
	
		$id = md5(time().$this->tizi_uid);
		$id = substr($id, 0, 10).'-'
			.substr($id, 10, 6).'-'
			.substr($id,16, 6).'-'
			.substr($id,22,5).'-'
			.dechex($p_c_id);
		return $id;

	}



}
