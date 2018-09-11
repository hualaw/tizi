<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('paper_controller.php');

class Paper_Question extends Paper_Controller {
	
	public function __construct()
    {
        parent::__construct();
		$this->load->model('question/question_category_model');
		$this->load->model("question/question_level_model");
		$this->load->model("question/question_type_model");
		$this->load->model("question/question_model");
    }

    public function index($subject_id=0,$category_select=0,$page_mode='question',$reset=false)
    {
        //$paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);
        //if($reset) $this->erase_paper($paper_id,0,true,true);

		//http get and params initalize
        $data['subject_id']=$this->input->get('sid');
        $data['category_select']=$this->input->get("cselect");
        $data['qtype']=$this->input->get('qtype');
        $data['qlevel']=$this->input->get('qlevel');
        $data['node_select']=$this->input->get("nselect");

        $data=$this->get_subject($data,$subject_id);

        if(!$data['category_select']) $data['category_select']=$category_select;
        if($data['qtype']=="") $data['qtype']=0;
        if($data['qlevel']=="") $data['qlevel']=0;

        $template=$this->_smarty_dir.'paper_question.html';
        $cache_id="paper_".$page_mode."_s".$data['subject_id']."_c".$data['category_select']."_t".$data['qtype']."_l".$data['qlevel'];
        if(!$this->smarty->isCached($template,$cache_id))
        {
            //get question type
            $question_type_all[0]=(object)array('id'=>0,'name'=>"全部题型");
            $data['question_type']=$this->question_type_model->get_subject_question_type($data['subject_id'],false,false);
            $data['question_type']=array_merge($question_type_all,$data['question_type']);

            //get question level
            $question_level_all[0]=(object)array('id'=>0,'name'=>"全部难度",'level'=>0);
            $data['question_level']=$this->question_level_model->get_question_level_names($data['subject_id']);
            $data['question_level']=array_merge($question_level_all,$data['question_level']);

            //get category depth 1
            $data['category_root_id']=$this->question_category_model->get_root_id($data['subject_id']);

    		foreach($data['category_root_id'] as $key=>$c_r_id)
            {
                if($c_r_id->category_type!=0)//网盘目录
                {
                    unset($data['category_root_id'][$key]);
                }
                else if($page_mode=='course'&&$c_r_id->type)//NULL是同步教材
                {
                    unset($data['category_root_id'][$key]);
                }
                else if($page_mode=='question'&&!$c_r_id->type)//1,2知识点
                {
                    unset($data['category_root_id'][$key]);
                }
                //else if($page_mode=='cequestion'&&$c_r_id->type!=2)//2是冲刺
                //{
                //    unset($data['category_root_id'][$key]);
                //}
                if($data['category_select']&&$data['category_select']==$c_r_id->id)
                {
                    $data['category_root_name']=$c_r_id->name;
                }
            }

            if(!empty($data['category_root_id']))
            {
                $data['category_root_id']=array_values($data['category_root_id']);
                if(!$data['category_select']) $data['category_select']=$data['category_root_id'][0]->id;

                if(!isset($data['category_root_name']))
                {
                    $data['category_root_name']=$data['category_root_id'][0]->name;
                }	
            }

            $data['category_second_root_id']=array();
    	    $data['category_second_root_name']='';

            if($page_mode == 'course' && !empty($data['category_root_id']))
            {
    	        //get category depth 2
    	        if($data['category_select'])
    	        {
    	            $select_parent_id=$this->question_category_model->get_parent_id($data['category_select']);
    	            $data['category_root_select']=$select_parent_id?$select_parent_id:$data['category_select'];
    	        }	
    			else
    			{
    				$data['category_select']=$data['category_root_select']=$data['category_root_id'][0]->id;
    			}

        		$data['category_second_root_id']=$this->question_category_model->get_subtree_node($data['category_root_select']);
        		
                if(!empty($data['category_second_root_id']))
                {
                    if($data['category_select']==$data['category_root_select']) $data['category_select']=$data['category_second_root_id'][0]->id;

            		foreach($data['category_second_root_id'] as $key=>$c_s_r_id)
                    {
                        $data['category_second_root_id'][$key]->name=$c_s_r_id->name;
                        if($data['category_select']==$c_s_r_id->id)
                        {
                            $data['category_second_root_name']=$c_s_r_id->name;
                        }
                    }
            		if(!isset($data['category_second_root_name']))
                    {
                        $data['category_second_root_name']=$data['category_second_root_id'][0]->name;
                    }
                }
    	    }
    	    else
    	    {
            	$data['category_root_select']=$data['category_select'];
    	    }

            $data['category']=$this->get_category_list($data['category_select']);

            $data['pagemode']=$page_mode;
            
            // if($page_mode == 'cequestion')
            // {
            //     if($subject_id<=9) $exam_start = "2014-6-20";
            //     else $exam_start = "2014-6-6";
            //     $data['countdown']=intval(floor((float)(strtotime($exam_start)-strtotime(date("Y-m-d")))/86400));
            //     if($data['countdown'] < 0) $data['countdown']=0;
            // }

            $this->smarty->assign('sj_url',site_url().'teacher/paper/'.$data['pagemode']);
            $this->smarty->assign('data',$data);
        }
        $this->smarty->display($template,$cache_id);
    }
	
    public function course($subject_id=0,$category_select=0)
    {
        $this->index($subject_id,$category_select,'course');
    }

    public function college_exam($subject_id=0,$category_select=0)
    {
        $this->index($subject_id,$category_select,'question');
    }

    public function reset()
    {
        /*
        $paper_ids=$this->paper_model->get_unsaved_paper_id_by_user_id($this->tizi_uid);
        foreach($paper_ids as $paper_id)
        {
            $this->erase_paper($paper_id->id,0,true,true);
        }
        */
        $this->index(0,0,'question');
    }

	/*desc:ajax get question list and pagination*/
	public function get_question()
	{
		//http get
        $question_type=$this->input->get("qtype");
        $question_level=$this->input->get("qlevel");
        $node_select=$this->input->get("nselect");      
        $subject_id=$this->input->get("sid");
        $page_select=$this->input->get("page");
        if(!$page_select) $page_select=1;

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
        $question=parent::get_question($paper_id,$subject_id,$node_select,$page_select,$question_type,$question_level);
        if($question['errorcode'])
        {
            $data['total']=$question['pagination']['question_total'];
            $data['page_num']=$question['pagination']['page'];
            $data['question']=$question['question'];
            $data['question_count']=$question['question_count'];
            $data['category_id']=$node_select;
            $this->smarty->assign('pages',$this->get_pagination($data['page_num'],$data['total'],'Teacher.paper.paper_question.page'));
            $this->smarty->assign('data',$data);
            $question['html']=$this->smarty->fetch($this->_smarty_dir.'paper_question_tpl.html');
        }
        echo json_token($question);
        exit();	
	}
	/*desc:ajax get sub category*/
	public function get_category()
	{
		$category_node_select=$this->input->get("cnselect");
        $category_node_list=parent::get_category($category_node_select);
        echo json_token($category_node_list);
        exit();
	}

    public function get_category_list($category_node_select)
    {
        $category_node_list=parent::get_category_list($category_node_select);
        return $category_node_list;
    }

	/*desc:ajax add question to paper*/
	public function add_question_to_paper()
	{
		$question_id=$this->input->post('qid',true);
        $subject_id=$this->input->post('sid',true);
        $question_origin=$this->input->post('qorigin',true);
        $category_id=$this->input->post('category_id',true);
        $course_id=$this->input->post('course_id',true);

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
        $paper=parent::add_question_to_paper($paper_id,$question_id,$question_origin,$category_id,$course_id,true);
        echo json_token($paper);
        exit();	
	}	

    /*desc:ajax add question to paper*/
    public function add_questions_to_paper()
    {
        $question_ids=$this->input->post('qids',true);
        $subject_id=$this->input->post('sid',true);
        $question_origin=$this->input->post('qorigin',true);
        
        if(!empty($question_ids))
        {
            $question_add=0;
            $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
            foreach($question_ids as $question_id)
            {
                $paper=parent::add_question_to_paper($paper_id,$question_id['id'],$question_origin,$question_id['category_id'],$question_id['course_id'],true);
                if($paper['errorcode']) $question_add++;
            }
            if($question_add != count($question_ids)) 
            {
                $paper['errorcode']=false;
                if(!$paper['error']) $paper['error']=$this->lang->line("error_add_questions");
            }
        }
        else
        {
            $paper=array('errorcode'=>false,'error'=>$this->lang->line("error_add_questions"));
        }
        echo json_token($paper);
        exit();
    }

	/*desc:ajax remove question from paper*/
    public function remove_question_from_paper()
   	{
		$question_id=$this->input->post('qid',true);
        $subject_id=$this->input->post('sid',true);
        $question_origin=$this->input->post('qorigin',true);

        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
        $paper=parent::remove_question_from_paper($paper_id,$question_id,$question_origin,true);
        echo json_token($paper);
        exit();       
	}	
	
	/*remove questions form question cart with question type*/
	public function remove_question_from_cart()
	{
		$question_type=$this->input->post('qtype',true);
        $subject_id=$this->input->post('sid',true);
		
		if($question_type==0) $erase_all=true;
		else $erase_all=false;
		
        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
		$question=parent::remove_question_from_cart($paper_id,$question_type,$erase_all,true);
		echo json_token($question);
		exit();
	}
	
	/*reset paper*/
    public function reset_paper()
    {
        $subject_id=$this->input->post('sid',true);

		if(!$this->question_subject_model->check_subject($subject_id,'paper'))
		{
			$errorcode=false;
			$error=$this->lang->line("error_reset_paper");
		}
		else
		{
        	$paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
            $paper=$this->paper_model->set_paper_is_saved($paper_id,$this->tizi_uid);
			if($paper)
			{
				$errorcode=true;
				$error=$this->lang->line("success_reset_paper");
			}
			else 
			{
				$errorcode=false;
				$error=$this->lang->line("error_reset_paper");
			}
		}
		echo json_token(array('errorcode'=>$errorcode,'error'=>$error));
        exit();
    }

	/*get question cart*/
	public function get_question_cart()
	{
		$subject_id=$this->input->get('sid');
        if(!$this->question_subject_model->check_subject($subject_id,'paper')) $subject_id=Constant::DEFAULT_SUBJECT_ID;
			
        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
        $question['question_cart']=$this->get_paper_question_cart($paper_id,true);
		if(empty($question['question_cart']))
        {
            $question['errorcode']=false;
            $question['error']=$this->lang->line("error_get_cart");
        }
        else
        {
            $question['errorcode']=true;
        }
        echo json_token($question);
        exit();	
	}
	
    public function change_question()
    {
        $question_ids=$this->input->post('qids');
        $question_id=$this->input->post('qid');
        $category_id=$this->input->post("cid");       
        $level=$this->input->post("qlevel");
        $question_type_id=$this->input->post("qtype");
        $paper_question_type_id=$this->input->post("pqtype");
        $quesetion_index=$this->input->post("qindex");
        $subject_id=$this->input->post('sid');

        $difficult=round($level*0.2,2);

        $questions=$this->get_intelligent_question($difficult,array($category_id),array($question_type_id=>1),$question_ids);

        if($questions['errorcode']&&!empty($questions['question']))
        {
            $data['question']=$questions['question'][0];
            $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);

            $paper_del=parent::remove_question_from_paper($paper_id,$question_id,0,true);
            $paper_add=parent::add_question_to_paper($paper_id,$data['question']['id'],0,$data['question']['category_id'],0,true);
            if($paper_add['errorcode']&&$paper_del['errorcode'])
            {
                $this->paper_question_model->change_paper_question_type($paper_id,$paper_add['paper_question_id'],$paper_question_type_id);
                $data['question']['body']=path2img($data['question']['body']);
                $data['question']['answer']=path2img($data['question']['answer']);
                $data['question']['analysis']=path2img($data['question']['analysis']);
                $data['paper_question_id']=$question['pqid']=$paper_add['paper_question_id'];
                $data['qindex']=$quesetion_index;
                $question['qid']=$data['question']['id'];
                $question['qtitle']=$data['question']['title'];
                $this->smarty->assign('data',$data);
                $question['html']=$this->smarty->fetch($this->_smarty_dir.'paper_preview_question_tpl.html');
                $question['errorcode']=true;
            }
            else
            {
                $question['errorcode']=false;
                $question['error']=$this->lang->line('error_change_question');
            }
        }
        else
        {
            $question['errorcode']=false;
            $question['error']=$this->lang->line('error_wise_no_record');
        }

        echo json_token($question);
        exit;
    }
}
	
/* End of file question.php */
/* Location: ./application/controllers/paper/question.php */

