<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

require_once dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php";
class Invite extends Class_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index($invite){
		$invite = strtoupper($invite);
		$class_id = alpha_id_num($invite, true);
		$this->load->model("class/classes");
		$class_info = $this->classes->get($class_id);
		if (null !== $class_info){
			$this->load->model("class/classes_schools");
			$creator_name = $this->classes->get_realname($class_info["creator_id"]);
			if ($class_info["school_id"] > 0){
				$school_info = $this->classes_schools->school_info($class_info["school_id"]);
			} else {
				$school_info = $this->classes_schools->define_school_info($class_info["school_define_id"]);
			}
			
			$this->smarty->assign("creator_name", $creator_name);
			$this->smarty->assign("school_info", implode("", $school_info));
			$this->smarty->assign("class_info", $class_info);
			$this->smarty->assign("class_id", $invite);
			$this->smarty->assign("UnLoginPage", "classInvite");
			$this->smarty->display("teacher/class/invite.html");
		} else {
			show_404();
		}
	}
	
	public function accept(){
		$class_id = $this->input->post("class_id");
		$user_id = intval($this->session->userdata("user_id"));
		$user_type = $this->session->userdata("user_type");
		if ($user_id > 0 && $user_type == Constant::USER_TYPE_TEACHER){
			$data = $this->teacher($class_id);
		} else if ($user_id > 0 && $user_type == Constant::USER_TYPE_STUDENT){
			$data = $this->student($class_id);
		}
		$this->load->helper("json");
		json_get($data);
	}
	
	/**
	 * 3.0直接加入模式
	 */ 
	private function teacher($invite){
		$teacher_id = intval($this->session->userdata("user_id"));
		$class_id = alpha_id_num($invite, true);
		
		$this->load->model("class/classes");
		$class_info = $this->classes->get($class_id, "class_status,creator_id");
		if (null !== $class_info && $class_info["class_status"] == 0){
			$this->load->model("class/classes_teacher");
			$bt = $this->classes_teacher->get_bt($teacher_id, "class_id");	//get classes_teacher by teacher_id
			if (in_array(array("class_id" => $class_id), $bt)){
				//redirect(site_url()."teacher/class/my");
				$data["code"] = -1;
				$data["msg"] = "您已经在该班级里面了";
				$data["redirect"] = site_url()."teacher/class/{$invite}/teacher";
			} else {
				$this->load->model("class/class_model");
				$this->load->model("login/register_model");
				$user_info = $this->register_model->get_user_info($teacher_id);
				if ($user_info["user"]->register_subject > 0){
					$this->load->model("question/question_subject_model");
					$subject_id = $user_info["user"]->register_subject;
					$subject_id = $this->question_subject_model->get_subject_type_by_id($subject_id);
				} else {
					$subject_id = 0;
				}
				$res = $this->class_model->i_join_class($class_id, $teacher_id, $subject_id, time());
				$data["code"] = 1;
				$data["msg"] = "加入成功";
				$data["redirect"] = site_url()."teacher/class/{$invite}/teacher";
			}
		} else {
			$data["code"] = -2;
			$data["msg"] = "班级不存在或已经被解散";
		}
		return $data;
	}
	
	public function t_invite_s(){
		exit;
		$user_id = $this->session->userdata("user_id");
		$alpha_class_id = $this->input->post("class_id");
		$versign = $this->input->post("versign");
		$class_id = alpha_id_num($alpha_class_id, true);
		$this->load->helper("json");
		if ($class_id > 0 && $user_id > 0){
			$this->load->model("class/classes");
			$this->load->model("class/classes_student_create");
			$class_info = $this->classes->get($class_id, "stu_count");
			$create_number = $this->classes_student_create->total($class_id);
			if (($class_info["stu_count"] + $create_number) >= Constant::CLASS_MAX_HAVING_STUDENT){
				$data["code"] = -7;
				$data["msg"] = "每个班级最多加入".Constant::CLASS_MAX_HAVING_STUDENT."个学生";
				json_get($data);
			}
			
			
			$this->load->model("class/classes_teacher");
			$bt = $this->classes_teacher->get_bt($user_id, "class_id");
			if (in_array(array("class_id" => $class_id), $bt)){
				$this->load->model("login/register_model");
				$type = preg_utype($versign);
				if (Constant::LOGIN_TYPE_STUID === $type){
					$data["code"] = -8;
					$data["msg"] = "学生不存在.";
					json_get($data);
				}
				$user_info = $this->register_model->get_user_id($versign, $type, "id,user_type,password,name");
				if (isset($user_info["user_id"]) && $user_info["user_id"] > 0 && 
						$user_info["user_type"] == Constant::USER_TYPE_STUDENT){
					$this->load->model("class/classes_student");
					$classes_student = $this->classes_student->userid_get($user_info["user_id"]);
					if (true === empty($classes_student)){
						$res = $this->classes_student->add($class_id, $user_info["user_id"], time(), 
							Classes_student::JOIN_METHOD_TLET);
						if (false !== $res){
							$this->load->model("login/session_model");
							$class_pwd = rand6pwd($class_id);
							$cp = $this->register_model->compare_password(md5("ti".$class_pwd."zi"), 
								$user_info["password"]);
							$data["students"][0] = array(
								"student_name"	=> $user_info["name"],
								"student_id"	=> $res,
								"password"		=> $cp === true ? $class_pwd : "学生已自设",
								"lastactive"	=> $this->session_model->get_lastgen($user_info["user_id"])
							);
							$data["code"] = 1;
							$data["msg"] = "学生加入班级成功.";
						} else {
							$data["code"] = -6;
							$data["msg"] = "加入失败,请稍后再试.";
						}
					} else if (isset($classes_student[0]) && $classes_student[0]["class_id"] == $class_id){
						$data["code"] = -4;
						$data["msg"] = "该学生已经在本班了.";
					} else {
						$data["code"] = -5;
						$data["msg"] = "该学生已经加入其他班级了.";
					}
				} else {
					$data["code"] = -3;
					$data["msg"] = "学生不存在.";
				}
			} else {
				$data["code"] = -2;
				$data["msg"] = "您没有权限操作.";
			}
		} else {
			$data["code"] = -1;
			$data["msg"] = "您没有权限操作.";
		}
		json_get($data);
	}
	
	private function student($invite){
		$user_id = intval($this->session->userdata("user_id"));
		$class_id = alpha_id_num($invite, true);

		$this->load->model("class/classes");
		$this->load->model("class/classes_student_create");
		$class_info = $this->classes->get($class_id);
		if (null !== $class_info && $class_info["class_status"] == 0){
			$create_number = $this->classes_student_create->total($class_id);
			
			//权限控制增加
			$this->load->library("credit");
			$userlevel_privilege = $this->credit->userlevel_privilege($class_info["creator_id"]);
			$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
			
			if (($class_info["stu_count"] + $create_number) >= $max_student_number){
				$data["code"] = -1;
				$data["msg"] = "该班级已经超过了成员数量限制";
			} else {
				$this->load->model("class/classes_student");
				$classes_student = $this->classes_student->userid_get($user_id, "class_id");
				if (!$classes_student){
					$this->classes_student->add($class_id, $user_id, time(), 
						Classes_student::JOIN_METHOD_INVITESITE);
					$data["code"] = 1;
					$data["msg"] = "加入成功";
					$data["redirect"] = redirect_url(Constant::USER_TYPE_STUDENT, "tizi");
				} else {
					$data["code"] = -3;
					$data["msg"] = "您已经加入过班级了";
					$data["redirect"] = redirect_url(Constant::USER_TYPE_STUDENT, "tizi");
				}
			}
		} else {
			$data["code"] = -2;
			$data["msg"] = "班级不存在或已经被解散";
		}
		return $data;
	}
}
