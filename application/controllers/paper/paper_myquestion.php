<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'user/user_question.php'); 

class Paper_Myquestion extends User_Question {

    private $_smarty_dir = 'teacher/paper/';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('paper/paper_model');
        $this->load->model('paper/paper_question_model');
    }

    function index($subject_id=0)
    {
        $data['subject_id']=$this->input->get('sid');
        $data['qtype']=$this->input->get('qtype',true,false,0);
        $data['qlevel']=$this->input->get('qlevel',true,false,0);

        $data=$this->get_subject($data,$subject_id,true);

        /*
        //get question type
        $question_type_all[0]=(object)array('id'=>0,'name'=>"全部题型");
        $data['question_type']=$this->question_type_model->get_subject_question_type($data['subject_id'],false);
        $data['question_type']=array_merge($question_type_all,$data['question_type']);

        //get question level
        $question_level_all[0]=(object)array('id'=>0,'name'=>"全部难度",'level'=>0);
        $data['question_level']=$this->question_level_model->get_question_level_names($data['subject_id']);
        $data['question_level']=array_merge($question_level_all,$data['question_level']);
        */

        list($data['all_total'],$data['groups']) = self::_get_group($data['subject_id']);
        //$data['filter_show'] = $data['all_total'] > 0 ? true : false;
        //print_r($data);die();
        $this->smarty->assign('sj_url',site_url().'teacher/paper/myquestion');
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'paper_myquestion.html');
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

    public function get_myquestion()
    {
        $question_type=$this->input->get("qtype",true);
        $question_level=$this->input->get("qlevel",true);
        $group_select=$this->input->get("gid",true);
        $data['group_name']=$this->input->get("gname",true);
        $page_select=$this->input->get("page",true,false,1);
        $subject_id=$this->input->get("sid",true);       
        $questions = array();
        if($page_select<=0) $page_select=1;
        if($group_select=='') $group_select = false;
        $data['total']       = self::_get_question($group_select,$page_select,$question_type,$question_level,$subject_id,true);
        $data['page_total']  = ceil($data['total']/Constant::QUESTION_PER_PAGE);
        $data['page_num']    = $page_select;
        $pages = self::get_pagination($data['page_num'],$data['total'],'Teacher.UserCenter.itemLib.page');
        $data['easy_level'] = Constant::level_name(null,true);//难度级别数组(常量函数)
        $data['question'] = self::_get_question($group_select,$page_select,$question_type,$question_level,$subject_id,false);

        $data['is_add_paper'] = $this->get_myquestion_list($subject_id);

        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('data',$data);
        $questions['html'] = $this->smarty->fetch($this->_smarty_dir.'paper_myquestion_tpl.html');
        $questions['errorcode'] = true;
        echo json_encode($questions);
        exit();
    }

    private function get_myquestion_list($subject_id)
    {
        $paper_id=$this->get_paper_id($subject_id,$this->tizi_uid);
        $question_id_list=$this->paper_question_model->get_paper_questions($paper_id);

        $question_list=array();
        foreach($question_id_list as $q_i_l)
        {
            if($q_i_l->question_origin == Constant::QUESTION_ORIGIN_MYQUESTION) $question_list[$q_i_l->question_id]=$q_i_l->question_id;
        }

        return $question_list;
    }

}
	
/* End of file myquestion.php */
/* Location: ./application/controllers/paper/myquestion.php */

