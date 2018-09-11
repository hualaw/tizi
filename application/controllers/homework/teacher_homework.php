<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."Controller.php");
class Teacher_Homework extends Controller {

	private $_smarty_dir="teacher/homework/";

	function __construct() {
		parent::__construct();
		$this->load->model('class/classes_teacher','ct');
        $this->load->model('class/classes_schools');
        $this->load->model('class/classes');
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $this->load->model('homework/zuoye_model');
        $this->load->model('homework/check_zuoye_model');
        $this->load->model('homework/unit_model');
        $this->load->model('homework/zuoye_intro_model');
        $this->load->model('question/question_category_model');
	}

	function intro(){
		if($this->tizi_uid && $this->tizi_utype==Constant::USER_TYPE_TEACHER){
            $this->load->model("class/classes_teacher");
            $this->load->model('video/videos_model');
			$classes = $this->get_my_classes();
			$class_num = count($classes);
			if($class_num){
                $zy_list = $this->zuoye_model->intro_zy_list($this->tizi_uid);
                foreach($zy_list as $zys=>&$zy){
                    $tmp = $bb = array();
                    $_unit_ids = explode(',', $zy['unit_ids']);
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
                    $zy['units'] = $tmp;
                    $zy['banbens'] = $bb;//作业的版本信息
                    $stu = $this->check_zuoye_model->complete_zy_stu($zy['id']);//学生完成人数
                    $zy['stu_complet_sum'] = $stu['stu_comp'];
                    $zy['stu_sum'] = $stu['stu_sum'];
                    //视频作业内容
                    $zy['video_entities'] = $this->zuoye_intro_model->get_videos_ids($zy['video_ids']);
                    $zy['game_entities'] = $this->zuoye_intro_model->get_game_entites($zy['unit_game_ids']);
                    
                    $zy['paper_entity'] = $this->zuoye_intro_model->get_paper_entites($zy);
                    foreach($classes as $k=>&$val){
                        if($val['alpha_id'] == alpha_id_num($zy['class_id'])){
                            $val['zuoye'] = $zy;
                        }
                    }
                }
                foreach($classes as $k=>&$val){
                    // if(!isset($val['zuoye'])){//今天，这个班，已经布置的作业的数
                        $val['has_assigned_today'] = $this->zuoye_model->has_assigned_today(alpha_id_num($val['alpha_id'],true),$this->tizi_uid);
                    // }
                }
                $this->smarty->assign('classes',$classes);
                $this->smarty->display($this->_smarty_dir.'class_list.html');
            }else{
                $this->homework_no($class_num);
            }
        }else{
            $this->homework_no();
        }
        
    }

    function homework_no($class_num = 0)
    {
        $this->smarty->display($this->_smarty_dir.'homework_no.html',"zuoye_t".$this->tizi_utype."_c".($class_num>0));
    }

    /*删除作业*/
    function del($assign_id){
        $assign_id = intval($assign_id);
        $this->load->model('homework/zuoye_model');
        $res = $this->zuoye_model->del_zuoye($assign_id,$this->tizi_uid);
        if($res){
            echo json_token(array('errorcode'=>true,'error'=>'删除成功'));die;
        }else{
            echo json_token(array('errorcode'=>false,'error'=>'删除失败'));die;
        }
    }

    
	//获取老师名下的所有班级
	protected function get_my_classes(){
        $all_class_info = $this->ct->get_classes_by_tch($this->tizi_uid);
        foreach($all_class_info as $k=>$c){
            $name = $c['school_define_id']?$this->classes_schools->define_school_info($c['school_define_id'],true):$this->classes_schools->getsh_info($c['id']);//学校名字
            $c_name = $this->hcm->get_class_whole_name($c['id']);
            if($c_name[0]['class_year']){
                if(isset($name['schoolname']) and isset($name['classname'])){
                    $c['class_name'] =  $name['schoolname'].$c_name[0]['class_year'].'级'.$name['classname'];
                }elseif(isset($name['schoolname'])){
                    $c['class_name'] =  $name['schoolname'].$c_name[0]['class_name'];
                }else{
                    $c['class_name'] =  '';
                }
            }else{
                $c['class_name'] = $c['class_grade'].$c['classname'];
            }
            unset($c['classname']);
            $c['alpha_id'] = alpha_id_num($c['id']);
            unset($c['id']);
            $all_class_info[$k] = $c;
        }
        return $all_class_info;
    }

    protected function get_school_class_name($class_id){
        $name = $this->classes_schools->getsh_info($class_id);//省市、城区、学校、班级名字
        $city = ''; $school = '';
        if(isset($name['province']))$city .= $name['province'];
        if(isset($name['county']))$city .= $name['county'];
        if(isset($name['schoolname']))$school .= $name['schoolname'];
        $arr =array('city'=>$city,'school'=>$school,'class_name'=>$name['classname']);
        return $arr;
    }

    function view_zuoye_package($pack_id){
        $is_paper = $this->input->get('is_paper',true,true,false);//为1的话，$pack_id就是paper_id,意思是要看的是布置出去的卷子，不是上传的卷子
        $package_id = $this->input->get('package_id',true,true,false);
        $q_list = array();
        if(!$is_paper){
            $this->load->model('homework/homework_package_model');
            $package = $this->homework_package_model->get_package_by_id($pack_id,true,'pic');
            $this->load->model('exercise_plan/student_homework_model');
            foreach( $package['questions'] as $key=>$val){
                /* 文字题目内容
                    if(isset($val->body_text)){
                        $q_list[$key]['body'] = $val->body_text;
                    }else{
                        $q_list[$key]['body'] = $val->body;
                    }
                    if(isset($val->answer_text)){
                        $_answer = $val->answer_text;
                    }else{
                        $_answer = $val->answer;
                    }
                    $_answer = str_replace("</div></body></html>", '',$_answer);
                    $_answer = str_replace("</body></html>", '',$_answer);
                    $q_list[$key]['answer_text'] = $_answer;
                    //放出解析 2014-05-07
                    if(isset($val->analysis_text)){
                        $_analysis = $val->analysis_text;
                    }else{
                        $_analysis = $val->analysis;
                    }
                    $_analysis = str_replace("</div></body></html>", '',$_analysis);
                    $_analysis = str_replace("</body></html>", '',$_analysis);
                    $q_list[$key]['analysis_text'] = $_analysis;
                */
                //问题内容形式：图片   
                $q_list[$key]['body'] = path2img($val->body);
                $q_list[$key]['analysis_text'] = path2img($val->analysis);
                $q_list[$key]['answer_text'] = path2img($val->answer);
                $q_list[$key]['asw'] = $val->asw;
                $q_list[$key]['qid'] = $val->id;
                $q_list[$key]['qtype_id'] = $val->qtype_id;
            }
            $q_list = $this->student_homework_model->_replace_img_url($q_list);
        }else{
            $qlist = $this->pp_q_list($pack_id, $package_id);//$pack_id is paper_id
            $q_list = $qlist['q_list'];
        }
        
        $this->smarty->assign('q_list',$q_list);
        $this->smarty->assign('is_paper',$is_paper);
        // $this->smarty->assign('package',$package);
        $this->smarty->display($this->_smarty_dir.'zy_question_view.html');
    }

     //tizi 4.0 给func_checkhomework用，试卷题目列表
    protected function pp_q_list($paper_id, $package_id=false ){
        $paper=$this->get_paper($paper_id);
        $online_q_list = array();
        //有单选题
        if(isset($paper['paper_question'][1]) && $paper['paper_question'][1]){
            $online_q = array_values($paper['paper_question'][1]);
            $online_q = $online_q[0]; //在线作业的qid数组
            if($online_q){
                foreach($online_q as $key=>$val){
                    $ori = $paper['question_origin'][$key];

                    $asw = isset($paper['question'][$ori][$val]->asw)?$paper['question'][$ori][$val]->asw:$paper['question'][$ori][$val]->answer;
                    $online_q_list[$val]['asw'] = $asw;
                }
            }   
        }
        //获取题目的顺序
        $on_ids =  array();
        $on_question_ids = $paper['paper_question'][1];
        if($on_question_ids){
            foreach($on_question_ids as $k=>$val){
                if($val) {$on_ids = array_merge($on_ids,$val);}
            }
        }
        $off_ids =  array();
        $off_question_ids = $paper['paper_question'][2];
        if($off_question_ids){
            foreach($off_question_ids as $k=>$val){
                if($val) {$off_ids = array_merge($off_ids,$val);}
            }
        }//获取题目顺序结束
        $orders = array_merge($on_ids,$off_ids);
        $q_list = array();
        $this->load->model('exercise_plan/student_homework_model');
        foreach( $paper['question'] as $k=>$v){
            if($v){
                foreach($v as $key=>$val){
                    /* 文字题目内容
                        if(isset($val->body_text)){
                            $q_list[$key]['body'] = $val->body_text;
                        }else{
                            $q_list[$key]['body'] = $val->body;
                        }
                        if(isset($val->answer_text)){
                            $_answer = $val->answer_text;
                        }else{
                            $_answer = $val->answer;
                        }
                        $_answer = str_replace("</div></body></html>", '',$_answer);
                        $_answer = str_replace("</body></html>", '',$_answer);
                        $q_list[$key]['answer_text'] = $_answer;
                        //放出解析 2014-05-07
                        if(isset($val->analysis_text)){
                            $_analysis = $val->analysis_text;
                        }else{
                            $_analysis = $val->analysis;
                        }
                        $_analysis = str_replace("</div></body></html>", '',$_analysis);
                        $_analysis = str_replace("</body></html>", '',$_analysis);
                        $q_list[$key]['analysis_text'] = $_analysis;
                    */

                    //问题内容形式：图片    
                    $q_list[$key]['body'] = ($val->body);
                    $q_list[$key]['analysis_text'] = ($val->analysis);
                    $q_list[$key]['answer_text'] = ($val->answer);
                    $q_list[$key]['asw'] = isset($val->asw)?$val->asw:'';

                    $q_list[$key]['qid'] = $val->id;

                    if(isset($online_q) and in_array($val->id, $online_q) ){
                        $q_list[$key]['qtype_id'] = 3;
                    }else{
                        $q_list[$key]['qtype_id'] = false;
                    }
                }
            }
        }
        $q_list = $this->student_homework_model->_replace_img_url($q_list);
        $new_q_list = array();
        if(!$package_id){
            foreach($orders as $os=>$qid){
                $new_q_list[$qid] = $q_list[$qid];
            }
        //     // $q_list = array_reverse($q_list);//要判断是否是从package来，是就不用reverse
        }else{
            $new_q_list = $q_list;
        }
        return array('q_list'=>$new_q_list,'online_q'=>$online_q_list,'orders'=>$orders);
    }



}
