<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'user/user_upload_controller.php'); 
class Lesson_Cloud extends User_Upload_Controller {
	
	private $_smarty_dir="teacher/lesson/";
	private $_user_id=0;
	private $_user_type=0;
    private $_lesson_doc_key='';
    private $_redis=false;
    public function __construct()
    {
        parent::__construct();
        $this->_user_id=$this->session->userdata('user_id');
        $this->_user_type=$this->session->userdata('user_type');
        $this->load->model('login/register_model');
        $this->load->model('redis/redis_model');
        $this->load->model('question/question_category_model');
        $this->load->model("lesson/document_type_model");
        $this->load->model('question/question_subject_model');
    }

	public function file_upload_index($subject_id=null,$nselect=null,$doc_type=0)
    {
        // if($this->_user_id && $this->_user_type!=Constant::USER_TYPE_TEACHER)
        // {
        //     $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
        //     redirect('');
        // }
        if(is_null($subject_id)||is_null($nselect)||is_null($doc_type)){
            /*文档类型列表*/
            $data['type_list']=Constant::resource_type(); 
            $this->smarty->assign('data1',$data);
        }else{
            /*学科列表*/
            $data['subject_id'] = $subject_id;
            $data['subject_list'] = $this->question_subject_model->get_subjects_by_sid($subject_id);
            $subject_msg = $this->question_subject_model->get_grade_by_subject($subject_id);
            /*当前年级*/
            $data['grade_id'] = $subject_msg->grade;
            /*文档类型列表*/
            $data['type_list']=Constant::resource_type();
            $data['current_file_type'] = $doc_type!=0?$doc_type:'';
            /*版本列表*/
            $data['version_list'] = array();
            $category_root_arr=$this->question_category_model->get_root_id($subject_id);
            foreach ($category_root_arr as $key => $value) {
                if($value->type==0){
                    $data['version_list'][] = $value;
                }
            }
            /*当前版本和当前年级*/
            $data['version_id'] = $data['stage_id'] = 0;
            $single_path=$this->question_category_model->get_single_path($nselect,'name,parent.id,parent.depth');
            if($single_path){
                foreach ($single_path as $val) {
                    if($val->depth==1){$data['version_id']=$val->id;continue;}
                    if($val->depth==2){$data['stage_id']=$val->id;continue;}
                    if($data['version_id']>0 and $data['stage_id']>0) break;
                }
            }
            /*年级列表*/
            $data['stage_list'] = $this->question_category_model->get_subtree_node($data['version_id']);
            /*章节列表*/
            $data['node_list'] = array();
            $data['node_id'] = $nselect;
            parent::_get_category_tree($data['stage_id'],$data['node_list']);
            /*输出*/
            $this->smarty->assign('data1',$data);
        }
        
        $this->smarty->display($this->_smarty_dir.'lesson_file_upload.html');
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

    public function complete_upload()
    {
        if($this->_user_type!=Constant::USER_TYPE_TEACHER)
        {
            $response = array('error_code'=>false, 'error'=>$this->lang->line('error_user_type_teacher'));
            echo json_token($response); die;
        }
        $data['dir_cat_id'] = $this->input->post('stage',true);
        $data['sub_cat_id'] = $this->input->post('to_dir_id',true);
        $data['resource_type'] = $this->input->post('type',true);
        $data['is_share_to_tizi'] = $this->input->post('share',true);
        $error_code['is_share'] = intval($data['is_share_to_tizi'])>0?true:false;
        //$error_code['category_id'] = &$data['sub_cat_id'];
        $subject_id = $this->input->post('subject_type',true);
        /*生成我的文件跳转链接*/
        $error_code['mine_file_url'] = tizi_url('teacher/lesson/prepare/mine');
        if(isset($subject_id)&&isset($data['dir_cat_id'])&&intval($data['dir_cat_id'])>0&&intval($subject_id)>0){
            $error_code['mine_file_url'] .= "/{$subject_id}/{$data['dir_cat_id']}";
        }
        if($data['dir_cat_id']!=$data['sub_cat_id']){
            $error_code['mine_file_url'] .= "?id={$data['sub_cat_id']}";
        }

        $file_ids = $this->input->post('new_file_id',true);
        if(!isset($file_ids) || empty($file_ids)){
            $error_code['error_code'] = false;
            $error_code['error'] = '请确认文件上传完成后再提交！';
        }else{
            $this->load->model('cloud/cloud_model');
            $this->load->model('space/space_user_model');
            /*更新文件信息文件修改为待审核*/
            $file_ids_arr = explode(',', trim($file_ids,','));
            $rel = $this->cloud_model->update_file_table($data,$file_ids_arr,true);
            /*开通空间*/
            if(intval($data['is_share_to_tizi']))$this->space_user_model->open_space_by_user_id($this->_user_id);
            if($rel){
                $error_code['error_code'] = true;
            }
        }
        echo json_token($error_code);die;
    }
	
}
	
/* End of file lesson_cloud.php */
/* Location: ./application/controllers/lesson/lesson_cloud.php */

