<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-6-14
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 */
class User_Model extends MY_Model {
	private $table= 'champion_user';

	/** 根据id得到一个用户
	 * @param $user_id
	 * @return mixed
	 */
	public function get_one_user ($user_id) {
		$this->db->where('id', $user_id);
		$this->db->where('is_online', 1);
		return $this->db->get($this->table)->row();
	}

	/** 得到所有数据的数量
	 * @return mixed
	 */
	public function get_all_user_count() {
		$this->db->where('is_online', 1);
		return $this->db->get($this->table)->num_rows();
	}

	/** 得到一页的用户数据
	 * @param $offset
	 * @param $page_size
	 */
	public function get_all_user($offset, $page_size) {
		$this->db->where('is_online', 1);
		$this->db->order_by('quarter', 'DESC');
		return $this->db->get($this->table, $page_size, $offset)->result_array();
	}


	/**
	 * add/modify
	 */ 
	public function replace($data){
		extract($data);
		$this->db->query('REPLACE INTO champion_user(id,name,short_msg,thumb_url,little_thumb_url,school,score,university,exp_desc,note,quarter,is_online) 
			VALUES(?,?,?,?,?,?,?,?,?,?,?,?)', array($id, $name, $short_msg, $thumb_url, $little_thumb_url, $school, $score, $university, $exp_desc, $note, $quarter, $is_online));
		return $this->db->affected_rows();
	}

	public function status($id, $status){
		$this->db->where('id', $id);
		$this->db->update($this->table, array('is_online' => $status));
		return $this->db->affected_rows();
	}



}