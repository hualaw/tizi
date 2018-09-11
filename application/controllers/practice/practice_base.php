<?php
/**
 * @author saeed
 * @date   2013-8-30 
 * @description 专项自主练习 base
 */
class Practice_Base extends My_Controller{

    protected $_smarty_dir = 'student/practice/';
	protected $p_c_id;
	protected $_my_rank = '--';
	protected $_my_score = '--';
	protected $_ranking_list = array();
	protected $_game_statistics_key_prefix = 'game_statistics_';
	protected $_statistics_url;
    protected $c_subject;

    public function __construct(){

		parent::__construct();

        $this->load->model('exercise_plan/student_homework_model');
        $this->load->model('user_data/student_data_model');
        $this->load->model('practice/practice_model');
        $this->load->model('practice/practice_statistics_model');
		$this->_statistics_url = site_url('image/1.gif?src=tizi_student&func=special');

    }

    /**
     * @info 获取分类 
     */
    protected function get_categories_by_sid($sid){
        $grades = Constant::practice_grade();
        $urls = Constant::practice_url();
        $icons = Constant::practice_icon();
        $categories = $this->practice_model->get_categories_by_sid($sid);
        $second_categories = $categories[0];
        $third_categories = $categories[1];
        foreach($third_categories as $key=>$val){
            if(isset($icons[$val['id']])){
                $third_categories[$key]['icon'] = $icons[$val['id']];
            }
            $third_categories[$key]['url'] = isset($urls[$val['p_c_type']])?$urls[$val['p_c_type']]:$urls[1];
            $third_categories[$key]['grade_name'] = isset($grades[$val['grade']])?$grades[$val['grade']]:'';

        }
        return array(
            'second_categories'=>$second_categories,
            'third_categories'=>$third_categories,
        );
    }

    //根据年级获取分类
    protected function get_categories_by_grade($grade){


        $grades = Constant::practice_grade();
        $urls = Constant::practice_url();

        $icons = Constant::practice_icon();

        $categories = $this->practice_model->get_categories_by_grade($grade);

        foreach($categories as $key=>$val){
            

            if(isset($icons[$val['p_c_type']])){
                $categories[$key]['icon'] =  $icons[$val['p_c_type']];
            }
            $categories[$key]['url'] = isset($urls[$val['p_c_type']])?$urls[$val['p_c_type']]:$urls[1];
            $categories[$key]['grade_name'] = isset($grades[$val['grade']])?$grades[$val['grade']]:'';

        }
        return $categories;   
    }

    protected function get_categories($grade_id = ''){

        $grades = Constant::practice_grade();
        $urls = Constant::practice_url();

        $categories = $this->practice_model->getCategoriesByGradeId($grade_id);
        $t_c = array();
        foreach($categories as $key=>$val){
            
            $val['icon'] = 'image/student/special/'.($val['p_c_type'] != 1 ?
                'gameImg'.$val['p_c_type'] : "subject_{$val['subject_id']}").'.png';
			$val['user_num'] = empty($val['user_num']) ? 0 : $val['user_num']; 
            $val['url'] = ($val['p_c_type'] == 1) ? $urls[1]:$urls[2];
            $val['grade_name'] = isset($grades[$val['grade']])?$grades[$val['grade']]:'';
			$t_c[] = $val;
		}

        return $t_c;   

    }

    protected function get_practice_list($grade_id = ''){

        $this->load->model('practice/practice_model');
        $categorie_group = array();
        $p_c_id_group = $this->practice_model->special_count();
        $grade_categories = $this->get_categories($grade_id);
		// if(empty($grade_categories)) redirect('pk');
        $grades = Constant::practice_grade();
        $grades = Constant::practice_grade_hide_midhigh();
        $c_subject = array();
        $categories_group = array();
        $categories_temp = array();
        foreach($grade_categories as $category){

			if(!($category['p_c_type']==1 && !in_array($category['id'],$p_c_id_group))){
				$categories_group[$category['subject_id']][] = $category;
			}

            $subjects = $this->student_homework_model->get_subject_type();
            $categories_temp = array();
            foreach($subjects as $subject){

                $s_id = $subject->id;
                if(isset($categories_group[$s_id])){
                    ksort($categories_group[$s_id]);
                    $categories_temp[$s_id] = $categories_group[$s_id];
                    $c_subject[$s_id] = $subject->name;
                }

            }
        }
		ksort($c_subject);
        $this->c_subject = $c_subject;
        $single_ids = Constant::practice_game_single();

        $this->smarty->assign('single_ids',$single_ids);
        $this->smarty->assign('grades',$grades);
        $this->smarty->assign('pra_subjects',$c_subject);
        $this->smarty->assign('grade_categories',$categories_temp);
    
    }

	protected function _info_handle($status,$msg){
		$text = json_token(array('status'=>$status,'msg'=>$msg));
		$text = json_decode($text,true);
		$text = array_merge($text,array('status'=>$status,'msg'=>$msg));
		if(isset($text['errorcode'])){
			unset($text['errorcode']);
		}
		exit(json_encode($text));
	}

	protected function ranking_list($p_c_id){

		$redis = $this->practice_model->connect_redis('practice_statistics');
		if($redis){
			$rank = $redis->zrevrank('special_stats_'.$p_c_id, $this->tizi_uid);
			if(is_numeric($rank)){
				$this->_my_rank = $rank + 1;
				$this->_my_score = $redis->zscore('special_stats_'.$p_c_id, $this->tizi_uid);
			}
			$ranking_list = $redis->zrevrange('special_stats_'.$p_c_id, 0, 9);
			$this->_ranking_list = $this->_statistics_data_process($ranking_list);
		}
		$this->smarty->assign('rule', Constant::practice_game_rule());
		$this->smarty->assign("id", $this->p_c_id);
		$this->smarty->assign("my_rank", $this->_my_rank);
		$this->smarty->assign("my_score", $this->_my_score);
		$this->smarty->assign("ranking_list", $this->_ranking_list);

	}

    protected function set_basic_info($p_c_id){
        
		if(empty($p_c_id)) redirect();
        $p_c_info = $this->practice_model->get_category_info($p_c_id);
        if(!empty($p_c_info)){
            $p_c_name = $p_c_info['p_c_name'];
        }else{
            redirect();
        }
        $data = $this->practice_model->get_sid_by_cid($p_c_id);
        $sid = $data['sid'];
        $subjects = $this->student_homework_model->get_subject_type();
        $subject_name = $subjects[$sid-1]->name;
        $level = $data['level'];
        if($level != 3){
            redirect('');
        }
        $this->smarty->assign('p_c_id',$p_c_id);
        $this->smarty->assign('grade_id',$p_c_info['grade']);
        $this->smarty->assign('p_c_name',$p_c_name);
        $this->smarty->assign('subject_name',$subject_name);
        $this->smarty->assign('sid',$sid);

    }

	protected function update_participants_stats($p_c_id){

        $redis = $this->practice_model->connect_redis('practice_statistics');
		$key = 'participants_stats_'.$this->tizi_uid;

        $pids = $redis->lrange($key, 0, -1);
        if(empty($pids) || !in_array($p_c_id, $pids)){
            $redis->lpush($key, $p_c_id);   
			$this->practice_statistics_model->update_participants_stats($p_c_id);
        }else{
            $redis->lrem($key, $p_c_id, 1);
            $redis->lpush($key, $p_c_id);   
        }
        
	}

	protected function _statistics_data_process($data){
	
		$stats = array();
		$uids = array_keys($data);
		$uids = array_filter($uids, function($val){if(!empty($val)) return true;});
		if(!empty($uids)){
			$users_info = $this->_get_users_info($uids);
			$area_info = $this->student_data_model->get_student_area($uids);

			$order = 0;
			foreach($data as $uid => $score){

				$area = '';

				foreach($area_info as $area_info_val){
					if($area_info_val['uid'] == $uid){
						if($area_info_val['name'] == '直辖县')continue;
						$area = $area_info_val['name'];
					}
				}

				foreach($users_info as $user){
					if($user['uid'] == $uid){
						if(empty($area)){
							if(isset($user['area']) && $user['area']){
								$area = array_pop(explode("|",$user['area']));
							}else{
								$area = '未知';
							}
						}
                        if (strlen($area) > 12) $area = sub_str($area, 0, 9); 
						$stats[] = array(

							'order' => ++$order,
							'uid' => $uid,
							'username' => sub_str($user['name'], 0, 10),
							'area' => $area,
							'score' => $score

						);
					}
				}
			}
		}
		return $stats;

	}

    protected function _get_users_info($uid_group){

        $this->load->model("exercise_plan/student_homework_model");
        $result = $this->student_homework_model->getStudentDataByUids($uid_group);
        return $result;
		
    }





	


}
