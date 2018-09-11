<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";

class Details extends Class_Controller {
	
	private $cas;
	
	private $class_id;
	
	private $user_id;
	
	public function __construct(){
		parent::__construct();
		$this->check(Constant::USER_TYPE_TEACHER);
	}
	
	public function index($alpha_class_id, $origin_part = "",$assignment_id=false){
		$allowparts = array("share", "homework", "student", "teacher", "checkhomework", "settings", "paper");
		$teacher_id = intval($this->session->userdata("user_id"));
		$class_id = alpha_id_num($alpha_class_id, true);
		$this->class_id	= $class_id;
		$this->user_id	= $teacher_id;
		if($origin_part == 'checkhomework'){
			$assignment_id = intval($assignment_id);
		}
		$this->load->model("class/classes_teacher");
		$idct = $this->classes_teacher->get_idct($class_id, "teacher_id");
		if (in_array(array("teacher_id" => $teacher_id), $idct)){
			$this->load->model("question/question_subject_model");
			$this->load->model("class/classes");
			$this->load->model("class/classes_schools");
			$this->load->model("class/classes_student_create");
			$this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
			$this->load->model('cloud/cloud_model');
            $this->load->model('homework/zuoye_model');
			$subject_type = $this->question_subject_model->get_subject_type();
			$this->cas = $this->classes->get($class_id);
			$creator_name = $this->classes->get_realname($this->cas['creator_id']);
			$school = $this->cas['school_id']>0?$this->classes_schools->school_info($this->cas['school_id']):$this->classes_schools->define_school_info($this->cas['school_define_id']);
			$school = $school?implode("", $school):'';
			$ulog_total = $this->classes_student_create->ulog_total($class_id);
			$ex_total = $this->zuoye_model->class_zy_sum($class_id,$teacher_id);
			$share_total = $this->cloud_model->share_file_total($class_id);
			$paper_total =  $this->tepm->get_class_exercise($class_id,$teacher_id,1,1,true,1);
            $paper_total = $paper_total['total'];
			//新的part选择机制
			$student_total = intval($this->cas["stu_count"] + $ulog_total);
			if (!in_array($origin_part, $allowparts)){
				if ($student_total === 0){
					$part = "student";
				} else {
					$part = "homework";
				}
			} else {
				$part = $origin_part;
			}
			
			//新增class_year
			$class_year = array();
			for ($i = 0; $i <= 9; ++$i){
				$class_year[] = date("Y") - $i;
			}
			
			$this->smarty->assign("classes", $this->cas);
			$this->smarty->assign("creator_name", $creator_name);
			$this->smarty->assign("school", $school);
			$this->smarty->assign("subject_type", $subject_type);
			$this->smarty->assign("alpha_class_id", $alpha_class_id);
			$this->smarty->assign("teacher_id", $teacher_id);
			$this->smarty->assign("part", $part);
            $this->smarty->assign("ex_total", $ex_total);
			$this->smarty->assign("paper_total", $paper_total);
			$this->smarty->assign("share_total", $share_total);
			$this->smarty->assign("student_total", $student_total);
			$this->smarty->assign("class_year", $class_year);
			$this->smarty->assign("vs", $this->input->get("vs"));
			if($origin_part != 'checkhomework'){
				call_user_func(array($this, "func_".$part), $teacher_id, $class_id);
			}else{
				call_user_func(array($this, "func_".$part), $teacher_id, $class_id,$assignment_id);
			}
		} else {
			redirect(site_url()."teacher/class/my");
			//$this->smarty->assign("notfound", 1);
		}
		parent::smarty_school_type();
        $this->smarty->assign('part',$part);
		$this->smarty->display("teacher/class/details_{$part}.html");
	}

	private function func_homework($teacher_id, $class_id,$page=1){
        $this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
        $this->load->model('homework/zuoye_model');
        $this->load->model('homework/check_zuoye_model');
        $this->load->model('homework/unit_model');
		$this->load->model('question/question_category_model');
        $zy = $this->zuoye_model->class_zy($class_id,$teacher_id,$page,10);
        $sum = $this->zuoye_model->class_zy_sum($class_id,$teacher_id);
        foreach($zy as $key=>&$val){
            $tmp = $bb = array();
            $_unit_ids = explode(',', $val['unit_ids']);
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
            $val['units'] = $tmp;
            $val['banben'] = $bb;//作业的版本信息
            $stu = $this->check_zuoye_model->complete_zy_stu($val['id']);
            if($stu['stu_sum'] == $stu['stu_comp'] or $val['end_time']<time()){
                $val['checkable'] = 1;
            }else{
                $val['checkable'] = 0;
            }
            $this->load->model('question/question_subject_model');
            $val['subject_name'] = $this->question_subject_model->get_subject_name($val['subject_id']);
            $val['type_video'] = $val['video_ids']?"视频":'';
            $val['type_game'] = $val['unit_game_ids']?"趣味":'';
            $val['type_paper'] = $val['paper_ids']?"普通":'';
            $val['stu'] = $stu;
        }
        // var_dump($zy);die;
        $pages =  self::get_pagination($page,$sum,'class_zy_pagination',10);//最后的是pagesize,待改
        $this->smarty->assign("pages", $pages);//分页
        $this->smarty->assign("zy", $zy);
        $this->smarty->assign("sum", $sum);
        $this->smarty->assign("current_page", $page);//当前分页
        $this->smarty->assign("alpha_class_id", alpha_id_num($class_id));

        //有无历史作业  历史时间  2014-08-18 18:00
        $paper_total =  $this->tepm->get_class_exercise($class_id,$teacher_id,1,1,true,1);
        $paper_total = $paper_total['total'];
        $this->smarty->assign("has_old_paper", $paper_total);
	}

    //update@tizi 4.0 班级 试卷  分页
    public function homework_page(){
        $teacher_id = $this->tizi_uid;
        $page = intval($this->input->get('page',true));
        $alpha_class_id = $this->input->get('class_id',true);
        $class_id = alpha_id_num($alpha_class_id,true);
        if(!$page)$page=1;
        $this->func_homework($teacher_id,$class_id,$page);
        $json['errorcode'] = true;
        $json['html'] = $this->smarty->fetch('teacher/class/class_zuoye_page.html');
        $this->smarty->assign("alpha_class_id", $alpha_class_id);


        echo json_token($json);die;
    }
	
	//获取分享资料
	private function func_share($teacher_id, $class_id){
		$this->load->model("class/classes_schools");
		$this->load->model("cloud/cloud_model");
        $this->load->helper('qiniu'); 
        $this->page(alpha_id_num($class_id));
		//目录tree
        $this->load->model('resource/tree_model');
        $res_html = $this->tree_model->from_cloud($teacher_id);
		$tree = $this->cloud_model->get_dir_tree($teacher_id);
        // $tree = $res_html['html'].$tree;
        $this->smarty->assign("tree", $tree);//网盘的目录树
		$this->smarty->assign("first_dir_id", $res_html['first_dir_id']);//当前分页
	}
	
	//获取班级布置的试卷作业					
	private function func_paper($teacher_id, $class_id,$page=1){
		$this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
        $pagesize = 10000;
		$ex_info = $this->tepm->get_class_exercise($class_id,$teacher_id,$page,$pagesize,true,1); //pagesize待改
		$ex_total = $ex_info['total'];
		$exercise = $ex_info['exercise'];
		$class_stu_total = $this->tepm->get_class_stu_num($class_id);
		foreach($exercise as $key=>&$val){
			if($val['online']){
				$val['has_completed_num'] = $this->tepm->has_finished_stu($val['id'],$class_id);
			}else{
				$val['has_completed_num'] = $this->tepm->has_finished_stu($val['id'],$class_id,'is_download');
			}
		}
        $this->load->model('redis/redis_model');
        if($this->redis_model->connect('download'))
        {
            $redis_key = date('Y-m-d').'_assignment_'.$teacher_id;
            $data['download_doc_count'] = $this->cache->get($redis_key);
            $data['download_doc_limit'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
        }else{
            $data['download_doc_count'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
            $data['download_doc_limit'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
        }   
		$pages =  self::get_pagination($page,$ex_total,'class_homework_pagination',$pagesize);//最后的是pagesize,待改
		$this->smarty->assign("pages", $pages);//分页
		$this->smarty->assign("exercise", $exercise);
		$this->smarty->assign("class_stu_total", $class_stu_total);
        $this->smarty->assign("current_page", $page);//当前分页
		$this->smarty->assign("data", $data);//用于下载作业的限制
		$this->smarty->assign("alpha_class_id", alpha_id_num($class_id));
	}

    //某次试卷  的详情信息
	private function func_checkhomework($teacher_id,$class_id,$assignment_id){
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
        $s_answer = array();
        foreach($student_infos as $key=>$val){
            $s_answer[] = $val['s_answer'];
            $val['has_cmt'] = $this->tepm->has_comment($val['paper_assign_id'],$val['user_id']);
            $student_infos[$key] = $val;
        }
        $processed_data = array();
        $this->load->helper('handle_answer');
        if($s_answer){
            $processed_data = process_answers($s_answer,$online_q,$orders);
            $this->tepm->array_sort_by_keys($processed_data,array('wrong_total'=>'desc'));//错的最多的排前面
        }
        if($student_infos){
            $student_infos = person_answer_color($student_infos,$online_q,$orders);
            foreach($student_infos as $k=>$s){
                $s['user_id'] = alpha_id_num($s['user_id']);//加密后外面看不到uid
                $student_infos[$k] = $s;
            }
        }
        $has_online_q = $online_q?true:false;
        $this->tepm->array_sort_by_keys($student_infos,array('correct_num'=>'desc','expend_time'=>'asc'));
        $onq_order = get_online_q_order($online_q,$orders);//在线题目with order

        //没人答过的选择题
        $oq_ids = array_keys($online_q);//所有在线题目的id
        if($processed_data){
            foreach($processed_data as $key=>$val){
                if(in_array($val['qid'],$oq_ids)){
                    unset($onq_order[$val['qid']]);
                }
            }
        }
        $wrong_not_in_oq = $onq_order;

        $this->smarty->assign('wrong_q',$processed_data);
        $this->smarty->assign('online_q',$online_q);
        $this->smarty->assign('wrong_not_in_oq',$wrong_not_in_oq);
        $this->smarty->assign('options',array('A','B','C','D'));//html上用到的
        // $this->smarty->assign('online_q_total',count($oq_ids));//html上用到的
        $this->smarty->assign('while',ceil(count($oq_ids)/20));//html上用到的
        $this->smarty->assign('need_blank',ceil(count($oq_ids)/20)*20-count($oq_ids));//html上用到的
        $this->smarty->assign('hw_q',$hw_q);
        $this->smarty->assign('has_online_q',$has_online_q);
        $this->smarty->assign('hw_info',$homework_info[0]);
        $this->smarty->assign('student_infos',$student_infos);
        $this->load->helper('time');
	}

	//tizi 3.0 给func_checkhomework用，homework question list of one homework
    	// private function hw_q_list($paper_id){
    	// 	$paper=$this->get_paper($paper_id);
         //        $online_q_list = array();
         //        //有单选题
         //        if(isset($paper['paper_question'][1]) && $paper['paper_question'][1]){
         //            $online_q = array_values($paper['paper_question'][1]);
         //            $online_q = $online_q[0]; //在线作业的qid数组
         //            foreach($online_q as $key=>$val){
         //                $ori = $paper['question_origin'][$key];

         //                $asw = isset($paper['question'][$ori][$val]->asw)?$paper['question'][$ori][$val]->asw:$paper['question'][$ori][$val]->answer;
         //                $online_q_list[$val]['asw'] = $asw;
         //            }   
         //        }
        	// 	$q_list = array();
         //        $this->load->model('exercise_plan/student_homework_model');
        	// 	foreach( $paper['paper_question_order'] as $key=>$val){
        	// 	    $origin = $paper['question_origin'][$key];
         //            if(isset($paper['question'][$origin][$val]->body_text)){
        	// 	        $q_list[$key]['body'] = $paper['question'][$origin][$val]->body_text;
         //            }else{
         //                $q_list[$key]['body'] = $paper['question'][$origin][$val]->body;
         //            }
         //            if(isset($paper['question'][$origin][$val]->answer_text)){
         //                $_answer = $paper['question'][$origin][$val]->answer_text;
         //            }else{
         //                $_answer = $paper['question'][$origin][$val]->answer;
         //            }
         //            $_answer = str_replace("</div></body></html>", '',$_answer);
         //            $_answer = str_replace("</body></html>", '',$_answer);
        	// 	    $q_list[$key]['answer_text'] = $_answer;
         //            //放出解析 2014-05-07
         //            if(isset($paper['question'][$origin][$val]->analysis_text)){
         //                $_analysis = $paper['question'][$origin][$val]->analysis_text;
         //            }else{
         //                $_analysis = $paper['question'][$origin][$val]->analysis;
         //            }
         //            $_analysis = str_replace("</div></body></html>", '',$_analysis);
         //            $_analysis = str_replace("</body></html>", '',$_analysis);
         //            $q_list[$key]['analysis_text'] = $_analysis;

        	// 	    $q_list[$key]['qid'] = $paper['question'][$origin][$val]->id;
        	// 	}
         //        $q_list = $this->student_homework_model->_replace_img_url($q_list);
        	// 	return array('q_list'=>$q_list,'online_q'=>$online_q_list,'orders'=>array_values($paper['paper_question_order']));
	// }

    //tizi 4.0 给func_checkhomework用，试卷题目列表
    private function pp_q_list($paper_id){
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
            // var_dump($paper['question']);die;
            if($v){
                foreach($v as $key=>$val){
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

                    $q_list[$key]['qid'] = $val->id;
                }
            }
        }
        $q_list = $this->student_homework_model->_replace_img_url($q_list);
        return array('q_list'=>$q_list,'online_q'=>$online_q_list,'orders'=>$orders);
    }
	
	private function func_student($teacher_id, $class_id){
		$this->load->model("class/classes_student");
		$this->load->model("class/classes_student_create");
		$this->load->model("login/session_model");
		$this->load->model("login/parent_model");
		$this->load->model("login/register_model");
		$students = $this->classes_student->get_cs($class_id);
		$student_create = $this->classes_student_create->get($class_id);
		$class_pwd = rand6pwd($class_id);
		foreach ($students as $key => $value){
			$students[$key]["gen"] = $this->session_model->get_lastgen($value["user_id"]);
			/**
			$students[$key]["parentinfo"] = $this->parent_model->get_lastinfo($value["user_id"], 
				"b.id,b.password,b.name,b.phone_mask");
			if (!$students[$key]["parentinfo"]){
				$students[$key]["parentinfo"] = $this->classes_student_create->get_extension($value["user_id"]);
			}
			*/
			$cp = $this->register_model->compare_password(md5("ti".$class_pwd."zi"), $value["password"]);
			$students[$key]["password"] = $cp === true ? $class_pwd : "学生已自设";
		}
		$this->load->library("credit");
		$userlevel_privilege = $this->credit->userlevel_privilege($teacher_id);
		$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
		
		$this->smarty->assign("students", $students);
		$this->smarty->assign("students_total", count($students));
		$this->smarty->assign("student_create", $student_create);
		$this->smarty->assign("max_student_number", $max_student_number);
		$this->smarty->assign("session_id", $this->session->userdata("session_id"));
		$this->smarty->assign("student_create_total", count($student_create));
		$this->smarty->assign("totals", count($students) + count($student_create));
	}
	
	private function func_teacher($teacher_id, $class_id){
		$this->load->model("login/session_model");
		$teachers = $this->classes_teacher->get_ct($class_id);
		foreach ($teachers as $key => $value){
			$teachers[$key]["gen"] = $this->session_model->get_lastgen($value["teacher_id"]);
		}
		$this->smarty->assign("teachers", $teachers);
		$this->smarty->assign("teacher_total", count($teachers));
	}
	
	private function func_settings(){
		$this->smarty->assign("class_info", $this->cas);
	}

	//update@tizi 4.0 班级 试卷  分页
	public function paper_page(){
		$teacher_id = intval($this->session->userdata("user_id"));
		$page = intval($this->input->get('page',true));
		$alpha_class_id = $this->input->get('class_id',true);
		$class_id = alpha_id_num($alpha_class_id,true);
		if(!$page)$page=1;
		$this->func_paper($teacher_id,$class_id,$page);
		$json['errorcode'] = true;
		$json['html'] = $this->smarty->fetch('teacher/class/class_paper_pagination.html');
		$this->smarty->assign("alpha_class_id", $alpha_class_id);
        echo json_token($json);die;
	}

	//班级分享文件，分页
	public function page($_class_id = 0){
        $flip = $this->input->get('flip',true,true,false);
		$this->load->library('qiniu');
		$page = intval($this->input->get('page',true,true,1));
		$alpha_class_id = $_class_id?$_class_id:$this->input->get('class_id',true);
		$class_id = alpha_id_num($alpha_class_id,true);
		$this->load->model('cloud/cloud_model');
        $this->load->model('resource/res_file_model');
        $this->load->model('login/parent_model');
        $share = $this->cloud_model->get_share_files_by_class(0,$class_id,$page,Constant::CLOUD_CLASSFILE_PER_PAGE_NUM);
        if(!$share and $flip and $page>1){
            while(--$page){
                $share = $this->cloud_model->get_share_files_by_class(0,$class_id,$page,Constant::CLOUD_CLASSFILE_PER_PAGE_NUM);
                if(!$share){
                    continue;
                }else{
                    break;
                }
            }
        }
        if($share){
            $this->load->model('lesson/document_model');
            $name_ids = array();
            foreach($share as $k=>&$v){
                // if($v['source']==1){//如果是上传的资源
                    if($v['file_type']==Constant::CLOUD_FILETYPE_PIC){
                        $this->load->helper('qiniu');
                        $share[$k]['file_path'] = qiniu_img($v['file_path'],0,0,400);
                    }elseif($v['file_ext'] and strpos(Constant::CLOUD_VIDEO_TYPES_JWPLAYER, $v['file_ext'])!==false){
                        $this->load->helper('qiniu');
                        $share[$k]['file_path'] = qiniui_get_vframe($v['file_path']);
                    }
                // }
                // elseif($v['source']==2){//如果是 收藏来的资源
                //     $doc_info = $this->document_model->get_doc_by_ids(array($v['file_id']));
                //     $doc_info = $doc_info[0];
                //     $v['file_name'] = $doc_info->file_name;
                //     $v['file_size'] = $doc_info->file_size;
                //     $v['user_id'] = $doc_info->user_id;
                // }
                //获取用户名
                if(array_key_exists($v['user_id'], $name_ids)){
                    $share[$k]['user_name'] = $name_ids[$v['user_id']];
                }else{
                    $info = $this->parent_model->get_info(intval($v['user_id']));
                    $name_ids[$v['user_id']] = isset($info[0]['name'])?$info[0]['name']:'佚名';
                    $share[$k]['user_name'] = $name_ids[$v['user_id']];
                }
                //判断是否能预览
                if($v['file_type'] == Constant::CLOUD_FILETYPE_PIC){
                    $v['ok_view'] = true;
                }elseif($v['file_type'] == Constant::CLOUD_FILETYPE_DOC and $v['queue_status']==1){
                    $v['ok_view'] = true;
                }elseif($v['file_type'] == Constant::CLOUD_FILETYPE_VIDEO){
                    $v['ok_view'] = true;
                }elseif($v['file_type'] == Constant::CLOUD_FILETYPE_AUDIO ){
                    $v['ok_view'] = true;
                }else{
                     $v['ok_view'] = false;
                }
            }
            $this->res_file_model->is_pfop_done($share);
        }
		$share_total = $this->cloud_model->share_file_total($class_id);
		/*分页*/
		$pages =  self::get_pagination($page,$share_total,'class_share_pagination');
        $this->smarty->assign('share',$share);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign("alpha_class_id", $alpha_class_id);
        $this->smarty->assign("current_page", $page);
        if($flip){
            $json['errorcode'] = true;
            $json['html']= $this->smarty->fetch("cloud/class_share_pagination.html");
            echo json_token($json);die;
        }
	}

	protected function get_pagination($page_num,$total,$func,$pagesize=false)
    {
    	$_pagesize = Constant::CLOUD_CLASSFILE_PER_PAGE_NUM; //分享每页显示数量
    	if($pagesize){
    		$_pagesize = $pagesize;
    	}
        $this->load->library('pagination'); 
        $config['total_rows']       = $total; //为页总数
        $config['per_page']       = $_pagesize;
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_ajax_links();
        return $pages;
    }
	
	/**
	 * 通过mask获取家长的联系手机
	 */ 
	public function phone_mask(){
		$this->ajax_check();
		
		$alpha_user_id = $this->input->post("uid");
		$user_id = alpha_id_num($alpha_user_id, true);
		$hash = $this->input->post("hash");
		$this->load->model("login/register_model");
		$user_info = $this->register_model->get_user_info($user_id);
		if (isset($user_info["user"])){
			$user = $user_info["user"];
			if ($hash === sha1($user->password)){
				$this->load->library("thrift");
				$this->load->helper("json");
				$phone = $this->thrift->get_phone($user_id);
				if ($phone == -127 || $phone == -1){
					$json["code"] = -1;
				} else {
					$json["code"] = 1;
					$json["phone"] = $phone;
				}
				json_get($json);
			}
		}
	}

	//旧的检查作业链接
	function old_check_class($assignment_id){
        $this->load->model('exercise_plan/homework_assign_model','ham');
        $homework_info = $this->ham->get_assigned_homework_info($assignment_id);
        if(empty($homework_info) || $homework_info[0]['is_assigned']==0){//被删除的作业也不能检查
            redirect("{$site_url}teacher/homework/center");// cannot find this homework
        }else{
            $alpha_class_id = alpha_id_num($homework_info[0]['class_id']);
            $this->index($alpha_class_id,'checkhomework',$assignment_id);
        }
    }
}