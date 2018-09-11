<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-6-14
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 */
class Question_Model extends MY_Model {
	private $table= 'football_exercises';

	/** 获取随机的试题
	 * @param $type_id
	 * @param $limit
	 * @return mixed
	 */
	public function get_training_question ($type_id, $limit) {
		$sql = "SELECT id, body FROM {$this->table} WHERE qtype = {$type_id} ORDER BY RAND() LIMIT {$limit}";
		return $this->db->query($sql)->result();
	}

	/** 获取试题表的字段信息
	 * @param $question_id
	 * @param $field
	 * @return mixed
	 */
	public function get_field_by_id ($question_id, $field) {
		$sql = "SELECT {$field} FROM {$this->table} WHERE id = {$question_id}";
		return $this->db->query($sql)->row();
	}
}