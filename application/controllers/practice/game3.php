<?php
/**
 * @author saeed
 * @date   2013-12-11 
 * @description 专项自主练习- 闯关游戏
 */
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'game_base.php');
class Game3 extends Game_Base{

    public function __construct(){
        parent::__construct();
    }
    
    //首页
    public function index($id=66){

		parent::index($id);
		$this->p_c_id = $id;
		if($this->tizi_role == 'student'){
			$this->update_participants_stats($id);
		}
		$redis = $this->practice_model->connect_redis('practice_statistics');
		$key = $this->_game_statistics_key_prefix.$id;
		if($redis){
			$rank = $redis->zrank($key, $this->tizi_uid);
			if(is_numeric($rank)){
				$this->_my_rank = $rank + 1;
				$this->_my_score = convertToMinsSecs($redis->zscore($key, $this->tizi_uid), "%s:%s");
			}
			$ranking_list = $redis->zrange($key, 0, 9);
			$this->_ranking_list = $this->_statistics_data_process($ranking_list);
			foreach($this->_ranking_list as $r_k => $val){
				$this->_ranking_list[$r_k]['score'] = convertToMinsSecs($val['score'], "%s:%s");
			}
		}
		$this->_game_display();

    }

    //获取问题
    public function get_question($id){

        $c_info = $this->practice_model->get_category_info($id);
        switch($c_info['p_c_type']){
        
            case 9:
                exit(json_encode($this->get_question_2($id)));
                break;
            case 10:
                exit(json_encode($this->get_question_3($id)));
                break;
            case 11:
                exit(json_encode($this->get_question_4($id)));
                break;
            case 12:
                exit(json_encode($this->get_question_3($id)));
        }
    }

    //游戏提交
    public function submit(){

        $redis = $this->practice_model->connect_redis('practice');

        if($this->input->post('id')){
            $id = $this->input->post('id');
        }else{
            $this->_info_handle(1,'faild');
        }
        if($this->tizi_role != 'student'){
			$ex_arr = explode('_', $id);
			$p_c_id = $ex_arr[1];
			goto showRankingList;
        }
        if($this->input->post('statusArr')){
            $result = explode(",", $this->input->post('statusArr'));
        }else{
            $this->_info_handle(1,'faild');
        }
        if($this->input->post('selectidArr')){
            $selected_option = explode(",", $this->input->post('selectidArr'));
        }else{
            $this->_info_handle(1,'faild');
        }       
        //游戏用时
        if($this->input->post('gameOverTimer')){
            $expend_time = $this->input->post('gameOverTimer');//用的时间
        }else{
            $this->_info_handle(1,'faild');
        }
        $practice = $redis->hgetall($id);
        if(!$practice || $practice['status']){//不存在或已提交
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
        //统计
        $redis = $this->practice_model->connect_redis('practice_statistics');
        $history_key = 'game_statistics_'.$p_c_id;
        $redis_score= $redis->zscore($history_key, $this->tizi_uid);

        //历史统计
        if(!$redis_score || $redis_score > $expend_time){

			$redis->zrem($history_key, $this->tizi_uid);
			$redis->zadd($history_key, $expend_time, $this->tizi_uid);

		}
        //设置用户地区
        $this->_set_user_area();

        $db_data['s_answer'] = implode(",",$result).'|'.implode(",",$selected_option);
        $db_data['correct_num'] = $correct_num;
        $db_data['practice_list'] = json_encode($questions);
        $db_data['start_time'] = $practice['start_time'];
        $db_data['p_c_id'] = $practice['p_c_id'];
        $db_data['end_time'] = time();
        $db_data['expend_time'] = $expend_time;
        $db_data['is_submit'] = 1;
        $db_data['uid'] = $this->tizi_uid;

        if($this->practice_model->insert_student_practice($db_data)){
			showRankingList:{
				$sta_today = $this->_get_statistics($p_c_id);
				exit(json_encode(array('rank'=>$sta_today)));
			}
        }
        $this->_info_handle(1,'faild');
        
    }

    private function _get_statistics($p_c_id){

        $redis = $this->practice_model->connect_redis('practice_statistics');
        $history_key = 'game_statistics_'.$p_c_id;
        $today_statistics = $redis->zrange($history_key,0,29);
        $uid_group = array_keys($today_statistics);
        $sta_today = array();
        $i = 1;
        $users_info = $this->_get_users_info($uid_group);
        foreach($today_statistics as $t_key=>$today_statistics_val){
            foreach($users_info as $users_info_val){
                if($users_info_val['uid'] == $t_key){
                    $today_statistics_val = convertToMinsSecs($today_statistics_val,"%s:%s");
                    $sta_today[] = array($i++,$users_info_val['name'],$today_statistics_val);
                }
            }
        }
        return $sta_today;    

    }



}
