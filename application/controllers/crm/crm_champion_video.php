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
class Crm_Champion_Video extends Crm_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->model('champion/video_model');
	}

	/**
	 * 更新状元开讲 播放次数更新
	 */
	public function update_champion_video_play_times() {
		$post_arr = json_decode($this->input->post('data', true));

		foreach ($post_arr as $kp => $vp){
			$this->video_model->update_video($vp->vid, array('play_nums' =>$vp->play_nums));
		}
	}
}