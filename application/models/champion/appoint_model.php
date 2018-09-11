<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-6-14
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 */
class Appoint_Model extends MY_Model {
	private $table= 'champion_appoint';

	/** 新增一行数据
	 * @param $param
	 * @return mixed
	 */
	public function insert_appoint($param) {
		return $this->db->insert($this->table, $param);
	}

	/** 根据用户id和状元id 看有没有预约过
	 * @param $user_id
	 * @param $champion_user_id
	 * @return mixed
	 */
	public function get_one_appoint($user_id, $champion_user_id) {
		$this->db->where('user_id', $user_id);
		$this->db->where('champion_user_id', $champion_user_id);
		return $this->db->get($this->table)->row();
	}

	/** 获得用户的预约信息
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_appoints ($user_id) {
		$this->db->where('user_id', $user_id);
		$tmp = $this->db->get($this->table)->result_array();
		$return = array();
		foreach ($tmp as $kt => $vt) {
			$return[$vt['champion_user_id']] = $vt;
		}
		return $return;
	}




}