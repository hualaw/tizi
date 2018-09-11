<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lesson_Mine_Model extends MY_Model {
    private $_table = 'cloud_user_file';
    // private $_redis=false;
    public function __construct(){
        parent::__construct();
        // $this->load->model("redis/redis_model");
    }

    //批量删除我的备课文件，$ids是数组
    function del_my_file($uid,$ids){       
        $index = 'id';
        $data = array('is_del'=>1);
        $this->db->where_in($index, $ids);
        $this->db->where('user_id', $uid);
        $this->db->where('is_del', 0);
         
        $data['del_time'] = time();
         
        $re_value =  $this->db->update($this->_table, $data); 
        return $re_value;
    }

    //检测文件在某个目录下，名字是否重复
    function check_duplicate_name($uid,$sub_cat_id,$input_name,$ext,$file_id=0){
     
        $select = 'file_name';
        $dir_index = 'dir_id';
        $ext_sql = " and file_ext='$ext' ";
        $cat = " and sub_cat_id = $sub_cat_id ";
     
        $sql="select count(1) as num from $this->_table where user_id=?$cat and is_del=0 and $select=?$ext_sql";
        $sql_arr = array($uid,$input_name);
        $num = $this->db->query($sql,$sql_arr)->row(0)->num;
        // echo $this->db->last_query();die;
        if(!$num){//不存在就返回当前名字
            return $input_name;
        }
        if($num == 1 and $file_id){//如果库中只有一个名字与之重复，看看这个文件是不是本身
            $sql="select id as fid from $this->_table where user_id=?$cat and is_del=0 and $select=?$ext_sql";
            $sql_arr = array($uid,$input_name);
            $fid = $this->db->query($sql,$sql_arr)->row(0)->fid;
            if($fid == $file_id){
                return $input_name;//是本身就直接返回这个名字
            }
        }
        $tmp_name = addslashes($input_name);
        $sql = "select $select from $this->_table where user_id=$uid $cat and is_del=0 $ext_sql and $select REGEXP '^$tmp_name\\\(?[0-9]*\\\)?$' order by $select ";
        $res = $this->db->query($sql)->result_array();
        if(!$res){
            return $input_name."(1)";
        }
        foreach($res as $key=>$val){
            $temp = explode($input_name.'(', $val[$select]);
            if(isset($temp[1])){
                $temp = rtrim($temp[1],')');
                $new[] = $temp;
            }
        }
        if(!isset($new) || $new[0]>1){
            return $input_name."(1)";
        }
        $count = count($new);
        for($i=0;$i<$count;$i++){
            $_tmp = $new[$i]+1;
            if(!in_array($_tmp,$new)){
                return $input_name."($_tmp)";
            }
        }
        return $input_name."($_tmp)";
    }
    
}