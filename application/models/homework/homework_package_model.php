<?php

class Homework_Package_Model extends MY_Model {
    protected $tab_hw_cat = "homework_category";
    protected $tab_hw_pack = "homework_package";
    protected $tab_question="question";
    protected $tab_ques_type="question_type";

    function __construct(){
        parent::__construct();
    }

    //category下的package
    public function get_package_by_cat($cat_id){
        $sql = "select pack.* from `{$this->tab_hw_cat}` hwcat left join {$this->tab_hw_pack} pack on pack.id= hwcat.homework_package_id where `category_id` in ({$cat_id}) and pack.online=1";
        $res = $this->db->query($sql)->result_array();        
        return $res;
    }

     //根据question_ids获取题目图片内容
    public function get_questions_by_ids($q_ids) {
        $sql = "select q.*,qtype.is_select_type from {$this->tab_question} q 
                left join {$this->tab_ques_type} qtype 
                on qtype.id = q.qtype_id where q.id in ($q_ids) and q.online=1";

        $res = $this->db->query($sql)->result_array();        
        return $res;
    }

    //获取题目的内容，支持两种内容   content= 'text' | 'pic' 
    public function get_package_by_id($package_id,$with_question_detail=false,$content='text'){
        $sql = "select pack.*,cat.category_id from {$this->tab_hw_pack} pack left join {$this->tab_hw_cat} cat on cat.homework_package_id = pack.id where pack.id=$package_id ";
        $package = $this->db->query($sql)->row_array();
        if(!$package)return null;
        if($with_question_detail and $content='text'){
            $this->load->model('question/question_model');
            $qids_arr = explode(',', $package['question_ids']);
            $package['questions'] = $this->question_model->get_question_by_ids_with_text($qids_arr,'paper');
        }elseif($with_question_detail and $content='pic'){
            $package['questions'] = $this->get_questions_by_ids($package['question_ids']);
        }
        // var_dump($package);die;
        return $package;
    }

     
    






}

