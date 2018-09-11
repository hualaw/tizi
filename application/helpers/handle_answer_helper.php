<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 计算出  某个人 某份作业的答案，对了哪些，错了哪些
 * @param array $arr 答案数组
 */
	function handle_person_answers($arr){
		if(!is_array($arr)){
			$arr = unserialize($arr);
		}
		$res = array();
		if(is_array($arr)){		
			$question_count = count($arr);
			$correct_answer = array();
			$answer_match_result = array();
			$question_ids = null; // 题目id串,comma seperated
			foreach ($arr as $key=>$val){
				if(!isset($val['answer'])){
						$val['answer'] = null;
				}
				if(strtolower($val['answer'])==strtolower($val['input']) && $val['answer']!=null){
					$correct_answer[] =$val['answer'];// $val['question_order'];
					$answer_match_result[] = 'active'; // 为了展示颜色,匹配前端的class 名称
				}elseif($val['input']==null){
					$answer_match_result[] = 'undo';// 为了展示颜色,匹配前端的class 名称
				}else{
					$answer_match_result[] = 'error';// 为了展示颜色,匹配前端的class 名称
				}
				$question_ids .= $val['question_id'].',';
			}
			// var_dump($answer_match_result);die;
			$res['answer_match_result'] = $answer_match_result;
			$res['question_ids'] = rtrim($question_ids,',');
			$res['question_arr'] = explode(',', $res['question_ids']);
			$res['correct_answer'] = $correct_answer;
			$res['correct_answer_count'] = count($correct_answer);
			$res['correct_answer_string'] = implode(',', $correct_answer);
			$res['question_count'] = $question_count;
			$res['score'] = 100*$res['correct_answer_count']/$question_count;
		}
		// var_dump($res);die;	
		return $res;
	}

	//tizi3.0 【错题排行】处理一次作业中所有学生的答案，统计每道题做对做错的人数
	function process_answers($arr,$online_q,$order_in_paper){
		if(!$arr or !$online_q){return null;}
		$answer = array();
		$student_answers = array();
		foreach($arr as $key=>$val){
			$temp = unserialize($val);
			if(isset($temp['online']) && $temp['online']){//2014/4/03之前，s_answer字段有online字段
				$backup_order = 1;
				foreach($temp['online'] as $k=>$v){
					if(!isset($answer[$v['question_id']])){
						if(!isset($temp['order'])){
							$order = $backup_order++;
						}else{
							$order = array_search($v['question_id'],$temp['order'])+1;//在作业中的题号
						}
						$answer[$v['question_id']] = init_process_answer($v['question_id'],ucfirst($v['answer']),$order);
					}
					$input = ucfirst($v['input']);
					if($input && $input!=ucfirst($v['answer'])){ //没作答的就不计入‘答错总人数’里了
						// log_message('error_tizi','answer is '.$v['answer'].', input is '.$v['input']);
						$answer[$v['question_id']]['wrong_total']++;
					}
					if($input){
						$answer[$v['question_id']][$input] +=1;
					}
				}
			}elseif(isset($temp['question'])){//2014/4/03开始，s_answer改成question字段,不再包含正确答案
				foreach($online_q as $k=>$v){
					if(!isset($answer[$k])){
						$answer[$k] = init_process_answer($k,ucfirst($online_q[$k]['asw']),array_search($k,$order_in_paper)+1);
					}
					if(isset($temp['question'][$k])){
						$input = ucfirst($temp['question'][$k]['input']);
						if($input && $input!=ucfirst($online_q[$k]['asw'])){ //的就不计入‘答错总人数’里了
                            // log_message('error_tizi','answer is '.$v['answer'].', input is '.$v['input']);
                           $answer[$k]['wrong_total']++;
                           if($input){
                           		if(!isset($answer[$k][$input])){
                           			$answer[$k][$input] =1;
                           		}
                                $answer[$k][$input] +=1;
                           }
                        }elseif($input){
                        	if(!isset($answer[$k][$input])){
                           			$answer[$k][$input] =1;
                           		}
                                $answer[$k][$input] +=1;
                        }
					}
				}
			}
		}
		return $answer;
	}

	//tizi 3.0 【学生答题统计】  online_q是本次作业的选择题；orders是所有的题目的顺序
	function person_answer_color($arr,$online_q,$order_in_paper){
		if(!$arr or !$online_q){return $arr;}
		foreach($arr as $key=>$val){
			$val['process_ans'] = array();
			if($val['s_answer']){
				$temp = unserialize($val['s_answer']);
				if(isset($temp['online']) && $temp['online']){
					$backup_order = 1;
					foreach($temp['online'] as $k=>$v){
						if(!isset($temp['order'])){
							$order = $backup_order++;
						}else{
							$order = array_search($v['question_id'],$temp['order'])+1;//在作业中的题号
						}
						$val['process_ans'][$order] = array();
						$val['process_ans'][$order]['order'] = $order;
						$input = ucfirst($v['input']);
						$val['process_ans'][$order]['correct_answer'] = ucfirst($v['answer']);
						$val['process_ans'][$order]['input'] = $input;
						if($input==ucfirst($v['answer'])){ //没作答的就不计入‘答错总人数’里了
							$val['process_ans'][$order]['color'] = 'green';
						}else{
							$val['process_ans'][$order]['color'] = $input?'red':'gray';
						}
						$val['process_ans'][$order]['qid'] = $v['question_id'];
					}
				}elseif(isset($temp['question']) && $temp['question']){
					foreach($online_q as $_key=>$_val){
						$__order = array_search($_key,$order_in_paper)+1;
						$val['process_ans'][$__order]['order'] = $__order;
						$val['process_ans'][$__order]['correct_answer']=ucfirst($online_q[$_key]['asw']);
						$input=isset($temp['question'][$_key])?ucfirst($temp['question'][$_key]['input']):'';
						$val['process_ans'][$__order]['input'] = $input;
						if($input==ucfirst($online_q[$_key]['asw'])){ //没作答的就不计入‘答错总人数’里了
                            $val['process_ans'][$__order]['color'] = 'green';
                        }else{
                            $val['process_ans'][$__order]['color'] = $input?'red':'gray';
                        }
                        $val['process_ans'][$__order]['qid'] = $_key;
					}
				}
			}
			$arr[$key] = $val;
		}
		return $arr;		
	}

	//tizi 3.0
	function init_process_answer($qid,$ca,$oip){
		return array('wrong_total'=>0,'A'=>0,'B'=>0,'C'=>0,'D'=>0,'correct_answer'=>$ca,'qid'=>$qid,'order_in_paper'=>$oip);
	}

	//tizi 4.0 在所有题目的顺序中取出online_q的各自的顺序
	function get_online_q_order($online_q,$orders){
		if(!$online_q)return null;
		foreach($online_q as $key=>&$val){
			$val['order'] = array_search($key, $orders)+1;
		}
		return $online_q;
	}

//tizi2.0 green red gray
	function handle_person_answers_color($arr){
		if(!is_array($arr)){
			$arr = unserialize($arr);
		}
		$res = array();
		$question_count = count($arr);
		if(is_array($arr)){		
			$correct_answer = array();
			$answer_match_result = array();
			$question_ids = null; // 题目id串,comma seperated
			foreach ($arr as $key=>$val){
				if(!isset($val['answer'])){
						$val['answer'] = null;
				}
				if(strtolower($val['answer'])==strtolower($val['input']) && $val['answer']!=null){
					$correct_answer[] =$val['answer'];// $val['question_order'];
					$answer_match_result[] = 'green'; // 为了展示颜色,匹配前端的class 名称
				}elseif($val['input']==null){
					$answer_match_result[] = 'gray';// 为了展示颜色,匹配前端的class 名称
				}else{
					$answer_match_result[] = 'red';// 为了展示颜色,匹配前端的class 名称
				}
				$question_ids .= $val['question_id'].',';
			}
			// var_dump($answer_match_result);die;
			$res['answer_match_result'] = $answer_match_result;
			$res['question_ids'] = rtrim($question_ids,',');
			$res['question_arr'] = explode(',', $res['question_ids']);
			$res['correct_answer'] = $correct_answer;
			$res['correct_answer_count'] = count($correct_answer);
			$res['correct_answer_string'] = implode(',', $correct_answer);
			$res['question_count'] = $question_count;
			if(!$question_count){
				$res['score'] = 0;
			}else{
				$res['score'] = 100*$res['correct_answer_count']/$question_count;
			}
		}else{
			$res['answer_match_result'] = array();
			$res['question_ids'] = '';
			$res['question_arr'] = array();
			$res['correct_answer'] = array();
			$res['correct_answer_count'] = 0;
			$res['correct_answer_string'] = '';
			$res['question_count'] = $question_count;
			$res['score'] = 0;
		}
		return $res;
	}
	
	/**
	 * 输入秒，返回分钟
	 */
	function s2m($i){
		if(!$i){
			return 0;
		}
		return ceil($i/60);
	}
	
	function array_to_query_str($arr){
		$query_string = null;

		if(is_array($arr)){
			foreach($arr as $key=>$val){
				$query_string .= "&$key=$val";
			}	
			$query_string = ltrim($query_string,'&');
		}
		return $query_string;
	}

	function get_answer_way_desc($num){
			switch ($num){
				 case 1: return '交作业后立刻获得';break;
				 case 2: return '作业时间截止后获得';break;
				 case 3: return '作业截止后第二天5pm后可查看';break;
			}
	}
