<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once "teacher_homework.php";
class Teacher_Report extends Teacher_Homework {

    private $_smarty_dir="teacher/class/";

    function __construct() {
        parent::__construct();
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
    }

    //作业汇总报告
    function class_report($alpha_class_id){
        $sub_type = $this->input->get('sub_type',true,true,3);
        $this->load->model("question/question_subject_model");
        $sub_ids = $this->question_subject_model->get_subject_by_type($sub_type);
        if($sub_ids){
            foreach($sub_ids as $val){
                $tmp_ids[] = $val->id;
            }
            $sub_ids = implode(',', $tmp_ids);
        }else{
            redirect(site_url('zuoye'));die;
        }
        $class_id = alpha_id_num($alpha_class_id,true);
        $class_info = $this->get_school_class_name($class_id);
        $class_name = implode('', $class_info);//班级名称

        //当前月份
        $monthes = $this->last_12_month();
        $this->load->model('homework/zuoye_report_model');
        $month_sum = 0;
         
        foreach($monthes as $key=>&$val){
            $val['zuoye'] = $this->zuoye_report_model->zuoye_done_by_student($val['start_unix'],$val['end_unix'],$class_id,$sub_ids);
            $month_sum += $val['zuoye']['month_zy_sum'];
        }

        //班级里所有学生id
        $this->load->model("class/classes_student");
        $students = $this->classes_student->get_user_ids($class_id,'user_id');
        $stu = array();
        if($students){
            foreach($students as $stus=>$s){
                $this->load->model('login/register_model');
                $stui = $this->register_model->get_user_info($s['user_id']);
                $stu[$s['user_id']]['stu_name'] = $stui['user']->name;//获取学生姓名
                $stu[$s['user_id']]['user_id'] = $stui['user']->id;//获取学生id
                foreach($monthes as $mons => $mon){
                    if(isset($mon['zuoye']['stu'])){
                        foreach($mon['zuoye']['stu'] as $stu_id=>$zy){
                            if( $stu_id == $s['user_id']){
                                $stu[$s['user_id']]['sum'] = isset($stu[$s['user_id']]['sum'])?$stu[$s['user_id']]['sum']+$zy:$zy;
                            }
                        }
                    }
                }
            }
        }

        $this->smarty->assign('alpha_class_id',$alpha_class_id);
        $this->smarty->assign('class_name',$class_name);
        $this->smarty->assign('stu',$stu);
        $this->smarty->assign('sub_type',$sub_type);
        $this->smarty->assign('monthes',$monthes);
        $this->smarty->assign('month_sum',$month_sum);
        $this->smarty->assign('y',date('Y',time()));
        $this->smarty->display($this->_smarty_dir.'homework_report.html');
    }

    //从今天向前推12个月
    protected function last_12_month(){
        $m = date('n',time());//n没有前导零
        $y = date('Y',time());
        $flag = 0 ; $arr = array();
        for($i=0;$i<12;$i++){
            if($m>0){
                $next = $m+1;
                $arr[$y.'-'.$m] = array('m'=>$m,'y'=>$y,
                                        'start_unix'=>strtotime(date("$y-$m-01")),
                                        'end_unix'=>strtotime(date("$y-$next-01"))-1);
            }else{
                $month = 12 - abs($m);
                $year = $y - 1;
                $next_mon = $month+1;
                $arr[$year.'-'.$month] = array('m'=>$month,'y'=>$year,
                                        'start_unix'=>strtotime(date("$year-$month-01")),
                                        'end_unix'=>strtotime(date("$year-$next_mon-01"))-1);
            }
            $m--;
        }
        $arr = array_reverse($arr);
        return $arr;
    }

    //作业 -试卷包- 错题报告
    function wrong_q_report($assignment_id=1){
        $this->load->model('exercise_plan/homework_assign_model','ham');
        $this->load->model('exercise_plan/student_homework_model','shm');
        $homework_info = $this->ham->get_assigned_homework_info($assignment_id);
        if(empty($homework_info) || $homework_info[0]['is_assigned']==0){//被删除的作业也不能检查
            redirect('');// cannot find this homework
        }
        $class_id = $homework_info[0]['class_id'];
        $class_info = $this->get_school_class_name($class_id);
        $class_name = implode('', $class_info);//班级名称

        $_q = $homework_info[0]['paper_id']?$this->pp_q_list($homework_info[0]['paper_id']):null;
        $hw_q = $this->shm->_replace_img_url($_q['q_list']);
        $online_q = $_q['online_q']; //选择题的正确答案
        $orders = $_q['orders']; // 所有题目的顺序
        $this->load->model('class/classes_student');
        $student_infos = $this->shm->get_all_stu_homework($assignment_id); //有此作业的所有学生id
        $s_answer = array();
        foreach($student_infos as $key=>$val){
            $s_answer[] = $val['s_answer'];
        }
        $processed_data = array();
        $this->load->helper('handle_answer');
        if($s_answer){
            $processed_data = process_answers($s_answer,$online_q,$orders);
            $this->tepm->array_sort_by_keys($processed_data,array('wrong_total'=>'desc'));//错的最多的排前面
        }
        
        if($processed_data){
            foreach($processed_data as $key=>$val){
                $tmpoption = array();
                $tmpoption = array('A'=>$val['A'],'B'=>$val['B'],'C'=>$val['C'],'D'=>$val['D']);
                $max = max($tmpoption);
                $processed_data[$key]['most_people_choice'][]=array_search(max($tmpoption),$tmpoption);
                // var_dump($processed_data);
            }
        }
        $this->smarty->assign('wrong_q',$processed_data);
        $this->smarty->assign('hw_q',$hw_q);
        $this->smarty->assign('hw_info',$homework_info[0]);
        $this->smarty->assign('class_name',$class_name);
        $this->smarty->display($this->_smarty_dir.'zuoye_wrong_q_report.html');
    }

    //作业 -试卷包 - 每个学生作答记录
    function student_record($assignment_id=1){
        $this->load->model('exercise_plan/homework_assign_model','ham');
        $this->load->model('exercise_plan/student_homework_model','shm');
        $homework_info = $this->ham->get_assigned_homework_info($assignment_id);
        if(empty($homework_info) || $homework_info[0]['is_assigned']==0){//被删除的作业也不能检查
            redirect('');// cannot find this homework
        }
        $_q = $homework_info[0]['paper_id']?$this->pp_q_list($homework_info[0]['paper_id']):null;
        $hw_q = $this->shm->_replace_img_url($_q['q_list']);
        $online_q = $_q['online_q']; //选择题的正确答案
        $orders = $_q['orders']; // 所有题目的顺序
        $this->load->model('class/classes_student');
        $student_infos = $this->shm->get_all_stu_homework($assignment_id); //有此作业的所有学生id
        foreach($student_infos as $key=>$val){
            $val['has_cmt'] = $this->tepm->has_comment($val['paper_assign_id'],$val['user_id']);
            $student_infos[$key] = $val;
        }
        // $processed_data = array();
        $this->load->helper('handle_answer');
        if($student_infos){
            $student_infos = person_answer_color($student_infos,$online_q,$orders);
            foreach($student_infos as $k=>$s){
                $s['user_id'] = alpha_id_num($s['user_id']);//加密后外面看不到uid
                $student_infos[$k] = $s;
            }
        }
        $this->tepm->array_sort_by_keys($student_infos,array('correct_num'=>'desc','expend_time'=>'asc'));
        $this->smarty->assign('hw_q',$hw_q);
        // $this->smarty->assign('hw_info',$homework_info[0]);
        $this->smarty->assign('student_infos',$student_infos);
        $this->load->helper('time');
        $this->smarty->display($this->_smarty_dir.'zuoye_student_record.html');   
    }

    




}