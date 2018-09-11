<?php
/**
 * @author saeed
 * @date   2013-8-30 * @description 专项自主练习-游戏
 */
class Game_Base extends Practice_Base{
    
    private $_symbol = array(
        1 => '<',
        2 => '>',
        3 => '=',
        4 => '+',
        5 => '-'
    );

    protected static $_permitted_ids = array();

    protected $correspond = array(
        // '37'=>'get_question_type1',
        // '38'=>'get_question_type6',
        // '39'=>'get_question_type7',
        '40,43,237,238,239,240,241,242'=>'get_question_type5',
    );
    //'get_question_type4',default function

    public function __construct(){

        parent::__construct();
        $this->load->model('practice/practice_questions_model');
        $this->load->model('practice/practice_challenge_model');
        $this->load->model('exercise_plan/student_homework_model');
    }

    /**
     * @info 获取问题
     */
    protected function get_question_1($p_c_id = 42,$num,$option_num=''){
        $match_status = false;
        foreach($this->correspond as $correspond_key=>$correspond_val){
            $correspond_key_group = explode(",",$correspond_key);   
            if(in_array($p_c_id,$correspond_key_group)){
                $match_status = true;
                break;
            }
        }
        if(!$match_status){
        
            $correspond_val = 'get_question_type4';//单关游戏
        }

        if($option_num){
            $question = $this->practice_questions_model->$correspond_val($this->tizi_uid,$p_c_id,$num,$option_num);
        }else{
            $question = $this->practice_questions_model->$correspond_val($this->tizi_uid,$p_c_id,$num);
        }

        $current_option = array();
        foreach($question as $question_val){
            $current_question[] = $question_val['question'];
            $current_option = array_merge($current_option,$question_val['option']);
            $current_answer[] = $question_val['answer'];
        }
        $key = 'game_'.$p_c_id."_".$this->tizi_uid."_".uniqid(time());
        $this->_practice_redis = $this->practice_model->connect_redis('practice');

        // save data to redis when redis connect success
		$this->_save_game_data($p_c_id, $key, $question);

        if(!empty($current_question) && !empty($current_option) && !empty($current_answer)){
            $new_question = array(
                'comm_status'=>99,
                'id'=>$key,
                'question'=>$current_question,
                'option'=>$current_option,
                'answer'=>$current_answer,
                'option_num'=>count($question[0]['option']),
            );
        }else{
            $new_question = array(
                'comm_status'=>1
            );
        }
        //
        exit(json_encode($new_question));
    }
    
    public function get_question_2($p_c_id){
        
        foreach($this->correspond as $correspond_key=>$correspond_val){
            $correspond_key_group = explode(",",$correspond_key);   
            if(in_array($p_c_id,$correspond_key_group)){
                break;
            }
        }

        $current_question = array();
        $current_option = array();
        $current_answer = array();
        //第一关
        $first_hurdle_question = $this->practice_questions_model->get_question_type8($this->tizi_uid,$p_c_id,10);

        foreach($first_hurdle_question as $question_val){
            $current_question[] = $question_val['question']." = ?";
            if(isset($question_val['option'])){
                $current_option = array_merge($current_option,$question_val['option']);
            }
            $current_answer[] = $question_val['answer'];
        }

        //第二关

        $second_temp_question = array();
        $second_temp_answer = array();
        $i = 0;
        $second_hurdle_question = $this->practice_questions_model->get_question_type9($this->tizi_uid,$p_c_id,15,2);
        foreach($second_hurdle_question as $key=>$si_question){
            $i++;
            $second_temp_question[] = explode(",",$si_question['question']);
            $second_temp_answer[] = array_search($si_question['answer'],$this->_symbol);
            if($i == 3){
                $current_question[] = $second_temp_question;
                $current_answer[] = $second_temp_answer;
                $second_temp_question = array();
                $second_temp_answer = array();
                $i = 0;
            }
        }

        //第三关
        $third_hurdle_question = $this->practice_questions_model->get_question_type9($this->tizi_uid,$p_c_id,5,3);

        foreach($third_hurdle_question as $question_val){
            $current_question[] = $question_val['question'];
            $current_answer[] = $question_val['answer'];
        }

        $current_option = array_pad($current_option,count($current_question)*4,NULL);

        $key = 'game_'.$p_c_id."_".$this->tizi_uid."_".uniqid(time());

        $this->_practice_redis = $this->practice_model->connect_redis('practice');
        //redis connect success
		$this->_save_game_data($p_c_id, $key, array($first_hurdle_question,$second_hurdle_question,$third_hurdle_question));
        if(!empty($current_question) && !empty($current_option) && !empty($current_answer)){

            $new_question = array(
                'comm_status'=>99,
                'id'=>$key,
                'question'=>$current_question,
                'option'=>$current_option,
                'answer'=>$current_answer,
                'option_num'=>4,
            );
        }else{
            $new_question = array(
                'comm_status'=>1
            );
        }
        return $new_question;

    }

    public function get_question_3($p_c_id){
        
        $current_question = array();
        $current_option = array();
        $current_answer = array();

        //第一关
        $first_hurdle_question = $this->practice_questions_model->get_question_type4($this->tizi_uid,$p_c_id,10,4,2);

        foreach($first_hurdle_question as $question_val){
            $current_question[] = $question_val['question'];
            if(isset($question_val['option'])){
                $current_option = array_merge($current_option,$question_val['option']);
            }
            $current_answer[] = $question_val['answer'];
        }
        //第二关
        $second_hurdle_question = $this->practice_questions_model->get_question_type10($this->tizi_uid,$p_c_id,5,1);

        foreach($second_hurdle_question as $question_val){
            
            $temp_question = array();
            $temp_answer = array();
            $cc_question = array();
            foreach($question_val as $question){
                $temp_question[] = $question['question'];
                $temp_answer[] = $question['answer'];
            }
            $t_answer = $temp_answer;
            shuffle($temp_answer);
            for($i=0;$i<count($temp_question);$i++){
                $cc_question[] = array($temp_question[$i],$temp_answer[$i]);
            }
            $current_question[] = $cc_question;
            $current_answer[] = $t_answer;
        }

        $current_option = array_pad($current_option,count($current_question)*4,NULL);

        //第三关
        $third_hurdle_question = $this->practice_questions_model->get_question_type4($this->tizi_uid,$p_c_id,5,6,3);

        foreach($third_hurdle_question as $question_val){
            $current_question[] = $question_val['question'];
            $current_answer[] = $question_val['answer'];
            $current_option = array_merge($current_option,array($question_val['option'],NULL,NULL,NULL));
        }

        $key = 'game_'.$p_c_id."_".$this->tizi_uid."_".uniqid(time());

        $this->_practice_redis = $this->practice_model->connect_redis('practice');

        //redis connect success
		$this->_save_game_data($p_c_id, $key, array($first_hurdle_question,$second_hurdle_question,$third_hurdle_question));

        if(!empty($current_question) && !empty($current_option) && !empty($current_answer)){

            $new_question = array(
                'comm_status'=>99,
                'id'=>$key,
                'question'=>$current_question,
                'option'=>$current_option,
                'answer'=>$current_answer,
                'option_num'=>4,
            );
        }else{
            $new_question = array(
                'comm_status'=>1
            );
        }
        return $new_question;
    }

    public function get_question_4($p_c_id){

        $current_question = array();
        $current_option = array();
        $current_answer = array();

        //10,5,5
        //第一关

        $first_hurdle_question = $this->practice_questions_model->get_question_type9($this->tizi_uid,$p_c_id,10,1);
        foreach($first_hurdle_question as $question_val){
            $current_question[] = $question_val['question'];
            $current_option = array_merge($current_option,array("Y","N",NULL,NULL));
            $current_answer[] = $question_val['answer'];
        }
        
        //第二关

        $second_temp_question = array();
        $second_temp_answer = array();
        $i = 0;
        $second_hurdle_question = $this->practice_questions_model->get_question_type9($this->tizi_uid,$p_c_id,15,2);
        foreach($second_hurdle_question as $key=>$si_question){
            $i++;
            $second_temp_question[] = $si_question['question'];
            $second_temp_answer[] = $si_question['answer'];

            if($i == 3){
                $current_answer[] = $second_temp_answer;
                $re_build_question = array();
                shuffle($second_temp_answer);

                foreach($second_temp_answer as $key=>$second_temp_answer_val){
                    $re_build_question[] = array($second_temp_question[$key],$second_temp_answer_val);
                }

                $current_question[] = $re_build_question;
                $second_temp_question = array();
                $second_temp_answer = array();
                $i = 0;
            }

        }

        $current_option = array_pad($current_option,count($current_question)*4,NULL);

        //第三关
        $third_hurdle_question = $this->practice_questions_model->get_question_type4($this->tizi_uid,$p_c_id,5,5,3);

        foreach($third_hurdle_question as $question_val){
            $current_question[] = $question_val['question'];
            $current_answer[] = $question_val['answer'];
            $current_option = array_merge($current_option,array($question_val['option'],NULL,NULL,NULL));
        }

        $key = 'game_'.$p_c_id."_".$this->tizi_uid."_".uniqid(time());

        $this->_practice_redis = $this->practice_model->connect_redis('practice');
        //redis connect success
		$this->_save_game_data($p_c_id, $key, array($first_hurdle_question,$second_hurdle_question,$third_hurdle_question));
        if(!empty($current_question) && !empty($current_option) && !empty($current_answer)){

            $new_question = array(
                'comm_status'=>99,
                'id'=>$key,
                'question'=>$current_question,
                'option'=>$current_option,
                'answer'=>$current_answer,
                'option_num'=>4,
            );
        }else{
            $new_question = array(
                'comm_status'=>1
            );
        }
        return $new_question;
        
    }

    protected function index($id){

        $game_info = Constant::practice_game_info();
        $single_ids = Constant::practice_game_single();
        $practice_urls = Constant::practice_url(); 
        $p_c_info = $this->practice_model->get_category_info($id);
        $game_info_val = $game_info[$p_c_info['p_c_type']];
        $back_url = ($p_c_info['p_c_type'] == 1) ? $practice_urls[1] : $practice_urls[2];

        //log record
        $s_info = $this->practice_model->get_sid_by_cid($id);
        $log_message = array(
            'subject_id' =>$s_info['sid'],
            'grade_id'=>$p_c_info['grade'],
            'p_c_id'=>$id,
            'type'=>3,
        );
        log_statistics($log_message,$this->_statistics_url); 

        if(!in_array($p_c_info['p_c_type'],array(9,10,11,12))){
            $this->smarty->assign('height','580');
        }
        $game_helps = Constant::practice_game_help();
        if(isset($game_helps[$p_c_info['p_c_type']])){
            $this->smarty->assign('game_help',$game_helps[$p_c_info['p_c_type']]);
        }else{
            $this->smarty->assign('game_help','');
        }
		$game_rule = Constant::practice_game_rule($p_c_info['game_type']);
        $this->smarty->assign('rule', $game_rule);
        
        if($p_c_info['p_c_type'] == 3){
            $this->smarty->assign('option_num',4);
        }else{
            $this->smarty->assign('option_num',3);
        }
		$this->set_basic_info($id);

        $this->smarty->assign('subject_id',$s_info['sid']);
        $this->smarty->assign('back_url',$back_url);
        $this->smarty->assign('game_path',$game_info_val[0]);
        $this->smarty->assign('game_file',$game_info_val[1]);

    }

    //游戏提示语
    protected function set_game_tip($id){

        $game_tip = array(
            '42,54,55,56,57,58'=>$this->lang->line('sp_game_tip1'), 
            '40'=>$this->lang->line('sp_game_tip2'),
            '43'=>$this->lang->line('sp_game_tip3'),
            '41'=>$this->lang->line('sp_game_tip4'),
            '66'=>$this->lang->line('sp_game_tip5'),
        );
        $status = false;
        foreach($game_tip as $key=>$tip){
            $id_group = explode(",",$key);
            foreach($id_group as $val){
                if($val == $id){
                    goto assign ;
                }
            }
        }
        assign:
            $this->smarty->assign('game_tip',$tip);   
    }

    protected function _get_ip(){

        $ip = '0.0.0.0';
        if (getenv('HTTP_CLIENT_IP')) {                                          
            $ip = getenv('HTTP_CLIENT_IP');                                      
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');                                
        } elseif (getenv('HTTP_X_FORWARDED')) {                                  
            $ip = getenv('HTTP_X_FORWARDED');                                    
        } elseif (getenv('HTTP_FORWARDED_FOR')) {                                
            $ip = getenv('HTTP_FORWARDED_FOR');                                  
        } elseif (getenv('HTTP_FORWARDED')) {                                    
            $ip = getenv('HTTP_FORWARDED');                                      
        } else {                                                                 
            $ip = $_SERVER['REMOTE_ADDR'];                                       
        }

        return $ip;
    }

    protected function _set_user_area(){
        //获取学生信息
        $this->load->model('homework/student_data_model');
        $student_data = $this->student_data_model->get_student_data($this->tizi_uid);
        $area_info = $this->student_data_model->get_student_area($this->tizi_uid);
        if(empty($area_info) && (!$student_data || !$student_data->area)){
            //$ip = $this->input->ip_address();
            $ip = $this->_get_ip();
            $ch = curl_init("http://ip.taobao.com/service/getIpInfo.php?ip=".$ip) ; 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); 
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
            $output = curl_exec($ch); 

            $area_info = json_decode($output,true);
            $area = array();
            if(!$area_info['code']){
                if(($area_info['data']['city'] || $area_info['data']['region'] || $area_info['data']['country']) && $area_info['data']['country'] != '未分配或者内网IP'){
                    if(isset($area_info['data']['country']) && !empty($area_info['data']['country'])){
                        $area[] = $area_info['data']['country'];
                    }
                    if(isset($area_info['data']['region']) && !empty($area_info['data']['region'])){
                        $area[] = $area_info['data']['region'];
                    }
                    if(isset($area_info['data']['city']) && !empty($area_info['data']['city'])){
                        $area[] = $area_info['data']['city'];
                    }
                }
            }
            if(!empty($area)){
                $stu_data = array('ip'=>$ip,'area'=>implode("|",$area));
            }else{
                $stu_data = array('ip'=>$ip);
            }
            $this->student_data_model->save_student_data($this->tizi_uid,$stu_data);
        }

    }

	protected function _game_display(){

		$this->smarty->assign("id", $this->p_c_id);
		$this->smarty->assign("my_rank", $this->_my_rank);
		$this->smarty->assign("my_score", $this->_my_score);
		$this->smarty->assign("ranking_list", $this->_ranking_list);
		$this->smarty->display($this->_smarty_dir.'play_game.html');

	}

	private function _save_game_data($p_c_id, $key, $questions){
	
		if($this->_practice_redis && $this->tizi_role == 'student'){

			$redis_data = array(
				'p_c_id'=>$p_c_id,
				'status'=>0,//提交状态
				'question'=>json_encode($questions),
				'start_time'=>time(),
			);
			$expire_time = strtotime(date("Y-m-d")." 23:59:59")-time();

			if($this->_practice_redis->hmset($key,$redis_data)){
				$this->_practice_redis->expire($key,$expire_time);
			}else{
				exit(json_encode(array('comm_status'=>1)));
			}
		}
	}


	



}
