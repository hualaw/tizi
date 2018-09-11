<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-3-1
 * Time: 上午11:39
 * To change this template use File | Settings | File Templates.
 */
if (!defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");
class crm_oral_daily extends Crm_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('video/stu_video_model');
	}

	/**
	 * 更新每日口语播放次数(crm调用)
	 */
	public function update_stu_video_play_times() {
		$post_arr = json_decode($this->input->post('data', true));

		foreach ($post_arr as $kp => $vp){
			$this->stu_video_model->update_video($vp->sv_id, array('play_times' =>$vp->play_times));
		}

	}
}