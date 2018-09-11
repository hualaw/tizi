<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Res_Dir_Model extends MY_Model {
    private $_table = 'cloud_user_directory';
    private $_redis=false;
    public function __construct(){
        parent::__construct();
    }

    /* 同步 and 知识点文件夹*/
    function get_res_dir($user_id , $page=1,$pagesize=10){
        $param['user_id'] = intval($user_id);
        if(!$param['user_id']){
            return false;
        }
        $page = $page>=1?$page:1;
        $pagesize = $pagesize>=1?$pagesize:10;
        $start = ($page-1)*$pagesize;
        $param['is_del'] = 0;
        $this->db->select(" * ");
        $this->db->where($param);
        $this->db->where('cat_id > ',0);
        $query=$this->db->get($this->_table,$pagesize,$start);//echo $this->db->last_query();die;
        return $query->result_array();
    }

    /*根据自定义条件获取指定字段*/
    function get_index($select,$where){
        if(!$select)return null;
        $this->db->select(" $select ");
        $this->db->where($where);
        $query=$this->db->get($this->_table); //  echo $this->db->last_query();die;
        return $query->row(0)->$select;
    }

    /* 通过sub_cat_id（此subcatid下必须已经有上传的文件）获取dir相关信息 */
    function get_dir_name_by_subid($user_id,$sub_cat_id){
        $sql = "select dir.dir_name as n,dir.cat_id as cat_id, dir.dir_id from  cloud_user_file f left join cloud_user_directory dir on dir.cat_id = f.dir_cat_id where dir.user_id=? and f.sub_cat_id=? and f.is_del=0 limit 1";
        $info = $this->db->query($sql,array($user_id,$sub_cat_id))->row(0);
        return $info;
    }

    /*检查有没有这个cat_id cat_type user_id的文件夹的存在，有就返回false；*/
    function check_res_dir_exist($param){
        $data['user_id'] = $param['user_id'];
        $data['cat_id'] = $param['cat_id'];
        $data['is_del'] = 0;
        $this->db->select("count(*) as num ");
        $this->db->where($data);
        $query=$this->db->get($this->_table);   
        return $query->row(0)->num;
    }

    /*同步/知识点文件夹被删除时，清空redis中的这个字段*/
    function del_cat_in_redis($user_id,$dir_cat_id){
        if($this->redis_model->connect('cloud_statistics')){
            $this->_redis = true;
        }
        if($this->_redis){
            $key = 'user_res_'.$user_id.'_'.$dir_cat_id;
            $count = $this->cache->del($key);
            return $count;
        }
    }

 
    /*创建过多少个资源库文件夹*/
    function count_res_dir($user_id){
        $param['user_id'] = intval($user_id);
        if(!$param['user_id']){
            return false;
        }
        $param['is_del'] = 0;
        $this->db->select("count(*) as num ");
        $this->db->where($param);
        $this->db->where('cat_id > ',0);
        $query=$this->db->get($this->_table);   
        return $query->row(0)->num;
    }
}