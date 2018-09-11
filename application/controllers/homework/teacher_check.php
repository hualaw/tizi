<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once "teacher_report.php";
class Teacher_Check extends Teacher_Report {

    private $_smarty_dir="teacher/class/";

    function __construct() {
        parent::__construct();
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $this->load->model('homework/check_zuoye_model');
        $this->load->model('question/question_category_model');
        $this->load->model('homework/unit_model');
        $this->load->model('homework/zuoye_comment_model');
        $this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
    }

    //老师检查作业
    function check($assignment_id){
        $result = $this->check_zuoye_model->checking_zuoye($assignment_id,$this->tizi_uid);
        if(!$result){//置位失败
            redirect(site_url('zuoye'));
        }
        $type = $this->input->get('type',true,true,'');
        $pass = $this->input->get('pass',true,true,'');//某个试卷包对应的assignment_id
        $has_paper = $has_game = false;

        $zuoye = $this->zuoye_model->get_assignment($assignment_id);
        $papers = null;
        if($zuoye['paper_ids']){//如果这次作业里有试卷
            $has_paper = true;
            $this->load->model('exercise_plan/homework_assign_model');
            $papers = json_decode($zuoye['paper_ids'],true);
            foreach($papers as $ps=>$p){
                $papers[$ps]['entity'] = $this->homework_assign_model->get_assigned_homework_info_by_id($p['assignment_id']);
            }
        }
        if($zuoye['unit_game_ids']){//判断作业里有无游戏
            $has_game_json = json_decode($zuoye['unit_game_ids']);
            foreach($has_game_json as $hh=>$has){
                $has_game = (isset($has->game_id))?true:false;
            }
        }
        if(!$zuoye){redirect(site_url('zuoye'));}
        $alpha_class_id = alpha_id_num($zuoye['class_id']);
        $class_info = $this->get_school_class_name($zuoye['class_id']);
        $class_name = implode('', $class_info);//班级名称
        $tmp = $bb = array();
        $_unit_ids = explode(',', $zuoye['unit_ids']);
        if($_unit_ids){
            foreach($_unit_ids as $_us=>$_u){
                $node = $this->question_category_model->get_node($_u);
                if(isset($node->name)){$tmp[$_u] = $node; }
                $_parent = '';
                if($_u){
                    $_parent = $this->question_category_model->get_single_path($_u,'*');
                }
                if($_parent){
                    foreach($_parent as $ps=>$p){
                        if($p->depth==1){
                            $bb[$p->id] = $p->name;
                        }
                    }
                }
            }
        }
        $units = $tmp;//作业单元信息
        $game_count = 0;//这次作业有几个游戏；
        if($type!='paper'){
            $res = $this->check_zuoye_model->finish_situation($assignment_id);
            if(isset($res['stu_info'][0]['done_game_data']['game'])){
                $game_count = count($res['stu_info'][0]['done_game_data']['game']);
            }
            $class_info = $res['class_info'];//平均分等统计数据
            $stu_info = $res['stu_info']; 
            if($stu_info){
                $this->tepm->array_sort_by_keys($stu_info,array('correct_num'=>'desc','game_time'=>'asc'));
                if($has_paper){//计算 总体报告中的  全班平均用时
                    $timecost = 0;
                    foreach($stu_info as $stuinfos=>$st){
                        $st['person_expend_all_paper_time'] = ($st['end_time'] and $st['start_time'])?$st['end_time']-$st['start_time']:0;
                        $timecost += $st['person_expend_all_paper_time'] ;
                        $stu_info[$stuinfos] = $st;
                    }
                    $class_info['avg_time'] = $timecost/count($res['stu_info']);
                    $this->tepm->array_sort_by_keys($stu_info,array('correct_num'=>'desc','person_expend_all_paper_time'=>'asc'));
                }
            }
        }
         
        if($type=='paper' and $pass) {//查看的是作业包的统计数据
            $this->load->model('exercise_plan/student_homework_model','shm');
            $this->load->model('exercise_plan/homework_assign_model','ham');
            $homework_info = $this->ham->get_assigned_homework_info($pass);
            $_q = $homework_info[0]['paper_id']?parent::pp_q_list($homework_info[0]['paper_id']):null;
            $hw_q = $this->shm->_replace_img_url($_q['q_list']);
            $online_q = $_q['online_q']; //选择题的正确答案
            $orders = $_q['orders']; // 所有题目的顺序
            $this->load->model('class/classes_student');
            $student_infos = $this->shm->get_all_stu_homework($pass); //有此作业的所有学生id
            foreach($student_infos as $key=>$val){
                $val['comment'] = $this->zuoye_comment_model->get_cmt($zuoye['id'],$val['user_id']);
                $student_infos[$key] = $val;
            }
            $this->load->helper('handle_answer');
            $avg_score = $avg_time = $complet_sum = $part_sum = $process_ans =0;
            if($student_infos){
                $student_infos = person_answer_color($student_infos,$online_q,$orders);
                foreach($student_infos as $k=>$s){
                    // $s['user_id'] = alpha_id_num($s['user_id']);//加密后外面看不到uid
                    $student_infos[$k] = $s;
                    $avg_time += $s['expend_time'];
                    $avg_score += $s['correct_num'];

                    if(isset($s['process_ans']) and $process_ans<count($s['process_ans'])){
                        $process_ans = count($s['process_ans']);//这份卷子的题目数
                    }
                    if($s['is_completed']){
                        $complet_sum ++;
                    }
                }
            }
            $class_info['avg_score'] =count($student_infos)*$process_ans?round($avg_score/(count($student_infos)*$process_ans)*100):0;
            $class_info['avg_time'] = count($student_infos)?round(($avg_time/count($student_infos))):0;
            $class_info['complet_sum'] = $complet_sum;
            $class_info['part_sum'] = 0;
            $this->tepm->array_sort_by_keys($student_infos,array('correct_num'=>'desc','expend_time'=>'asc'));
            $stu_info = $student_infos;
            $this->smarty->assign('student_infos',$student_infos);
        }
         
// var_dump($class_info);die;
        $this->load->helper('time');
        $this->smarty->assign('class_name',$class_name);
        $this->smarty->assign('has_paper',$has_paper);
        $this->smarty->assign('has_game',$has_game);
        $this->smarty->assign('alpha_class_id',$alpha_class_id);
        $this->smarty->assign('zuoye',$zuoye);
        $this->smarty->assign('units',$units);
        $this->smarty->assign('class_info',$class_info);
        $this->smarty->assign('bb',$bb);
        $this->smarty->assign('game_count',$game_count);
        $this->smarty->assign('papers',$papers);
        $this->smarty->assign('stu_info',$stu_info);
        
        $this->smarty->assign('type',$type);
        $this->smarty->assign('pass',$pass);
        $this->smarty->assign('assignment_id',$assignment_id);//作业的ass_id
        $this->smarty->display($this->_smarty_dir.'check_homework.html');
    }

    function give_cmt(){
        $this->load->helper('array');
        $stu_ids = $this->input->post('stuIds',true);
        $stu_ids = explode_to_distinct_and_notempty($stu_ids);
        if(empty($stu_ids)){
            echo json_token(array('errorcode'=>false,'error'=>'没有找到学生'));die;
        }
        $cmt = $this->input->post('comment',true);
        $cmt = sub_str(strip_tags(trim($cmt)),0,300,'');//100个字的限制
        if(strlen($cmt)<1){
            echo json_token(array('errorcode'=>false,'error'=>'评语内容不能为空'));die;
        }
        $cmt = addslashes($cmt); //防注入
        $teacher_id = $this->tizi_uid;
        $assignment_id = intval($this->input->post('ass_id',true));
        if(!$assignment_id){
            echo json_token(array('errorcode'=>false,'error'=>'非法请求'));die;
        }
        $this->load->model('homework/zuoye_model');
        $belong = $this->zuoye_model->is_belong($assignment_id,$teacher_id);

        if(!$belong){// 判断是否自己的作业
            echo json_token(array('errorcode'=>false,'error'=>'只能对自己布置的作业给评语'));die;
        }
        $this->load->model('homework/zuoye_comment_model'); 
        foreach($stu_ids as $s){
            $data = array('user_id'=>$s,'zy_assign_id'=>$assignment_id,'content'=>$cmt,'teacher_id'=>$teacher_id,'create_time'=>time());
            $res = $this->zuoye_comment_model->insert($data);
        }
        echo json_token(array('errorcode'=>$res));die;
    }
 


}