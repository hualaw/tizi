<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('paper_controller.php');

class Paper_Exam extends Paper_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('question/question_model');
        $this->load->model('question/question_exam_model');
        $this->load->helper('teacher_data_helper');
    }

    public function index($subject_id=0,$exam_id=0)
    {   
        $data['stype']=$this->input->get('stype',true,false,0);
        $data['gtype']=$this->input->get('gtype',true,false,0);
        $data['gttype']=$this->input->get('gttype',true,false,0);
        $data['etype']=$this->input->get('etype',true,false,0);
        $data['atype']=$this->input->get('atype',true,false,0);
        $data['subject_id']=$this->input->get('sid',true);
        $data['exam_id']=$exam_id;
        
        if($data['stype']||$data['gtype']||$data['gttype'])
        {
            $subject=$this->question_subject_model->get_subject_by_type($data['stype'],$data['gtype'],$data['gttype']);
            if(isset($subject[0])) $subject_id=$subject[0]->id;
        }

        if($exam_id) $exam=$this->question_exam_model->get_exam_by_id($exam_id);
        if(isset($exam)&&isset($exam->subject_id)) $subject_id=$exam->subject_id;
        $data=$this->get_subject($data,$subject_id);

        /*
        //get subject type
        $subject_type_all[0]=(object)array('id'=>0,'name'=>"全部");
        $data['subject_type']=$this->question_subject_model->get_subject_type(true);
        $data['subject_type']=array_merge($subject_type_all,$data['subject_type']);
        */

        //get grade
        $data['grade']=array(0=>'全部');
        if(strpos($data['subject_name'],"初中")!==false)
        {
            for($i=1;$i<=3;$i++)
            {
                $data['grade'][$i]=Constant::grade($i);
            }
        }
        else if(strpos($data['subject_name'],"高中")!==false)
        {
            for($i=4;$i<=6;$i++)
            {
                $data['grade'][$i]=Constant::grade($i);
            }
        }
        else if(strpos($data['subject_name'],"小学")!==false)
        {
            for($i=7;$i<=12;$i++)
            {
                $data['grade'][$i]=Constant::grade($i);
            }
        }

        //get exam type
        $exam_type_all[0]=(object)array('id'=>0,'name'=>"全部");
        $data['exam_type']=$this->question_exam_model->get_exam_type();
        $data['exam_type']=array_merge($exam_type_all,$data['exam_type']);

        //get area
        $area_type_all[0]=(object)array('id'=>0,'name'=>"全部");
        $data['area']=$this->question_exam_model->get_area();
        $data['area']=array_merge($exam_type_all,$data['area']);

        $this->smarty->assign('sj_url',site_url().'teacher/paper/exam');
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'paper_exam.html');
    }

    
    public function get_question()
    {
        $data['subject_id']=$this->input->get('sid',true);
        $data['exam_id']=$this->input->get('exam_id',true);
        $data['exam']=$this->question_exam_model->get_exam_by_id($data['exam_id']);
        $question_ids = array();
        if(!$data['subject_id']||!isset($data['exam']->subject_id)||$data['exam']->subject_id!=$data['subject_id'])
        {
            $question['error']=$this->lang->line('error_get_exam_question');
            $question['errorcode']=false;
        }
        else
        {
            if(isset($data['exam']->question_ids)) $question_ids=explode(',',$data['exam']->question_ids);
            if(!empty($question_ids))
            {
                $paper_id=$this->get_paper_id($data['subject_id'],$this->tizi_uid);
                $paper_question_list=$this->get_paper_question_cart($paper_id,false);
                $question_list=$this->question_model->get_question_by_ids($question_ids);

                $question_format=$this->question_format($question_list,$paper_question_list);
                $data['total']=count($question_ids);
                $data['question']=$question_format['question'];
                $data['question_count']=$this->question_count($question_format['question_ids']);

                $data['grade']=Constant::grade();
                $data['exam_level']=$this->question_exam_model->get_exam_level(true);
                $data['exam_type']=$this->question_exam_model->get_exam_type(true);
                $data['area']=$this->question_exam_model->get_area(true);

                $this->smarty->assign('pages','');
                $this->smarty->assign('data',$data);
                $question['html']=$this->smarty->fetch($this->_smarty_dir.'paper_exam_question_tpl.html');
                $question['errorcode']=true;
            }
            else
            {
                $question['error']=$this->lang->line('error_get_exam_question');
                $question['errorcode']=false;
            }
        }
        echo json_token($question);
        exit(); 
    }

    public function get_exam($pagemode='hall')
    {
        //http get
        $subject_type=$this->input->get('stype',true);
        $grade_type=$this->input->get('gtype',true);
        $exam_type=$this->input->get('etype',true);
        $area_type=$this->input->get('atype',true);
        $subject_id=$this->input->get('sid',true);
        $page_select=$this->input->get("page",true,false,1);

        $data['exam']=$this->question_exam_model->get_search_exam($page_select,$subject_type,$grade_type,$exam_type,$area_type,$subject_id);
        $data['total']=$this->question_exam_model->get_search_exam($page_select,$subject_type,$grade_type,$exam_type,$area_type,$subject_id,true);
        $data['page_num']=$page_select;

        $data['subject_id']=$subject_id;
        $data['grade']=Constant::grade();
        $data['exam_level']=$this->question_exam_model->get_exam_level(true);
        $data['exam_type']=$this->question_exam_model->get_exam_type(true);
        $data['area']=$this->question_exam_model->get_area(true);

        $data['pagemode']=$pagemode;

        $this->smarty->assign('pages',$this->get_pagination($data['page_num'],$data['total'],'exam_page'));
        $this->smarty->assign('data',$data);
        $exam['html']=$this->smarty->fetch($this->_smarty_dir.'paper_exam_tpl.html');
        $exam['errorcode']=true;
        echo json_token($exam);
        exit(); 
    }

    //only for exam hall
    public function hall()
    {   
        $data['stype']=$this->input->get('stype',true,false,0);
        $data['gtype']=$this->input->get('gtype',true,false,0);
        $data['etype']=$this->input->get('etype',true,false,0);
        $data['atype']=$this->input->get('atype',true,false,0);
        
        //get subject type
        $subject_type_all[0]=(object)array('id'=>0,'name'=>"全部");
        $data['subject_type']=$this->question_subject_model->get_subject_type(true,"exam_paper");
        $data['subject_type']=array_merge($subject_type_all,$data['subject_type']);

        //get grade
        $data['grade']=array(0=>'全部');
        $data['grade']=array_merge($data['grade'],Constant::grade());
        
        //get exam type
        $exam_type_all[0]=(object)array('id'=>0,'name'=>"全部");
        $data['exam_type']=$this->question_exam_model->get_exam_type();
        $data['exam_type']=array_merge($exam_type_all,$data['exam_type']);

        //get area
        $area_type_all[0]=(object)array('id'=>0,'name'=>"全部");
        $data['area']=$this->question_exam_model->get_area();
        $data['area']=array_merge($exam_type_all,$data['area']);
        
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'paper_hall.html');
    }

    public function recover_exam($exam_id=0)
    {
        if($exam_id) $exam=$this->question_exam_model->get_exam_by_id($exam_id);

        $question_ids=array();
        if(isset($exam)&&!empty($exam->question_ids)) $question_ids=explode(',',$exam->question_ids);
        if(!empty($question_ids)&&$exam->subject_id)
        {
            $paper_id=$this->get_paper_id($exam->subject_id,$this->tizi_uid);

            $this->paper_question_model->delete_questions_by_paper_question_type($paper_id,0,true);
            $this->paper_question_type_model->reset_paper_question_type($paper_id);

            foreach($question_ids as $question_id)
            {
                $paper=parent::add_question_to_paper($paper_id,$question_id,0,0,0,true);
            }
        }
        redirect('teacher/paper/preview/'.$exam->subject_id);
    }

	public function position(){
		$this->load->helper("json");
		$area_id = intval($this->input->get("area_id"));
		$stype_id = intval($this->input->get("stype_id"));
		$this->input->set_cookie("HM_AREA", $area_id, 86400 * 30);
		$this->input->set_cookie("HM_STYPE", $stype_id, 86400 * 30);
		$this->load->model("question/question_exam_model");
		$this->load->model("question/question_subject_model");
		$grade = Constant::grade();
		$position = $this->question_exam_model->homepage_position($area_id, $stype_id);
		$exam_type = $this->question_exam_model->get_exam_type(true,true);
		$subject_type = $this->question_subject_model->get_subject_type(false, "exam_paper");
		$this->smarty->assign("exams", $position);
		$this->smarty->assign("grade", $grade);
		$this->smarty->assign("exam_type", $exam_type);
		$this->smarty->assign("area_id", $area_id);
		$this->smarty->assign("stype_id", $stype_id);
		$this->smarty->assign("subject_name", $subject_type[$stype_id]);
		$json["html"] = $this->smarty->fetch($this->_smarty_dir."paper_exam_position.html");
		json_get($json);
	}
}
	
/* End of file question.php */
/* Location: ./application/controllers/paper/question.php */

