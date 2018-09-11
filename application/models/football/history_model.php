<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-6-14
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 */
class History_Model extends MY_Model {
	private $table= 'football_history';

	/** 获取一条刷题信息
	 * @param $type_id
	 * @param $limit
	 * @return mixed
	 */
	public function get_history_info ($id) {
		$sql = "SELECT * FROM {$this->table} WHERE id = {$id}";
		return $this->db->query($sql)->row();
	}
	/** 获取用户刷题信息
	 * @param $type_id
	 * @param $limit
	 * @return mixed
	 */
	public function get_history_info_by_user ($user_id) {
		$sql = "SELECT * FROM {$this->table} WHERE user_id = {$user_id}";
		return $this->db->query($sql)->result();
	}

	/** 得到用户答题最好的记录
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_best_history($user_id) {
		$sql = "SELECT MAX(correct_num) max_correct_num FROM {$this->table} WHERE user_id = {$user_id} LIMIT 1";
		return $this->db->query($sql)->row()->max_correct_num;
	}

	/** 插入答题信息
	 * @param $param
	 * @return mixed
	 */
	public function insert_history ($param) {
		$this->db->insert($this->table, $param);
		return $this->db->insert_id();
	}
}