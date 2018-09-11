<?php
/**
 * @author saeed
 * @date   2013-8-30
 * @description 学生基本类
 */
require(__DIR__.'/student_parent_common.php');
class Student_Base extends Student_Parent_Common{

    protected $weekarray=array("日","一","二","三","四","五","六");

	protected $_smarty_dir="student/";

    protected $show_test_account = false;

    protected $online_questions_answer;

    public function __construct(){
        parent::__construct();
        if(isset($_POST)){
           $_POST = $this->_filter_input($_POST,1);
        }
        if(isset($_GET)){
            $_GET = $this->_filter_input($_GET,0);
        }

        $this->load->model('login/register_model');
        $this->load->model('exercise_plan/student_homework_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('user_data/student_data_model');
        $this->user_type = $this->session->userdata('user_type');
        //测试帐号
        $test_account_number = Constant::test_account_number();
        $is_test_account = false;
        if(in_array($this->uid,$test_account_number)){
            $is_test_account = true;
        }
        $this->smarty->assign('is_test_account',$is_test_account);
        $pid = $this->session->userdata('parent_id');
        if($pid){
            $this->show_test_account = $pid;
        }
        
        $this->smarty->assign('show_test_account',$this->show_test_account);
        $this->set_user_info();
        $this->smarty->assign('types',$this->_get_subject_type()); // 左侧科目导航

    }

    protected function set_user_info(){
        $this->load->model('class/classes_schools');
        $this->user_info = new stdClass();
        if($this->kid_user_id == 0) {
            $this->user_info->class_name = '';
            goto render;
        }
        $user_info = $this->register_model->get_user_info($this->kid_user_id);
        $this->user_info = $user_info['user'];
        $this->user_info->name = sub_str($this->user_info->name);

        $this->user_info->class_name = $this->user_info->class_year
            = $this->user_info->class_grade
            = $this->user_info->school_name
            = $this->user_info->city
            = $this->user_info->join_date
            = $this->user_info->create_date
            = $this->user_info->class_id
            = $this->user_info->province
            = $this->user_info->county = '';

        if($classinfo = $this->get_class_info()){
            $this->user_info->class_name = $classinfo->classname;
            $this->user_info->join_date = date("Y",$classinfo->join_date);
            $this->user_info->class_year = date("Y",$classinfo->class_year);
            $this->user_info->create_date = date("Y-m-d",$classinfo->create_date);
            if($classinfo->class_year) $this->user_info->class_year=$classinfo->class_year."级";
            else $this->user_info->class_year='';
            $this->user_info->class_grade = $classinfo->class_grade."级";
            $this->user_info->class_id = $classinfo->id;
            if($classinfo->school_id > 0){
                $school_info = $this->classes_schools->school_info($classinfo->school_id);
            } else if ($classinfo->school_define_id > 0){
				$school_info = $this->classes_schools->define_school_info($classinfo->school_define_id);
			} else {
				$school_info = array();
			}
			$this->user_info->school_name = $school_info ? implode("", $school_info) : "";
			
			$this->load->model("constant/grade_model");
			$grade = $this->grade_model->get_grade();
			$arr_grade = array();
			foreach ($grade as $gt){
				foreach ($gt as $key => $value){
					$key !== 0 ? $arr_grade[$key] = $value : "";
				}
			}
			if (isset($arr_grade[$classinfo->class_grade])){
				$this->user_info->class_name = $arr_grade[$classinfo->class_grade].$this->user_info->class_name;
			}
        }
        $student_data = $this->student_data_model->get_student_data($this->kid_user_id);
        if(!empty($student_data)){
            $this->user_info->sex = $student_data->sex; 
        }else{
            $this->user_info->sex = '1'; 
        }
        render:{
            $this->smarty->assign('user_info',$this->user_info);
        }
    }  

    protected function get_categories($grade_id = ''){

        $grades = Constant::practice_grade();
        $urls = Constant::practice_url();
        $icons = Constant::practice_icon();

        $categories = $this->practice_model->getCategoriesByGradeId($grade_id);
        $t_c = array();
        foreach($categories as $key=>$val){
            
            if(isset($icons[$val['p_c_type']])){
                $val['icon'] =  $icons[$val['p_c_type']];
            }
            $val['url'] = isset($urls[$val['p_c_type']])?$urls[$val['p_c_type']]:$urls[1];
            $val['grade_name'] = isset($grades[$val['grade']])?$grades[$val['grade']]:'';
			$t_c[] = $val;
		}

        return $t_c;   

    }


    protected function get_practice_list($grade_id = ''){

        $this->load->model('practice/practice_model');
        $categorie_group = array();
        $p_c_id_group = $this->practice_model->special_count();
        $grade_categories = $this->get_categories($grade_id);
        $grades = Constant::practice_grade();
        $c_subject = array();
        $categories_group = array();

        foreach($grade_categories as $category){

			if(!($category['p_c_type']==1 && !in_array($category['id'],$p_c_id_group))){
				$categories_group[$category['subject_id']][] = $category;
			}

            $subjects = $this->student_homework_model->get_subject_type();
            $categories_temp = array();
            foreach($subjects as $subject){

                $s_id = $subject->id;
                if(isset($categories_group[$s_id])){
                    ksort($categories_group[$s_id]);
                    $categories_temp[$s_id] = $categories_group[$s_id];
                    $c_subject[$s_id] = $subject->name;
                }

            }
        }
		ksort($c_subject);

        $single_ids = Constant::practice_game_single();

        $this->smarty->assign('single_ids',$single_ids);
        $this->smarty->assign('grades',$grades);
        $this->smarty->assign('pra_subjects',$c_subject);
        $this->smarty->assign('grade_categories',$categories_temp);

    
    }

    /**
     * @info 获取所有科目类型
     */
    protected function _get_subject_type(){
        $types = $this->question_subject_model->get_subject_type(true,'homework');
        return $types;
    }
    /**
     * @info 获得所有科目
     */
    protected function _get_subject(){
        $subjects =  $this->question_subject_model->get_subjects();
        $new_subjects = array();
        foreach($subjects as $val){
            $new_subjects[$val->id] = $val;
        }
        return $new_subjects;

    }

    protected function  get_class_info(){
        return $this->student_homework_model->fetch_user_class($this->kid_user_id);
    }

    protected function _info_handle($status,$msg){
        $text = json_token(array('status'=>$status,'msg'=>$msg));
        $text = json_decode($text,true);
        $text = array_merge($text,array('status'=>$status,'msg'=>$msg));
        if(isset($text['errorcode'])){
            unset($text['errorcode']);
        }
        exit(json_encode($text));
    }   

    protected function _filter_input($data,$type){
        if(!empty($data)){
            if(is_array($data)){
                foreach($data as $key=>$data_val){
                    $data[$key] = $this->_filter_input($key,$type);
                }
            }else{
                if($type){
                    return  trim($this->input->post($data,true));
                }else{
                    return  trim($this->input->get($data,true));
                }
            }
            return $data;
        }
    }
    
    protected function _match_url($urls){
        foreach($urls as $url){
            if(preg_match("/.*$url.*/",$_SERVER['REQUEST_URI'])){
                return false;
            }
        }
        return true;
    }
    
    protected function get_stype_id($subject_id){
        $result = $this->question_subject_model->get_subject_type_info($subject_id);
        return $result['id'];
    }

    protected function redirect_to_home(){
        redirect("student/homework/home");
        exit;
    }

    protected function _get_pra_grade_level($grade_id){
        
        if($grade_id < 8){
            return 1;
        }elseif($grade_id < 12){
            return 2;
        }else{
            return 3;
        }
    }

    private function _convert_user_grade($grade_id){
        
        if($grade_id <= 3){
            return $grade_id + 8;
        }elseif($grade_id <= 6){
            return $grade_id + 9;
        }elseif($grade_id <= 12){
            return $grade_id - 5;
        }elseif($grade_id <= 14){
            return $grade_id + 1;
        }
        return $grade_id;

    }

    



}
   
