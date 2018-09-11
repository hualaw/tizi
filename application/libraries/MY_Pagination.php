<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Pagination extends LI_Pagination {

	var $per_page	= Constant::DEFAULT_PER_PAGE;
	var $page_limit	= Constant::DEFAULT_PAGE_LIMIT;

	public function __construct($params = array())
	{
		parent::__construct($params);
	}
	
}
// END Pagination Class

/* End of file MY_Pagination.php */
/* Location: ./application/libraries/MY_Pagination.php */