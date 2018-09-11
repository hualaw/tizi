<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Controller extends MY_Controller {

    public function __construct () 
    {
        parent::__construct();
    }

    protected function get_statistics()
    {
        $this->load->model('user_data/user_statistics_model');
        $statistics=array();
        if($this->tizi_uid && $this->tizi_utype == Constant::USER_TYPE_TEACHER)
        {
            $statistics=$this->user_statistics_model->get_teacher_statistics($this->tizi_uid);
        }elseif($this->tizi_uid && $this->tizi_utype == Constant::USER_TYPE_PARENT){
            //获取绑定的孩子信息
            $this->load->model('login/parent_model');
            $statistics['kids'] = $this->parent_model->get_kids($this->tizi_uid);
        }
        $_static = $this->user_statistics_model->get_statistics_data();
        $statistics = array_merge($statistics, $_static);
        return $statistics;
    }

    protected function get_paper_id($subject_id,$user_id,$namespace='paper',$guest=true)
    {
        $this->load->model('paper/paper_model');
        $this->load->model('question/question_subject_model');
        $cookie_name='_paper_id';
        $paper_id=0;
        if($this->question_subject_model->check_subject($subject_id,$namespace))
        {
            if($user_id)
            {
                $paper_id=$this->paper_model->get_unsaved_paper_id($subject_id,$user_id);

                if(!$guest)
                {
                    if(!$paper_id) $paper_id=$this->paper_model->init_paper_record($subject_id,$user_id);
                }
                else
                {
                    $paper_id_list=json_decode($this->input->cookie($cookie_name),true);
                    if(isset($paper_id_list[$subject_id])&&$this->paper_model->check_paper_id($paper_id_list[$subject_id]))
                    {
                        $paper_id=$paper_id_list[$subject_id];
                        $update_paper=$this->paper_model->update_paper_user_id($user_id,$paper_id);
                        if($update_paper)
                        {
                            unset($paper_id_list[$subject_id]);
                            $this->input->set_cookie($cookie_name,json_encode($paper_id_list),Constant::COOKIE_EXPIRE_TIME);
                        }
                        else
                        {
                            if(!$paper_id) $paper_id=$this->paper_model->init_paper_record($subject_id,$user_id);
                        }
                    }
                    else
                    {
                        if(!$paper_id) $paper_id=$this->paper_model->init_paper_record($subject_id,$user_id);
                    }
                }
            }
            else if($guest)
            {
                $paper_id_list=json_decode($this->input->cookie($cookie_name),true);
                if(isset($paper_id_list[$subject_id])&&$this->paper_model->check_paper_id($paper_id_list[$subject_id]))
                {
                    $paper_id=$paper_id_list[$subject_id];
                }
                else
                {
                    $paper_id=$this->paper_model->init_paper_record($subject_id,$user_id);
                    $paper_id_list=json_decode($this->input->cookie($cookie_name),true);
                    $paper_id_list[$subject_id]=$paper_id;
                    $this->input->set_cookie($cookie_name,json_encode($paper_id_list),Constant::COOKIE_EXPIRE_TIME);
                }
            }
        }
        return $paper_id;
    }

    protected function get_paper($paper_id,$with_text=true)
	{
		$this->load->model('paper/paper_model');
		$this->load->model('paper/paper_question_type_model');
		$this->load->model('paper/paper_question_model');
		$this->load->model('paper/paper_section_model');
		$this->load->model('question/question_model');
	
        $paper = array();
        $paper_question_order=array(1=>null,2=>null);
		//get paper
		$paper['paper_config']=$this->paper_model->get_paper_by_id($paper_id);
		//get section
		$section_list=$this->paper_section_model->get_sections_by_paper($paper_id);	
		foreach($section_list as $sl)
		{
			$paper['section_config'][$sl->type]=$sl;
			$section_type[$sl->id]=$sl->type;
			if($sl->question_type_order) 
			{
				$paper_question_order[$sl->type]=explode(",",$sl->question_type_order);
				foreach($paper_question_order[$sl->type] as $key=>$question_type_id)
				{	
					$paper_question_order[$sl->type][$question_type_id]=null;
					unset($paper_question_order[$sl->type][$key]);
				}
			}
		}		
		
		//get paper question type
        $question_type_list=$this->paper_question_type_model->get_paper_question_types($paper_id);
		$paper_question_type_id_list=array(1=>array(),2=>array());
        $paper['question_config']=array(1=>array(),2=>array());
        foreach($question_type_list as $qtl)
        {
			$paper_question_type_id_list[$section_type[$qtl->section_id]][]=$qtl->id;
            $paper['question_config'][$section_type[$qtl->section_id]][$qtl->id]=$qtl;
			
			if($qtl->question_order)
			{
				if($qtl->question_order) $paper_question_order[$section_type[$qtl->section_id]][$qtl->id]=explode(",",$qtl->question_order);
				foreach($paper_question_order[$section_type[$qtl->section_id]][$qtl->id] as $key=>$paper_question_id)
				{
					$paper_question_order[$section_type[$qtl->section_id]][$qtl->id][$paper_question_id]=null;
					unset($paper_question_order[$section_type[$qtl->section_id]][$qtl->id][$key]);
				}
			}
			
			if(!isset($paper_question_order[$section_type[$qtl->section_id]][$qtl->id])) $paper_question_order[$section_type[$qtl->section_id]][$qtl->id]=null;
			
        }

        // get paper question
        $paper_question_list=$this->paper_question_model->get_paper_questions($paper_id);
        $question_id_list=array();
		$paper_question_id_list=array();
		$paper['question_total']=array(1=>0,2=>0);
        $paper['question_origin']=array();
        foreach($paper_question_list as $ql)
        {
            $paper_question_id_list[]=$ql->id;
            $paper_question_order[$section_type[$ql->section_id]][$ql->qtype_id][$ql->id]=$ql->question_id;
            $paper['question_origin'][$ql->id]=$ql->question_origin;
            $paper['question_category'][$ql->id]['category']=$ql->category_id;
            $paper['question_category'][$ql->id]['course']=$ql->course_id;
            $question_id_list[$ql->question_origin][]=$ql->question_id;
            $paper['question_total'][$section_type[$ql->section_id]]++;
        }

        foreach($paper_question_order as $paper_section_type=>$paper_question_type)
        {
            if($paper_question_type)
            {
                foreach($paper_question_type as $paper_question_type_id=>$paper_question)
                {
                    if(!in_array($paper_question_type_id,$paper_question_type_id_list[$paper_section_type])) 
                    {
                        unset($paper_question_order[$paper_section_type][$paper_question_type_id]);
                    }
                    if($paper_question)
                    {
                        foreach($paper_question as $paper_question_id=>$question)
                        {
                            if(!in_array($paper_question_id,$paper_question_id_list))
                            {
                                unset($paper_question_order[$paper_section_type][$paper_question_type_id][$paper_question_id]);
                            }
							else if($question==null)
							{
								unset($paper_question_order[$paper_section_type][$paper_question_type_id][$paper_question_id]);
							}
                        }
                    }
                }
            }
        }
		//if(!isset($paper_question_order[1])) $paper_question_order[1]=null;
		//if(!isset($paper_question_order[2])) $paper_question_order[2]=null;

		$paper['paper_question']=$paper_question_order;
        $paper['question']=array(0=>null,1=>null);
		// get question
        if(!empty($question_id_list))
        {
            foreach($question_id_list as $k_list => $qid_list)
            {
        		$question_list=array();
                $question_list=$this->get_question_by_origin($k_list,$qid_list,$with_text);
                if(!empty($question_list))
        		{
        			foreach($question_list as $ql)
        			{
        				$paper['question'][$k_list][$ql->id]=$ql;
                        if($k_list == Constant::QUESTION_ORIGIN_QUESTION)
                        {
            				$this->load->helper('img_helper');
            				$paper['question'][$k_list][$ql->id]->body=path2img($ql->body);
            				$paper['question'][$k_list][$ql->id]->answer=path2img($ql->answer);
                            $paper['question'][$k_list][$ql->id]->analysis=path2img($ql->analysis);
                        }
        			}
        		}
        		else
        		{
        			$paper['question'][$k_list]=null;
        		}
            }
        }

		return $paper;
	}

    protected function get_homework_id($subject_id,$user_id)
    {
        return $this->get_paper_id($subject_id,$user_id,'homework');
    }

	protected function get_homework($paper_id,$with_text=true)
	{
		$this->load->model('exercise_plan/homework_model');
        $this->load->model('exercise_plan/homework_question_type_model');
        $this->load->model('exercise_plan/homework_question_model');
        $this->load->model('question/question_model');

        $paper = array();
		$paper['paper_config']=$this->homework_model->get_paper_by_id($paper_id);

        $paper_order=array();
        $paper_order_list=array();
        if($paper['paper_config']->question_order)
        {
            $paper_order=explode(',',$paper['paper_config']->question_order);
            foreach($paper_order as $value)
            {
                $paper_order_list[$value]=0;
            }
        }

		//get paper question type
        $question_type_list=$this->homework_question_type_model->get_paper_question_types($paper_id);
		$paper_question_type_id_list=array(1=>array(),2=>array());
        $paper['question_config']=array(1=>array(),2=>array());
        foreach($question_type_list as $qtl)
        {
			$paper['question_config'][$qtl->is_select_type][$qtl->id]=$qtl;	
			$paper_question_type_id_list[$qtl->is_select_type][]=$qtl->id;

			if($qtl->question_order)
            {
                if($qtl->question_order) $paper_question_order[$qtl->is_select_type][$qtl->id]=explode(",",$qtl->question_order);
                foreach($paper_question_order[$qtl->is_select_type][$qtl->id] as $key=>$paper_question_id)
                {
                    $paper_question_order[$qtl->is_select_type][$qtl->id][$paper_question_id]=null;
                    unset($paper_question_order[$qtl->is_select_type][$qtl->id][$key]);
                }
            }
        }

		// get paper question
        $paper_question_list=$this->homework_question_model->get_paper_questions($paper_id);
        $question_id_list=array();
		$paper_question_id_list=array();
		$paper_question_order_list=array(1=>array(),0=>array());
		$paper['question_total']=array(1=>0,0=>0);
        foreach($paper_question_list as $ql)
        {
			$paper_question_id_list[]=$ql->id;
			if(!isset($paper_question_order_list[$ql->is_select_type][$ql->qtype_id])&&isset($paper_question_order[$ql->is_select_type][$ql->qtype_id])) $paper_question_order_list[$ql->is_select_type][$ql->qtype_id]=$paper_question_order[$ql->is_select_type][$ql->qtype_id];
			if(in_array($ql->qtype_id,$paper_question_type_id_list[$ql->is_select_type]))
			{
            	$paper_question_order_list[$ql->is_select_type][$ql->qtype_id][$ql->id]=$ql->question_id;
                $paper_order_list[$ql->id]=$ql->question_id;
                $paper['question_origin'][$ql->id]=$ql->question_origin;
                $paper['question_category'][$ql->id]['category']=$ql->category_id;
                $paper['question_category'][$ql->id]['course']=$ql->course_id;
                $question_id_list[$ql->question_origin][]=$ql->question_id;
				$paper['question_total'][$ql->is_select_type]++;
			}
        }

		foreach($paper_question_order_list as $paper_section_type=>$paper_question_type)
        {
            if($paper_question_type)
            {
                foreach($paper_question_type as $paper_question_type_id=>$paper_question)
                {
                    if(!in_array($paper_question_type_id,$paper_question_type_id_list[$paper_section_type]))
                    {
                        unset($paper_question_order_list[$paper_section_type][$paper_question_type_id]);
                    }
                    if($paper_question)
                    {
                        foreach($paper_question as $paper_question_id=>$question)
                        {
                            if(!in_array($paper_question_id,$paper_question_id_list))
                            {
                                unset($paper_question_order_list[$paper_section_type][$paper_question_type_id][$paper_question_id]);
                            }
                            else if($question==null)
                            {
                                unset($paper_question_order_list[$paper_section_type][$paper_question_type_id][$paper_question_id]);
                            }
                        }
                    }
                }
            }
        }

		$paper_question_order_list[2]=$paper_question_order_list[0];       
        unset($paper_question_order_list[0]);   
        $paper['paper_question']=$paper_question_order_list;

        foreach($paper_order_list as $paper_question_id=>$paper_question)
        {
            if($paper_question == 0) unset($paper_order_list[$paper_question_id]);
        }

        $paper['paper_question_order']=$paper_order_list;

        if(isset($paper['question_config'][0])){
            $paper['question_config'][2]=$paper['question_config'][0];
            unset($paper['question_config'][0]);	
        }
		$paper['question_total'][2]=$paper['question_total'][0];
		unset($paper['question_total'][0]);

        // get question
        if(!empty($question_id_list))
        {
            foreach($question_id_list as $k_list => $qid_list)
            {
                $question_list=$this->get_question_by_origin($k_list,$qid_list,$with_text);
                if(!empty($question_list))
                {
                    foreach($question_list as $ql)
                    {
                        $paper['question'][$k_list][$ql->id]=$ql;
                        if($k_list == Constant::QUESTION_ORIGIN_QUESTION || $k_list == Constant::QUESTION_ORIGIN_EXERCISE)
                        {
                            $this->load->helper('img_helper');
                            $paper['question'][$k_list][$ql->id]->body=path2img($ql->body);
                            $paper['question'][$k_list][$ql->id]->answer=path2img($ql->answer);
                            $paper['question'][$k_list][$ql->id]->analysis=path2img($ql->analysis);
                        }
                        else
                        {
                            //$paper['question'][$k_list]=$this->handle_paper_question_style($paper['question'][$k_list]);
                        }
                    }
                }
                else
                {
                    $paper['question'][$k_list]=null;
                }
            }
        }
		//echo '<pre>';
        //print_r($paper['question']);
        //print_r($paper);
        //echo '</pre>';

        //处理题干、解析、答案的文字中的样式
		return $paper;
	}

    protected function get_question_by_origin($question_origin,$question_ids,$with_text=true)
    {
        $this->load->model('user_data/user_question_model');
        $question_list=array();
        switch ($question_origin) {
            case Constant::QUESTION_ORIGIN_QUESTION:
                $this->question_model->init();
                if($with_text) $question_list=$this->question_model->get_question_by_ids_with_text($question_ids,'paper');
                else $question_list=$this->question_model->get_question_by_ids($question_ids,'paper');
                break;
            case Constant::QUESTION_ORIGIN_EXERCISE:
                $this->question_model->init('exercise');
                if($with_text) $question_list=$this->question_model->get_question_by_ids_with_text($question_ids,'paper');
                else $question_list=$this->question_model->get_question_by_ids($question_ids,'paper');
                break;
            case Constant::QUESTION_ORIGIN_MYQUESTION:
                $this->question_model->init('exercise');
                $question_list=$this->user_question_model->get_question_by_ids($question_ids,'paper');
                break;
            default:
                break;
        }
        return $question_list;
    }

    //处理题目的题干和选项、答案的样式，避免div、html、body等标签不能闭合的情况
    protected function handle_paper_question_style($question){
        if(!empty($question))
        {
            $this->load->model('exercise_plan/student_homework_model');
            foreach($question as $qs=>$q){
                $temp_arr = array();
                $temp_arr['body'] = $q->body_text;
                $temp_arr['analysis'] = $q->analysis_text;
                $temp_arr['answer'] = $q->answer_text;
                $temp_arr = $this->student_homework_model->separateQuestion($temp_arr);
                $q->process_question_title = str_replace("</div></body></html>", '',$temp_arr['title']);
                $q->process_question_title = str_replace("</body></html>", '',$q->process_question_title);
                $q->process_question_options = $temp_arr['option'];
                $q->process_question_analysis = $temp_arr['analysis'];
                $q->process_question_answer = $temp_arr['answer'];
                $question[$qs] = $q;
                if(!$question[$qs]->asw) $question[$qs]->asw = $q->process_question_answer;
            }
        }
        return $question;
    }

    protected function get_pagination($page_num,$total,$func,$conf=array())
    {
        $config['total_rows']       = $total; //为页总数
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        //$config['params']           = array('b'=>'"123"','a'=>'1');

        if(is_array($conf)) $config=array_merge($config,$conf);

        $this->load->library('pagination'); 
        //获取分页
        $this->pagination->initialize($config);
        if(isset($config['base_url'])&&$config['base_url']) $pages = $this->pagination->create_links();
        else $pages = $this->pagination->create_ajax_links();
        return $pages;
    }
}
