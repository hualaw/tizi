<?php

class Document_Favorite_Model extends MY_Model {

	private $_table_favorite='lesson_favorite';
    private $_table_lesson='lesson_document';
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 新建收藏
     */
    public function insert($insert_data)
    {
        $this->db->insert($this->_table_favorite,$insert_data);
        return $this->db->insert_id();
    }

    /**
     * 删除收藏记录
     */
    public function delete($user_id,$doc_id)
    {
        $this->db->where('user_id',$user_id);
        $this->db->where('doc_id',$doc_id);
        return $this->db->update($this->_table_favorite,array('is_del'=>1));
    }

    /**
     * 查询是否文档已被用户收藏
     */
    public function is_favorite_exist($user_id,$doc_id)
    {
        $query = $this->db->get_where($this->_table_favorite,array('user_id'=>$user_id,'doc_id'=>$doc_id));
        return $query->row();
    }

    /**
     * 更新收藏记录
     */
    public function update($user_id,$doc_id,$update_data)
    {
        $this->db->where(array('user_id'=>$user_id,'doc_id'=>$doc_id));
        return $this->db->update($this->_table_favorite,$update_data);
    }

    /**
     * 查询收藏文档的数据(带分页)
     */
    public function get_data($user_id,$dir_cat_id=0,$sub_cat_id=0,$page_num=1,$total=false)
    {
        $this->db->where($this->_table_favorite.'.is_del',0);
        $this->db->where($this->_table_favorite.'.user_id',$user_id);
        if($dir_cat_id>0)$this->db->where($this->_table_favorite.'.dir_cat_id',$dir_cat_id);
        if($sub_cat_id>0)$this->db->where($this->_table_favorite.'.sub_cat_id',$sub_cat_id);
        $this->db->join($this->_table_lesson,$this->_table_lesson.'.id='.$this->_table_favorite.'.doc_id','left');

        if($total){
            $this->db->select('count( DISTINCT '.$this->_table_lesson.'.id) as total');
            $query=$this->db->get($this->_table_favorite);
            // echo $this->db->last_query();die;
            $doc_count=isset($query->row()->total)?$query->row()->total:0;
            return $doc_count;
        }else{
            $this->db->select($this->_table_lesson.'.*');
            $this->db->select($this->_table_favorite.'.id as fav_id');
            $this->db->select($this->_table_favorite.'.dir_cat_id');
            $this->db->select($this->_table_favorite.'.sub_cat_id');
            $this->db->select($this->_table_favorite.'.res_type');
            $this->db->select($this->_table_favorite.'.add_time');
            $limit=Constant::RES_LIST_PAGESIZE;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            $this->db->order_by($this->_table_favorite.'.add_time','desc');
            $this->db->group_by($this->_table_lesson.'.id');
            $this->db->limit($limit,$offset);
            $query=$this->db->get($this->_table_favorite);
            return $query->result();
        }
    }

}

/* end of document_favorite_model.php */
