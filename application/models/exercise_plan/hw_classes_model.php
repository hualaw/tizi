<?php
class Hw_Classes_Model extends MY_Model{
	
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * 通过班级id获取下面的学生
	 * @param int $class_id
	 */
	function get_students($class_id){
		$res = $this->db->query("select user_id from classes_student where class_id = $class_id");
		$data = array();
		foreach($res->result_array() as $row){
			$data[] = $row['user_id'];
		}
		return $data;
	}
	
	//查出老师所教的[该科目的]班级名称
	function get_classes_name_by_teacher_id($id, $subject_id=false){
				$sql = " select c.class_grade, c.classname, c.id as class_id from classes_teacher as t, classes as c where t.class_id=c.id and t.teacher_id=$id ";
				if($subject_id){
							$sql .= " and t.subject_id = $subject_id ";
				}
     		return  $this->db->query($sql)->result_array();
	}
	
	function get_class_name_by_class_id($id){
		return $this->db->query("select classname from classes where id = $id limit 1")->row(0)->classname;
	}

	//通过班级id获取班级的名字的全部信息：class_grade, class_year, class_name
	function get_class_whole_name($id){
			return $this->db->query("select classname as class_name , class_grade , class_year from classes where id = $id limit 1")->result_array();
	}

	//通过user_id  从user表   获取老师的姓名
	function get_teacher_name($user_id){
			return $this->db->query("select name from user where id = $user_id")->row(0)->name;
	}

	//通过apply_id 从user 和 classes_teacher_apply表  获取申请者的姓名
	function get_applyer_name($id){
		   return $this->db->query("select u.name,u.id from user u left join classes_teacher_apply cta on cta.teacher_id = u.id where cta.id=$id limit 1 " )->result_array();
	}
}