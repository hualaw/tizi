<?php

class User_Category_Model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
    }

    function del_all_relation($object_id)
    {
        self::_del_question_category($object_id);
    }

    function make_category_rela($object_id,$category_id,$op_type='add',$type='document',$namespace='course')
    {
        $exception_id = '';
        $this->db->trans_start();
        switch ($type) {
            case 'document':
                if($op_type == 'edit'){
                    self::_del_doc_course($object_id);
                }
                if (is_array($category_id) && !empty($category_id)) {
                    foreach($category_id as $cid => $type) {
                        if($type == 0){
                            $ret = self::_add_document_course($object_id, $cid);
                        }
                        if (!$ret) {
                            $exception_id .= $cid.'-';
                        }
                    }
                }else{
                    $ret = true;
                }
                break;
            case 'question':
                if($op_type == 'edit'){
                    self::_del_question_category($object_id);
                }
                if (is_array($category_id) && !empty($category_id)) {
                    foreach($category_id as $cid => $type) {
                        if($type == 1){
                            $ret = self::_add_question_category($object_id, $cid);
                        }
                        else{
                            $ret = self::_add_question_course($object_id, $cid);
                        }
                        if (!$ret) {
                            $exception_id .= $cid.'-';
                        }
                    }
                }else{
                    $ret = true;
                }
                break;
            default:
                return false;
                break;
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            log_message('Error', "Add object_id: $object_id to category_id: $exception_id");
        }
        return $ret;
    }

    protected function _add_question_category($question_id, $category_id){
        $data = array('question_id'=>$question_id, 'category_id'=>$category_id);
        return $this->db->insert('teacher_question_category', $data);
    }
    protected function _add_question_course($question_id, $course_id){
        $data = array('question_id'=>$question_id, 'category_id'=>$course_id,'type'=>1);
        return $this->db->insert('teacher_question_category', $data);
    }
    protected function _add_document_course($doc_id, $course_id){
        $data = array('doc_id'=>$doc_id, 'course_id'=>$course_id);
        return $this->db->insert('teacher_lesson_course', $data);
    }

    protected function _del_question_category($question_id){
        $this->db->where('question_id', $question_id);
        return $this->db->delete('teacher_question_category');
    }
    
    protected function _del_question_course($question_id){
        $this->db->where('question_id', $question_id);
        return $this->db->delete('teacher_question_category');
    }
    protected function _del_doc_course($doc_id){
        $this->db->where('doc_id', $doc_id);
        return $this->db->delete('teacher_lesson_course');
    }
    
}

/* end of user_category_model.php */
