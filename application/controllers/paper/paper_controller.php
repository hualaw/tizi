<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once dirname(__FILE__) . "/../Controller.php";

class Paper_Controller extends Controller {
	
	protected $_smarty_dir="teacher/paper/";

    public function __construct()
    {
        parent::__construct();
		$this->load->helper('cookie');
		$this->load->model('paper/paper_model');
		$this->load->model('question/question_subject_model');
		$this->load->model('paper/paper_question_model');
		$this->load->model('paper/paper_question_type_model');
        $this->load->model("login/register_model");
        $this->load->library('search');
        $this->load->config('search');

		$this->smarty->assign('sj_url',site_url().'teacher/paper/question');
        $this->smarty->assign('source',Constant::FEEDBACK_SOURCE_PAPER);

        //未登录不能访问整个Controller
        // if($this->tizi_uid && $this->tizi_utype!=Constant::USER_TYPE_TEACHER)
        // {
        //     if($this->tizi_ajax)
        //     {
        //         echo json_token(array('errorcode'=>false,'error'=>$this->lang->line('error_user_type_teacher')));
        //         exit();
        //     }
        //     else
        //     {
        //         $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
        //         redirect($this->tizi_redirect);
        //     }
        // }
    }
	
	protected function get_subject($data,$subject_id,$check_all=false)
    {
        if(!$data['subject_id']) $data['subject_id']=$subject_id;
        if(!$data['subject_id']) $data['subject_id']=$this->register_model->my_subject($this->tizi_uid,'paper');
        if(!$this->question_subject_model->check_subject($data['subject_id'],'paper'.($check_all?'':'_question'))) $data['subject_id']=Constant::DEFAULT_SUBJECT_ID;
        $this->register_model->set_favorate_subject($data['subject_id'],'paper');
        $data['subject_name']=$this->question_subject_model->get_subject_name($data['subject_id']);
        return $data;
    }

    /*get paper questions*/
    protected function get_paper_question_cart($paper_id,$return_type_array=true)
    {
        $question_id_list=$this->paper_question_model->get_paper_questions($paper_id);
        $question_type_list=$this->paper_question_type_model->get_paper_question_types($paper_id);

        $data=array('question_list'=>array(),'question_type_list'=>array(),'question_total'=>0,'question_section_total'=>array(1=>0,2=>0));
        if(empty($question_type_list))
        {
            return $data;
        }

        $question_type_id_list=array();
        foreach($question_type_list as $q_t_l)
        {
            $question_type_id_list[]=$q_t_l->id;
            $data['question_type_list'][$q_t_l->id]['name']=$q_t_l->name;
            $data['question_type_list'][$q_t_l->id]['count']=0;
        }

        foreach($question_id_list as $q_i_l)
        {
            $data['question_list'][]=$q_i_l->question_id;
            if(!isset($data['question_type_list'][$q_i_l->qtype_id]))
            {
                $data['question_type_list'][$q_i_l->qtype_id]['count']=1;
                $data['question_type_list'][$q_i_l->qtype_id]['name']=$q_i_l->name;
            }
            else $data['question_type_list'][$q_i_l->qtype_id]['count']++;
			//if($namespace=='homework'&&isset($q_i_l->is_select_type)) $data['question_section_total'][(2-$q_i_l->is_select_type)]++;	
        }
        $data['question_total']=count(array_unique($data['question_list']));

        $this->smarty->assign('data',$data);
        $data['html']=$this->smarty->fetch($this->_smarty_dir.'paper_cart_tpl.html');

		if($return_type_array)
		{
       		 $data['question_type_list_array']=array();
       		 $i=0;
       		 foreach($data['question_type_list'] as $qtype_id=>$qtl)
       		 {
           		 $data['question_type_list_array'][$i]=$qtl;
           		 $data['question_type_list_array'][$i]['id']=$qtype_id;
           		 $i++;
       		 }
			$data['question_type_list']=$data['question_type_list_array'];
			unset($data['question_type_list_array']);
		}
        return $data;
	}
	
	protected function get_category($category_node_select)
	{
        if($category_node_select<=0)
        {
            $category_node_list['errorcode']=false;
			$category_node_list['error']=$this->lang->line("error_get_category");
        }
        else
        {
            $category_list=$this->question_category_model->get_subtree_node($category_node_select);
            if(empty($category_list))
            {
                $category_node_list['errorcode']=false;
                $category_node_list['error']=$this->lang->line("error_get_category");
            }
            else
            {
                $category_node_list=array();
                $i=0;
                foreach($category_list as $c_l)
                {
                    $category_node_list['category'][$i]['id']=$c_l->id;
                    $category_node_list['category'][$i]['depth']=$c_l->depth;
    				//if($namespace=='course') $category_node_list['category'][$i]['depth']--;
                    $sublen = 81 - (($category_node_list['category'][$i]['depth'] - 1) * 6);
                    $category_node_list['category'][$i]['name']=sub_str($c_l->name,0,$sublen);
                    $category_node_list['category'][$i]['category_name']=$c_l->name;
                    if($c_l->lft==$c_l->rgt-1) $category_node_list['category'][$i]['is_leaf']=1;
                    else $category_node_list['category'][$i]['is_leaf']=0;
                    $i++;
                }
                $category_node_list['errorcode']=true;
            }
        }	
		return $category_node_list;
	}

    protected function get_category_list($category_node_select)
    {
        if($category_node_select<=0)
        {
            $category_node_list=false;
        }
        else
        {
            $category_list=$this->question_category_model->get_node_tree($category_node_select);
            if(empty($category_list))
            {
                $category_node_list=false;
            }
            else
            {
                $category_node_list=array();
                $i=0;
                foreach($category_list as $c_l)
                {
                    $category_node_list[$i]['id']=$c_l->id;
                    $category_node_list[$i]['depth']=$c_l->depth;
                    //if($namespace=='course') $category_node_list[$i]['depth']--;
                    $sublen = 41 - (($category_node_list[$i]['depth'] - 1) * 6);
                    $category_node_list[$i]['name']=sub_str($c_l->name,0,$sublen);
                    $category_node_list[$i]['category_name']=$c_l->name;
                    if($c_l->lft==$c_l->rgt-1) $category_node_list[$i]['is_leaf']=1;
                    else $category_node_list[$i]['is_leaf']=0;
                    $i++;
                }
            }
        }   
        return $category_node_list;
    }

	protected function get_question($paper_id,$subject_id,$node_select,$page_select,$question_type,$question_level)
	{
		if($page_select<=0) $page_select=1;
        if($question_level<0) $question_level=0;
        if($question_type<0) $question_type=0;

        if($node_select<=0||!$paper_id||$page_select>Constant::PAGE_LIMIT)
        {
            $question['errorcode']=false;
			$question['error']=$this->lang->line("error_get_question");
        }
        else
        {
			$breadcrumb=$this->question_category_model->get_single_path($node_select);		
			$i=0;
			foreach($breadcrumb as $b)
			{
				$question['breadcrumb'][$i]=$b->name;
				$i++;
			}

            $paper_question_list=$this->get_paper_question_cart($paper_id,false);
           
            $question['question']=array();
            $question_list=false;

            //debug search
            if($this->config->item('solr_search_paper'))
            {
                $question_list=$this->search->init('paper')->search(array('qtype_id'=>$question_type,'level_id'=>$question_level,'category_id'=>$node_select),$page_select,Constant::QUESTION_PER_PAGE,'id desc');
                
                if($question_list !== false)
                {
                    $question['pagination']['question_total']=$question_list['total'];
                    $question['pagination']['page_total']=ceil($question['pagination']['question_total']/Constant::QUESTION_PER_PAGE);
                    $question['pagination']['page']=$page_select;

                    if($page_select>$question['pagination']['page_total']) $page_select=$question['pagination']['page_total'];

                    $question_format=$this->question_format($question_list['result'],$paper_question_list);
                    $question['question']=$question_format['question'];
                }
            }

            if($question_list === false)
            {
                $category_list=$this->question_category_model->get_node_tree($node_select);
                $category_id_list=array();
                foreach($category_list as $c_l)
                {
                    $category_id_list[]=$c_l->id;
                }

                //get pagination
                $question['pagination']['question_total']=$this->question_model->get_search_question($category_id_list,$page_select,$question_type,$question_level,$subject_id,true);
                $question['pagination']['page_total']=ceil($question['pagination']['question_total']/Constant::QUESTION_PER_PAGE);
                $question['pagination']['page']=$page_select;

                if($page_select>$question['pagination']['page_total']) $page_select=$question['pagination']['page_total'];

                //get question
                $question_list=$this->question_model->get_search_question($category_id_list,$page_select,$question_type,$question_level,$subject_id);                                      
                $question_format=$this->question_format($question_list,$paper_question_list);
                $question['question']=$question_format['question'];
            }

            $question['question_count']=array();
            $question['question_count']=$this->question_count($question_format['question_ids']);
            
    		//get question cart
            $question['question_cart']['question_type_list']=$paper_question_list['question_type_list'];
            $question['question_cart']['question_total']=$paper_question_list['question_total'];
            $question['errorcode']=true;
        }
		return $question;
	}

	protected function add_question_to_paper($paper_id,$question_id,$question_origin=0,$category_id=0,$course_id=0,$return_type_array=true)
	{
		if($question_id<=0||!$paper_id)
        {
            $paper['errorcode']=false;
			$paper['error']=$this->lang->line("error_add_question");
        }
        else
        {
			$question_count=$this->paper_question_model->count_questions($paper_id);

			$question_limit=Constant::PAPER_QUESTION_LIMIT;
			if($question_count>=$question_limit)
			{
				$paper['errorcode']=false;
				$paper['error']=$this->lang->line("error_to_many_question");
			}
			else
			{
       		    $paper_question_id=$this->paper_question_model->add_question_to_paper($paper_id,$question_id,$question_origin,$category_id,$course_id);
    	        if($paper_question_id)
				{
                    $paper['errorcode']=true;
					$paper['paper_question_id']=$paper_question_id;
	            }
				else
				{
					$paper['errorcode']=false;
					$paper['error']=$this->lang->line("error_add_question");				
				}
			}

            //get question cart
            $paper['question_cart']=$this->get_paper_question_cart($paper_id,$return_type_array);
            if(empty($paper['question_cart']))
            {
              $paper['errorcode']=false;
              $paper['error']=$this->lang->line("error_get_cart");
            }
        }
		return $paper;
	}

	protected function remove_question_from_paper($paper_id,$question_id,$question_origin=0,$return_type_array=true)
	{
		if($question_id<=0||!$paper_id)
        {
            $paper['errorcode']=false;
			$paper['error']=$this->lang->line("error_remove_question");
        }
        else
        {
            $paper_question_return=$this->paper_question_model->delete_question_from_paper($paper_id,$question_id,$question_origin);

            //get question cart
			$paper['question_cart']=$this->get_paper_question_cart($paper_id,$return_type_array);
			if(empty($paper['question_cart']))
			{
				$paper['errorcode']=false;
				$paper['error']=$this->lang->line("error_get_cart");
			}
            if($paper_question_return)
			{
				$paper['errorcode']=true;
			}
            else
			{
				$paper['errorcode']=false;
				$paper['error']=$this->lang->line("error_remove_question");
			}
        }
		return $paper;
	}
	
	protected function remove_question_from_cart($paper_id,$question_type,$erase_all=false,$return_type_array=true)
	{
		if($paper_id)
        {
            //$this->{$namespace.'_question_model'}->delete_questions_by_paper_question_type($paper_id,$question_type,$erase_all);
            //if($erase_all) $this->{$namespace.'_question_type_model'}->reset_paper_question_type($paper_id);//临时方法
            $this->erase_paper($paper_id,$question_type,$erase_all,false);
            //get question cart
			$question['question_cart']=$this->get_paper_question_cart($paper_id,$return_type_array);
			if(empty($question['question_cart']))
        	{
            	$question['errorcode']=false;
            	$question['error']=$this->lang->line("error_get_cart");
       		}
        	else
        	{
            	$question['errorcode']=true;
        	}
        }
        else
        {
            $question['errorcode']=false;
			$question['error']=$this->lang->line("error_remove_questions");
        }
		return $question;
	}

    protected function erase_paper($paper_id,$question_type,$erase_all=false,$erase_recover=false)
    {
        $this->paper_question_model->delete_questions_by_paper_question_type($paper_id,$question_type,$erase_all);
        if($erase_all) $this->paper_question_type_model->reset_paper_question_type($paper_id);
        if($erase_recover) $this->paper_model->reset_paper_recovery($paper_id);
    }

    //get intelligent questions
    protected function get_intelligent_question($totalDifficult=0.65,$categorys=array(1,2,3),$qtypeArr=array(3=>20),$excludeQuestions=array())
    {
        $this->load->model("question/question_intelligent");
        $this->load->model("question/question_level_model");

        $questionLevels = $this->question_level_model->get_question_level_names();
        $this->question_intelligent->init($totalDifficult, $categorys, $questionLevels, $excludeQuestions);

        foreach($qtypeArr as $qtype=>$records){
            if($records < 0) continue;
            $wiseRangeIds = $this->question_intelligent->getWiseRangeIds($qtype);
            if(is_array($wiseRangeIds))
            {//成功获取该题库下该知识点题型在考察范围中的最大和最小题目id
                $randId = mt_rand($wiseRangeIds['minWiseId'], $wiseRangeIds['maxWiseId']);//获取随机题目id                        
                $this->question_intelligent->getDataRecords($records, $randId, $qtype);             
            }
            else if($wiseRangeIds !== true)
            {
                //错误,后面改弹框
                //show_error($this->_errorMsg[$wiseRangeIds]);
                return array('errorcode'=>false,'error'=>$this->lang->line('error_wise_no_record'));
            }
        }
        $data_results = $this->question_intelligent->getAllResults();
        if(empty($data_results))
        {//没有记录
            //show_error($this->_errorMsg[constant::ERROR_WISE_NO_RECORDS]);
            return array('errorcode'=>false,'error'=>$this->lang->line('error_wise_no_record'));
        }
        else
        {//该题型获取记录的统计信息
            //$totalRecords = $this->question_intelligent->getTotal();
            //$total = isset($totalRecords[$qtype]) ? $totalRecords[$qtype] : 0;
            /*
            foreach($qtypeArr as $qtype=>$records)
            {
                echo $qtype . "题型提交" . $records . "条记录，实际获取" . (isset($totalRecords[$qtype]) ? $totalRecords[$qtype] : 0) ."<br />";
            }
            echo "计划总体难度" . $totalDifficult . "，实际难度" . number_format($totalRecords['difficult']/$totalRecords['nums'], 2) ."<br />"."<br />";
            foreach($data_results as $val)
            {
                echo  "难度值id:" . $val["level_id"] . " 难度值" . $val["level"] . " 题型" . $val['qtype'] . " id：" . $val['id']. " 标题：".$val['title']." 时间:".$val['date']."<br />";                        
            }
            */
            return array('errorcode'=>true,'error'=>'','question'=>$data_results,'total'=>count($data_results));          
        }
    }

    protected function question_format($question_list,$paper_question_list)
    {
        $question=$question_ids=array();
        if(!empty($question_list))
        {
            $i=0;
            foreach($question_list as $key=>$q_l)
            {
                $question_ids[]=$q_l->id;
                $question[$i]['id']=$q_l->id;
                $question[$i]['title']=$q_l->title;
                $question[$i]['category_name']=isset($q_l->name)?$q_l->name:'';
                $question[$i]['category_id']=isset($q_l->category_id)?$q_l->category_id:0;
                $question[$i]['course_id']=isset($q_l->course_id)?$q_l->course_id:0;
                $question[$i]['date']=date("Y-m-d",strtotime($q_l->date));
                $question[$i]['qtype']=$q_l->qtype_id;
                $question[$i]['qlevel']=$q_l->level_id;
                $question[$i]['qlevel_name']='（'.Constant::level_name($q_l->level_id).'）';
                $question[$i]['source']=$q_l->source;
                $this->load->helper('img_helper');
                $question[$i]['body']=isset($q_l->body)?path2img($q_l->body):'';
                $question[$i]['answer']=isset($q_l->answer)?path2img($q_l->answer):'';
                $question[$i]['analysis']=isset($q_l->analysis)?path2img($q_l->analysis):'';
                if(in_array($q_l->id,$paper_question_list['question_list'])) $question[$i]['is_add_paper']=true;
                else $question[$i]['is_add_paper']=false;
                $i++;
            }
        }
        return array('question'=>$question,'question_ids'=>$question_ids);
    }

    protected function question_count($question_ids)
    {
        $question_count=array();
        if(is_array($question_ids)&&!empty($question_ids))
        {
            $this->load->model("redis/redis_model");
            if($this->redis_model->connect('pq_count'))
            {
                $question_count=$this->cache->redis->mget($question_ids);
            }
        }
        return $question_count;
    }
    
    protected function get_pagination($page_num,$total,$func,$conf=array())
    {
        if(!isset($conf['per_page'])) $conf['per_page']=Constant::QUESTION_PER_PAGE;
        return parent::get_pagination($page_num,$total,$func,$conf);
    }

}	
/* End of file paper_controller.php */
/* Location: ./application/controllers/paper/paper_controller.php */

