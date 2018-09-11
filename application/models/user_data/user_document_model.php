<?php

class User_Document_Model extends MY_Model {

	public $_table="teacher_document";
    public $_course_table="teacher_lesson_course";
	private $_doc_type_table="lesson_document_type";
    private $_redis=false;
    public function __construct()
    {
        parent::__construct();
    }


    /*get document*/
    public function get_docs_list($group_id,$page_num,$doc_type,$subject_id=0,$total=false,$status_where=false)
    {
        $uid = $this->session->userdata('user_id');
        $this->db->where($this->_table.'.subject_id',$subject_id);
        $this->db->where($this->_table.'.user_id',$uid);
        $this->db->where($this->_table.'.status',1);
        if($status_where){
            $this->db->where_in($this->_table.'.queue_status',array(1,4));
        }
		
        if ($doc_type)
		{
			$sub_types = Constant::new_doc_type($doc_type);
			if($sub_types && is_array($sub_types))$this->db->where_in($this->_table.'.doc_type',$sub_types);
            if($sub_types && !is_array($sub_types))$this->db->where($this->_table.'.doc_type',$sub_types);
            //$this->db->where($this->_table.'.doc_type',$doc_type);
		}
        if($group_id !== FALSE)
        {   
            $this->db->where($this->_table.'.group_id',$group_id);
        }
        if($total)
        {
            $this->db->select('count('.$this->_table.'.id) as total');
            $query=$this->db->get($this->_table);
            $doc_count=isset($query->row()->total)?$query->row()->total:0;
            return $doc_count;
        }
        else
        {
            $this->db->select($this->_table.".*,".$this->_course_table.".course_id");
            $this->db->join($this->_course_table,$this->_course_table.'.doc_id='.$this->_table.'.id','left');
            $limit=Constant::LESSON_PER_PAGE;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            $this->db->order_by($this->_table.".upload_time","desc");
            $this->db->order_by($this->_table.".id","desc");
            $this->db->group_by($this->_table.'.id');
            $this->db->limit($limit,$offset);
            //print_r($this->db);die;
            $query=$this->db->get($this->_table);
            $doc_content=$query->result();
            return $doc_content;
        }       
    }

    public function get_single_file($doc_id,$user_id,$is_join = true)
    {
        $this->db->where($this->_table.'.id',$doc_id);
        $this->db->where($this->_table.'.user_id',$user_id);
        $this->db->where($this->_table.'.status',1);
        if($is_join){
            $this->db->join('teacher_lesson_course','teacher_document.id=teacher_lesson_course.doc_id','left');
            $this->db->select($this->_table.'.*,teacher_lesson_course.course_id');
        }
        return $this->db->get($this->_table)->row();
    }
    
    public function get_single_doc_preview($doc_id,$user_id,$is_join = false)
    {
        $this->db->where($this->_table.'.id',$doc_id);
        $this->db->where($this->_table.'.user_id',$user_id);
        $this->db->where($this->_table.'.status',1);
        $this->db->where($this->_table.'.queue_status',1);
        if($is_join){
            $this->db->join('teacher_lesson_preview','teacher_document.id=teacher_lesson_preview.doc_id','left');
            $this->db->select($this->_table.'.*,teacher_lesson_preview.swf_folder_path,teacher_lesson_preview.page_count');
        }
        $this->db->limit(1);
        return $this->db->get($this->_table)->row();
    }

    /*添加文件（上传）*/
    public function add_file($insert_data)
    {
        $query = $this->db->insert($this->_table,$insert_data);
        if($query)
            return $this->db->insert_id();
        else
            return false;
    }

    /*更新文件信息*/
    public function update_file_info($file_id,$user_id,$update_data)
    {
        $this->db->where('id',$file_id);
        $this->db->where('user_id',$user_id);
        $query = $this->db->update($this->_table,$update_data);
        return $query?true:false;
    }

    /*查询已上传但未完善信息的文件(不分页)*/
    public function get_uploaded_files($user_id)
    {
        $this->db->where(array('user_id'=>$user_id,'status'=>0));
        $this->db->order_by('upload_time','desc');
        return $this->db->get($this->_table)->result();
    }

    /*将已有分组更新为未选分组返回未选分组的count*/
    public function remove_no_group($group_id,$uid,$subject_id)
    {
        $this->db->where(array('group_id'=>$group_id,'user_id'=>$uid,'status'=>1,'subject_id'=>$subject_id));
        $status = $this->db->update($this->_table,array('group_id'=>0));
        if($status){
            $this->db->select('count(`id`) as num');
            return $this->db->get_where($this->_table,array('group_id'=>0,'user_id'=>$uid,'status'=>1,'subject_id'=>$subject_id))->row()->num;
        }
        else
        {
            return 0;
        }
    }

    /**
     * 判断文档是否存在
     * @param  int $doc_id 源文件ID
     * @return boolean        
     */
    public function is_exist_doc($doc_id)
    {
        $query = $this->db->get_where($this->table, array('id'=>$doc_id));
        return $query->row() ? TRUE : FALSE;
    }

    public function task_lpush_queue($doc_id,$table_flag=1)
    {
        $namespace = $table_flag == 1?'teacher_document':'cloud_user_file';
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('statistics'))   
        {
            $this->_redis=true;
        }
        if($this->_redis){
            self::get_faild_add_queue($table_flag);
            $this->db->select('id,file_path,file_ext');
            $where_arr = $table_flag == 1 ? array('id'=>$doc_id,'status'=>1,'queue_status'=>2):
            array('id'=>$doc_id,'is_del'=>0,'queue_status'=>2);
            $doc_info = $this->db->get_where($namespace,$where_arr)->row_array();
            $doc_info['type'] = $table_flag;
            $key = 'make_swf_task';
            $json = json_encode($doc_info);
            return $this->cache->redis->lpush($key,$json);
        }else{

            $this->db->where('id',$doc_id);
            $re = $this->db->update($namespace,array('queue_status'=>3));//加入队列失败
            log_message('error_tizi', 'add_queue_error:'.$doc_id);
            return $re;
        }
        //return  $this->cache->redis->rPop($key);
    } 

    private function get_faild_add_queue($table_flag=1)
    {
        $namespace = $table_flag == 1?'teacher_document':'cloud_user_file';
        $where_arr = $table_flag == 1 ? array('status'=>1,'queue_status'=>3):
            array('is_del'=>0,'queue_status'=>3);
        $this->db->select('id,file_path,file_ext');
        $faild_queue = $this->db->get_where($namespace,$where_arr)->result_array();
        if($faild_queue)
        {
            $key = 'make_swf_task';
            foreach ($faild_queue as &$value) {
                $value['type'] = $table_flag;
                $json = json_encode($value);
                $this->cache->redis->lpush($key,$json);
            }
        }
        return true;
    } 

    public function insert_swf_data($status,$insert_data,$table_flag=1)
    {
        $update_namespace = $table_flag == 1?'teacher_document':'cloud_user_file';
        $namespace = $table_flag == 1?'teacher_lesson_preview':'cloud_document_preview';
        $this->db->trans_start();
        if($status){
            //$insert_data = (array)
            $query = $this->db->insert($namespace,$insert_data);
            if($query){
                $this->db->where('id',$insert_data->doc_id);
                $re = $this->db->update($update_namespace,array('queue_status'=>1));
            }
            else $re = false;
        }else{
            $this->db->where('id',$insert_data->doc_id);
            $re = $this->db->update($update_namespace,array('queue_status'=>4));
        }

        $this->db->trans_complete();  // 事务结束
        if (FALSE === $this->db->trans_status())
        {
            log_message('error_tizi', "make_swf_error:doc_id:{$insert_data->doc_id}");
            return false;
        }

        if($re)return true;
        else return false;
        
    }

}

/* end of user_document_model.php */
