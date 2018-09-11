<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('paper_controller.php');

class Paper_Intelligent extends Paper_Controller {
	
    protected $_page_conf=array();
    protected $_redis=false;

    public function __construct()
    {
        parent::__construct();  
		$this->load->model('question/question_category_model');
		$this->load->model("question/question_type_model");
		$this->load->model("question/question_model");
        $this->load->model("redis/redis_model");

        //智能选题每页100题
        $this->_page_conf=array('per_page'=>Constant::INTELLIGENT_PER_PAGE);

        if($this->redis_model->connect('intelligent'))
        {
            $this->_redis=true;
        }
    }

	public function index($subject_id=0,$category_select=0)
	{
		//http get and params initalize
        $data['subject_id']=$this->input->get('sid');
        $data['category_select']=$this->input->get("cselect");

        $data=$this->get_subject($data,$subject_id);
        
        if(!$data['category_select']) $data['category_select']=$category_select;

        //get question type
        $data['question_type']=$this->question_type_model->get_subject_question_type($data['subject_id'],false,false);

        //get category depth 1
        $data['category_root_id']=$this->question_category_model->get_root_id($data['subject_id']);
        if(!$data['category_select']) $data['category_select']=$data['category_root_id'][0]->id;

        foreach($data['category_root_id'] as $key=>$c_r_id)
        {
            if($c_r_id->category_type!=0)//网盘目录
            {
                unset($data['category_root_id'][$key]);
            }
            if($data['category_select']==$c_r_id->id)
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

        //get category depth 2-3
        if(!empty($data['category_root_id'])&&$data['category_select'])
        {
            $category_node=$this->question_category_model->get_subtree_node($data['category_select']);
            foreach($category_node as $cat_key=>$cat_node)
            {
                $data['category_node'][$cat_key][$cat_node->depth]=$cat_node;
                $data['category_node'][$cat_key][$cat_node->depth+1]=$this->question_category_model->get_subtree_node($cat_node->id);
            }
        }
        
		$this->smarty->assign('sj_url',site_url().'teacher/paper/intelligent');
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'paper_intelligent.html');	
	}

    public function select()
    {
        $data['subject_id']=$this->input->get('sid');
        $data['category_id']=$this->input->get("cid");       
        $data['difficult']=$this->input->get("diff");
        $category_ids=$this->input->get("cids");
        $type_list=$this->input->get("typelist");

        $data['type_list']=array();
        $total=0;
        if($type_list)
        {
            $type_list=explode(",",$type_list);
            if(is_array($type_list))
            {
                foreach($type_list as $tl)
                {
                    $tvalue=explode("-",$tl);
                    $data['type_list'][(int)$tvalue[0]]=(int)$tvalue[1];
                    $total+=(int)$tvalue[1];
                }
            }
        }

        $data['category_ids']=array();
        if($category_ids)
        {
            $data['category_ids']=explode(",",$category_ids);
            $sub_category_node=array();
            foreach($data['category_ids'] as $category_id)
            {
                $sub_category_node=array_merge($sub_category_node,$this->question_category_model->get_subtree_node($category_id));
            }
            if(!empty($sub_category_node))
            {
                foreach($sub_category_node as $node)
                {
                    $data['category_ids'][]=$node->id;
                }
            }
        }
        
        if($total <= Constant::PAPER_QUESTION_LIMIT)
        {
            $questions=$this->get_intelligent_question($data['difficult'],$data['category_ids'],$data['type_list']);
            
            if($questions['errorcode']&&!empty($questions['question']))
            {
                if($this->_redis)
                {
                    $this->cache->save($this->tizi_uid.'_'.$data['subject_id'].'_total',$questions['total'],Constant::REDIS_INTELLIGENT_TIMEOUT);
                    $this->cache->save($this->tizi_uid.'_'.$data['subject_id'].'_question',json_encode($questions['question']),Constant::REDIS_INTELLIGENT_TIMEOUT);
                }    

                $paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);

                $data['total']=$questions['total'];
                $question_format=$this->question_format($paper_id,$questions['question'],1,$data['total']);
                $data['question']=$question_format['question'];
                $data['question_count']=$question_format['question_count'];
                
                //$this->smarty->assign('pages',$this->get_pagination(1,$data['total'],'Teacher.paper.intelligent.page',$this->_page_conf));
                $this->smarty->assign('pages','');
                $this->smarty->assign('data',$data);
                $json['errorcode']=true;
                $json['html']=$this->smarty->fetch($this->_smarty_dir.'paper_intelligent_tpl.html');
            }
            else
            {
                $json['errorcode']=false;
                $json['error']=$questions['error'];
            }
        }
        else
        {
            $json['errorcode']=false;
            $json['error']=$this->lang->line('error_intelligent_maxnum');
        }
        echo json_token($json);
        exit();
    }

	/*desc:ajax get question list and pagination*/
	public function get_question()
	{
        $data['subject_id']=$this->input->get('sid');
        $data['difficult']=$this->input->get("diff");
        $data['page_num']=$this->input->get('page');
        if(!$data['page_num']) $data['page_num']=1;

        if($this->_redis)
        {
            $paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);

            $total=$this->cache->get($this->tizi_uid.'_'.$data['subject_id'].'_total');
            $question=$this->cache->get($this->tizi_uid.'_'.$data['subject_id'].'_question');
            if($total>0)
            {
                $data['total']=$total;
                $question_format=$this->question_format($paper_id,json_decode($question,true),$data['page_num'],$data['total']);
                $data['question']=$question_format['question'];
                $data['question_count']=$question_format['question_count'];

                $this->smarty->assign('pages',$this->get_pagination($data['page_num'],$data['total'],'Teacher.paper.intelligent.page',$this->_page_conf));
                $this->smarty->assign('data',$data);
                $json['errorcode']=true;
                $json['html']=$this->smarty->fetch($this->_smarty_dir.'paper_intelligent_tpl.html');
            }
            else
            {
                $json['errorcode']=false;
                $json['error']=$this->lang->line('error_get_page');
            }
        }
        else
        {
            $json['errorcode']=false;
            $json['error']=$this->lang->line('error_get_page');
        }
        echo json_token($json);
        exit();
	}
	
    public function change_question()
    {
        $question_ids=$this->input->get('qids');
        $question_id=$this->input->get('qid');
        $category_id=$this->input->get("cid");       
        $difficult=$this->input->get("diff");
        $question_type_id=$this->input->get("qtype");
        $data['subject_id']=$this->input->get('sid');

        $data['page_num']=$this->input->get('page');
        if(!$data['page_num']) $data['page_num']=1;

        if($this->_redis)
        {
            $questions=$this->get_intelligent_question($difficult,array($category_id),array($question_type_id=>1),$question_ids);
            
            if($questions['errorcode']&&!empty($questions['question']))
            {
                $total=$this->cache->get($this->tizi_uid.'_'.$data['subject_id'].'_total');
                $question=$this->cache->get($this->tizi_uid.'_'.$data['subject_id'].'_question');
                $question=json_decode($question,true);
                foreach($question as $k=>$q)
                {
                    if($q['id']==$question_id)
                    {
                        $question[$k]=$questions['question'][0];
                        $data['qindex']=$k;
                    }
                }
                $this->cache->save($this->tizi_uid.'_'.$data['subject_id'].'_question',json_encode($question),Constant::REDIS_INTELLIGENT_TIMEOUT);              
                $paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);

                $data['total']=$total;
                $question_format=$this->question_format($paper_id,$questions['question'],1,$data['total']);

                $data['question']=$question_format['question'];
                $data['question_count']=$question_format['question_count'];

                $json['errorcode']=true;
                $this->smarty->assign('data',$data);
                $json['html']=$this->smarty->fetch($this->_smarty_dir.'paper_intelligent_question_tpl.html');
                //$this->get_question();
            }
            else
            {
                $json['errorcode']=false;
                $json['error']=$this->lang->line('error_wise_no_record');
            }
        }
        else
        {
            $json['errorcode']=false;
            $json['error']=$this->lang->line('error_wise_no_record');
        }

        echo json_token($json);
        exit;
    }

    protected function question_format($paper_id,$question_list,$page_num,$total)
    {
        $paper_question_list=$this->get_paper_question_cart($paper_id,false);

        $per_page=Constant::INTELLIGENT_PER_PAGE;    
        $start=($page_num - 1) * $per_page;
        $end=($page_num * $per_page - 1) > $total ? $total : ($page_num * $per_page - 1);
        if($end > count($question_list)) $end = count($question_list);
        $question=$question_ids=array();
        for($i=$start;$i<$end;$i++)
        {
            $question_ids[]=$question_list[$i]['id'];
            $question[$i]['id']=$question_list[$i]['id'];
            $question[$i]['title']=$question_list[$i]['title'];
            $question[$i]['category_name']=isset($question_list[$i]['name'])?$question_list[$i]['name']:'';
            $question[$i]['category_id']=isset($question_list[$i]['category_id'])?$question_list[$i]['category_id']:0;
            $question[$i]['date']=date("Y-m-d",strtotime($question_list[$i]['date']));
            $question[$i]['qtype']=$question_list[$i]['qtype_id'];
            $question[$i]['qlevel']=$question_list[$i]['level_id'];
            $question[$i]['qlevel_name']='（'.Constant::level_name($question_list[$i]['level_id']).'）';
            $question[$i]['source']=$question_list[$i]['source'];
            $this->load->helper('img_helper');
            $question[$i]['body']=path2img($question_list[$i]['body']);
            $question[$i]['answer']=path2img($question_list[$i]['answer']);
            $question[$i]['analysis']=path2img($question_list[$i]['analysis']);
            if(in_array($question_list[$i]['id'],$paper_question_list['question_list'])) $question[$i]['is_add_paper']=true;
            else $question[$i]['is_add_paper']=false;
        }
        //return $question;
        $question_count=$this->question_count($question_ids);
        return array('question'=>$question,'question_count'=>$question_count);
    }
    
}

