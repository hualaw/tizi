<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Game extends MY_Controller {

	protected $_smarty_dir = 'student/class/game/';

	public function __construct(){

		parent::__construct();

		$this->load->model('homework/game_model');
        $this->load->model('homework/student_zuoye_model');
		$this->load->library('qiniu');
		$this->qiniu->change_bucket('tizi_game_');

	}

	public function index($zuoye_id='', $zuoye_game_id=''){

        $zuoye_id  = (int)$zuoye_id;
        $zuoye_game_id  = (int)$zuoye_game_id;
        if(empty($zuoye_id) || empty($zuoye_game_id)){
            redirect();
        }
        $zuoye_game_id = $zuoye_game_id -1;
        $student_zuoye = $this->_get_student_zuoye($zuoye_id);
        if(empty($student_zuoye)){
            $this->session->set_flashdata("errormsg",$this->lang->line('sh_homework_not_exists'));
            redirect('student/class/homework');
        }
        if($student_zuoye['user_id'] != $this->tizi_uid)
            redirect('student/class/homework');
        if(!empty($student_zuoye['zuoye_info'])){
            $zuoye_info = json_decode($student_zuoye['zuoye_info'], true);
            if(isset($zuoye_info['game'][$zuoye_game_id])) redirect('student/class/homework');
        }
        if (empty($student_zuoye['start_time']))
            $this->student_zuoye_model->update($zuoye_id,
                array('start_time'=>time())
            );

        $zuoye_game = json_decode($student_zuoye['unit_game_ids'], true);
        if(!isset($zuoye_game[$zuoye_game_id])) redirect();
        $game = $zuoye_game[$zuoye_game_id];
        $category_id = $game['unit_id'];
        $game_id = $game['game_id'];
        $game_type_id = (isset($game['game_type_id']) and intval($game['game_type_id']) ) ? $game['game_type_id']:null;

        if(!$this->_game_initialize($category_id, $game_id)) redirect();

        $this->smarty->assign('category_id', $zuoye_id);
        $this->smarty->assign('game_id', $zuoye_game_id + 1);
        $this->smarty->assign('game_type_id', $game_type_id);
        $this->smarty->assign('preview', false);
		$this->smarty->display($this->_smarty_dir.'playGame.html');

	}

    public function preview($category_id = '', $game_id ='',$game_type_id=null){
        $category_id = (int)$category_id;
        $game_id = (int)$game_id;
        if(empty($category_id) || empty($game_id)) redirect();

        if(!$this->_game_initialize($category_id, $game_id)){
            redirect();
        }

        $this->smarty->assign('category_id', $category_id);
        $this->smarty->assign('game_type_id', $game_type_id);
        $this->smarty->assign('game_id', $game_id);
        $this->smarty->assign('preview', true);
		$this->smarty->display($this->_smarty_dir.'playGame.html');

    }

	public function get_question($zuoye_id = '', $zuoye_game_id = '',$game_type_id=null){
        $zuoye_id = (int)$zuoye_id;
        $zuoye_game_id = (int)$zuoye_game_id;
        if(!$zuoye_id || !$zuoye_game_id)exit('faild');

        $zuoye_game_id = $zuoye_game_id -1;
        $student_zuoye = $this->_get_student_zuoye($zuoye_id);
        if($student_zuoye['user_id'] != $this->tizi_uid) exit('faild');
        $zuoye_game = json_decode($student_zuoye['unit_game_ids'], true);
        if(!isset($zuoye_game[$zuoye_game_id])) exit('faild');
        $game = $zuoye_game[$zuoye_game_id];
        $category_id = $game['unit_id'];
        $game_id = $game['game_id'];
        if(!$game_type_id){$game_type_id=null;}

        $questions = $this->_get_question($category_id, $game_id,$game_type_id);
		$game_data = array(
			'id' => $zuoye_id.'_'.($zuoye_game_id+1),
			'question' => $questions,
            'user_id' => $this->tizi_uid,
            'unit_id' => $category_id
		);
		exit(json_encode($game_data));
	}

    public function get_simulate_question($category_id = '', $game_id = '',$game_type_id=null){
		$category_id = (int)$category_id;
        $game_id = (int)$game_id;
		$game_type_id = (int)$game_type_id;
		if(empty($category_id) || empty($game_id)) exit();
        $questions = $this->_get_question($category_id, $game_id,$game_type_id);
   		$game_data = array(
			'id' => NULL,
			'question' => $questions,
            'user_id' => $this->tizi_uid,
            'unit_id' => $category_id
		);
		exit(json_encode($game_data));
    }

    public function image(){
        
        header("Content-type:image/jpeg");
        $image_url = $this->input->get('url', true);
        if(!preg_match("/^http:\/\/tizi-game.qiniudn.com.+/", $image_url))exit('123');
        $image = file_get_contents($image_url);
        exit($image);

    }

    public function submit(){
            

        $id = $this->input->post('id', true);
        $time = $this->input->post('time', true);
        $questions = $this->input->post('questions', true);

        $correct_num = 0;
        if ($questions && is_array($questions)) {
            foreach ($questions as $question) {
                if ( $question['rightCount'] == 1 &&
                    $question['isRight'] == true) {
                    $correct_num++; 
                }
            }
            $questions_num = count($questions);
        }else{
            $correct_num = $this->input->post('correct_num', true);
        }
        
        $id_arr = explode('_', $id);
        if(isset($id_arr[0]) && !empty($id_arr[0])){
            $zuoye_id = $id_arr[0];
        }else{
            exit('faild');
        }
        if(isset($id_arr[1]) && !empty($id_arr[1])){
            $zuoye_game_id = $id_arr[1];
        }else{
            exit('faild');
        }

        $zuoye_game_id = $zuoye_game_id -1;
        $zuoye = $this->_get_student_zuoye($zuoye_id);
        if(empty($zuoye)) exit('faild');
        //成功提交给老师积分，需要teacher_id 和 teacher_is_cert
        $teacher_id = $zuoye['teacher_id'];
        $ass_id = $zuoye['zy_assign_id'];
        $this->load->model('login/register_model');
        $teacher_is_cert = $this->register_model->get_user_info($teacher_id,0,'certification');
        if(isset($teacher_is_cert['user']->certification)){
            $teacher_is_cert = $teacher_is_cert['user']->certification?1:0;
        }else{
            $teacher_is_cert = 0;
        }

        $zuoye_all_info = $zuoye_info = json_decode($zuoye['zuoye_info'], true);
        $zuoye_info = isset($zuoye_info['game']) ? $zuoye_info['game'] : array();

        $zuoye_db = array();
        $video_num = $zuoye['video_ids'] ? count(explode(',', $zuoye['video_ids'])) : 0;
        $video_complete_num = (isset($zuoye_all_info['video']) && !empty($zuoye_all_info['video'])) ? count($zuoye_all_info['video']) : 0;

        if(isset($zuoye['unit_game_ids']) && !empty($zuoye['unit_game_ids'])){
            $unit_game_ids = json_decode($zuoye['unit_game_ids'], true);
            if(isset($unit_game_ids[$zuoye_game_id])){
                $unit_game = $unit_game_ids[$zuoye_game_id];
                $zuoye_game_info = array();
                if(isset($zuoye_info[$zuoye_game_id])){
                    $zuoye_game_info = $zuoye_info[$zuoye_game_id];
                    $zuoye_game_info['correct_num'] += $correct_num;
                    $zuoye_game_info['game_time'][] = $time;
                }else{
                    if (!empty($questions)) {
                        $questions_num = count($questions);
                    }else{
                        $questions = $this->_get_question($unit_game['unit_id'], $unit_game['game_id']);
                    }
                    $questions_num = count($questions) > 10 ? 10 : count($questions);
                    $zuoye_game_info['correct_num'] = $correct_num;
                    $zuoye_game_info['question_num'] = $questions_num;
                    $zuoye_game_info['game_time'][] = $time;
                    $zuoye_db['question_num'] = $zuoye['question_num'] + $questions_num;
                }
                if($zuoye_game_info['correct_num'] > $zuoye_game_info['question_num'])exit('faild');
                $zuoye_info[$zuoye_game_id] = $zuoye_game_info;
                $zuoye_all_info['game'] = $zuoye_info;
                $zuoye_db['zuoye_info'] = json_encode($zuoye_all_info);
                $zuoye_db['correct_num'] = $zuoye['correct_num'] + $correct_num;
                $zuoye_db['game_time'] = $zuoye['game_time'] + $time;
                if($this->student_zuoye_model->update($zuoye_id, $zuoye_db)){
                $this->student_zuoye_model->updateCompleteStatus($this->tizi_uid, $zuoye_id);
                    //提交成功，老师获得积分   2014-07-26
                    $this->load->library("credit");
                    $score = $this->credit->exec($teacher_id, "upsubmit_homework", $teacher_is_cert,'',array($this->tizi_uid));
                    $this->load->model('homework/zuoye_intro_model');
                    $this->zuoye_intro_model->change_assign_score($ass_id,$score);
                    exit('success');
                }
            }else{
                exit('faild');
            }
        }else{
            exit('faild');
        }
        
    }
    //数字对应的是game.game_data_type
	private function game_1($data){

		$questions = array();
		$i = 0;
		foreach($data as $val){
			$i++;
			$word = $this->game_model->game_word($val['pronunciation']);
            if(empty($word)) continue;
			$link = $this->qiniu->qiniu_public_link($word['word']);
			$questions[] = array(
				'word' => $val['word'],
				'explanation' => $val['explanation'],
				'audio' => $link

			);
		}
		return $questions;
	}

	private function game_2($data){
		
		$questions = array();
		foreach($data as $val){

			$word = $this->game_model->game_word($val['pronunciation']);
            if(empty($word)) continue;
			$link = $this->qiniu->qiniu_public_link($word['word'], '', false);

			$options = explode('|', $val['options']);
			$options[] = $val['word'];
			shuffle($options);
			$answer_id = array_search($val['word'], $options) + 1;
			$questions[] = array(
				'word'=>$val['word'],
				'option'=>$options,
				'rightId'=>$answer_id,
				'audio'=>$link
			);	
		}
        return $questions;

	}

    private function game_3($data){
        
        $questions = array();       
        foreach($data as $val){
                       
            $questions[] = array(
                'word'=>$val['word'],
                'letter'=>$val['correct_letter'],
                'index'=>$val['position']
            );              
            
        }

        return $questions;
        
    }

    private function game_4($data){
        
        $questions = array();
        foreach($data as $val){

			$word = $this->game_model->game_word($val['pronunciation']);
            if(empty($word)) continue;
            $word_image = $this->game_model->game_word_image($val['word']);
            $options = explode('|', $val['options']);
            $options[] = $val['word'];
            shuffle($options);
            $answer_id = array_search($val['word'], $options);
			$link = $this->qiniu->qiniu_public_link($word['word'], '', false);
			$image_link = $this->qiniu->qiniu_public_link('images/'.$word_image, '', false);

            $questions[] = array(
                'word'=>$val['word'],
                'option'=>$options,
                'correctId'=>$answer_id,
                'audio'=>$link,
                'image'=>site_url().'class/game/image?url='.$image_link,
            );

        }

        return $questions;
    
    }

    private function game_5($data){
    
        
        $questions = array();
        foreach($data as $val){

            $word = $this->game_model->game_word($val['pronunciation']);
            if(empty($word)) continue;
            $options = explode('|', $val['options']);
            $options[] = $val['explanation'];
            shuffle($options);
            $answer_id = array_search($val['explanation'], $options);
            $link = $this->qiniu->qiniu_public_link($word['word'], '', false);

            $questions[] = array(
                'word'=>$val['word'],
                'option'=>$options,
                'correctId'=>$answer_id,
                'audio'=>$link,
            );

        }

        return $questions;
       
    
    }

    private function game_6($data){

        $questions = array();
        foreach($data as $val){

            $questions[] = array(
                'word'=>$val['word'],
                'explanation'=>explode('|', $val['options'])
            );              

        }

        return $questions;

    }

    private function game_7($data) {
        
        $questions = array();
        foreach($data as $val){
            $punctuation = array('？','。',"‘","！",'’','?','.',"'",'!',"'");
            $word = $this->game_model->game_word($val['pronunciation']);
            if(empty($word)) continue;
            $link = $this->qiniu->qiniu_public_link($word['word'], '', false);
            $questions[] = array(
                'word'=>$val['word'],
                'audio'=>$link,
                'explanation'=>$val['explanation'],
                'wid'=>$word['id']
            );

        }
        return $questions;
    
    }

    private function game_8($data){
        
        $questions = array();
        foreach($data as $val){
            $punctuation = array('？','。',"‘","！",'’','?','.',"'",'!',"'",',');
            $word = $this->game_model->game_word($val['pronunciation']);
            if(empty($word)) continue;
            $words = array_filter(explode('#', $val['word']), 
                function($val)use($punctuation){ 
                    if (!in_array($val, $punctuation)) return true;
                }
            );
            $options = explode(' ',$val['options']);
            $options = array_merge(array_slice($options, 0, 14 - count($words)), $words);
            shuffle($options);
            $link = $this->qiniu->qiniu_public_link($word['word'], '', false);

            $questions[] = array(
                'word'=>$val['word'],
                'option'=>$options,
                'audio'=>$link,
            );

        }

        return $questions;


    }

    private function game_9($data){
        
        $questions = array();

        foreach($data as $val){ $options = array();
            $options[] = explode('#', $val['word']);
            $options[] = explode('#', $val['word_1']);

            $questions[] = array(
                'word'=> str_replace('#', ' ', $val['word']),
                'option'=>$options,
                'explanation'=>$val['explanation']
            );

        }
        return $questions;

    }
    //作业中的专项（数学语文游戏，小猪过河 等）
    private function game_10($data, $option_num) {
        $option_num = $option_num - 1;
        $questions = array();
        foreach($data as $val){
            $options = array_filter(explode('|', $val['options']), 
                function ($val) { if($val == '0' || !empty($val)) return true; }
            );
            shuffle($options);
            $options = array_merge(array($val['answer']), 
                array_slice($options, 0, $option_num)
            );
            shuffle($options);
            $questions[] = array(
                'qid' => $val['id'],
                'word' => $val['question'],
                'option' => $options,
                'correctId' => array_search($val['answer'], $options) + 1,
            );
        }
        return $questions;
    }
    
    private function _game_initialize($category_id, $game_id){
        
        $game_info = $this->game_model->get_game_info($game_id);
        if(empty($game_info)) return false;
        if ($game_info['option_num'] === 0) {
            $have_option = false;
        }else {
            $have_option = true;
        }
        $have_image = $game_info['image'] && 1;

        $other_data = array(
            'closeButtonVisible'=>true,
        );
        if($game_info['game_data_type'] == 3){
            $other_data  = array_merge($other_data, 
                array(
                    'winString'=>"letter",
                    'loseIndex'=>'index'
                )
            );
        }elseif($game_info['game_data_type'] == 6){
            $other_data  = array_merge($other_data, 
                array(
                    'answerOptionStringName'=>"answerOptionString",
                )
            );
        }elseif($game_info['game_data_type'] == 7){
            $other_data = array(
                'closeButtonVisible'=>false,      
                'indexName_unitId'=>'user_id',
                'indexName_unitId'=>'unit_id',
                'questionIdName'=>'wid',
                'submitUrl_upload'=>'http://192.168.11.18:9191/arservice/upload',
                'submitUrl_save'=>'http://192.168.11.18:9191/arservice/save'
            );
        }elseif ($game_info['game_data_type'] == 10) {

            $other_data = array_merge($other_data,
                array(
                    'answerOptionIdArr'=>array_slice(array('A', 'B', 'C', 'D'), 0, $game_info['option_num']),
                    'gameHelpString'=>'总共10道题，来比比谁做对的更多！',
                    'downWindowTextFormatType'=>2
                )
            );
        }
        $this->smarty->assign('game_path', $game_info['game_path']);
        $this->smarty->assign('have_option', $have_option);
        $this->smarty->assign('have_image', $have_image);
        $this->smarty->assign('other_data', json_encode($other_data));

        return true;
    }

    private function _get_student_zuoye($zuoye_id){
    
        $this->load->model('homework/student_zuoye_model');
        $data = $this->student_zuoye_model->get(array('zuoye_student.id'=>$zuoye_id));
        if(isset($data[0]) && !empty($data[0])){
            $student_zuoye = $data[0];
            return $student_zuoye;
        }

        return false;

    }

    private function _get_question($category_id, $game_id,$game_type_id=null) {
        $category_id = (int)$category_id;
        $game_id = (int)$game_id;
        if(empty($category_id) || empty($game_id)) exit();

        $game_info = $this->game_model->get_game_info($game_id);
        if($game_type_id == null){
            $game_type_id = $this->game_model->get_game_type($game_id, $category_id);
        }
        if (!$game_type_id) return false;
        $questions = $this->game_model->get_question($category_id, $game_type_id, $game_info['question_num']);
        $func = 'game_'.$game_info['game_data_type'];
        $questions = $this->$func($questions, $game_info['option_num']);
        shuffle($questions);

        return $questions;
        
    }




    







}
