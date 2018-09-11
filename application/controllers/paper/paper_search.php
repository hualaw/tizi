<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('paper_controller.php');

class Paper_Search extends Paper_Controller {
	
	public function __construct()
    {
        parent::__construct();
		$this->load->model('question/question_category_model');
		$this->load->model("question/question_level_model");
		$this->load->model("question/question_type_model");
		$this->load->model("question/question_model");
        $this->load->library('search');
    }

    public function index($subject_id=0)
    {
		//http get and params initalize
        $data['subject_id']=$this->input->get('sid');

        $data=$this->get_subject($data,$subject_id);

        //get question type
        $question_type_all[0]=(object)array('id'=>0,'name'=>"全部题型");
        $data['question_type']=$this->question_type_model->get_subject_question_type($data['subject_id'],false,false);
        $data['question_type']=array_merge($question_type_all,$data['question_type']);

        //get question level
        $question_level_all[0]=(object)array('id'=>0,'name'=>"全部难度",'level'=>0);
        $data['question_level']=$this->question_level_model->get_question_level_names($data['subject_id']);
        $data['question_level']=array_merge($question_level_all,$data['question_level']);

   		$this->smarty->assign('sj_url',site_url().'teacher/paper/search');
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'paper_search.html');
    }
	
	/*desc:ajax get question list and pagination*/
    public function get_question()
    {
        $data['subject_id']=$this->input->get('sid');
        $data['page_num']=$this->input->get('page');
        if(!$data['page_num']) $data['page_num']=1;

        $data['skeyword']=$this->input->get('skeyword',true);
        $data['stype']=$this->input->get('stype');
        $data['slevel']=$this->input->get('slevel');

        $paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);

        if(strap($data['skeyword']))
        {
            $addi_params=array('params'=>array('qtype'=>$data['stype'],'qlevel'=>$data['slevel'],'keyword'=>'"'.str_replace('"','\"',$data['skeyword']).'"'),
                'per_page'=>Constant::QUESTION_PER_PAGE);

            $node = $this->question_category_model->get_root_id($data['subject_id']);
            $question_list = $this->search->init('question')->search(array(
                'keyword'=>$data['skeyword'],'qtype_id'=>$data['stype'],'level_id'=>$data['slevel'],
                'subject_id'=>$data['subject_id']),$data['page_num'],Constant::QUESTION_PER_PAGE);
            
            if($question_list['total'] > 0)
            {
                $data['total']=$question_list['total'];
            
                $paper_question_list=$this->get_paper_question_cart($paper_id,false,'paper');

                $question_format=$this->question_format($question_list['result'],$paper_question_list);
                $data['question']=$question_format['question'];
                $data['question_count']=$this->question_count($question_format['question_ids']);

                $this->smarty->assign('pages',$this->get_pagination($data['page_num'],$data['total'],'Teacher.paper.search.page',$addi_params));
                $this->smarty->assign('data',$data);
                $json['errorcode']=true;
                $json['html']=$this->smarty->fetch($this->_smarty_dir.'paper_search_question.html');
            }
            else
            {
                $json['errorcode']=false;
                $json['error']=$this->lang->line('error_search_question');
            }
        }    
        else
        {
            $json['errorcode']=false;
            $json['error']=$this->lang->line('error_search_question');
        }

        echo json_token($json);
        exit();
    }
	
}
	
/* End of file question.php */
/* Location: ./application/controllers/paper/question.php */

