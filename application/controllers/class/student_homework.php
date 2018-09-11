<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Student_Homework extends MY_Controller {

	private $_smarty_dir="student/class/";

	function __construct()
	{
		parent::__construct();
	}

    public function video_submit(){
        
        $this->load->model('homework/student_zuoye_model');
        $zuoye_id = $this->input->post('id', true);
        $video_id = $this->input->post('video_id', true);
        if(!$zuoye_id || !$video_id) exit('faild');
        
        $student_zuoye = $this->student_zuoye_model->get(array('zuoye_student.id'=>$zuoye_id));
        if(isset($student_zuoye[0]) && !empty($student_zuoye[0])){
            $student_zuoye = $student_zuoye[0];
        }else{
            exit('faild');
        }
        
        $zuoye_info = array();
        if(!empty($student_zuoye['zuoye_info'])){
            $zuoye_info = json_decode($student_zuoye['zuoye_info'], true);
        }
        if(!empty($student_zuoye['video_ids'])){
            $video_ids = explode(',', $student_zuoye['video_ids']);
        }else{
            exit('faild');
        }
        if(!in_array($video_id, $video_ids)) exit('faild');
        if(isset($zuoye_info['video']) && in_array($video_id, $zuoye_info['video'])){
            exit('faild');
        }else{
            $zuoye_info['video'][] = $video_id;
        }

        $game_num = (isset($student_zuoye['unit_game_ids']) && !empty($student_zuoye['unit_game_ids'])) ? count(json_decode($student_zuoye['unit_game_ids'], true)) : 0;
        $game_complete_num = (isset($zuoye_info['game']) && !empty($zuoye_info['game'])) ? count($zuoye_info['game']) : 0;
        $db_data = array('end_time'=>time(), 'zuoye_info'=>json_encode($zuoye_info));
        if(empty($student_zuoye['start_time'])){
            //$db_data['start_time'] = time();
        }
        
        $this->student_zuoye_model->update($zuoye_id, 
            $db_data
        );
        $this->student_zuoye_model->updateCompleteStatus($this->tizi_uid, $zuoye_id);
        
        exit(json_token(array('status'=>1,'msg'=>'success')));

    }





}
