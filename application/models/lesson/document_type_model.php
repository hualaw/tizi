<?php

class Document_Type_Model extends MY_Model {

	public $_table="lesson_document_type";
    private $_redis=false;

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取所有文档类型信息
     * @return array 
     */
    public function get_type_list()
    {
        $this->db->order_by('order','asc');
        $types_arr = $this->db->get_where($this->_table,array('is_use'=>1))->result();
        return $types_arr;
    }

}

/* end of document_type_model.php */
