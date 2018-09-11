<?php

class Lesson_Course_Model extends MY_Model {

	public $_table="lesson_category";
    public function __construct()
    {
        parent::__construct();
    }


   protected function get_doc_course($doc_id)
   {
        $this->db->limit(1);
        $query = $this->db->get_where($this->_table,array('doc_id'=>$doc_id));
        if($query->row())
        {
            return $query->row();
        }
        return false;
   }

   public function get_subject_by_docid($doc_id)
   {
        $category_id = self::get_doc_course($doc_id);
        if($category_id)
        {
            $this->load->model('question/question_category_model');
            $subject_id = $this->question_category_model->get_subject_id($category_id->category_id);
            if($subject_id) return $subject_id;
        }
        return false;
   }

}

/* end of document_type_model.php */
