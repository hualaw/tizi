<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lesson_Search extends MY_Controller {

    private $_smarty_dir = 'teacher/lesson/';
    public function __construct()
    {
        parent::__construct();
        $this->_user_id=$this->session->userdata('user_id');
        $this->_user_type=$this->session->userdata('user_type');
        $this->load->model('lesson/document_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('question/question_course_model');
        $this->load->model("lesson/document_type_model");
        $this->load->model("login/register_model");
        $this->load->library('search');
        
        if(!$this->_user_id)
        {
            $this->session->set_flashdata('errormsg',$this->lang->line('error_login'));
            redirect($this->_redirect_url);
        }
        else if($this->_user_type!=Constant::USER_TYPE_TEACHER)
        {
            $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
            redirect($this->_redirect_url);
        }
    }

    public function index($subject_id=0)
    {
        
        $this->smarty->assign('sj_url',site_url().'teacher/lesson/prepare');
        $this->smarty->assign('data',$data);
        $this->smarty->display($this->_smarty_dir.'lesson_prepare.html');
    }

    

    public function lesson_search()
    {
        $data['subject_id']=$this->input->get('sid');
        $data['page_num']=$this->input->get('page');
        if(!$data['page_num']) $data['page_num']=1;
        $data['skeyword']=$this->input->get('skeyword',true);
        if(strap($data['skeyword'])){
           $document_list = $this->search->init('lesson')->search(array(
                'keyword'=>$data['skeyword'],
                'subject_id'=>$data['subject_id']),$data['page_num'],Constant::SEARCH_PER_PAGE);
            if($document_list['total'] > 0)
            {
                $i=0;
                foreach($document_list['result'] as $q_l)
                {
                    $data['document'][$i]['id']=alpha_id($q_l->id);
                    $data['document'][$i]['page_count']=$q_l->page_count;
                    $data['document'][$i]['file_name']=$q_l->file_name;
                    $data['document'][$i]['file_ext']=Constant::document_icon($q_l->file_ext);
                    $data['document'][$i]['upload_time']=date("Y-m-d",$q_l->upload_time);
                    $data['document'][$i]['file_size']=self::prase_file_size($q_l->file_size);
                    $data['document'][$i]['downloads']=$q_l->downloads;
                    $i++;
                }
                $data['total']=$document_list['total'];
                $this->smarty->assign('pages',$this->get_pagination($data['page_num'],$data['total'],'Teacher.lesson.search.page'));
                $this->smarty->assign('data',$data);
                $this->smarty->assign('keyword',$data['skeyword']);
                $json['errorcode']=true;
                $json['html']=$this->smarty->fetch('teacher/lesson/lesson_search_tpl.html');
            }
            else
            {
                $json['errorcode']=false;
                $json['error']=$this->lang->line('error_search_lesson');
            } 
        }
        else{

            $json['errorcode']=false;
            $json['error']=$this->lang->line('error_search_lesson');
        }
        
        echo json_token($json);
        exit();
    } 

    protected function prase_file_size($file_size)
    {
        $mod = 1024;
        $units = explode(' ','B KB MB');
        for ($i = 0; $file_size > $mod; $i++) 
        {
            $file_size /= $mod;
        }
        return round($file_size, 2) . ' ' . $units[$i];
    } 

    protected function get_pagination($page_num,$total,$func)
    {
        $this->load->library('pagination'); 
        $config['total_rows']       = $total; //为页总数
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        //获取分页
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_ajax_links();
        return $pages;
    }

}
    
/* End of file lesson_prepare.php */
/* Location: ./application/controllers/lesson/lesson_prepare.php */

