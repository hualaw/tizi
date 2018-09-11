<?php

class Video_Info_Model extends MY_Model {

    private $_table = 'fls_video_lesson';
    // private $_table = 'student_video';
    private $_tb_common_stage = 'common_stage';
    private $_tb_common_unit = 'common_unit';
    const HOMEPAGE_RAND_VIDEO = "homepage_rand_video";

    public function __construct(){
        parent::__construct();
    }

    
    /*视频信息，附带unit_name, stage_name */
    function get_random_video(){
        $all = $this->video_ids();
        if(!$all){
            return null;
        }
        $video_id = array_rand($all);
        $video_id = ($all[$video_id]);

        $sql = "select v.*,u.unit_name as unit_name, z.name, z.semester as semester from {$this->_table} v 
                left join {$this->_tb_common_unit} u on u.id=v.unit_id 
                left join {$this->_tb_common_stage} z on z.id = u.stage_id 
                where v.id=$video_id and v.online=1 and u.status=1";
        $result = $this->db->query($sql)->result_array();
        $res = array();
        if(isset($result[0]) and $result[0]){
            $res = $result[0];
            $res['stage'] = $result[0]['name'];
            $res['stage'] .= $result[0]['semester']==1?"上":"下";
        }
        return $res;
    }

    //课外阅读的随机视频
    function get_random_after_class(){
        $all = $this->video_ids();
        if(!$all){return null; }
        $video_id = array_rand($all);
        $video_id = ($all[$video_id]);

        $sql = "select * from {$this->_table} where id=$video_id ";
        $result = $this->db->query($sql)->result_array();
        $res = array();
        if(isset($result[0]) and $result[0]){
            $res = $result[0];
            $res['thumb_uri'] = path2video($result[0]['thumb_uri']);
            $res['stage']  = Constant::grade_video($result[0]['grade_id']);
            $title = explode('-', $result[0]['title']);
            $res['en_title']  = $title[0];
            $res['chs_title']  = $title[1];
        }
        return $res;
    }
    
    public function homepage_video(){
		$fields = "default";
		$this->load->model("redis/redis_model");
		$data = array();
    	if($this->redis_model->connect("statistics")){
			$data = $this->cache->hget(self::HOMEPAGE_RAND_VIDEO, $fields);
			if ($data){
				$data = json_decode($data, true);
			}
		}
		if (!$data or $data["last_update"] != date("Y-m-d")){
			
            $data = $this->get_random_video();
            // var_dump($data);die;
			// $data = $this->get_random_after_class();
			$data["last_update"] = date("Y-m-d");
			if($this->redis_model->connect("statistics")){
                $this->cache->hset(self::HOMEPAGE_RAND_VIDEO, $fields, json_encode($data));
            }
		}
		return $data;
	}

    function video_ids(){
        $sql = "select v.id from {$this->_table} v 
                left join {$this->_tb_common_unit} u on u.id=v.unit_id 
                left join {$this->_tb_common_stage} z on z.id = u.stage_id 
                where v.online = 1 and z.grade_type in (1) and v.id between 75 and 200 and u.status=1 order by v.id desc   ";
        $result = $this->db->query($sql)->result_array();
        $res = array();
        if($result){
            foreach ($result as $key => $val) {
                $res[] = $val['id'];
            }
        }
        return $res;
    }
    
   
}