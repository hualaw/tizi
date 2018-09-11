<?php
/**
 * @author saeed
 * @date   2013-8-30 * @description 专项自主练习-首页
 */
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'practice_base.php');
class Practice extends Practice_Base{

    protected $_practice_redis;

    public function __construct(){
        parent::__construct();
        $this->load->model('practice/practice_model');
        $this->_statistics_redis = $this->practice_model->connect_redis('practice_statistics');
    }
    /**
     * @info 首页 
     */
    public function index($grade_id = 1){

		if(!$grade_id) redirect();
        
        $category = $this->input->cookie(Constant::COOKIE_STUDENT_PK.'grade');
        $sub_id = $this->input->cookie(Constant::COOKIE_STUDENT_PK.'sub');

        $status = true;
        $category = (int)$category;
        $category = ($category>7)?1:$category;
        if(preg_match("/.*pk.*/", current_url())){
            if(!empty($category)){
                $grade_id = $category;
            }
        }else{
            if(!empty($category)){
                if($category == $grade_id){
                    $status = false;
                }
            }
            if($status) $this->_save_category('grade', $grade_id);
        }

        $sub_id = (int)$sub_id;
        if (!$sub_id || !in_array($sub_id, $this->_get_grade_sub($grade_id))) {
            $sub_id = 1;
        }

        $template=$this->_smarty_dir.'practice.html';
        $cache_id="pk_g".$grade_id."_s".$sub_id;
        if(!$this->smarty->isCached($template, $cache_id))
        {
            $this->smarty->assign('sub_id', $sub_id);
            $this->get_practice_list($grade_id);
            $this->_update_grade_sub($grade_id, array_keys($this->c_subject));
            //for seo
            $grades = Constant::practice_grade();
            $subject_game = array();
            if(in_array($grade_id, array(1, 8, 12))){
                
                if($grade_id == 1){
                    $grade_name = Constant::grade_type(1);
                }elseif($grade_id == 8){
                    $grade_name = Constant::grade_type(2);
                }elseif($grade_id == 12){
                    $grade_name = Constant::grade_type(3);
                }
                foreach($this->c_subject as $subject){
                    $subject_game[] = $grade_name.$subject.'游戏';   
                }
                $title = "{$grade_name}生游戏_{$grade_name}生学习游戏_".implode("_", $subject_game);
                $keywords = "{$grade_name}生游戏,{$grade_name}游戏,".implode(',', $subject_game);
                $description = "梯子网提供为全国{$grade_name}生提供好玩生动学习型的{$grade_name}游戏，包括".implode(',', $subject_game);
            }else{
                if($grade_id < 8){
                    $grade_name_1 = Constant::grade_type(1);
                }elseif($grade_id < 12){
                    $grade_name_1 = Constant::grade_type(2);
                }else{
                    $grade_name_1 = Constant::grade_type(3);
                }
                $grade_name = $grades[$grade_id];
                foreach($this->c_subject as $subject){
                    $subject_game[] = $grade_name.$subject.'游戏';   
                }
                $title = "{$grade_name}学习游戏_".implode("_", $subject_game); 
                $keywords = "{$grade_name}学习游戏,".implode(',', $subject_game);
                $description = "梯子网为全国{$grade_name_1}生提供生动好玩的{$grade_name}学习游戏，包括".implode(',', $subject_game)." 等。";
            }

    		$this->get_participants_stats();
            $this->smarty->assign('title', $title);
            $this->smarty->assign('keywords', $keywords);
            $this->smarty->assign('description', $description);
            $this->smarty->assign('grade_id', $grade_id);
        }
        $this->smarty->display($template, $cache_id);

    }

    public function record_sub() {

        $sub_id = $this->input->post('sub_id', true);
        $sub_id = (int)$sub_id;

        if($sub_id){
            $this->_save_category('sub', $sub_id);
            $this->_info_handle(99, $sub_id);
        }
        $this->_info_handle(1, $sub_id);
    
    }
        
	private function get_participants_stats(){
			
		$stats = $this->practice_statistics_model->participants_top(10);
		$this->smarty->assign('participants_stats', $stats);

	}

    private function _save_category($name, $val){

        $data = array(
            'name'   => $name,
            'value'  => $val,
            'expire' => 86400 * 30,
            'prefix' => Constant::COOKIE_STUDENT_PK,
        );
        $this->input->set_cookie($data);
    
    }

    private function _update_grade_sub($grade_id, $sublist) {

        $this->_statistics_redis->hset("grade_sub", $grade_id, json_encode($sublist));
               
    }

    private function _get_grade_sub($grade_id) {
        
        $sublist = array();
        if (!$this->_statistics_redis) return $sublist;
        $sublist_str = $this->_statistics_redis->hget("grade_sub", $grade_id);
        if (!empty($sublist_str)) {
            $sublist = json_decode($sublist_str, true);
        }
        return $sublist;
    }
	



}
