<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Share_To_Tizi_Model extends MY_Model {
    private $_table = 'share_to_tizi';
    private $_file_table = 'cloud_user_file';
    private $_lesson_document = 'lesson_document';
    private $_lesson_category = 'lesson_category';
    public function __construct(){
        parent::__construct();
    }
    
    function insert($param){
        $res =  $this->db->insert($this->_table,$param);
        if($res){//插入表成功，更新cloud_user_file表中的is_share_to_tizi字段
            $this->load->model('cloud/cloud_model');
            $where['user_id'] = $param['user_id'];
            $where['id'] = $param['file_id'];
            $where['is_del'] = 0;
            $data['is_share_to_tizi'] = 1;
            return $this->cloud_model->update_file_table($data,$where);
        }
        return false;
    }

    /*共享转移到备课正式表中*/
    public function user_file_online($file_id)
    {
        if(!$file_id) return false;
        $file_info = $this->db->get_where($this->_file_table,array(
            'id' =>$file_id,
            'is_del'=>0,
            'is_share_to_tizi'=>2))->row();
        $file_preview_info = $this->db->get_where('cloud_document_preview',array('doc_id'=>$file_id))->row();
        if($file_info){
            $insert_data = array(
                'doc_type'=>$file_info->file_type,
                'user_id'=>$file_info->user_id,
                'file_name'=>$file_info->file_name,
                'file_ext'=>$file_info->file_ext,
                'file_size'=>$file_info->file_size,
                'file_path'=>$file_info->file_path,
                'page_count'=>$file_preview_info?$file_preview_info->page_count:0,
                'upload_ip'=>$file_info->upload_ip,
                'upload_time'=>time(),
                'status'=>1
                );
            $this->db->insert($this->_lesson_document,$insert_data);
            $new_id = $this->db->insert_id();
            if(!$new_id) return false;
            if($file_preview_info && $new_id){
                $this->db->insert('lesson_preview_doc_new',array(
                    'doc_id'=>$new_id,
                    'swf_folder_path'=>$file_preview_info->swf_folder_path,
                    'page_count'=>$file_preview_info->page_count
                    ));
            }

            if($file_info->dir_cat_id || $file_info->sub_cat_id){
                $rela_data['category_id'] = $file_info->dir_cat_id?$file_info->dir_cat_id:$file_info->sub_cat_id;
                $rela_data['doc_id']=$new_id;
                $this->db->insert('lesson_category',$rela_data);
            }
            return array($new_id,$file_info->user_id);
        }
        return false;

    }
}