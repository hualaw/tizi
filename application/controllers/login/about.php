<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class About extends MY_Controller {

    function __construct()
    {	

        parent::__construct();

    }

	public function agreements()
	{	

		$this->smarty->display('header/agreements.html');
	}

	public function show_about($tpl)
	{
		$about = array('partner','us','contact','agreements','correction','user','school','wish','guide','information','notice','feedback');
		if(in_array($tpl,$about))
		{
			$this->smarty->display("about/$tpl.html");
		}
		//错误短信
		else if(strpos($tpl,'wish') !== false)
		{
			redirect('about/wish');
		}
		else
		{
			tizi_404('about/wish');
		}
	}

	public function download_school()
	{
		$file_url=dirname(__FILE__).'/tizi_school.xlsx';
		$content=file_get_contents($file_url);
		$sname='校园团体账户申请表.xlsx';
		$this->load->helper('download');
		if(stripos($this->input->server('HTTP_USER_AGENT'),'windows'))
		{
			force_download(iconv('utf-8', 'gbk//IGNORE', $sname), $content);
		}
		else
		{
			force_download($sname, $content);
		}
	}
	
	public function report($page = 1){
		$catid = 6;
		$pagesize = 10;
		$offset = ($page - 1) * $pagesize;
		$this->load->model("news/v9_news_model");
		$total = $this->v9_news_model->count($catid);
		$news = $this->v9_news_model->listget($catid, $offset, $pagesize, 
			"title,thumb,copyfrom,inputtime,description,islink,url,reports","listorder DESC", $this->_is_preview());
		foreach ($news as $key => $value){
			$news[$key]["copyfrom"] = explode("|", $value["copyfrom"]);
			$news[$key]["reports"] = json_decode($value["reports"], true);
		}
		$pageconf = array(
			"uri_segment" => 3, 
			"page_query_string" => false, 
			"base_url" => site_url()."about/report/"
		);
		$pages = $this->get_pagination($page, $total, "reportGo", $pageconf);
		$this->smarty->assign("news", $news);
		$this->smarty->assign("pages", $pages);
		$this->smarty->display("about/report.html");
	}
	
	public function witness(){
		$catid = 7;
		$offset = 0;
		$pagesize = 50;
		$this->load->model("news/v9_news_model");
		$witness = $this->v9_news_model->listget($catid, $offset, $pagesize, 
			"title,thumb,copyfrom,inputtime,description,islink,url,reports,content", "listorder DESC",$this->_is_preview());
		
		$this->smarty->assign("witness", $witness);
		$this->smarty->display("about/witness.html");
	}
	
	public function join(){
		$catid = 8;
		$offset = 0;
		$pagesize = 50;
		$this->load->model("news/v9_news_model");
		$resume = $this->v9_news_model->listget($catid, $offset, $pagesize, 
			"title,content",'listorder DESC',$this->_is_preview());
		$this->smarty->assign("resume", $resume);
		$this->smarty->display("about/join.html");
	}
	
    
	public function advisor(){
		$catid = 15;
		$offset = 0;
		$pagesize = 50;
		$this->load->model("news/v9_news_model");
		$advisor = $this->v9_news_model->listget($catid, $offset, $pagesize, 
			"title,thumb,copyfrom,inputtime,description,islink,url,reports,content","listorder DESC",$this->_is_preview());
		
        // print_r($advisor);
		$this->smarty->assign("advisor", $advisor);
		$this->smarty->display("about/advisor.html");
	}
	
	public function newsdetails($id){
		$id = intval($id);
		$this->load->model("news/v9_news_model");
		$news = $this->v9_news_model->get($id);
		if ($news === null || !in_array($news["catid"], array(19,20))){
			tizi_404();
		}
		$_copyfrom = explode("|", $news["copyfrom"]);
		$news["copyfrom"] = $_copyfrom[0];
		
		if ($news["catid"] == 19){
			$catname = "梯子公告";
		} else {
			$catname = "教师资讯";
		}
		
		$this->smarty->assign("news", $news);
		$this->smarty->assign("catname", $catname);
		$this->smarty->display("about/newsdetails.html");
	}
	
	public function newslist($catid, $page){
		$catid = intval($catid);
		$page = intval($page) > 0 ? intval($page) : 1;
		if (!in_array($catid, array(19, 20))){
			tizi_404();
		}
		$this->load->model("news/v9_news_model");
		$pagesize = 10;
		$offset = ($page - 1) * $pagesize;
		$newslist = $this->v9_news_model->listget($catid, $offset, $pagesize);
		if (empty($newslist)){
			tizi_404();
		}
		foreach ($newslist as $key => $value){
			$_copyfrom = explode("|", $value["copyfrom"]);
			$newslist[$key]["copyfrom"] = $_copyfrom[0];
		}
		if ($catid == 19){
			$catname = "梯子公告";
		} else {
			$catname = "教师资讯";
		}
		
		$total = $this->v9_news_model->count($catid);
		$pageconf = array(
			"uri_segment" => 4, 
			"page_query_string" => false,
			"base_url" => site_url()."about/newslist/".$catid."/"
		);
		$pages = $this->get_pagination($page, $total, "newslistGo", $pageconf);
		$this->smarty->assign("newslist", $newslist);
		$this->smarty->assign("pages", $pages);
		$this->smarty->assign("catid", $catid);
		$this->smarty->assign("catname", $catname);
		$this->smarty->display("about/newslist.html");
	}
    
	
	
	
	/*
		读取友情链接内容->v9_news_model.php->links
		author:zhangxiaoming 
		date:2014-04-21
	*/
	public function links(){
		$this->load->model("news/v9_news_model");
		$links = $this -> v9_news_model ->get_links();
		$this->smarty->assign("links", $links);
	}
	
	
	public function sitemap(){
		$this->smarty->display("about/sitemap.html");
	}
	// 内页友情链接
	public function link(){
		$this->load->model("news/v9_news_model");
		$links = $this->v9_news_model->get_links("10,500");
		$this->smarty->assign("links", $links);
		$this->smarty->display("about/link.html");
	}
	
	private function get_pagination($page_num,$total,$func,$conf=array())
    {
        $config['total_rows']       = $total; //为页总数
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        //$config['params']           = array('b'=>'"123"','a'=>'1');
        
        if(is_array($conf)) $config=array_merge($config,$conf);

        $this->load->library('pagination'); 
        //获取分页
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_links();
        return $pages;
    }
    private function _is_preview()
    {
    	if(isset($_GET['preview']) && isset($_GET['token']))
        {
            $token = $this->input->get('token');
            $sum = substr($token, 0,8);
            $time = substr($token, 8);
            $token_my = substr(md5($time . "tiziwang"),0,8);
            if ($token_my == $sum) {
                $flag  =  true;
            }else{
                $flag  =  false;
            }
        }else{
            $flag  =  false;
        }
        return $flag;
    }
}
/* End of file about.php */
/* Location: ./application/controllers/login/about.php */
