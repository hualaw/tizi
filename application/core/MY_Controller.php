<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends LI_Controller{

	protected $_check_authcode=false;

	public function __construct()
	{
		echo("In MY_Controller construct<br>");
		parent::__construct('tizi');
	}

	protected function token_list()
	{
		//不登录情况下可以使用的ajax请求
		$this->_unloginlist=array(
			'an'=>array(
				'404',
				'invite'
			),
			'ar'=>array(
				'homepage',
				'login',
				'captcha_code',
				'area',
				'schools',
				'about',
                'redirect',
                'agents_school',
                //teacher
				'teacher_home',
				'lesson_prepare',
				'dafen',
				//paper
				'paper_question',
				'paper_preview',
				'paper_exam',
				'paper_search',
				'paper_intelligent',
				//student
				'champions',
				'practice',
				'game_center',
                'game',
				'practice_training',
				//appcenter
				'appcenter',
				//feedback
				'feedback'
			),
			'r'=>array(
				'/lesson_document/flash_get_json',
				'/paper_archive/intro',
				'/my/intro',
				'/teacher_homework/intro',
				'/student_class/intro'
			)
		);

		//登录情况下不可以使用的ajax请求
		$this->_dnloginlist=array(
			'ar'=>array(
				//'register_football'
			)
		);

		$this->_captchalist=array(
			'r'=>array(
				'/account_bind/child_create',
				'/feedback/send_feedback',
				'/feedback/correction',
				'/download/paper',
				'/lesson_document/download_verify'
			)
		);

		$this->_authcodelist=array(
			'r'=>array(
				'/download/paper',
				'/lesson_document/download_verify'
			)
		);

		$this->_postlist=array(
			'r'=>array(
				//'/login/submit',
				//'/feedback/send_feedback'
				'/cloud_base/download_verify',
				'/cloud_base/add_download_count',
				'/student_homework/video_submit',
			)
		);
	}

}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
