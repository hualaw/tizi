<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('paper_controller.php');

class Paper_Archive extends Paper_Controller {

	public function __construct () 
    {
       	parent::__construct();
		$this->load->model('paper/paper_save_log');
        $this->load->model('user_data/teacher_data_model');
    }

    public function intro()
   	{
   		if($this->tizi_uid)
   		{
   			$this->index();
   		}
   		else
   		{
   			$this->no();
   		}
   	}

	public function index($subject_id=0)
	{
		$refresh=$this->input->get('rf');
		$data = array();
		$data['page_num']=$this->input->get('page');
		if(!$data['page_num']) $data['page_num']=1;

		$data['type']=$this->input->get('type');
		if(!$data['type']) $data['type']=0;

		$data['save_log']=array();	
		$data['save_log']=$this->paper_save_log->get_save_logs($this->tizi_uid,$data['page_num'],$data['type']);
		$data['save_log_count']=$this->paper_save_log->get_save_logs($this->tizi_uid,0,$data['type'],true);	

		if($data['save_log_count']||$refresh)
		{
	        if($this->tizi_utype == Constant::USER_TYPE_TEACHER) $download_default=$this->teacher_data_model->get_teacher_data($this->tizi_uid);
	        $data['download_default']['card']=isset($download_default->card_download_default)?json_decode($download_default->card_download_default):null;
	        $data['download_default']['paper']=isset($download_default->paper_download_default)?json_decode($download_default->paper_download_default):null;

			$this->smarty->assign('data',$data);
			$this->smarty->assign('pages',$this->get_pagination($data['page_num'],$data['save_log_count'],'archive_page'));
	        
			if($refresh)
			{
				$json['html']=$this->smarty->fetch($this->_smarty_dir.'paper_archive_tpl.html');
				$json['errorcode']=true;
				echo json_token($json);
				exit();
			}
			else
			{
				$this->smarty->display($this->_smarty_dir.'paper_archive.html');
			}
		}
		else
		{
			$this->no();
		}
	}

    public function save()
	{
        $subject_id = $this->input->post('subject_id',true);
        $save_as = $this->input->post('save_as',true);
        $title = $this->input->post('title',true,true);
        if(!$this->question_subject_model->check_subject($subject_id,'paper')) $subject_id=Constant::DEFAULT_SUBJECT_ID;
        $paper_id = $this->get_paper_id($subject_id, $this->tizi_uid);
		//需要检测试卷是否为空
		$count=$this->paper_question_model->count_questions($paper_id);
		if(!$count) 
		{
			echo json_token(array('errorcode'=>false,'error'=>$this->lang->line("error_paper_no_question"),'status'=>'1'));
			exit; 	
		}
        $paper_save=$this->paper_model->save_paper($paper_id,$this->tizi_uid,$title,false,true,Constant::LOCK_TYPE_ARCHIVE,$save_as);
		if(!empty($paper_save))
		{
			$errorcode=true;
			$error=$this->lang->line("success_save_paper");
			$save_log_id=$paper_save['save_log_id'];
			
			$this->load->library("credit");
			$this->credit->exec($this->tizi_uid, "paper_firstsave", $this->tizi_cert);
			
			$this->load->library("task");
			$this->task->exec($this->tizi_uid, "archive_paper");
		}
		else
		{
			$errorcode=false;
            $error=$this->lang->line("error_save_paper");
            $save_log_id=0;
		}
		echo json_token(array('errorcode'=>$errorcode,'error'=>$error,'save_log_id'=>$save_log_id,'status'=>'0'));exit;	
    }

	public function recover_paper($save_log_id)
	{
		if(!$save_log_id) $save_log_id=$this->input->get('slig');
		$save_log=$this->paper_save_log->get_save_log($save_log_id,$this->tizi_uid);
		$paper_id=isset($save_log->paper_id)?$save_log->paper_id:0;
		if($paper_id) $this->paper_model->recover_save_paper($paper_id,$this->tizi_uid);	
		$subject_id=$this->paper_model->get_subject_id_by_paper_id($paper_id);
		redirect('teacher/paper/preview/'.$subject_id);
	}

	public function delete_save_log()
	{
		$save_log_id=$this->input->post('slid',true);
		$errorcode=$this->paper_save_log->delete_save_log($save_log_id,$this->tizi_uid);	
		if($errorcode)
        {
            $errorcode=true;
            $error=$this->lang->line('success_delete_log');
        }
        else
        {
            $errorcode=false;
            $error=$this->lang->line('error_delete_log');
        }
        echo json_token(array('errorcode'=>$errorcode,'error'=>$error));
        exit();
	}

	public function no()
	{
		$this->smarty->display($this->_smarty_dir.'no.html',"juanzi");
	}

}
