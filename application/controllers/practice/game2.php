<?php
/**
 * @author saeed
 * @date   2013-8-30
 * @description 专项自主练习-游戏
 */
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'game_base.php');
class Game2 extends Game_Base{
    
    public function __construct(){
        parent::__construct();

    }

    /**
     * @info 首页 
     */
    public function index($id){

		parent::index($id);
		$this->p_c_id = $id;
		if($this->tizi_role == 'student'){
			$this->update_participants_stats($id);
		}
		$redis = $this->practice_model->connect_redis('practice_statistics');
        $key = $this->_game_statistics_key_prefix.$id;
		if($redis){
			$rank = $redis->zrevrank($key, $this->tizi_uid);
			if(is_numeric($rank)){
				$this->_my_rank = $rank + 1;
				$this->_my_score = $redis->zscore($key, $this->tizi_uid);
			}
			$ranking_list = $redis->zrevrange($key, 0, 9);
			$this->_ranking_list = $this->_statistics_data_process($ranking_list);
		}
		$this->_game_display();

    }
    
    public function get_question($id=42){

        $num = 50;
        exit(json_encode($this->get_question_1($id,$num)));

    }

    public function submit($game_id){

		if($this->tizi_role != 'student'){
			$this->_info_handle(1, 'faild');
		}
        $redis = $this->practice_model->connect_redis('practice');
		$id = $game_id;
        if($this->input->post('result')){
            $result = explode(",",$this->input->post('result'));
        }else{
            $this->_info_handle(1,'faild');
        }
        if($this->input->post('selected_option')){
            $selected_option = explode(",",$this->input->post('selected_option'));
        }else{
            $this->_info_handle(1,'faild');
        }
        $practice = $redis->hgetall($id);
        if(!$practice){
            $this->_info_handle(1,'faild');
        }
        if($practice['status']){
            //已提交
            $this->_info_handle(1,'faild');
        }
        $p_c_id = $practice['p_c_id'];
        $questions = json_decode($practice['question']);
        $db_data['question_num'] = count($questions);
        $correct_num = 0;
        $do_question_num = 0;
        $q_n = 0;
        if(!empty($result)){
            foreach($result as $result_val){
                if($result_val == 1){
                    $correct_num += 1;
                }
                if($result_val != 3){
                    $do_question_num++;
                }
                if($result_val == 3){
                    break;
                }
                $q_n++;
            }
        }
        if($q_n != 0){
            $questions = array_slice($questions,0,$q_n);
        }else{
            $questions = array();
        }
        $redis->hset($id,'question',json_encode($questions));
        $redis->hset($id,'status',1);//更新状态，表示已提交
        $redis = $this->practice_model->connect_redis('practice_statistics');
        $history_key = 'game_statistics_'.$p_c_id;
        $redis_history_score = $redis->zscore($history_key,$this->tizi_uid);

        //历史最高成绩
        if (!$redis_history_score || $redis_history_score < $correct_num){
            $redis->zrem($history_key, $this->tizi_uid);
			$redis->zadd($history_key, $correct_num, $this->tizi_uid);
		}

        $this->_set_user_area();
        
        $db_data['s_answer'] = implode(",",$result).'|'.implode(",",$selected_option);
        $db_data['correct_num'] = $correct_num;
        $db_data['practice_list'] = json_encode($questions);
        $db_data['start_time'] = $practice['start_time'];
        $db_data['p_c_id'] = $practice['p_c_id'];
        $db_data['end_time'] = time();
        $db_data['is_submit'] = 1;
        $db_data['uid'] = $this->tizi_uid;
        if($this->practice_model->insert_student_practice($db_data)){
            $this->_info_handle(99,'sucess');
        }
        $this->_info_handle(1,'faild');

    }


}
