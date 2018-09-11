<?php

class Homework_Paper_Model extends MY_Model {
    function __construct(){
        parent::__construct();
    }

    /**
     * 设置学生完成作业后的答案解析方式
     * @param int $id      homework_assign的id
     * @param int $way_id  答案解析方式id
     */
    function update_get_answer_way($id, $way_id){
    	$this->db->where('id',$id);
    	$this->db->set('get_answer_way',$way_id);
    	$this->db->update('homework_assign');
    	$affected_row=$this->db->affected_rows();
        return $affected_row;
    }
    
    /**
     * 设置学生做作业的开始/截止时间
     * $param int $uid    当前user_id，为的是只能修改自己的作业
     * @param int $id      homework_assign的id
     * @param int $start_time 开始时间  optinal
     * @param int $deadline  截止时间   required
     */
    function update_homework_time($uid, $id, $start_time, $deadline){
        $this->db->where('id',$id);
        $this->db->where('user_id',$uid);
        if($start_time){
            $this->db->set('start_time',$start_time);
        }
        $this->db->set('deadline',$deadline);
        $this->db->trans_start();
    	$this->db->update('homework_assign');
        $this->db->trans_complete();  // 事务结束
        if (!$this->db->trans_status()) {
            return false;
        }
        return true;
    }

    function get_homework_name_by_id($id){
            return $this->db->query("select name from homework_paper where id = $id limit 1")->row(0)->name;    
    }
    
	/**
	 * 通过paperid获取科目id和试卷标题
	 * @param   int   $paper_id paperid
	 * @renturn array 科目id，标题
	 */
	public function getSubjetIdByPaper($paper_id){
		$paper_id = intval($paper_id);
				
		$sql = "SELECT subject_id FROM `homework_paper` WHERE id=" . $paper_id;
		$query    = $this->db->query($sql);
		$paper = $query->row();

		if(!empty($paper)){
			return $paper;
		}
		
		return false;
	}

    /*2014-07-11 tizi4.0*/
    public function getSubjetIdByTestPaperId($paper_id){
        $paper_id = intval($paper_id);
                
        $sql = "SELECT subject_id FROM `paper_testpaper` WHERE id=" . $paper_id;
        $query    = $this->db->query($sql);
        $paper = $query->row();

        if(!empty($paper)){
            return $paper;
        }
        
        return false;
    }
}

/* end of homework_paper.php */
