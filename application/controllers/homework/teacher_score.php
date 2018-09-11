<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once "teacher_homework.php";
class Teacher_Score extends Teacher_Homework {

    private $_smarty_dir="teacher/homework/";

    function __construct() {
        parent::__construct();
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $this->load->model('homework/check_zuoye_model');
    }
 

    //分数明细
    function score($assignment_id){
        $zuoye = $this->zuoye_model->get_assignment($assignment_id);
        if(!$zuoye or $zuoye['user_id']!=$this->tizi_uid ){redirect(site_url('zuoye'));}
        $alpha_class_id = alpha_id_num($zuoye['class_id']);
        $game_info = $zuoye['unit_game_ids'];
        $this->load->model('homework/game_type_model');
        $game_entity = array();
        if($game_info){
            $game_info = json_decode($game_info,true);
            foreach($game_info as $k=>$val){
                if(isset($val['game_type_id']) and intval($val['game_type_id'])){
                    $tmp = $this->game_type_model->get_game_type($val['game_type_id']);
                    $game_entity[$k] = array('game_id'=>$val['game_id'],'type_name'=>$tmp['type_name']);
                }else{
                    $game_entity[$k] = $this->game_type_model->get_game_with_game_type($val['game_id']);
                }
                
            }
        }

        $res = $this->check_zuoye_model->finish_situation($assignment_id);
        $class_info = $res['class_info'];//平均分等统计数据
        $stu_info = $res['stu_info']; 

        // var_dump($game_entity,$stu_info);
        $this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');//排 名次
        $this->tepm->array_sort_by_keys($stu_info,array('correct_num'=>'desc','game_time'=>'asc'));

        $this->load->helper('time');
        
        $this->smarty->assign('game_entity',$game_entity);
        $this->smarty->assign('class_info',$class_info);
        $this->smarty->assign('stu_info',$stu_info);
        $this->smarty->assign('zuoye',$zuoye);


        $this->smarty->assign('alpha_class_id',$alpha_class_id);
        $this->smarty->display($this->_smarty_dir.'detail_scores.html');
    }
 



}