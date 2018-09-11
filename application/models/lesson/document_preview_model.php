<?php

class Document_Preview_Model extends MY_Model {

	public $_table="lesson_preview_doc";
    private $_redis=false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据源文件ID获取被分割后的文件
     * @param  int $doc_id 源文件ID
     * @return type 
     */
    public function get_split_files($doc_id)
    {
        $query = $this->db->get_where($this->_table, array('doc_id' => $doc_id));
        return $query->result();
    }
}

/* end of document_preview_model.php */
