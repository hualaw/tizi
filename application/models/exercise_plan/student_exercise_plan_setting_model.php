<?php
/**
 * @description 学生 寒假作业计划 ，设置地区和学段
 */
class Student_Exercise_Plan_Setting_Model extends MY_Model{
    private $_table= '';
    public function __construct(){
        $this->_table = "student_exercise_plan_setting";
        parent::__construct();
    }
    
    /**
     * @info 获取某个学生的设置
     */
    public function get_one_setting($uid){
        $uid = intval($uid);
        if(!$uid){
            return null;
        }
        $sql = "select * from $this->_table where user_id=$uid limit 1";
        $res = $this->db->query($sql);
        if($res){
            return $res->row(0);
        }
        return null;
    }

    /**
     * @info 设置某个学生: 初三和高三的二次划分字段是'地区'；其他是'教材版本'
     */
    public function set_info($uid,$grade_id,$other){
        $grade_id = intval($grade_id);
        $other = intval($other);
        if(!$grade_id || !$other){
            return false;
        }
        $index = '';
        if(3==$grade_id || 6==$grade_id){ //升学考试的，就插入地区
            $index = 'area_id'; 
        }else{ //非升学考试的，插入教材版本
            $index = "category_version";
        }
        if(!$index){
            return false;
        }
        $sql = "insert into $this->_table(id,uid,grade_id,$index) values(0,$uid,$grade_id,$other)";
        return $this->db->query($sql);
    }

    //修改 设置 (如果原来已经选了 某个地区的学习计划，那么修改了地区后，原来的计划怎么办？)
    public function update_info($uid,$grade_id,$other){
        $grade_id = intval($grade_id);
        $other = intval($other);
        if(!$grade_id || !$other){
            return false;
        }
        $index = '';
        if(3==$grade_id || 6==$grade_id){ //升学考试的，就插入地区
            $index = 'area_id'; 
        }else{ //非升学考试的，插入教材版本
            $index = "category_version";
        }
        if(!$index){
            return false;
        }
        $sql = "update $this->_table set $index = $other and grade_id = $grade_id where uid = $uid";
        return $this->db->query($sql);
    }

    /**
     * @param uid
     * @param data
     */
    public function set_plan_info($uid,$data){
        $plan_info = $this->get_one_setting($uid);
        if(empty($data))return false;
        if(!$plan_info){
            $fields = implode(",",array_keys($data));
            $values = implode(",",array_values($data));
            $sql = "insert into $this->_table(user_id,".$fields.") values($uid,$values)";
        }else{
            $ext_group = array();
            foreach($data as $key=>$val){
                $ext_group[] = "`".$key."` = '".$val."'";
            }
            $sql = "update ".$this->_table." set ".implode(" , ",$ext_group)." where `user_id` = {$uid}";
        }
        return $this->db->query($sql);
        
    }






}
