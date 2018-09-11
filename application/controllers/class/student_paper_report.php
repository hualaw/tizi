<?php

require(dirname(__FILE__).DIRECTORY_SEPARATOR.'student_paper_base.php');

class Student_Paper_Report extends Student_Paper_Base{

	public function __construct(){
	
		parent::__construct();

	}

	public function index($zuoye_id='', $paper_index=''){
	
        $paper_assign_id = $this->_get_assign_id($zuoye_id, $paper_index);
		if(empty($paper_assign_id)){
			redirect();
		}
		$student_paper = $this->student_paper_model->get_student_paper($this->tizi_uid, $paper_assign_id);

		$paper_id = (int)$student_paper['paper_id'];

		if(empty($student_paper)){
			redirect();
		}
		if(!$student_paper['end_time']){
			redirect();
		}

		$have_access = false;
        if($student_paper['get_answer_way'] == 2){
            if($student_paper['deadline'] < time()){
                $have_access = true;
            }       
        }else{
            $have_access = true;
        }
		$deadline = date("Y-m-d H:i", $student_paper['deadline']);
		$this->load->helper('time');
		$s_answer = unserialize($student_paper['s_answer']);

		$questions = $this->_getQuestionsByPid($paper_id);
        if($student_paper['is_shuffled'] == 2){
            $question_order = $this->_get_question_order($this->tizi_uid + $paper_assign_id, $student_paper['count']);
            $questions = $this->_question_restructing($questions, $question_order);
        }

		$online_questions_num = 0;//线上作业问题数量
		$c_question_num = 0;//问题完成数量
		$online_c_num = 0;//线上问题完成数量

		if(!empty($s_answer)){
			$s_answer = $s_answer['question'];
			foreach($questions as $q_id=>$question){
				if($question['is_online']){
					if(isset($s_answer[$q_id]) && !empty($s_answer[$q_id]['input'])){
						if($question['asw'] == $s_answer[$q_id]['input']){
							$question['status'] = 2;
						}else{
							$question['status'] = 1;
						}
						$c_question_num++;
						$online_c_num++;
						$question['input'] = $s_answer[$q_id]['input'];
					}else{
						$question['status'] = 0;
					}
					$questions[$q_id] = $question;
				}else{
					if(isset($s_answer[$q_id])){
						if($s_answer[$q_id]['q_status'] == 1){
							$questions[$q_id]['status'] = 1;
						}else{
							$questions[$q_id]['status'] = 2;
						}
						$c_question_num++;
					}else{
						$questions[$q_id]['status'] = 0;
					}
				}
			}
		}else{
			foreach($questions as $key=>$question){

				$questions[$key]['status'] = 0;

			}
			$correct_rate = 0;

		}

		if($online_c_num){
			$correct_rate = round($student_paper['correct_num']/$online_c_num,2)*100;
		}else{
			$correct_rate = 0;
		}

		$this->smarty->assign('zuoye_id', $zuoye_id);
		$this->smarty->assign('paper_index', $paper_index);
		$this->smarty->assign('paper_deadline', date("Y-m-d H:i",$student_paper['deadline']));
		$this->smarty->assign('paper_starttime', date("Y-m-d H:i",$student_paper['begin_time']));
		$this->smarty->assign('correct_num', $student_paper['correct_num']);
		$this->smarty->assign('correct_rate', $correct_rate);
		$this->smarty->assign('deadline', $deadline);
		$this->smarty->assign('have_access', $have_access);
		$this->smarty->assign('questions', $questions);

		$this->smarty->display('student/class/paper/paper_report.html');

	}


}
