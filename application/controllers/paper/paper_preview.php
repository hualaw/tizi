<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('paper_controller.php');

class Paper_Preview extends Paper_Controller {
	
	public function __construct()
   	{
    	parent::__construct();
		$this->load->model('paper/paper_question_type_model');
		$this->load->model('paper/paper_section_model');
		$this->load->model('paper/paper_save_log');
        $this->load->model('question/question_category_model');
    }
	
	public function index($subject_id=0)
    {
        $data['subject_id']=$this->input->get('sid');

        $data=$this->get_subject($data,$subject_id,true);
        
        $paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);

        //get paper
        $data['paper']=$this->get_paper($paper_id);

        $data['save_log']=array();
        if($data['paper']['paper_config']->is_recovery)
        {
        	$data['save_log']=$this->paper_save_log->get_save_log_by_paper_id($data['paper']['paper_config']->is_recovery,$this->tizi_uid);      	
        }

        $this->smarty->assign('sj_url',site_url().'teacher/paper/preview');
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'paper_preview.html');		
   	}

	//move question
	public function move_question()
	{
		$paper_question_id=$this->input->post('pqid',true);
		$new_paper_question_type=$this->input->post('qtype',true);
		$subject_id=$this->input->post('sid',true);
	
		$error=$this->lang->line("default_error");

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);	

		$errorcode=false;
		if($paper_id&&$paper_question_id&&$new_paper_question_type)
		{
			$errorcode=$this->paper_question_model->change_paper_question_type($paper_id,$paper_question_id,$new_paper_question_type);
			if(!$errorcode) $error=$this->lang->line("error_save_qtype");
		}
		if($errorcode) $this->save_question_order();
		else echo json_token(array('errorcode'=>$errorcode,'error'=>$error));
		exit();
	}

	//save paper question order to table paper_question_type
	public function save_question_order()
	{
		$subject_id=$this->input->post('sid',true);
		$paper_question_order=$this->input->post('qorder',true);	
		$paper_question_type=$this->input->post('qtype',true);

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
		if($paper_id)
		{	
			$paper['errorcode']=$this->paper_question_type_model->save_question_order($paper_id,$paper_question_type,$paper_question_order);
			if(!$paper['errorcode']) $paper['error']=$this->lang->line("error_save_order");
			$paper['question_cart']=$this->get_paper_question_cart($paper_id,true);
			if(empty($paper['question_cart']))
            {
                $paper['errorcode']=false;
                if(!isset($paper['error'])) $paper['error']=$this->lang->line("error_get_cart");
            }
		}
		echo json_token($paper);	
		exit();
	}

	//save paper question type order to table section
	public function save_question_type_order()
	{
		$subject_id=$this->input->post('sid',true);
		$paper_question_type_order=$this->input->post('qtorder',true);
        $section_type=$this->input->post('sectiontype',true);

		$error='';

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
		if($paper_id)
		{
			$section_id=$this->paper_section_model->get_section_by_type($paper_id,$section_type);
			if($section_id)
			{
				$errorcode=$this->paper_section_model->save_question_type_order($paper_id,$section_id,$paper_question_type_order);
				if(!$errorcode)
				{
					$errorcode=false;
					$error=$this->lang->line("error_save_torder");
				}
			}	
			else 
			{
				$errorcode=false;
				$error=$this->lang->line("error_save_torder");
			}
		}
		else
		{
			$errorcode=false;
			$error=$this->lang->line("error_save_torder");
		}
        
		echo json_token(array('errorcode'=>$errorcode,'error'=>$error));
		exit();
	}

	//delete question type
	public function delete_question_type()
	{
		$subject_id=$this->input->post('sid',true);
        $paper_question_type_id=$this->input->post('qtype',true);

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);		
		if($paper_id)
		{
			$paper['errorcode']=$this->paper_question_type_model->delete_question_type($paper_id,$paper_question_type_id);
			if(!$paper['errorcode']) $paper['error']=$this->lang->line("error_delete_qtype");
			//get question cart
            $paper['question_cart']=$this->get_paper_question_cart($paper_id,true);
			if(empty($paper['question_cart']))
            {
                $paper['errorcode']=false;
               	if(!isset($paper['error'])) $paper['error']=$this->lang->line("error_get_cart");
            }
		}
		else
		{
			$paper['errorcode']=false;
			$paper['error']=$this->lang->line("error_delete_qtype");
		}
		echo json_token($paper);
		exit();
	}

	//save question style
	private function save_paper_style()
	{
		$subject_id=$this->input->get('sid');
        $style=$this->input->get('style');

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);	
		
		$errorcode=false;
		if($paper_id&&$style)
		{
			$errorcode=$this->paper_model->save_paper_style($paper_id,$style);
		}

		echo json_token(array('errorcode'=>$errorcode));
		exit();
	}

	//save paper config
	public function save_paper_config()
	{
		$subject_id=$this->input->post('sid',true);
		$config=json_decode($this->input->post('config',true),true);
		$style=$this->input->post('type',true);

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);

		$paper_config=array();
		$section_config=array();
		$question_type_config=array();
		
		if(is_array($config)&&!empty($config))
		{
			foreach($config as $key=>$c)
			{
				if(strpos($key,'question-type'))
				{
					$question_type_config[$c['id']]=$c;
					unset($question_type_config[$c['id']]['id']);
					$question_type_config[$c['id']]['name']=strip_tags($c['title']);
	                unset($question_type_config[$c['id']]['title']);
	                $question_type_config[$c['id']]['note']=$c['content'];
	                unset($question_type_config[$c['id']]['content']);
	                $question_type_config[$c['id']]['is_show_question_type']=$c['ischecked'];
	                unset($question_type_config[$c['id']]['ischecked']);
					$question_type_config[$c['id']]['is_show_performance']=$c['ischeckedscore'];
	                unset($question_type_config[$c['id']]['ischeckedscore']);
				}
				else if(strpos($key,'paper-type'))
				{
					$section_config[$c['type']]=$c;
					$section_config[$c['type']]['label']=strip_tags($c['title']);
					unset($section_config[$c['type']]['title']);
					$section_config[$c['type']]['note']=$c['content'];
	                unset($section_config[$c['type']]['content']);
					$section_config[$c['type']]['is_show_section_header']=$c['ischecked'];
	                unset($section_config[$c['type']]['ischecked']);
				}
				else 
				{
					if(isset($c['title'])) $paper_config[$key]=strip_tags($c['title']);
					if(isset($c['content'])) $paper_config[$key]=$c['content'];
					if(isset($c['ischecked'])) $paper_config['is_show_'.$key]=$c['ischecked'];
				}
			}	

			if($style) $paper_config['style']=$style;
		}

		if($paper_id&&!empty($paper_config))
		{
			$paper['errorcode']=$this->paper_model->save_paper_config($paper_id, $paper_config, $section_config, $question_type_config);		
			if(!$paper['errorcode']) $paper['error']=$this->lang->line("error_save_config");
			//get question cart
            $paper['question_cart']=$this->get_paper_question_cart($paper_id,true);
			if(empty($paper['question_cart']))
            {
                $paper['errorcode']=false;
                if(!isset($paper['error'])) $paper['error']=$this->lang->line("error_get_cart");
            }	
			echo json_token($paper);
		}
		else
		{
			echo json_token(array('errorcode'=>false,'error'=>$this->lang->line("error_save_config")));
		}
		exit();	
	}
		
}	
/* End of file paper.php */
/* Location: ./application/controllers/paper/paper.php */

