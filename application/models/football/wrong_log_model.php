<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-6-14
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 */
class Wrong_Log_Model extends MY_Model {
	private $table= 'football_question_wrong_log';

	/** 获得错题和题目信息
	 * @param $user_id
	 * @param $offset
	 * @param $page_size
	 * @return array
	 */
	public function get_wrong_history_info_by_user ($user_id, $history_id, $offset, $page_size) {
		$where = (!empty($history_id)) ? " AND history_id = {$history_id} " : '';
		$is_page = (!empty($offset) && !empty($page_size)) ? " LIMIT {$offset}, {$page_size} " : '';
		$sql = "SELECT * FROM {$this->table} WHERE user_id = {$user_id} {$where} ORDER BY history_id DESC {$is_page}";
		$tmp =  $this->db->query($sql)->result();

		$question_ids = array();
		$wrong_log = array();
		$exercises = array();
		if (count($tmp) > 0) {
			foreach ($tmp as $kt => $vt) {
				$wrong_log[$vt->id] = $vt;
				$question_ids[] = $vt->question_id;
			}

			$question_ids_str = implode(',', $question_ids);
			$sql_q = "SELECT * FROM football_exercises WHERE id IN ({$question_ids_str})";
			$tmp2 = $this->db->query($sql_q)->result();

			foreach ($tmp2 as $ktt => $vtt) {
				$exercises[$vtt->id] = $vtt;
			}
		}
		return array($wrong_log, $exercises);
	}

	public function get_user_wrong_count ($user_id, $history_id) {
		$this->db->where('user_id', $user_id);
		if ($history_id) $this->db->where('history_id', $history_id);
		return $this->db->get($this->table)->num_rows();
	}

	/** 插入答题信息
	 * @param $param
	 * @return mixed
	 */
	public function insert_wrong_log ($history_id, $user_id, $qtype, $wrong_log) {
		foreach ($wrong_log as $key=>&$val) {
			$data = $this->db->get_where($this->table,array('user_id' => $user_id, 'question_id' => $val['id']))->result();
			if($data){
				$this->db->where(array('user_id' => $user_id, 'question_id' => $val['id']));
				$this->db->update($this->table, array('history_id'=>$history_id,'sel_option'=>$val['option']));
				unset($wrong_log[$key]);
			}
		}
		if (count($wrong_log) > 0) {
			$insert_sql = "INSERT INTO {$this->table}(`user_id`,`history_id`,`qtype`,`question_id`,`sel_option`) VALUES ";
			foreach ($wrong_log as $q) {
				$insert_sql.= "('{$user_id}','{$history_id}','{$qtype}','{$q['id']}','{$q['option']}'),";
			}
			$insert_sql = trim($insert_sql,',');
			$this->db->trans_start();
			$this->db->query($insert_sql);
			$this->db->trans_complete();
			if (FALSE === $this->db->trans_status())
			{
				log_message('Error', "Add Training Wrong Log: history_id:{$history_id}");
				return false;
			}
		}

		return true;
	}
}