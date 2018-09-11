<?php
include(dirname(__DIR__).'/base_script.php');

$My_Doc_Move_Obj = new My_Doc_Move();
$My_Doc_Move_Obj->do_work();

class My_Doc_Move{

    private $_wait_move_total = 0;

    public function __construct(){
        init();
        //init(false, 'development');
        self::get_mydoc_total();
    }

    protected function get_mydoc_total()
    {
    	$query = mysql_query("SELECT COUNT(m.`id`) AS total FROM teacher_document AS m LEFT JOIN teacher_lesson_preview AS p ON m.`id`=p.`doc_id` WHERE m.`status`=1");
		while (!empty($query) and $row = mysql_fetch_assoc($query))
		{
			$this->_wait_move_total = $row['total'];
		}
    }

    public function do_work()
    {
    	if($this->_wait_move_total > 0)
    	{
    		echo date("Y-m-d")."转移任务开始....\r\n";
    		$query = mysql_query("SELECT m.*,p.`doc_id`,p.`swf_folder_path`,p.`page_count` FROM teacher_document AS m LEFT JOIN teacher_lesson_preview AS p ON m.`id`=p.`doc_id` WHERE m.`status`=1");
	        $my_doc_list = array(); 
	        while(!empty($query) and $data = mysql_fetch_array($query)){
	            $my_doc_list[] = $data;  
	        }
	        $faild_num = $success_num = 0;
	        foreach ($my_doc_list as $key => $val) {
	        	$sql_str = "INSERT INTO cloud_user_file(`dir_id`,`file_name`,`file_size`,`file_type`,`user_id`,`upload_time`,`file_path`,`file_ext`,`upload_ip`,`is_del`,`queue_status`) "
	        	." VALUES ('0','{$val['file_name']}','{$val['file_size']}','1','{$val['user_id']}','{$val['upload_time']}','{$val['file_path']}','{$val['file_ext']}','{$val['upload_ip']}','0','{$val['queue_status']}')";
	        	$new_doc_id = 0;
	        	if(mysql_query($sql_str)){
	        		$new_doc_id = mysql_insert_id();
	        	}
	        	$new_swf_id = 0;
	        	$is_swf = true;
	        	if($new_doc_id > 0)
	        	{
	        		if($val['swf_folder_path'] and $val['page_count']){
	        			$preview_sql_str = "INSERT INTO cloud_document_preview(`doc_id`,`swf_folder_path`,`page_count`) VALUES ('{$new_doc_id}','{$val['swf_folder_path']}','{$val['page_count']}')";
	        			if(mysql_query($preview_sql_str)){
			        		$new_swf_id = mysql_insert_id();
			        	}
	        		}else{
	        			$is_swf = false;
	        		}
	        	}
	        	if(($new_doc_id > 0 and $new_swf_id > 0) || ($new_doc_id > 0 and $is_swf==false)){
	        		$success_num++;
	        		local_log(date("Y-m-d H:i:s").' 文件: '.$val['id'].'转换成功!','my_doc_move_log');
	        		echo "table:teacher_document->id:{$val['id']} ->new_doc_id:{$new_doc_id} successed!\r\n";
	        	}else{
	        		$faild_num++;
	        		local_log(date("Y-m-d H:i:s").' 文件: '.$val['id'].'转换失败!','my_doc_move_log');
	        		echo "table:teacher_document->id:{$val['id']}->new_doc_id:{$new_doc_id} faild!\r\n";
	        	}
	        }
	        echo date("Y-m-d H:i:s").' 我的文档转移结束: '.'成功转移: '.$success_num.'个 , 失败 '.$faild_num.'个;总计: '.$this->_wait_move_total."个文件需要转移\r\n";
	        local_log(date("Y-m-d H:i:s").' 我的文档转移日志: '.'成功转移: '.$success_num.'个 , 失败 '.$faild_num.'个;总计: '.$this->_wait_move_total.'个文件需要转移','my_doc_move_log');
    	}
    }
}

