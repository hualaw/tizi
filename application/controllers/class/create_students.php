<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

require(dirname(__FILE__).DIRECTORY_SEPARATOR."class_controller.php");
class Create_Students extends Class_Controller{
	
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 创建学号并返回创建的结果
	 * @return JSON
	 * success as array
	 * @author jiangwuzhang
	 */
	public function b_name(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$alpha_class_id = $this->input->post("class_id");
		$student_names = $this->input->post("student_names");
		$class_id = alpha_id_num($alpha_class_id, true);
		$student_names = explode("\n", $student_names);
		$students = array();
		foreach ($student_names as $key => $value){
			$name = trim(strip_tags($value));
			if ("" !== $name){
				$students[] = mb_strlen($name, "utf-8") > 5 ? mb_substr($name, 0, 5, "utf-8") : $name;
			}
		}
		
		$this->load->model("class/classes_student_create");
		$this->load->model("class/classes");
		$class_info = $this->classes->get($class_id);
		$total_creat = $this->classes_student_create->get($class_id, "id");
		
		//检查每次添加的数量限制
		//权限控制增加
		$this->load->library("credit");
		$userlevel_privilege = $this->credit->userlevel_privilege($class_info["creator_id"]);
		$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
		if (count($students) > $max_student_number){
			$json["code"] = -2;
			$json["msg"] = "每次最多添加".$max_student_number."名学生";
			json_get($json);
		}
		if ((count($total_creat) + count($students) + $class_info["stu_count"]) > $max_student_number){
			$json["code"] = "-3";
			$json["msg"] = "每个班级最多加入".$max_student_number."个学生";
			json_get($json);
		}
		
		$students = array_reverse($students);
		$this->load->model("class/classes_teacher");
		$idct = $this->classes_teacher->get_idct($class_id, "teacher_id");
		if (in_array(array("teacher_id" => $teacher_id), $idct)){
			$students = $this->classes_student_create->create($class_id, $students);
			if (0 >= count($students)){
				$json["code"] = -4;
				$json["msg"] = "没有添加学生帐号.";
			} else {
				$json["code"] = 1;
				$json["msg"] = "成功添加".count($students)."个学生帐号.";
				$json["students"] = $students;
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "您没有权限操作.";
		}
		json_get($json);
	}
	
	public function b_number(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$alpha_class_id = $this->input->post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		$create_number = intval($this->input->post("create_number"));
		$user_id = $this->session->userdata("user_id");
		
		$this->load->model("class/classes_teacher");
		$bt = $this->classes_teacher->get_bt($user_id, "class_id");
		if (in_array(array("class_id" => $class_id), $bt)){
			//检查班级已创建的班级剩余数量.
			$this->load->model("class/classes_student_create");
			$this->load->model("class/classes");
			$class_info = $this->classes->get($class_id);
			$total_creat = $this->classes_student_create->get($class_id, "id");
			$current_number = count($total_creat) + $class_info["stu_count"];
			
			//每次创建检测
			//权限控制增加
			$this->load->library("credit");
			$userlevel_privilege = $this->credit->userlevel_privilege($class_info["creator_id"]);
			$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
			if ($create_number > $max_student_number){
				$json["code"] = -1;
				$json["msg"] = "每次最多添加".$max_student_number."名学生";
				json_get($json);
			} else if (0 >= $create_number){
				$json["code"] = -4;
				$json["msg"] = "请输入一个大于0的数字.";
				json_get($json);
			}
			
			if (($current_number + $create_number) > $max_student_number){
				$surplus = intval($max_student_number - $current_number);
				$json["code"] = -3;
				$json["msg"] = "每次最多添加".$max_student_number."名学生<br/>";
				$json["msg"] .= "您还剩余".$surplus."个名额";
				$json["surplus"] = $surplus;
			} else {
				$students = array();
				for ($i = 0; $i < $create_number; ++$i){
					$students[] = "";
				}
				$students = $this->classes_student_create->create($class_id, $students);
				$json["code"] = 1;
				$json["msg"] = "成功添加".count($students)."个学生帐号.";
				$json["students"] = $students;
			}
		} else {
			$json["code"] = -2;
			$json["msg"] = "您没有权限操作.";
		}
		json_get($json);
	}
	
	/**
	 * 下载学生帐号信息
	 */ 
	public function dl(){
		$this->ajax_check();
		
		$alpha_class_id = $this->input->get("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		$teacher_id = intval($this->session->userdata("user_id"));
		if ($teacher_id > 0){
			$this->load->model("class/classes_teacher");
			$idct = $this->classes_teacher->get_idct($class_id, "teacher_id");
			if (in_array(array("teacher_id" => $teacher_id), $idct)){
				$this->load->model("class/classes_student_create");
				$this->load->model("class/classes");
				$this->load->model("class/classes_student");
				$this->load->model("login/register_model");
				$user_create = $this->classes_student_create->get($class_id);
				$user_student = $this->classes_student->get_cs($class_id);
				$class_pwd = rand6pwd($class_id);
				foreach ($user_student as $value){
					$cp = $this->register_model->compare_password(md5("ti".$class_pwd."zi"), $value["password"]);
					$user_create[] = array(
						"student_id" => $value["student_id"],
						"password" => $cp === true ? $class_pwd : "学生已自设",
						"student_name" => $value["name"]
					);
				}
				
				
				$class = $this->classes->get($class_id, "classname,class_status,school_id");
				if ($class["class_status"] == 0){
					$this->load->model("class/classes_schools");
					if ($class["school_id"] > 0){
						$school = $this->classes_schools->get($class["school_id"], "schoolname");
					} else {
						$school["schoolname"] = "";
					}
					$file_name = $school["schoolname"] . $class["classname"].".doc";
					header("Content-Type:application/msword");
					if(stripos($this->input->server("HTTP_USER_AGENT"), "windows") !== false){
						header("Content-Disposition: attachment; filename=" . iconv("utf-8", "gbk//IGNORE", $file_name));
					}else{
						header("Content-Disposition: attachment; filename=" . $file_name);
					}
					
					$this->smarty->assign("user_create", $user_create);
					$this->smarty->display("teacher/class/msword.html");
				}
			}
		} else {
			header("Location:".site_url());
		}
	}
	
	public function remove(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$teacher_id = intval($this->session->userdata("user_id"));
		if (!$teacher_id){
			$json["code"] = -999;
			$json["msg"] = "登录超时,是否重新登录?";
			json_get($json);
		}
		
		$student_id = intval($this->input->post("sid"));
		$this->load->model("class/classes_student_create");
		$this->load->model("class/classes_teacher");
		$info = $this->classes_student_create->studentid_get($student_id, "class_id");
		$bt = $this->classes_teacher->get_bt($teacher_id, "class_id");
		if (null !== $info && in_array(array("class_id" => $info["class_id"]), $bt)){
			$remove = $this->classes_student_create->remove($student_id);
			if ($remove > 0){
				$json["code"] = 1;
				$json["msg"] = "删除成功.";
			} else {
				$json["code"] = -2;
				$json["msg"] = "该帐号的状态可能已经改变,请尝试刷新页面.";
			}
		} else {
			$json["code"] = -1;
			$json["msg"] = "您没有删除这个帐号的权限.";
		}
		json_get($json);
	}
	
	/**
	 * 上传解析
	 */
	public function xls(){
		$this->ajax_check();
		
		$this->load->helper("json");
		$this->load->model("class/classes_teacher");
		$alpha_class_id = $this->input->get_post("class_id");
		$class_id = alpha_id_num($alpha_class_id, true);
		$user_id = $this->session->userdata("user_id");
		$bt = $this->classes_teacher->get_bt($user_id, "class_id");
		if (!in_array(array("class_id" => $class_id), $bt)){
			self::_stream(-7, "无权操作.");
		}
		
		if ($class_id <= 0){
			$json["code"] = -4;
			$json["msg"] = "上传失败.";
			json_get($json);
		}

		$name = "fileField";
		if (!isset($_FILES[$name]["error"]) || $_FILES[$name]["error"] != 0){
			$json["code"] = -3;
			$json["msg"] = "上传失败.";
			json_get($json);
		}
		
		$pathinfo = pathinfo($_FILES[$name]["name"]);
		if ($pathinfo["extension"] !== "xls" && $pathinfo["extension"] !== "xlsx"){
			$json["code"] = -1;
			$json["msg"] = "请上传excel格式的学生信息.";
			json_get($json);
		}
		
		require_once LIBPATH."third_party/phpexcel/PHPExcel.php";
		require_once LIBPATH."third_party/phpexcel/PHPExcel/IOFactory.php";
		require_once LIBPATH."third_party/phpexcel/PHPExcel/Reader/Excel2007.php"; 
		$objReader = PHPExcel_IOFactory::createReader("Excel2007");
		if (!$objReader->canRead($_FILES[$name]["tmp_name"])){
			$objReader = PHPExcel_IOFactory::createReader("Excel5");
		}
		$objPHPExcel = $objReader->load($_FILES[$name]["tmp_name"]);
		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow(); // 取得总行数  
		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
		$cells = array();
		for ($j = 1; $j <= $highestRow; $j++){
			for($k = "A", $z = 1;$k <= $highestColumn; $k++, $z++){
				$cells[$j][$z] = $sheet->getCell($k.$j)->getValue();
			}
		}
		/**
		require_once dirname(__FILE__)."/../../third_party/excelreader/excel_reader2.php";
		$data = new Spreadsheet_Excel_Reader($_FILES[$name]["tmp_name"]);
		$data->setOutputEncoding("UTF-8");
		*/
		
		$student_info = $this->gitname($cells);
		$this->bigdata($cells, $student_info);
		if (!empty($student_info)){
			
			//检查班级已创建的班级剩余数量.
			$this->load->model("class/classes_student_create");
			$this->load->model("class/classes");
			$class_info = $this->classes->get($class_id);
			$total_creat = $this->classes_student_create->get($class_id, "id");
			$current_number = count($total_creat) + $class_info["stu_count"];
			
			//每次创建检测
			//权限控制增加
			$this->load->library("credit");
			$userlevel_privilege = $this->credit->userlevel_privilege($class_info["creator_id"]);
			$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
			if (($current_number + count($student_info)) > $max_student_number){
				$surplus = intval($max_student_number - $current_number);
				$json["code"] = -3;
				$json["msg"] = "xx每次最多添加".$max_student_number."名学生<br/>";
				$json["msg"] .= "您还剩余".$surplus."个名额";
				$json["surplus"] = $surplus;
				json_get($json);
			}
			
			$this->load->model("class/classes_student_create");
			$create_prepare = $this->classes_student_create->create_prepare($student_info, $class_id);
	
			$data = array();
			foreach ($create_prepare as $value){
				$data[] = array(
					"student_id" => $value["student_id"],
					"password" => $value["password"],
					"student_name" => $value["student_name"]
				);
			}
			$json["code"] = 1;
			$json["data"] = $data;
			$json["msg"] = "成功添加".count($data)."个学生帐号.<br/>请将学号和密码告知您的学生，尽快登录。";
		} else {
			$json["code"] = -2;
			$json["msg"] = "未发现相关数据,请检查excel格式或者下载excel模板.";
		}
		json_get($json);
	}
	
	/**
	 * 获取姓名列表
	 */ 
	private function gitname($cells){
		$gitname = array();
		$match = array("姓名", "学生姓名", "学生");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match)){
				for ($row = 2; $row <= Constant::CM_XLS_MAX_ROW; ++$row){
					if (isset($cells[$row][$col])){
						$name = strip_tags($cells[$row][$col]);
						$name = trim($name);
						if ("" !== $name){
							$name = mb_strlen($name, "utf-8") > 5 ? mb_substr($name, 0, 5, "utf-8") : $name;
							$gitname[$row] = array("name" => $name);
						}
					}
				}
				break 1;
			}
		}
		return $gitname;
	}
	
	/**
	 * 数据挖掘
	 */
	private function bigdata($cells, &$student_info){
		//挖掘性别
		$match = array("性别");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match)){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col])){
						if ($cells[$key][$col] === "男"){
							$student_info[$key]["sex"] = 1;
						} else if ($cells[$key][$col] === "女"){
							$student_info[$key]["sex"] = 2;
						}
					}
				}
				break 1;
			}
		}
		
		//QQ号挖掘
		$match = array("QQ", "QQ号", "QQ号码");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array(strtoupper($cells[1][$col]), $match)){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col]) && intval($cells[$key][$col]) > 10000){
						$student_info[$key]["QQ"] = $cells[$key][$col];
					}
				}
				break 1;
			}
		}
		
		//学生手机号挖掘
		$match = array("手机", "手机号码", "手机号", "联系手机");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match) 
				&& strpos($cells[1][$col], "家长") === false){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col]) && (strlen($cells[$key][$col]) == 11 || strlen($cells[$key][$col]) == 12)){
						$student_info[$key]["telephone"] = $cells[$key][$col];
					}
				}
				break 1;
			}
		}
		
		//家庭地址挖掘
		$match = array("地址", "住址", "家庭地址", "家庭住址");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match)){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col])){
						$address = strip_tags($cells[$key][$col]);
						$address = trim($address);
						if ("" !== $address){
							$address = mb_strlen($address, "utf-8") > 120 ? mb_substr($address, 0, 120, "utf-8") : $address;
							$student_info[$key]["address"] = $address;
						}
					}
				}
				break 1;
			}
		}
		
		//年龄数据挖掘
		$match = array("年龄");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match)){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col])){
						$age = intval($cells[$key][$col]);
						if ($age > 1 && $age <120){
							$student_info[$key]["age"] = $age;
						}
					}
				}
			}
		}
		
		//jiazhang xingming wajue 
		$match = array("家长姓名");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match)){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col])){
						$student_info[$key]["parent_name"] = $cells[$key][$col];
					}
				}
			}
		}
		
		//家长手机列挖掘
		$match = array("家长手机", "家长联系手机", "家长手机号码", "家长手机号", "联系电话");
		for ($col = 1; $col <= Constant::CM_XLS_MAX_COL; ++$col){
			if (isset($cells[1][$col]) && in_array($cells[1][$col], $match)){
				foreach ($student_info as $key => $value){
					if (isset($cells[$key][$col]) && (strlen($cells[$key][$col]) == 11 || strlen($cells[$key][$col]) == 12)){
						$student_info[$key]["parent_phone"] = $cells[$key][$col];
					}
				}
			}
		}
	}
	
	private function _stream($code, $msg){
		$this->load->helper("json");
		$data = array(
			"code" => $code,
			"msg" => $msg
		);
		json_get($data);
	}
}

/* end of create_invite.php */
