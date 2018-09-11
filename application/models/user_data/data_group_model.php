<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_Group_Model extends MY_Model {

	private $_table = 'teacher_data_group';
    private $_redis=false;
    public function __construct(){
        parent::__construct();
    }

    public function insert_group($insert_data)
    {   
    	if(!is_array($insert_data) || empty($insert_data)){
    		return -2;
    	}
        $this->db->select("COUNT(`id`) AS num");
        $group_num = $this->db->get_where($this->_table,array(
            'user_id'=>$insert_data['user_id'],
            'subject_id'=>$insert_data['subject_id'],
            'status'=>0,
            'type'=>$insert_data['type']))->row()->num;
        if($group_num <= Constant::QUESTION_GROUP_MAX_NUM){
            $this->db->insert($this->_table,$insert_data);
            $new_id = $this->db->insert_id();
            if($new_id) return $new_id;
            else        return -1;
        }else{
            return -3;
        }
    	
    }

    public function group_data_statistics($subject_id,$user_id,$group_id,$type='document',$op_type='get',$status_where=false)
    {
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('teacher_data_group'))   
        {
            $this->_redis=true;
        }
        $key = $type.'_'.md5($user_id.'_'.$subject_id.'_'.$group_id);
        $null_group_key = $type.'_'.md5($user_id.'_'.$subject_id.'_'.'0');
        if($type == 'document' and $status_where){
            $key = 'lesson_'.$key;
        }
        $value='';
        if($this->_redis&&$op_type == 'delete')
        {
            if($type == 'document'){
                $this->cache->delete('lesson_'.$key);
                $this->cache->delete('lesson_'.$null_group_key);
            }
            $status=$this->cache->delete($null_group_key);//删除未分组缓存
            $status=$this->cache->delete($key);
            return $status;
        }
        if($this->_redis)
        {
            $value=$this->cache->get($key);
            if($value) return $value;
        }
        if(false==$value)
        {   
            $select_table = $type=='document'?'teacher_document':'teacher_question';
            if($select_table=='teacher_document'){
                $where = array(
                'subject_id'=>$subject_id,
                'status'=>1,
                'user_id'=>$user_id,
                'group_id'=>$group_id
                );
            }else{
                $where = array(
                'subject_id'=>$subject_id,
                'status'=>0,
                'user_id'=>$user_id,
                'group_id'=>$group_id
                );
            }
            if($status_where and $type=='document' and $op_type=='get'){
                $this->db->where_in($select_table.'.queue_status',array(1,4));
            }
            $this->db->where($where);
            $this->db->select('count(`id`) as total');
            $query=$this->db->get($select_table);
            $value=isset($query->row()->total)?$query->row()->total:0;
            if($this->_redis)
            {
                $expire=Constant::REDIS_GROUP_DATA_TIMEOUT;
                $this->cache->save($key, $value, $expire);
            }
            return $value;
        }       
    }

    public function update($group_id,$user_id,$update_data)
    {
    	if(!is_array($update_data) || empty($update_data)){
    		return false;
    	}
    	$this->db->where(array('id'=>$group_id,'user_id'=>$user_id));
    	$query = $this->db->update($this->_table,$update_data);
    	return $query;
    }

    public function get_list($subject_id,$user_id,$group_type=Constant::QUESTION_GROUP)
    {
        $where_arr = array('user_id'=>$user_id,
            'subject_id'=>$subject_id,
            'type'=>$group_type,
            'status'=>0
            );
        if(empty($subject_id)) unset($where_arr['subject_id']);
        $this->db->order_by('id','desc');
    	$query = $this->db->get_where($this->_table,$where_arr);
    	return $query->result();
    }

    public function get_single_group($user_id,$group_id)
    {
        $this->db->select("id,name,subject_id");
        return $this->db->get_where($this->_table,array('user_id'=>$user_id,'id'=>$group_id,'status'=>0))->row();
    }

}
/* end of data_group_model.php */
