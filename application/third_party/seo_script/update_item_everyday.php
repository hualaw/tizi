<?php
set_time_limit(0);
include(dirname(__DIR__).'/base_script.php');
$update = new Update_item_everyday();
$update->action();

class Update_item_everyday{

    private $_wait_move_total = 0;

    public function __construct(){
        init(false);
       // init(false, 'development');
       // self::get_mydoc_total();
    }

	
	//程序执行入口文件
    public function action()
    {
	
			//读取所有的categoryids
    		$query = mysql_query("SELECT id from category order by id asc");
	        $category_ids_list = array(); 
	        while(!empty($query) and $data = mysql_fetch_array($query)){
	            $category_ids_list[] = $data;  
	        }
			
			//echo "<pre>";print_r($category_ids_list);exit;
			$c=0;
			foreach($category_ids_list as $cil){
				if($max_question_id = $this->check_max_num_form_seo_question($cil['id'])){
					//echo $max_question_id;exit;
					$question_arr = $this->get_question_id_by_max_question_id($cil['id'],$max_question_id,1);
				}else{
					$question_arr = $this->get_question_id_by_max_question_id($cil['id'],0,1);
				}
				//echo count($question_arr);exit;
				if($question_arr){//如果该分类下有剩余的文章，就写入到表seo_question表中
					foreach($question_arr as $q_arr){
						if(!$this->check_exist($q_arr['question_id'])){//如果表中不存在该question_id，则写入
							$date=date("Y-m-d");
							$sql="insert into seo_question(question_id,category_id,insert_date) values ({$q_arr['question_id']},{$q_arr['category_id']},'$date')";
							
							mysql_query($sql);
						}
					}
					
				}
			}
			
		
	}
	
	
	//检查根据给的question_id查看seo_question表中是否存在该文章id
	//返回是bo0le
	//zhangxiaoming 2014-04-29
	public function check_exist($question_id){
			$query = mysql_query("SELECT id from seo_question where question_id=$question_id limit 1");
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
	
	
	
	//检查根据给的categoryid和该分类下目前seo_question表中最大的question_id返回question_category中该分类其他的文章id
	//返回是一个数组或者false
	//zhangxiaoming 2014-04-29
	
	private function get_question_id_by_max_question_id($cateid,$maxid,$limit=1){
			$query = mysql_query("SELECT question_id,category_id from question_category where category_id=$cateid and question_id > $maxid order by question_id asc limit $limit");
			$question_ids_list = array(); 
			while(!empty($query) and $data = mysql_fetch_array($query)){
					$question_ids_list[] = $data;  
			}
			if($question_ids_list){
				return $question_ids_list;
			}else{
				return false;
			}
	}
	
	
	
	//检查根据给的categoryid返回seo_question中该分类最大的question_id
	//返回是一个整数
	//zhangxiaoming 2014-04-29
	private function check_max_num_form_seo_question($cateid){
		$query = mysql_query("SELECT question_id from seo_question where category_id=$cateid order by question_id desc limit 1");
	    $question_ids_list = array(); 
	    while(!empty($query) and $data = mysql_fetch_array($query)){
	            $question_ids_list[] = $data;  
	    }
	
		if($question_ids_list){
			return $question_ids_list[0]['question_id'];
		}else{
			return false;
		}
	}
}

