<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-6-14
 * Time: 上午11:45
 * To change this template use File | Settings | File Templates.
 */
class Video_Model extends MY_Model {
	private $video_table= 'champion_video';
	private $user_table= 'champion_user';

	/** 根据id得到一个视频信息
	 * @param $video_id
	 * @return mixed
	 */
	public function get_one_video ($video_id) {
		$this->db->where('id', $video_id);
		$this->db->where('is_online', 1);
		return $this->db->get($this->video_table)->row();
	}

	public function get_video_play_nums ($video_id) {
		$r_key = 'zhuangyuan_video_' . $video_id;

		$this->load->model('redis/redis_model');
		if($this->redis_model->connect('statistics')){
			if ($play_nums = $this->cache->redis->hget($r_key, 'zhuangyuan')) {
				//redis 视频播放量 + 1
				$this->cache->redis->hset($r_key, 'zhuangyuan',  $play_nums + 1);
				return $play_nums;
			}
		}

		$video_info = $this->get_one_video($video_id);
		//redis 视频播放量 + 1
		$this->cache->redis->hset($r_key, 'zhuangyuan', $video_info->play_nums + 1);
		$this->cache->redis->expire($r_key, Constant::ZHUANGYUAN_VIDEO_TIMEOUT);
		return $video_info->play_nums;
	}

	/** 得到用户的第一段视频
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_first_video ($user_id) {
		$sql = "SELECT * FROM {$this->video_table} WHERE champion_user_id = {$user_id} LIMIT 1";
		return $this->db->query($sql)->row();
	}

	/** video 表 字段值 + 1
	 * @param $video_id
	 * @param $field_name
	 * @return mixed
	 */
	public function add_field_add_one ($video_id, $field_name) {
		$this->db->where('id', $video_id);
		$this->db->set($field_name, $field_name . ' + 1', false);
		return $this->db->update($this->video_table);
	}
	/** 得到所有的视频数据
	 * @return mixed
	 */
	public function get_all_video_info($user_id = 0) {
		$where = '';
		if ($user_id) {
			$where = " AND v.champion_user_id = {$user_id}";
		}
		$sql = "SELECT v.id vid,u.id uid,v.*, u.* FROM {$this->video_table} v LEFT JOIN {$this->user_table} u ON v.champion_user_id = u.id WHERE v.is_online = 1 AND u.is_online = 1 {$where}";
		return $this->db->query($sql)->result_array();
	}
	/** 获取播放列表 右侧除当前点击 状元外的其他状元视频
	 * @param $no_user_ids
	 * @param $limit
	 * @return mixed
	 */
	public function get_others_not_users_video ($no_user_ids, $limit) {
		$sql = "SELECT v.id vid,u.id uid,v.*, u.* FROM {$this->video_table} v LEFT JOIN {$this->user_table} u ON v.champion_user_id = u.id WHERE v.id NOT IN ({$no_user_ids}) AND v.is_online = 1 AND u.is_online = 1 ORDER BY v.play_nums DESC LIMIT {$limit}";
		return $this->db->query($sql)->result_array();
	}

	/** 更新视频信息
	 * @param $id
	 * @param $param
	 * @return mixed
	 */
	public function update_video($id, $param) {
		$this->db->where('id', $id);
		return $this->db->update($this->video_table, $param);
	}
	
	public function get_of_video($video_id) {
		$this->db->where('id', $video_id);
		return $this->db->get($this->video_table)->row();
	}
	
	/**
	 * add/modify
	 */
	public function replace($data){
		if ($this->get_of_video($data["id"])){
			$id = $data["id"];
			unset($data["id"]);
			unset($data["play_nums"]);
			unset($data["praise_nums"]);
			$this->db->where("id", $id);
			$this->db->update($this->video_table, $data);
		} else {
			$this->db->insert($this->video_table, $data);
		}
		return $this->db->affected_rows();
	}
	
	public function status($id, $status){
		$this->db->where('id', $id);
		$this->db->update($this->video_table, array('is_online' => $status));
		return $this->db->affected_rows();
	}

}