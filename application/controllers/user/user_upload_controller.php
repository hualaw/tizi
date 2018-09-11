<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');             
require(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Controller.php');

class User_Upload_Controller extends Controller{

    protected $_uid = 0;
       
    public function __construct(){
       	parent::__construct();
        $this->load->model('user_data/data_group_model','group');
        $this->load->model('question/question_subject_model');
        $this->load->model('login/register_model');
        //$this->load->model('question/question_category_model');
        $this->load->model('question/question_category_model');
        $this->load->model("question/question_type_model");
        $this->load->helper('teacher_data');
        $this->_uid = $this->session->userdata('user_id');
        // $user_type = $this->session->userdata('user_type');             
        // if (!$this->_uid){                                                       
        //     $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
        //     redirect('login');
        // }                      
        // if ($user_type != Constant::USER_TYPE_TEACHER){
        //     redirect('login');
        // }      
    }

    function ajax_subject()
    {
        if($this->input->is_ajax_request())
        {
            $grade_id = $this->input->get('grade_id');
            $error = array();
            if (!empty($grade_id)) {
                $result = $this->question_subject_model->get_subject_by_grade($grade_id);
                $error['error'] = "<option value=\"\" >请选择学科</option>";
                $error['error_code'] = true;
                foreach ($result as $value) {
                   $error['error'] .= "<option value=\"{$value->id}\" >{$value->name}</option>"; 
                }
            }else{
                $error['error_code'] = false;
                $error['error'] = '请选择学段';
            }
            echo json_token($error);die;
        }
    }

    function ajax_version_msg()
    {
        $subject_id = $this->input->get('subject_id',true);
        $is_doc = $this->input->get('doc_upload',true);
        if (!empty($subject_id)) {
            if(!$is_doc){
                /*分组*/
                $groups_arr = $this->group->get_list($subject_id,$this->_uid,Constant::DOCUMENT_GROUP);
                $error['groups'] = "<option value=\"0\">请选择分组</option>";
                foreach ($groups_arr as $value) {
                   $error['groups'] .= "<option value=\"{$value->id}\" >{$value->name}</option>"; 
                }
            }
            $category_root_id=$this->question_category_model->get_root_id($subject_id);
            /*临时增加过滤20140603 end*/
            $error['course'] = "<option value=\"\" >请选择版本</option>";
            $error['error_code'] = true;
            foreach ($category_root_id as $key => $value) {
                if($value->type > 0){
                    continue;
                }
                /*临时增加过滤20140603 begin*/
                if($value->category_type!=0){
                    continue;
                }

                $version_name = mb_substr($value->name,4);
                if(!$is_doc){
                    $error['course'] .= "<option value=\"{$value->id}\" data-type=\"selplus\" >{$version_name}</option>"; 
                }else{
                    $error['course'] .= "<option value=\"{$value->id}\">{$version_name}</option>"; 
                }
            }
        }else{
            $error['error_code'] = false;
            $error['error'] = '请选择学科';
        }
        echo json_token($error);die;
    }

    function group_ajax_select(){
        $subject_id = $this->input->get('subject_id');
        $error['groups'] = "<option value=\"0\">请选择分组</option>";
        if (!empty($subject_id)) {
            /*分组*/
            $groups_arr = $this->group->get_list($subject_id,$this->_uid,Constant::QUESTION_GROUP);
            foreach ($groups_arr as $value) {
               $error['groups'] .= "<option value=\"{$value->id}\" >{$value->name}</option>"; 
            }
        }
        $error['error_code'] = true;
        echo json_token($error);die;
    }

    function subject_ajax_select()
    {
        $subject_id = $this->input->get('subject_id');
        if (!empty($subject_id)) {
            /*分组*/
            $groups_arr = $this->group->get_list($subject_id,$this->_uid,Constant::QUESTION_GROUP);
            $error['groups'] = "<option value=\"0\">请选择分组</option>";
            foreach ($groups_arr as $value) {
               $error['groups'] .= "<option value=\"{$value->id}\" >{$value->name}</option>"; 
            }
            /*题型*/
            $qtype_arr = $this->question_type_model->get_subject_question_type($subject_id,false);
            $error['qtype'] = "<option value=\"\" >请选择题型</option>";
            foreach ($qtype_arr as $value) {
               $error['qtype'] .= "<option value=\"{$value->id}\" >{$value->name}</option>"; 
            }
            $category_root_id = $course_root_id = array();
            /*知识点*/
            $category_root_arr=$this->question_category_model->get_root_id($subject_id);
            foreach ($category_root_arr as $key => $value) {
                if($value->type>0){
                    $category_root_id[] = $value;
                }else{
                    $course_root_id[] = $value;
                }
            }
            $categort_root = $category_root_id[0];
            $error['category'] = "<option value=\"\" >请选择版本</option>";
            $error['category'].= "<option value=\"{$categort_root->id}\" data-type=\"selplus\" >{$categort_root->name}</option>"; 
            /*教材同步*/
            //$course_root_id=$this->question_category_model->get_root_id($subject_id);
            $error['course'] = "<option value=\"\" >请选择版本</option>";
            $error['error_code'] = true;
            foreach ($course_root_id as $key => $value) {
                $version_name = mb_substr($value->name,4);
                $error['course'] .= "<option value=\"{$value->id}\" data-type=\"selplus\" >{$version_name}</option>"; 
            }
        }else{
            $error['error_code'] = false;
            $error['error'] = '请选择学科';
        }
        echo json_token($error);die;
    }

    protected function edit_category_band($subject_id,$is_all = true)
    {
        $list = $category_root_id = $course_root_id = array();
        $category_root_arr=$this->question_category_model->get_root_id($subject_id);
        foreach ($category_root_arr as $key => $value) {
            if($value->type>0){
                $category_root_id[] = $value;
            }else{
                $course_root_id[] = $value;
            }
        }
        //$course_root_id=$this->question_category_model->get_root_id($subject_id);
        $list['course'] = "<option value=\"\" >请选择版本</option>";
        foreach ($course_root_id as $key => $value) {
            $version_name = mb_substr($value->name,4);
            $list['course'] .= "<option value=\"{$value->id}\" data-type=\"selplus\" >{$version_name}</option>"; 
        }
        if($is_all){
            //$category_root_id=$this->question_category_model->get_root_id($subject_id);
            $categort_root = $category_root_id[0];
            $list['category'] = "<option value=\"\" >请选择版本</option>";
            $list['category'].= "<option value=\"{$categort_root->id}\" data-type=\"selplus\" >{$categort_root->name}</option>"; 
        }
        return $list;
    }

    function ajax_cate_node()
    {
        $category_node_select=$this->input->get("cnselect");
        $category_node_list=self::_get_category($category_node_select,'category');
        if($category_node_list['errorcode'] == true){
            $error['error'] = '<select class="tree-item" onchange="Teacher.UserCenter.my_question_ajax.treeItemClick(this);">';
            $error['error'] .= '<option value="" >请选择</option>';
            foreach ($category_node_list['category'] as $key => $value) {
                $error['error'].="<option value=\"{$value['id']}\" data-type=\"{$value['is_leaf']}\" >{$value['name']}</option>"; 
            }
            $error['error'] .= '</select>'; 
            $error['error_code'] = true;
        }else{
            $error['error_code'] = false;
            $error['error'] = '请选择版本';
        }
        echo json_token($error);die;
    }

    function ajax_node_select()
    {
        $category_node_select=$this->input->get("cnselect");
        $category_node_list=self::_get_category($category_node_select,'category');
        if($category_node_list['errorcode'] == true){
            $error['error'] = '<select class="tree-item" onchange="Teacher.UserCenter.ajax_select.treeItemClick(this);">';
            $error['error'] .= '<option value="" >请选择</option>';
            foreach ($category_node_list['category'] as $key => $value) {
                $error['error'].="<option value=\"{$value['id']}\" data-type=\"{$value['is_leaf']}\" >{$value['name']}</option>"; 
            }
            $error['error'] .= '</select>'; 
            $error['error_code'] = true;
        }else{
            $error['error_code'] = false;
            $error['error'] = '请选择版本';
        }
        echo json_token($error);die;
    }

    protected function _get_category($category_node_select,$type='course')
    {
        if($category_node_select<=0)
        {
            $category_node_list['errorcode']=false;
        }
        else
        {
           
            $category_list=$this->{'question_'.$type.'_model'}->get_subtree_node($category_node_select);
           
            
            $category_node_list=array();
            $i=0;
            foreach($category_list as $c_l)
            {
                $category_node_list['category'][$i]['id']=$c_l->id;
                $category_node_list['category'][$i]['depth']=$c_l->depth;
                //if($namespace=='course') $category_node_list['category'][$i]['depth']--;
                $category_node_list['category'][$i]['name']=$c_l->name;
                if($c_l->lft==$c_l->rgt-1) $category_node_list['category'][$i]['is_leaf']='selitem';//1;
                else $category_node_list['category'][$i]['is_leaf']='selplus';//0;
                $i++;
            }
            $category_node_list['errorcode']=true;
        }   
        return $category_node_list;
    }

    public function ajax_get_grades()
    {
        $version=$this->input->get("v",true);
        $data=$this->question_category_model->get_subtree_node($version);
        $error['html'] = '<option value="" >请选择年级</option>';
        if($data){
            foreach($data as $key=>$value)
            {
                $error['html'].="<option value=\"{$value->id}\">{$value->name}</option>";
            }
            $error['error_code'] = true;
        }else{
            $error['error_code'] = false;
            $error['error'] = '服务器繁忙请稍后重试！';
        }
        echo json_token($error);die;
    }

    public function ajax_get_nodes()
    {
        $grade=$this->input->get("grade",true);
        $data=array();
        self::_get_category_tree($grade,$data);
        $error['html'] = "<a href=\"javascript:void(0)\" title=\"全部\" data-source=\"node\" class=\"depth_1\" data-nselect=\"{$grade}\">全部</a>";
        if($data){
            foreach($data as $key=>$value)
            {
                $error['html'].="<a href=\"javascript:void(0)\" title=\"{$value['name']}\" data-source=\"node\" class=\"depth_{$value['depth']}\" 
                data-nselect=\"{$value['id']}\">".sub_str($value['name'],0,30)."</a>";
            }
            $error['error_code'] = true;
        }else{
            $error['error_code'] = false;
            $error['error'] = '服务器繁忙请稍后重试！';
        }
        echo json_token($error);die;
    }

    protected function _get_category_tree($node,&$category_tree)
    {
        $category_list=$this->question_category_model->get_node_tree($node);
        $root_depth = $category_list[0]->depth;
        unset($category_list[0]);
        array_values($category_list);
        $i=0;
        foreach($category_list as $c_l)
        { 
            if($c_l->depth-$root_depth>0){
                $category_tree[$i]['id']=$c_l->id;
                $category_tree[$i]['depth']=$c_l->depth-$root_depth;
                $category_tree[$i]['name']=$c_l->name;
                $i++;
            }
        }
    }
}

/* End of file user_document.php */
