<?php
/**
 * Game Center
 */
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'practice_base.php');
class Game_Center extends My_Controller{

	public function __construct(){
		
		parent::__construct();
        $this->load->helper('time');
        $this->load->model('practice/practice_questions_model');
		$this->load->model('practice/practice_challenge_model');
	}

	public function index($game_id = 0){
		
		if (!$game_id) redirect();
		$this->getGameMoudle($game_id)->index($game_id);
		
	}

	public function get_question($game_id = 0){
		
		if (!$game_id) exit();
		$this->getGameMoudle($game_id)->get_question($game_id);
	}

	public function submit(){

		$game_id = $this->input->post('id');
		$s_f = explode("_", $game_id);
		$p_c_id = (int)$s_f[1];
		if (!$game_id) exit();
		$this->getGameMoudle($p_c_id)->submit($game_id);
			
	}

	private function getGameMoudle($game_id){

        $p_c_info = $this->practice_model->get_category_info($game_id);
		$game_type = (int)$p_c_info['game_type'];
		if(!$game_type) redirect();
		$module_n  = 'game'.$game_type;
		require_once(__DIR__.'/'.$module_n.'.php');
		return new $module_n();

	}




	
	
}

