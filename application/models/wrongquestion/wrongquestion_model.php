<?php
/**
 *  出错本模块
 *  @package models
 *  @author huangjun
 *	@version 1.0
 */
class Wrongquestion_Model extends MY_Model {  
	private $_question_table = 'exercise';//'question';//练习题表
	private $_question_category_table = 'exercise_category';//"question_category";//练习题知识点表
    /**
	 * 构造函数
	 * @access public
	 */
	 public function __construct(){
        parent::__construct();		
     }
	 
	/**
	 * 保存错题信息
	 * @param  array $wrong_questions   错题信息数组，数组元素:array("question_id"=>题目id, "category_id"=>知识点id, "wrong_nums"=>错题数量)
	 * @param  int   $user_id           用户id
	 * @param  int   $assignment_id       作业id
	 * @return bool  成功保存则返回true,失败则返false
	 */
	 private function saveQuestion($wrong_questions, $user_id, $assignment_id){
		if(empty($wrong_questions)){
			return FALSE;
		}
		
		$values  = '';//错题插入值集合
		$modified = date("Y-m-d H:i:s", time()); //当前时间

		foreach($wrong_questions as $wrong_question){
			$values .= '(' . $user_id . ',' . $wrong_question["question_id"] . ',' . $wrong_question["category_id"] . ',' . $assignment_id . ',"' . $modified . '"),';
			
		}
		$values = trim($values, ',');
		
		$sql = "INSERT INTO `wrong_question` (`user_id`,`question_id`,`category_id`,`assignment_id`,`modified`) values " .  $values 
					. " ON DUPLICATE KEY UPDATE modified='" . $modified . "'";
		$this->db->query($sql);
		return $this->db->affected_rows() ? TRUE : FALSE;		
	 }
	 
	/**
	 * 保存知识点统计信息
	 * @param  array $categories    错题信息数组，数组元素:知识点id=>array('wrong_nums'=>该知识点错题数量 , 'nums'=>该知识点做题数量)
	 * @param  int   $user_id       用户id
	 * @param  int   $assignment_id 作业id
	 * @return bool  成功保存则返回true,失败则返false
	 */
	 private function saveStatistics($categories, $user_id, $assignment_id){
		if(empty($categories)){
			return FALSE;
		}
		
		$values  = '';//知识点统计信息插入值集合
		$created = date("Y-m-d H:i:s", time()); //当前时间
		
		foreach($categories as $categorie_id => $categorie){
			$values .= '(' . $user_id . ',' . $assignment_id . ',' . $categorie_id . ',' . $categorie["subject_id"] . ',' . $categorie["wrong_nums"] . ',' . $categorie["nums"] . ',"' . $created . '"),';
		}
		$values = trim($values, ',');

		$sql = "INSERT INTO `wrong_statistics` (`user_id`,`assignment_id`,`category_id`,`subject_id`,`wrong_nums`,`nums`,`created`) values " .  $values 
					. " ON DUPLICATE KEY UPDATE created='" . $created . "'";
		$this->db->query($sql);
		
		return $this->db->affected_rows() ? TRUE : FALSE;
	 }
	 
	/**
	 * 保存错题记录
	 * @param  int   $history_questions  错题信息，array("id"=>题目做对为true,做错为false)
	 * @param  int   $user_id            用户id
	 */
	 private function saveQuestionHistory($history_questions, $user_id){
		if(empty($history_questions)){
			return FALSE;
		}
		
		$values  = '';//知识点统计信息插入值集合
		$modified = date("Y-m-d H:i:s", time()); //当前时间

		foreach($history_questions as $history_question){
			$values .= "(" . $user_id . "," . $history_question["question_id"] . "," . $history_question["category_id"] . "," . $history_question["latest_success"] . ',"' . $modified . '"),';
		}
		$values = trim($values, ',');
		
		$sql = "INSERT INTO `wrong_success_question` (`user_id`,`question_id`,`category_id`,`latest_success`,`modified`) values " .  $values 
					. " ON DUPLICATE KEY UPDATE modified='" . $modified . "'";
		$this->db->query($sql);

		return $this->db->affected_rows() ? TRUE : FALSE;
	}

	/**
	 * 根据题目id集合存入错题信息
	 * @access public
	 * @param  int   $wrong_infos   错题信息，array("题目id"=>题目做对为1,做错为0)
	 * @param  int   $user_id       用户id
	 * @param  int   $subject_id    科目id
	 * @param  int   $assignment_id 作业id
	 */
	 public function saveWrongInfos($wrong_infos, $user_id, $subject_id, $assignment_id){
		if(empty($wrong_infos) || empty($user_id)) return false;
		$categories        = array();				  //各知识点的做题数量nums和错题数量wrong_nums
		$questions         = array();				  //题目对应的多个知识点
		$wrong_questions   = array();
		$history_questions = array();
	
		$question_ids    = '(' . implode(',', array_keys($wrong_infos)) . ')';
		$sql             = 'SELECT question_id,category_id FROM ' . $this->_question_category_table . ' WHERE question_id in ' . $question_ids;
		$query           = $this->db->query($sql);
		$results         = $query->result_array();

		if(empty($results)){
			return FALSE;
		}
			
		foreach($results as $result){
			$questions[$result['question_id']][] = intval(trim($result['category_id']));
		}
		foreach($wrong_infos as $id => $success){
			$id = intval($id);
			if($id <= 0) continue;
			if(isset($questions[$id])){
				foreach($questions[$id] as $category_id){//题目可能有多个知识点
					$categories[$category_id]['wrong_nums'] = 0;
					$categories[$category_id]['nums'] = 0;
				}
			}
		}

		foreach($wrong_infos as $id => $success){
			$id = intval($id);
			if($id <= 0) continue;
			if(isset($questions[$id])){
				foreach($questions[$id] as $category_id){//题目可能有多个知识点
					if($success == 0){//错题
						$wrong_questions[] = array("question_id"=>$id, "category_id"=>$category_id);
						$categories[$category_id]['wrong_nums'] += 1;
					}
				
					$history_questions[] = array("question_id"=>$id, "category_id"=>$category_id, "latest_success"=>intval($success));
				
					$categories[$category_id]['subject_id'] = $subject_id;
					$categories[$category_id]['nums'] += 1;
				}
			}
		}
		
		if(!empty($wrong_questions)){//保存错题信息
			$this->saveQuestion($wrong_questions, $user_id, $assignment_id);			   
		}

		$this->saveQuestionHistory($history_questions, $user_id);      //保存各题目做对做错的历史记录，用于掌握程度
		$this->saveStatistics($categories, $user_id, $assignment_id);  //保存知识点统计信息
	 }

	/**
	 * 获取用户所选知识点所做错题,即重做错题
	 * @param  mixed $category 知识点id数组或以,分割的知识点id字符串
	 * @param  int   $user_id  用户id
	 * @param  int   $page     当前页面数
	 * @param  int   $per_page 每页多少记录
	 * @return array 该用户重做错题信息数组
	 */
	 public function redoWrongQuestions($category, $user_id, $page=1, $per_page=30){
		if(is_array($category)){
			$category = array_map("intval", $category);
			$category = implode(',', $category);
		}
		$questions = array(); //所有错题信息数组
		
		$start = ($page-1) * $per_page;
		$limit = " LIMIT " . $start . "," . $per_page;
		//获取选中知识点的最新错题
		$sql   = "SELECT DISTINCT question_id FROM `wrong_question` WHERE user_id=" . $user_id . " AND category_id in (" . $category . ") ORDER BY modified " . $limit;

		$query = $this->db->query($sql);
		$question_id_arr = $query->result_array();
		$question_ids    = ''; //该知识点错题id集合
		if(!empty($question_id_arr)){
			foreach($question_id_arr as $question){
				$question_ids .= intval(trim($question["question_id"])) . ',';
			}
			$question_ids = trim($question_ids, ',');
			
			$sql   = "SELECT * FROM `" . $this->_question_table . "` WHERE id in (" . $question_ids . ")";
			$query = $this->db->query($sql);
			$questions = $query->result_array();
		}		

		return $questions;
	 }
	 
	 /**
	 * 获取用户所选知识点所做错题总数,即重做错题
	 * @param  mixed $category 知识点id数组或以,分割的知识点id字符串
	 * @param  int   $user_id  用户id
	 * @return array 该用户重做错题信息数组
	 */
	 public function redoWrongCounts($category, $user_id){
		if(is_array($category)){
			$category = array_map("intval", $category);
			$category = implode(',', $category);
		}
		$counts = array(); //所有错题信息数组

		//获取选中知识点的最新错题
		$sql   = "SELECT count(DISTINCT question_id) as nums FROM `wrong_question` WHERE user_id=" . $user_id . " AND category_id in (" . $category . ")";

		$query = $this->db->query($sql);
		$counts = $query->result_array();
		$counts = $counts[0]["nums"];		

		return $counts;
	 }
	 
	 /**
	  *	获取用户在科目指定库里的做题统计信息
	  * @param  int   $user_id      用户id
	  * @param  int   $subject_id   科目id 
	  */
	 public function getWrongQuestions($user_id, $subject_id){
		$sql      = "SELECT category_id,sum(`wrong_nums`) as wrong_nums,sum(`nums`) as nums FROM `wrong_statistics` WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id . 
						" GROUP BY category_id";

		$query    = $this->db->query($sql);
		$cat_nums = $query->result_array();
		$result   = array();
		foreach($cat_nums as $cat){
			$result[$cat["category_id"]] = $cat;
		}

		return $result;
	 }
	 
	/**
	 *	获取用户的某知识点最新若干道题的掌握程度
	 *  @param  array $recentQuestions 该知识点最新题目数组，"问题id" => array("level"=>"难度程度", "success"=>"是否做对1/0");
	 *  @return int   $extendMaxIndex  用户该知识点掌握程度
	 */
	 public function getStudyExtend($recentQuestions){
		if(empty($recentQuestions) || count($recentQuestions) <= Constant::WRONG_CONFIG_QUESTION_MINS) return 0;
		
		$level_statistics = array();	//记录各难度的做题数量和做对数量
		$success_sum	  = 0;			//总共做对题数量
		foreach($recentQuestions as $recentQuestion){
			$level_statistics[$recentQuestion["level"]]['nums'] = isset($level_statistics[$recentQuestion["level"]]) && isset($level_statistics[$recentQuestion["level"]]['nums']) ? ($level_statistics[$recentQuestion["level"]]['nums']+1) : 1;
			$level_statistics[$recentQuestion["level"]]['success_nums'] = isset($level_statistics[$recentQuestion["level"]]) && isset($level_statistics[$recentQuestion["level"]]['success_nums']) ? ($level_statistics[$recentQuestion["level"]]['success_nums']+$recentQuestion["success"]) : $recentQuestion["success"];
			$success_sum += $recentQuestion["success"];
		}
		if($success_sum < count($recentQuestions) * Constant::WRONG_CONFIG_SUCCESS_MIN_RATIO){//题目正确率小于该值则返回最低用户等级
			return 1;
		}
		
		foreach($level_statistics as $key => &$level_statistic){//计算各难度等级的加权数量		
			$weightNums = $key * $level_statistic["nums"];
			$level_statistic["weightNums"] = $weightNums;
		}

		$extendMax = 0;		//记录用户1-10个等级的最大概率
		$extendMaxIndex = 0;//记录用户等级
		for($i = -5; $i <= 15; ++$i){//生成用户1-10个等级的概率，最高值为用户掌握程度
			$extend = 0;//该用户等级下的掌握程度
			foreach($level_statistics as $key => &$level_statistic){
				$extend += $level_statistic["weightNums"] * $this->getDifficultExtend($level_statistic['nums'], $level_statistic['success_nums'], $key, $i);
			}

			if($extendMax < $extend){
				$extendMax = $extend;
				$extendMaxIndex = $i;
			}
		}
		
		if($extendMaxIndex < 1){//用户掌握程度合法等级为1，2，3，4，5，6，7，8，9，10
			$extendMaxIndex = 1;
		}elseif($extendMaxIndex > 10){
			$extendMaxIndex = 10;
		}
		return $extendMaxIndex;
	 }
	 
	/**
	 *	获取用户掌握程度若为$extend则在该知识点某一难度下$nums道题目做对$success_nums的概率
	 *  @param  int $nums 		  某一难度做题数量
	 *  @param  int $success_nums 某一难度做对的数量
	 *  @param  int $level		  难度级别
	 *  @param  int $extend		  用户掌握程度等级
	 *  @return double			  做对概率
	 */
	 private function getDifficultExtend($nums, $success_nums, $level, $extend){
		$probability = $this->getProbability($level, $extend);		
		$extend      = $this->computeExtend($nums, $success_nums, $probability);

		return $extend;
	 }
	 
	/**
	 *  计算某难度下做对概率
	 *  @param  int     $nums 		 某一难度做题数量
	 *  @param  int     $success_nums 某一难度做对的数量
	 *  @param  double  $probability  做对一道某难度的概率
	 *  @return double  做对概率
	 */
	 private function computeExtend($nums, $success_nums, $probability){ 
		$up_nums = $success_nums * 2 > $nums ? ($nums-$success_nums) : $success_nums; //Cn(下标)m(上标) 使上标$up_nums尽量小		
		if($up_nums <= 0){
			$mainVal = array();
			$subArr  = array();
		}else{
			$mainArr = range($nums, $nums-$up_nums+1);//Cn(下标)m(上标)的分子部分,有$success_nums个数
			$subArr  = range($up_nums, 1);			   //Cn(下标)m(上标)的分母部分

			foreach($mainArr as &$mainVal){//Cn(下标)m(上标)的分子和分母消元
				foreach($subArr as $k=>$subVal){
					if($mainVal % $subVal == 0){
						$mainVal /= $subVal;
						unset($subArr[$k]);
					}
				}
			}
		}

		$result = 1;
		$i = 0;
		while($nums){
			if($success_nums > 0){//计算做对部分的概率
				if($i % 2 == 0 || $success_nums == $nums){//偶数行或做错部分已经计完则计算做对部分，否则计算做错部分概率
					$result *= $probability;
					$success_nums--;
				}else{
					$result *= (1 - $probability);
				}
			}else{
				$result *= (1 - $probability);
			}
			
			if($i < $up_nums){//$mainArr为$up_nums大小
				$result *= $mainArr[$i];
			}

			$i++;
			$nums--;
		}
		
		if(!empty($subArr)){
			foreach($subArr as $subval){
				$result /= $subval;
			}
		}

		return $result;
	 }
	 
	/**
	 *	掌握程度为$extend的用户做对$level题的概率
	 *  @param  int $level  题目难度级别
	 *  @param  int $extend 掌握程度
	 *  @return double      做对概率
	 */
	 private function getProbability($level, $extend){
	 
		$temp   = exp(Constant::WRONG_DISTINCT_EXTENDS * ($level * 2 - $extend));
		$result = Constant::WRONG_GUESS_PROBABILITY + (1 - Constant::WRONG_GUESS_PROBABILITY) / (1 + $temp);

		return $result;
	 }
	 
	/**
	 *	获取知识点下用户最新的若干道题目,上级知识点有多个下级知识点
	 *  @param  mixed $sub_cats    若干道题目的知识点id数组,或以,分割id字符串
	 *  @param  int   $user_id     用户id
	 *  @param  int   $limit       取得题量
	 *  @return array              题目数组
	 */
	 public function getLatestQuestions($sub_cats, $user_id, $limit){
		if(is_array($sub_cats)){
			$sub_cats = implode(',', $sub_cats);
		}

		$sql = "SELECT question_id,latest_success FROM `wrong_success_question` WHERE user_id=" . $user_id . " AND category_id in (" . $sub_cats . ") order by modified limit " . $limit;

		$query  = $this->db->query($sql);
		$questions = $query->result_array();
		$results   = array();
		foreach($questions as $question){
			$results[$question ["question_id"]] = array("success" => $question["latest_success"]);
		}
		
		return $results;
	 }
	 
	/**
	 * 获取指定作业某道题做错数量
	 * @param  int   $assignment_id        作业id,单个id或数组或以,分割的字符串
	 * @param  mixed $question_ids       问题id集合
	 * @return array 题目做错数量信息数组
	 */
	 public function getQuestionWrongs($assignment_id, $question_ids){
		if(!is_array($question_ids)){
			$question_id_arr = explode(",", $question_ids);
		}else{
			$question_id_arr = $question_ids;
		}
		
		$sql = 'SELECT question_id,COUNT(distinct user_id) AS counts FROM `wrong_question` WHERE assignment_id=' . $assignment_id;
		if(count($question_id_arr) <= 1){
			$sql .= ' AND question_id=' . $question_ids;
		}else{
			$sql .= ' AND question_id IN (' . implode(',', $question_id_arr) . ') GROUP BY `question_id` order by counts';
		}
		
		$query    = $this->db->query($sql);		
		$quesions = $query->result_array();
		
		return $quesions;
	 }

	/**
	 * 获取知识点的统计信息
	 * @param  array $categorys      用户知识点id数组，或以,分割的字符串
	 * @param  int   $user_id        用户id
	 */
	public function getCategoryStats($categorys, $user_id){
		if(empty($categorys)){
			return array();
		}
		
		if(is_array($categorys)){
			$categorys = implode(',', $categorys);
		}
		
		$sql      = "SELECT category_id,sum(`wrong_nums`) as wrong_nums,sum(`nums`) as nums FROM `wrong_statistics` WHERE user_id=" . $user_id . " AND category_id in (" . $categorys . 
						") GROUP BY category_id";

		$query    = $this->db->query($sql);
		$cat_nums = $query->result_array();
		$result   = array();
		foreach($cat_nums as $cat){
			$result[$cat["category_id"]] = $cat;
		}

		return $result;		
	}
}
/* end of wrongquestion_model.php */
