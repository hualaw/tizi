<?php
set_time_limit(0);
include(dirname(__DIR__).'/base_script.php');
$update = new Update_ziyuan_exam_everyday();
$update->action();

class Update_ziyuan_exam_everyday{

    private $_wait_move_total = 0;

    public function __construct(){
        init(false);
        //init(false, 'development');
       // self::get_mydoc_total();
    }

	
	//程序执行入口文件
    public function action()
    {
	
			//读取所有的categoryids
    		$query = mysql_query("SELECT id from subject order by id asc");
	        $category_ids_list = array(); 
	        while(!empty($query) and $data = mysql_fetch_array($query)){
	            $category_ids_list[] = $data;  
	        }
			
			//echo "<pre>";print_r($category_ids_list);exit;
			foreach($category_ids_list as $cil){
				if($max_exam_id = $this->check_max_exam_id_form_ziyuan_exam($cil['id'])){
					//echo $max_exam_id;exit;
					$exam_arr = $this->get_exam_id_by_max_exam_id($cil['id'],$max_exam_id,30);
				}else{
					$exam_arr = $this->get_exam_id_by_max_exam_id($cil['id'],0,30);
				}
				
				//echo "<pre>";print_r($exam_arr);exit;
				if($exam_arr){//如果该分类下有剩余的文章，就写入到表seo_question表中
					foreach($exam_arr as $q_arr){
						if(!$this->check_exist($q_arr['id'])){//如果表中不存在该question_id，则写入
							$date=date("Y-m-d H:i:s");
							$sql="insert into ziyuan_exam(subject_id,exam_id,insert_time) values ({$q_arr['subject_id']},{$q_arr['id']},'$date')";
							
							mysql_query($sql);
						}
					}
					
				}
			}
			
		
	}
	
	
	//检查根据给的exam_id查看 ziyuan_exam 表中是否存在该文章id
	//返回是bo0le
	//zhangxiaoming 2014-07-23
	public function check_exist($exam_id){
			$query = mysql_query("SELECT id from ziyuan_exam where exam_id=$exam_id limit 1");
			$question_ids_list = array(); 
			while(!empty($query) and $data = mysql_fetch_array($query)){
					$question_ids_list[] = $data;  
			}
			if($question_ids_list){
				return true;
			}else{
				return false;
			}
	}
	
	
	
	//检查根据给的 subjectid 和该分类下目前 ziyuan_exam 表中最大的exam_id返回 exam 中该分类其他的试卷id
	//返回是一个数组或者false
	//zhangxiaoming 2014-07-23
	
	private function get_exam_id_by_max_exam_id($subjectid,$maxid,$limit=1){
			$query = mysql_query("SELECT id,subject_id from exam where subject_id=$subjectid and id > $maxid order by id asc limit $limit");
			$exam_ids_list = array(); 
			while(!empty($query) and $data = mysql_fetch_array($query)){
					$exam_ids_list[] = $data;  
			}
			if($exam_ids_list){
				return $exam_ids_list;
			}else{
				return false;
			}
	}
	
	
	
	//检查根据给的subjectid返回 ziyuan_exam 中该分类最大的exam_id
	//返回是一个整数
	//zhangxiaoming 2014-04-29
	private function check_max_exam_id_form_ziyuan_exam($subjectid){
		$query = mysql_query("SELECT exam_id from ziyuan_exam where subject_id=$subjectid order by id desc limit 1");
	    $question_ids_list = array(); 
	    while(!empty($query) and $data = mysql_fetch_array($query)){
	            $question_ids_list[] = $data;  
	    }
	
		if($question_ids_list){
			return $question_ids_list[0]['exam_id'];
		}else{
			return false;
		}
	}
}

