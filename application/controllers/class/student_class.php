<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."Controller.php");

class student_class extends Controller {
	
	protected $class_id;

    private $_student_paper_num;
    private $_student_homework_num = 0;
	
    public function __construct() {
        parent::__construct();
        $this->load->model("class/classes_student");
    }

    public function intro($part = "homework") {
		if ($this->tizi_uid > 0){
			$iclass = $this->classes_student->userid_get($this->tizi_uid);
			if(isset($iclass[0])){			
				$allow_part = array("resource", "teacher", "paper", "mate", "homework");
				!in_array($part, $allow_part) && redirect();
				$this->load->model("class/classes");
				$this->load->model("class/classes_schools");
				$this->load->model("class/classes_student_create");
				$this->class_id = $iclass[0]["class_id"];
				$class_info = $this->classes->get($this->class_id);
				$school = $class_info["school_id"] > 0 ? $this->classes_schools->school_info($class_info["school_id"]) : 
							$this->classes_schools->define_school_info($class_info["school_define_id"]);
				$school_name = $school ? implode("", $school) : "";
				$ulog_total = $this->classes_student_create->ulog_total($this->class_id);

                //get paper num
                $this->load->model('exercise_plan/student_paper_model');
                $this->_student_paper_num = $this->student_paper_model->get_student_paper_num($this->tizi_uid);

                //get homework num
                $this->_get_homework_num();
				
				//shared file total
                $this->load->model('cloud/cloud_model');
                $share_total = $this->cloud_model->share_file_total($this->class_id);
				self::smarty_grade();
				$this->smarty->assign("part", $part);
				$this->smarty->assign("class_info", $class_info);
				$this->smarty->assign("school_name", $school_name);
				$this->smarty->assign("alpha_class_id", alpha_id_num($this->class_id));
				$this->smarty->assign("stu_total", intval($class_info["stu_count"] + $ulog_total));
				$this->smarty->assign("paper_num", $this->_student_paper_num);
				$this->smarty->assign("homework_num", $this->_student_homework_num);
				$this->smarty->assign("share_total", $share_total);

				call_user_func("self::".$part);exit;
			}
        }
		call_user_func("self::class_join");
    }

	protected function homework(){
		
        $this->load->model('homework/student_zuoye_model');
        $this->load->model('exercise_plan/student_paper_model');
        $this->load->model('homework/game_model');
        $this->load->model('question/question_category_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('video/videos_model');
        $this->load->model('homework/unit_model');
        $this->load->library('qiniu');
        $this->qiniu->change_bucket('fls_');

        $page_num = $this->input->get('page', true);
        $page_num = (int)$page_num;
        if(!$page_num) $page_num = 1;
        $pagination = $this->get_pagination($page_num, $this->_student_homework_num, 
            'student_homework_pagination', Constant::STUDENT_HOMEWORK_PER_PAGE);

        $pagination_index = ($page_num - 1) * Constant::STUDENT_HOMEWORK_PER_PAGE;
        $data = $this->student_zuoye_model->get(array('zuoye_student.`user_id`'=>$this->tizi_uid), "", 
            array($pagination_index, Constant::STUDENT_HOMEWORK_PER_PAGE));
        $zuoye_status = array(
            0 => '未完成',
            1 => '部分完成',
            2 => '已完成'
        );
        foreach($data as $key=>$val){
            $subject_type = $this->question_subject_model->get_subject_type_by_id($val['subject_id']);
            $val['subject_name'] = $this->question_subject_model->get_subject_type_name($subject_type);
            $val['assign_start_date'] = date("Y-m-d H:i", $val['assign_start_time']);
            $val['assign_end_date'] = date("Y-m-d H:i", $val['assign_end_time']);
            $val['complete_status'] = $zuoye_status[$val['is_complete']];
            $val['score'] = '--';
            $zuoye_info = array();
            $papers = $videos = $games = array();
            if(!empty($val['zuoye_info'])){
                $zuoye_info  = json_decode($val['zuoye_info'], true);
            }

            $units = array();
            $banbens = array();
            if (!empty($val['unit_ids'])) {
                $unit_ids = explode(',', $val['unit_ids']);
                foreach ($unit_ids as $unit_id) {
                    $categories = $this->question_category_model->get_single_path($unit_id);
                    if (count($categories) < 3) continue;
                    $category = array_pop($categories);
                    $units[] = $category->name;       
                    $banbens[] = $categories[0]->name.$categories[1]->name;
                }
            }
            $val['units'] = implode(' , ', $units);
            $banbens = array_unique($banbens);
            $val['banbens'] = implode(' , ', $banbens);

            $student_video = $student_game = $student_paper = array();
            if($val['is_complete'] != 2){
                if(isset($val['video_ids']) && !empty($val['video_ids'])){
                    $video_ids = explode(',', $val['video_ids']);
                    if(isset($zuoye_info['video']) && !empty($zuoye_info['video'])){
                        $student_video = $zuoye_info['video'];
                    }
                    foreach($video_ids as $video_id){
                        $video = array();
                        $video_info = $this->videos_model->get_lesson_by_id($video_id);
                        if(empty($video_info)) continue;
                        if(!empty($student_video) && in_array($video_id, $student_video)){
                            $video['is_complete'] = 1;
                        }else{
                            $video['is_complete'] = 0;
                        }
                        $video['id'] = $video_id;
                        $video['thumb_uri'] = $this->qiniu->qiniu_public_link($video_info->thumb_uri);
                        $video['video_link'] = waijiao_url('video/detail/'.$video_info->id);
                        $videos[] = $video;
                    }
                }
                if(isset($val['unit_game_ids']) && !empty($val['unit_game_ids'])){
                    $unit_game_ids = json_decode($val['unit_game_ids'], true);
                    if(isset($zuoye_info['game']) && !empty($zuoye_info['game'])){
                        $student_game = $zuoye_info['game'];
                    }
                    foreach($unit_game_ids as $unit_game_ids_key => $unit_game){
                        $game = $this->game_model->get_game_info($unit_game['game_id']);
                        $game['game_index'] = $unit_game_ids_key+1;
                        if(!empty($student_game) && isset($student_game[$unit_game_ids_key]) !== false){
                            $game['is_complete'] = 1;
                        }else{
                            $game['is_complete'] = 0;
                        }
                        $games[] = $game;
                    }
                }

            }
            if (isset($val['paper_ids']) && !empty($val['paper_ids'])) {
                $paper_ids = json_decode($val['paper_ids'], true);
                if($paper_ids){
                    foreach ($paper_ids as $paper_index=>$paper) {
                        $assignment_id = isset($paper['assignment_id'])?$paper['assignment_id']:0;
                        $paper_info = $this->student_paper_model->get_student_paper($this->tizi_uid, $assignment_id);
                        $papers[] = array(
                            'id'=>$paper_index+1,
                            'name'=>isset($paper_info['paper_name'])?$paper_info['paper_name']:'',
                            'is_complete'=>isset($paper_info['is_completed'])?$paper_info['is_completed']:0
                        );
                        if(isset($paper_info['is_completed']) and $paper_info['is_completed']){$student_paper[] = 1;}
                    }
                }
            }

            $val['task_num'] = count($videos) + count($games) + count($papers);
            $val['task_num_completed'] = count($student_video) + count($student_game) + count($student_paper);
            $val['videos'] = $videos;
            $val['games'] = $games;
            $val['papers'] = $papers;
            $val['score'] = $val['question_num'] ? round(($val['correct_num'] / $val['question_num']) * 100) : 0;
            $data[$key] = $val;
            //$unit_id = array_shift(json_decode($val['video_ids'], true));
        }

		$this->smarty->assign("student_zuoye", $data);
		$this->smarty->assign("pagination", $pagination);
        $this->smarty->display("student/class/student_homework.html");
		
	}

    // 老师
    protected function teacher(){
		$this->load->model("class/classes_teacher");
		$class_teacher = $this->classes_teacher->get_ct($this->class_id);
		$this->smarty->assign("class_teacher", $class_teacher);
        $this->smarty->display("student/class/teacher.html");
    }
    // 试卷
    protected function paper(){
        
        $this->load->model('exercise_plan/student_paper_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('class/classes_teacher');
        $this->load->model('class/classes_student');
        $this->load->model('login/register_model');
        $this->load->helper('time');

        $page_num = $this->input->get('page', true);
        $page_num = (int)$page_num;
        if(!$page_num) $page_num = 1;
        $pagination = $this->get_pagination($page_num, $this->_student_paper_num, 
            'student_paper_pagination', Constant::STUDENT_PAPER_PER_PAGE);

        $pagination_index = ($page_num - 1) * Constant::STUDENT_PAPER_PER_PAGE;
        $papers = $this->student_paper_model->get_student_paper($this->tizi_uid, "", 
            array($pagination_index, Constant::STUDENT_PAPER_PER_PAGE));
        $students = $this->classes_student->get_user_ids($this->class_id, "count(*) as user_num");
        $student_num = isset($students[0]['user_num']) ? $students[0]['user_num'] : 0 ;
        foreach($papers as $key=>$paper){

            if($paper['deadline'] > time()){
                $paper['residue_time'] = convertToHoursMinsSecs($paper['deadline'] - time(), "%s小时%s分%s秒");
            }else{
                $paper['residue_time'] = '答题已结束';
            }
            $subject = null;
            if($paper['subject_id']){
                $subject = $this->question_subject_model->get_subject_type_info($paper['subject_id']);
            }
            $paper['subject_name'] = $subject['name'];
            $paper['teacher_name'] = '';
            $paper['student_num'] = $student_num;
            $paper['correct_rate'] = $paper['online_done_num'] ? round(($paper['correct_num'] / $paper['online_done_num'])*100) : 0;
            $teacher = $this->register_model->get_user_info($paper['teacher_id']);
            $paper['teacher_name'] = isset($teacher['user']->name)?$teacher['user']->name:'';
            $class_correct_rate = $this->student_paper_model->get_class_correct_rate($paper['paper_assign_id']);
            if($class_correct_rate){
                $class_correct_rate = round($class_correct_rate * 100);
            }
            $paper['class_correct_rate'] = $class_correct_rate;
            $papers[$key] = $paper;
        }
        // var_dump($papers);die;
		$this->smarty->assign("papers", $papers);
		$this->smarty->assign("pages", $pagination);
        $this->smarty->display('student/class/paper.html');

    }
    // 同学
    protected function mate(){
		$this->load->helper("img");
		$class_mates = $this->classes_student->get_cs($this->class_id);
		$create_mates = $this->classes_student_create->get($this->class_id, "id,student_id,student_name");

		$this->smarty->assign("class_mates", $class_mates);
		$this->smarty->assign("create_mates", $create_mates);
        $this->smarty->display("student/class/mate.html");
    }

    // 文件
    protected function resource(){
        $this->load->model("class/classes_schools");
        $this->load->model("cloud/cloud_model");
        $this->load->helper('qiniu'); 
        $this->resource_page();
        $this->smarty->display('student/class/resource.html');
    }
    protected function get_pagination($page_num,$total,$func,$pagesize=false) {
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
    function resource_page(){//班级分享文件，分页
        $iclass = $this->classes_student->userid_get($this->tizi_uid);
        $this->class_id = $iclass[0]["class_id"];
        $flip = $this->input->get('flip',true,true,false);
        $this->load->library('qiniu');
        $page = intval($this->input->get('page',true,true,1));
        $class_id = $this->class_id;
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
                if($v['file_type']==Constant::CLOUD_FILETYPE_PIC){
                    $this->load->helper('qiniu');
                    $share[$k]['file_path'] = qiniu_img($v['file_path'],0,0,400);
                }elseif($v['file_ext'] and strpos(Constant::CLOUD_VIDEO_TYPES_JWPLAYER, $v['file_ext'])!==false){
                    $this->load->helper('qiniu');
                    $share[$k]['file_path'] = qiniui_get_vframe($v['file_path']);
                }
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
        $pages = self::get_pagination($page,$share_total,'sharelist_page');
        $this->smarty->assign('share',$share);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign("current_page", $page);
        if($flip){
            $json['errorcode'] = true;
            $json['html']= $this->smarty->fetch("student/class/share/stu_class_share_pagination.html");
            echo json_token($json);die;
        }
    }
    // 未登录或没有班级
    protected function class_join(){
        $template="student/class/no.html";
        $cache_id="ban_t".$this->tizi_utype;
        /*
        if(!$this->smarty->isCached($template, $cache_id))
        {
    		$this->load->model("class/class_model");
    		$class = $this->class_model->getnew();
    		$this->smarty->assign("class", $class);
        }
        */
        $this->smarty->display($template, $cache_id);
    }
    
    protected function smarty_grade(){
		$this->load->model("constant/grade_model");
		$grade = $this->grade_model->get_grade();
		$arr_grade = array();
		foreach ($grade as $gt){
			foreach ($gt as $key => $value){
				$key !== 0 ? $arr_grade[$key] = $value : "";
			}
		}
		$this->smarty->assign("igrade", $grade);
		$this->smarty->assign("arr_grade", $arr_grade);
	}
	
	public function sign(){
		$alpha_class_id = trim($this->input->post("class_id"));
		$alpha_class_id = strtoupper($alpha_class_id); //强制转成大写字母，修复区分大小写的问题，2014-03-04
		$alpha_class_id = str_replace(" ", "", $alpha_class_id);
		$user_id = $this->tizi_uid;
		$class_id = alpha_id_num($alpha_class_id, true);
		$this->load->helper("json");
		
		if ($class_id > 0 && $user_id > 0){
			$this->load->model("class/classes");
			$this->load->model("class/classes_student_create");
			$class_info = $this->classes->get($class_id);
			
			if (null === $class_info){
				$json["code"] = -6;
				$json["msg"] = "班级不存在.";
				json_get($json);
			}
			
			$create_number = $this->classes_student_create->total($class_id);
			//权限控制增加
			$this->load->library("credit");
			$userlevel_privilege = $this->credit->userlevel_privilege($class_info["creator_id"]);
			$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
			
			if ($class_info["class_status"]){
				$json["code"] = -2;
				$json["msg"] = "该班级已经被班级创始人解散,您现在无法加入它.";
			} else if ($class_info["close_status"]){
				$json["code"] = -3;
				$json["msg"] = "该班级已经关闭学生加入.";
			} else if (($class_info["stu_count"] + $create_number) >= $max_student_number){
				$json["code"] = -4;
				$json["msg"] = "该班级的人数已经达到了".$max_student_number."个,已经不能再加入更多的学生.";
			} else {
				$this->load->model("class/classes_student");
				$student_id = $this->classes_student->add($class_id, $user_id, time(), 
					Classes_student::JOIN_METHOD_REGISTER);
				if (false === $student_id){
					$json["code"] = -5;
					$json["msg"] = "您已经加入过班级了,请尝试刷新页面.";
				} else {
					$this->load->model("constant/grade_model");
					$grade = $this->grade_model->get_grade();
					$arr_grade = array();
					foreach ($grade as $gt){
						foreach ($gt as $key => $value){
							$key !== 0 ? $arr_grade[$key] = $value : "";
						}
					}
					
					$json["code"] = 1;
					$json["msg"] = "您已经成功加入该班级";
					$json["classname"] = $class_info["classname"];
					if (isset($arr_grade[$class_info["class_grade"]])){
						$json["classname"] = $arr_grade[$class_info["class_grade"]].$json["classname"];
					}
					$json["student_id"] = $student_id;
				}
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "班级不存在.";
		}
		json_get($json);
	}

    private function _get_homework_num(){
        
        $this->load->model('homework/student_zuoye_model');
        $data = $this->student_zuoye_model->get(
            array('zuoye_student.`user_id`'=>$this->tizi_uid),
            "count(*) as num"
        );
        if(isset($data[0]['num'])) 
            $this->_student_homework_num = $data[0]['num'];

    }
	
}
