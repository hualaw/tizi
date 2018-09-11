<?php

require(dirname(__FILE__).DIRECTORY_SEPARATOR.'student_paper_base.php');

class Student_Paper_Do extends Student_Paper_Base{

	private $redis;
	private $_smarty_dir = 'student/class/paper/';

	public function __construct(){

		parent::__construct();

		if(!($this->redis = $this->student_paper_model->connect_redis())){
			log_message('error_tizi','redis connect faild.module:do_homework');
		}

	}

	/**
	 * @info 写作业首页
	 */
	public function index($zuoye_id, $paper_index){

        if (!$zuoye_id || !$paper_index) redirect('student/class/homework');
        if(!($paper_assign_id = $this->_get_assign_id($zuoye_id, $paper_index)))
            redirect('student/class/homework');
		if(!$this->redis){
			$this->session->set_flashdata("errormsg",$this->lang->line('sh_wrong_homework_tip1'));
			redirect();
		}
		if(empty($paper_assign_id)){
			redirect();
		}
		$student_paper = $this->student_paper_model->get_student_paper($this->tizi_uid, $paper_assign_id);
		if(!isset($student_paper) || empty($student_paper)){
			redirect();
		}
        if (empty($this->_zuoye['start_time'])) {
            $this->student_zuoye_model->update($zuoye_id,
                array('start_time'=>time())
            );       
        }
        $paper_id = (int)$student_paper['paper_id'];
		if($student_paper['end_time']){
			redirect('student/homework/paper/report/'.$zuoye_id.'/'.$paper_index);
		}
		if(!$student_paper['start_time']){
			$this->student_paper_model->update($student_paper['id'],array('start_time'=>time()));
		}
		$questions = $this->_getQuestionsByPid($paper_id);
        if($student_paper['is_shuffled'] == 2){
            $question_order = $this->_get_question_order($this->tizi_uid + $paper_assign_id, $student_paper['count']);
            $questions = $this->_question_restructing($questions, $question_order);
        }
		$offline_questions = array();

		$temporary_paper_info['deadline'] = $student_paper['deadline'];
		if(!$this->redis->exists($this->tizi_uid."_".$paper_assign_id)){
			//初始化
			$temporary_paper_info['start_time'] = time();
			$temporary_paper_info['expend_time'] = 0;
			$temporary_paper_info['second_time'] = 0;
			$temporary_paper_info['s_answer'] = serialize(array());
			$temporary_paper_info['is_break'] = 0;
			$temporary_paper_info['expend_time'] = 0;
			$temporary_paper_info['deadline'] = $student_paper['deadline'];
			$temporary_paper_info['questions'] = serialize(array_keys($questions));
			$this->redis->hmset($this->tizi_uid."_".$paper_assign_id, $temporary_paper_info);
		}else{
			$temporary_paper_info = $this->redis->hgetall($this->tizi_uid."_".$paper_assign_id);
		}
		if($temporary_paper_info['is_break']){
			$this->goon($this->tizi_uid."_".$paper_assign_id);
		}

		//$this->smarty->assign('s_paper_id', $student_paper['id']);
		$this->smarty->assign('homework_starttime', date("Y-m-d H:i", $student_paper['begin_time']));
		$this->smarty->assign('homework_deadline', date("Y-m-d H:i", $student_paper['deadline']));
		$this->smarty->assign('paper_assign_id', $paper_assign_id);
		$this->smarty->assign('zuoye_id', $zuoye_id);
		$this->smarty->assign('paper_index', $paper_index);
		$this->smarty->assign('questions',$questions);
		$this->smarty->assign('question_done_num',count(unserialize($temporary_paper_info['s_answer'])));
		$this->smarty->display($this->_smarty_dir.'do_paper.html');

	}

	/**
	 * @info 作业提交
	 */
	public function submit(){

		if(!$this->tizi_uid){
			$this->_info_handle(1,'success');
		}
		$this->_redis_connect_check();
        $paper_assign_id = $this->_assign_id();
        $zuoye_assign = $this->_zuoye_assign_id();//获取zuoye_assign表中的id
        $zuoye_assign_id = $zuoye_assign['zy_assign_id'];
		$info = $this->student_paper_model->get_student_paper($this->tizi_uid , $paper_assign_id);
		//获取老师id 和 认证信息 2014-07-23
		$teacher_id = $info['teacher_id'];
		$this->load->model('login/register_model');
		$teacher_is_cert = $this->register_model->get_user_info($teacher_id,0,'certification');
		if(isset($teacher_is_cert['user']->certification)){
			$teacher_is_cert = $teacher_is_cert['user']->certification?1:0;
		}else{
			$teacher_is_cert = 0;
		}

		$paper_id = $info['paper_id'];
		if($info['end_time'] != 0){
			$this->session->set_flashdata("errormsg",$this->lang->line('sh_wrong_homework_tip3'));
			redirect();
		}

		$questions = $this->_getQuestionsByPid($paper_id);

		$temporary_paper_info = $this->redis->hgetall($this->tizi_uid."_".$paper_assign_id);
		$s_answer = unserialize($temporary_paper_info['s_answer']);//线上

		$correct_num = 0;//正确数量
		$online_done_num = 0; //线上作业答题数
		$offline_done_num = 0;//线下作业答题数
		$online_count = 0;

		foreach($questions as $q_id=>$question){

			if($question['is_online']){
				$online_count++;
				if(isset($s_answer[$q_id])){
					$online_done_num++;   
					if($s_answer[$q_id]['input'] == $question['asw']){
						$correct_num++;
					}
				} 
			}else{
				if(isset($s_answer[$q_id])){
					$offline_done_num++;   
				}
			}
		}
		//选择题必须全部完成
		if($online_count > $online_done_num){
			$this->_info_handle(2, '请完成所有选择题后再提交'); 
		}
		foreach($s_answer as $s_an_k => $s_an_val){
			unset($s_answer[$s_an_k]['question_id']);
		}
		$db_s_answer['question'] = $s_answer;

		if($temporary_paper_info['is_break']){
			$expend_time = $temporary_paper_info['expend_time'];
		}else{
			if($temporary_paper_info['second_time']){
				$expend_time = $temporary_paper_info['expend_time'] + (time()-$temporary_paper_info['second_time']);
			}else{
				$expend_time = time()-$temporary_paper_info['start_time'];
			}
		}
        $zuoye_db['correct_num'] = $this->_zuoye['correct_num'] + $correct_num;
        $zuoye_db['question_num'] = $this->_zuoye['question_num'] + $online_done_num;
        if (!$this->student_zuoye_model->update($this->_zuoye['id'], $zuoye_db)) $this->_info_handle(1, 'faild');
		$data = array('expend_time'=>$expend_time,'end_time' => time(),'s_answer'=>serialize($db_s_answer),'is_completed'=>1,'correct_num'=>$correct_num,'online_done_num'=>$online_done_num,'offline_done_num'=>$offline_done_num);

		if($this->student_paper_model->update($info['id'], $data)){
            $this->student_zuoye_model->updateCompleteStatus($this->tizi_uid, $this->_zuoye['id']);
			$this->redis->del($this->tizi_uid."_".$paper_assign_id);
			//提交试卷包作业成功，老师获得积分   2014-08-15
			$this->load->library("credit");
			$score = $this->credit->exec($teacher_id, "upsubmit_homework", $teacher_is_cert,'',array($this->tizi_uid));
			$this->load->model('homework/zuoye_intro_model');
			$zuoye_id = $this->input->post('homework_id', true);
            $this->zuoye_intro_model->change_assign_score($zuoye_assign_id,$score);

			$this->_info_handle(99, $paper_assign_id); 
		}else{
			$this->_info_handle(1,'faild'); 
		}

	}

	/**
	 * @info 暂停
	 */
	public function pause(){
		$this->_redis_connect_check();
        if (!($paper_assign_id = $this->_assign_id())) {
		    $this->_info_handle(1, 'faild');
        }
		$key = $this->tizi_uid."_".$paper_assign_id;
		$info = $this->redis->hgetall($key);
		if($info['is_break']){
			$this->_info_handle(99, 'success');
		}
		$expend_time = $this->_get_expend_time($key);
		$this->redis->hset($key,'is_break',1);
		$this->redis->hset($key,'expend_time',$expend_time);
		$this->_info_handle(99, 'success');
	}

	/**
	 * @info 线上作业做题
	 */
	public function online_question_save(){

		$this->_redis_connect_check();
        $paper_assign_id = $this->_assign_id();
		if($this->input->post('q_id')){
			$question_id = intval($this->input->post('q_id'));
		}else{
			$this->_info_handle('1','faild');
		}
		if($this->input->post('input') && in_array($_POST['input'],$this->question_option)){
			$input = $this->input->post('input', true);
		}else{
			$this->_info_handle('1','faild');
		}
		$temporary_paper_info = $this->redis->hgetall($this->tizi_uid."_".$paper_assign_id);
		if(empty($temporary_paper_info)) $this->_info_handle('1','faild');
		$questions = unserialize($temporary_paper_info['questions']);
		if (!in_array($question_id, $questions)){
			$this->_info_handle('1','faild');
		}
		$answer = unserialize($temporary_paper_info['s_answer']);
		$status = true;
		if(!empty($answer)){
			if(isset($answer[$question_id])){
				$answer[$question_id]['input'] = $input;
				$status = false;
			}else{
				foreach($answer as $answer_key=>$answer_val){
					if($question_id == $answer_val['question_id']){
						$answer[$answer_key]['input'] = $input;
						$status = false;
					}
				}
			}
		}
		if($status){
			$answer[$question_id] = array('question_id'=>$question_id,'input'=>$input);
		}
		if($this->redis->hset($this->tizi_uid."_".$paper_assign_id,'s_answer', serialize($answer))){
			$this->_info_handle(99,'success');
		}else{
			$this->_info_handle(99,'success');
		}

	}

	//报告页面线下作业提交
	public function paperwork_question_submit(){

		$this->_redis_connect_check();
        $paper_assign_id = $this->_assign_id();
		if($this->input->post('q_id')){
			$question_id = intval($this->input->post('q_id'));
		}else{
			$this->_info_handle('1','faild');
		}

		if($this->input->post('q_status')){
			$q_status = intval($this->input->post('q_status', true));
			if(!in_array($q_status, array(1, 2))){
				$this->_info_handle('1','faild');
			}
		}else{
			$this->_info_handle('1','faild');
		}
		$student_paper = $this->student_paper_model->get_student_paper($this->tizi_uid, $paper_assign_id);
		if(empty($student_paper)) $this->_info_handle('1','faild');
		if(!$student_paper['end_time']){
			$this->_info_handle('1','faild');
		}
		$paper_id = (int)$student_paper['paper_id'];
		$s_answer = unserialize($student_paper['s_answer']);
		$questions = $this->_getQuestionsByPid($paper_id);
		if(in_array($question_id,array_keys($questions))){
			if(!isset($s_answer['question'][$question_id])){
				$s_answer['question'][$question_id] = array(
					'q_status'=>$q_status,
				);
			}else{
				$this->_info_handle(1,'faild'); 
			}           
		}else{
			$this->_info_handle(1,'faild'); 
		}

		$offline_done_num = $student_paper['offline_done_num']+1;
		if($this->student_paper_model->update($student_paper['id'], array('s_answer'=>serialize($s_answer),'offline_done_num'=>$offline_done_num))){
			$this->_info_handle(99,'success'); 
		}else{
			$this->_info_handle(1,'faild'); 
		}

	}

	/**
	 * @info 获取答题信息
	 */
	public function get_answer(){

		$this->_redis_connect_check();

		$result = array();
		$paperwork_question = array();
		$online_question = array();

		$online_answer = array();
        $zuoye_id = $this->input->get('homework_id', true);
        $paper_index = $this->input->get('paper_id', true);
        if(!$zuoye_id || !$paper_index) $this->_info_handle(1,'faild');
        if(!($paper_assign_id = $this->_get_assign_id($zuoye_id, $paper_index))) 
            $this->_info_handle(1,'faild');
        $paper_assign_id = $this->_get_assign_id($zuoye_id, $paper_index);   
		$student_paper = $this->student_paper_model->get_student_paper($this->tizi_uid, $paper_assign_id);
		$paper_id = $student_paper['paper_id'];
		$questions = $this->_getQuestionsByPid($paper_id);
		$info = $this->redis->hgetall($this->tizi_uid."_".$paper_assign_id);
		$online_answer = unserialize($info['s_answer']);
		foreach($questions as $question){

			if(!empty($online_answer) && isset($online_answer[$question['id']])){
				if($question['is_online']){
					$db_info = $online_answer[$question['id']];
					if(empty($db_info['input']))continue;
					if($db_info['input'] == 'A'){
						$db_info['index'] = 1;
					}elseif($db_info['input'] == 'B'){
						$db_info['index'] = 2;
					}elseif($db_info['input'] == 'C'){
						$db_info['index'] = 3;
					}else{
						$db_info['index'] = 4;
					}
					$online_question[$question['id']] = $db_info;
				}else{
					$paperwork_question[] = $question['id'];
				}
			}
		}

		$result['online'] = $online_question;
		$result['offline'] = $paperwork_question;

		$this->_info_handle(99,json_encode($result));

	}

	/**
	 * @info 获取作业用时
	 */
	protected function _get_expend_time($key){
		$this->_redis_connect_check();

		$temporary_homework_info = $this->redis->hgetall($key);
		if($temporary_homework_info){
			if($temporary_homework_info['is_break']){
				$time = $temporary_homework_info['expend_time'];
			}else{
				if(isset($temporary_homework_info['second_time']) && $temporary_homework_info['second_time']){
					$time = $temporary_homework_info['expend_time']+time()-$temporary_homework_info['second_time'];
				}else{
					$time = time()-$temporary_homework_info['start_time']; 
				}
			}
			return $time;
		}
	}

	/**
	 * @info 继续
	 */
	private function goon($key){
		$this->redis->hset($key,'is_break',0);
		$this->redis->hset($key,'second_time',time());
	}

	private function _redis_connect_check(){
		if(!$this->redis){
			$this->_info_handle(1,'faild');
		}
	}

    private function _assign_id() {

        $zuoye_id = $this->input->post('homework_id', true);
        $paper_index = $this->input->post('paper_id', true);
        if(!$zuoye_id || !$paper_index) $this->_info_handle(1,'faild');
        if(!($paper_assign_id = $this->_get_assign_id($zuoye_id, $paper_index))) 
            $this->_info_handle(1,'faild');
        return $this->_get_assign_id($zuoye_id, $paper_index);   
    }

    private function _zuoye_assign_id(){
    	$zuoye_id = $this->input->post('homework_id', true);
        $paper_index = $this->input->post('paper_id', true);
        if(!$zuoye_id || !$paper_index) $this->_info_handle(1,'faild');
        if(!($data = $this->_get_zuoye_assign_id($zuoye_id, $paper_index))) 
            $this->_info_handle(1,'faild');
        return  $data;
    }






}

/*end of do_homework.php*/
