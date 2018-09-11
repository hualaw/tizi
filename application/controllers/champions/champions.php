<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Controller.php';
class Champions extends Controller {
	private $page_num = 20;
    function __construct() {
        parent::__construct();
		$this->load->model('champion/user_model');
		$this->load->model('champion/video_model');
		$this->load->model('champion/appoint_model');
	}

    // 首页
	public function index($page_index = 1){

		$template='student/champions/index.html';
        $cache_id="zhuangyuan_p".$page_index;
        if(!$this->smarty->isCached($template, $cache_id))
        {
			$count = $this->user_model->get_all_user_count();
			$champions = $this->user_model->get_all_user(($page_index - 1) * $this->page_num, $this->page_num);

			$this->load->model('champion/appoint_model');
			$my_appoint = array();
			if ($this->tizi_uid) {
				$my_appoint = $this->appoint_model->get_user_appoints($this->tizi_uid);
			}

			$page_config = array(
				'prev_link' =>'上一页',
				'next_link' =>'下一页',
				'per_page' => $this->page_num,
				"page_query_string" => false,
				"uri_segment" => 2,
				"base_url" => site_url()."zhuangyuan",
			);

			$page_str = $this->get_pagination($page_index, $count, '', $page_config);

			$this->smarty->assign('champions', $champions);
			$this->smarty->assign('my_appoint', $my_appoint);
			$this->smarty->assign('page_str', $page_str);
		}
		$this->smarty->display($template, $cache_id);
	}
    // 观看视频页面
	public function video($uid = 1, $video_id = 0){
		if (!$uid) {
			tizi_404('');
			exit;
		}

		$video = null;

		if (!$video_id) {
			$video = $this->video_model->get_user_first_video($uid);
		} else {
			$video = $this->video_model->get_one_video($video_id);
		}

		$user = $this->user_model->get_one_user($uid);
		$all_video = $this->video_model->get_all_video_info($uid);

		$appoints = array();
		if ($this->tizi_uid) {
			$appoints = $this->appoint_model->get_one_appoint($this->tizi_uid, $user->id);
		}

		$vids = array();
		foreach ($all_video as $ka => $va) {
			$vids[$ka] = $va['vid'];
		}
		$all_video_len = count($all_video);

		if (empty($all_video_len)) {
			tizi_404('');
			exit;
		}

		if ($all_video_len < 10) {
			$other_video = $this->video_model->get_others_not_users_video(implode(',', $vids), 10 - $all_video_len);
			$this->smarty->assign('other_video', $other_video);
		}

		$video_play_nums = $this->video_model->get_video_play_nums($video->id);

		$this->load->config('qiniu', true, true);
		$configs = $this->config->item('qiniu');

		$video_domain = (strpos($video->video_path, 'static/zhuangyuan') !== false) ? 'oss-tizi' : $configs['video_domain'];

		$this->smarty->assign('video', $video);
		$this->smarty->assign('video_domain', $video_domain);
		$this->smarty->assign('all_video', $all_video);
		$this->smarty->assign('user', $user);
		$this->smarty->assign('appoints', $appoints);
		$this->smarty->assign('video_play_nums', $video_play_nums);
		$this->smarty->display('student/champions/video.html');
	}

	/**
	 * 视频点赞
	 */
	public function praise() {
		$video_id = $this->input->post('video_id', true);
		//参数有误
		if (!$video_id) {
			echo -1;
			exit;
		}

		if ($this->video_model->add_field_add_one($video_id, 'praise_nums')) {
			echo 1;
		} else {
			//更新失败
			echo -2;
		}
		exit;
	}

	/**
	 * 预约
	 */
	public function appoint () {
		$data = array();
		$data['name'] = $this->input->post('userName', true, true);
		$data['phone'] = $this->input->post('userPhone', true, true);
		$data['email'] = $this->input->post('userEmail', true, true);
		$data['champion_user_id'] = $this->input->post('championId', true);

		$error = array('status' => 0, 'error_code' => '');
		if (empty($data['name']) || empty($data['phone']) || empty($data['email']) || empty($data['champion_user_id'])) {
			$error['status'] = -1;
			$error['error_code'] = '非法操作！';
			echo json_token($error);
			exit;
		}

		$data['user_id'] = $this->tizi_uid;
		$data['appoint_time'] = time();


		if ($this->appoint_model->get_one_appoint($data['user_id'], $data['champion_user_id'])) {
			$error['status'] = -2;
			$error['error_code'] = '您已经预约过该状元！';
			echo json_token($error);
			exit;
		}

		if ($this->appoint_model->insert_appoint($data)) {
			$error['status'] = 1;
			$error['error_code'] = '预约成功！<br />我们会第一时间通知您线上“见面会”时间，敬请关注。';
			echo json_token($error);
		} else {
			$error['status'] = -3;
			$error['error_code'] = '预约失败！';
			echo json_token($error);
		}
		exit;
	}

}