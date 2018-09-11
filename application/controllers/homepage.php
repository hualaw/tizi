<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('Controller.php');

class Homepage extends Controller {

    private $_smarty_dir="homepage/";

    function __construct()
    {
    	echo "start homepage construct<br>";
        parent::__construct();
        exit("end homepage construct");
    }
    function index()
    {
        @header("Expires: 0");
        @header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");        

		//$login_type=$this->input->cookie('_ln_type');
		//$login_type=$login_type>=1&&$login_type<=2?$login_type:2;

        $template=$this->_smarty_dir."homepage.html";
        $cache_id=$this->tizi_uid?"www_ln":"www_la";
        if(!$this->smarty->isCached($template, $cache_id))
        {
			$this->load_statistics();// *所中小学 *位老师 *名学生
	        $this->load_reports();	// 媒体报导推荐位
			$this->load_witness();	// 用户评价推荐位，4个
			$this->load_thankuser();// 获取致谢用户4个
			$this->load_spacenews();//获取space news
			$this->load_tizinews(); // 获取梯子网公告
			//$this->load_links();	//友情链接
			$tizi_uid = Constant::SPACE_SUBSCRIBE_ACCOUNT;
			$this->load_tizi_space_article($tizi_uid,1);	//友情链接
			
			$this->load_jia();
			$this->load_fls();
			$this->load_practice();

			//$this->smarty->assign('login_type',$login_type);
		}

		$this->smarty->assign('login_redirect','homepage');
		$this->smarty->display($this->_smarty_dir."homepage.html",$cache_id);
    }
    
    private function load_statistics(){
        $statistics=$this->get_statistics();
		$this->smarty->assign("statistics", $statistics);
	}
	
	/*
		读取友情链接内容->v9_news_model.php->links
		author:zhangxiaoming 
		date:2014-04-24
	*/
	private function load_links($limit="0,10"){
		$this->load->model("news/v9_news_model");
		$links = $this->v9_news_model->get_links($limit);
		$this->smarty->assign("links", $links);
	}
	
	private function load_reports(){
		$this->load->model("news/v9_position_model");
		$posid = 18;		//推荐号，一般情况下永久保持不变
        $reports = $this->v9_position_model->get($posid, 0, 4);
        foreach ($reports as $key => $value){
			$reports[$key]["data"] = json_decode($value["data"], true);
			$_copyfrom = explode("|", $reports[$key]["data"]["copyfrom"]);
			$reports[$key]["copyfrom"] = $_copyfrom[0];
		}
		$this->smarty->assign("reports", $reports);
	}
	
	private function load_witness(){
		$this->load->model("news/v9_position_model");
		$posid = 19;		//推荐号，一般情况下永久保持不变
		$witness = $this->v9_position_model->get($posid, 0, 4);
		$this->load->helper("teacher_data");
		foreach ($witness as $key => $value){
			$witness[$key]["data"] = json_decode($value["data"], true);
		}
		$this->smarty->assign("witness", $witness);
	}
	
	private function load_thankuser(){
		$this->load->model("news/v9_news_model");
		$catid = 18;		//分类id
		$thankuser = $this->v9_news_model->listget(18, 0, 4, "id,title,copyfrom");
		foreach ($thankuser as $key => $value){
			$_copyfrom = explode("|", $value["copyfrom"]);
			$thankuser[$key]["address"] = $_copyfrom[0];
		}
		$this->smarty->assign("thankuser", $thankuser);
	}
	
	private function load_spacenews(){
		$this->load->model("news/v9_news_model");
		$catid = 19;	//分类id
		$tizinews = $this->v9_news_model->listget(19, 0, 1, "id,title");
		$this->smarty->assign("tizinews", $tizinews);
	}
	
	private function load_tizi_space_article($tizi_uid,$limit=1){
		if(!$tizi_uid){
			$this->smarty->assign("tizi_space_article", "");
		}
		
		//根据uid获取空间id
		$this->load->model("space/space_user_model");
		
		$user_info = $this -> space_user_model -> get_user_data_by_user_id($tizi_uid);
		if(!$user_info->space_id){
			$this->smarty->assign("tizi_space_article", "");
		}
		
		$this->load->model("news/v9_news_model");
		$tizi_space_article = $this->v9_news_model->tizi_space_article($user_info->space_id,$limit);
		$this->smarty->assign("tizi_space_article", $tizi_space_article);
	}
	
	private function load_jia(){
		$this->load->model("news/v9_news_model");
		$catid = 14;	//分类id
		$jia = $this->v9_news_model->listget($catid, 0, 3, "id,title", "inputtime DESC");
		$jia_ex = $this->v9_news_model->get_article_list("全国", 0, 2);
		$jia = array_merge($jia, $jia_ex);
		$this->smarty->assign("jia", $jia);
	}
	
	private function load_fls(){
		$this->load->config("qiniu");
		$fls_domain = $this->config->item("fls_domain");
		$this->load->model("video/video_info_model");
    	$fls = $this->video_info_model->homepage_video();
    	$this->smarty->assign("fls", $fls);
    	$this->smarty->assign("fls_domain", $fls_domain);
	}
	
	private function load_practice(){
		$this->load->model("practice/practice_statistics_model");
		$practice = $this->practice_statistics_model->homepage_practice();
		unset($practice["last_update"]);
		$this->smarty->assign("practice", $practice);
	}
	
	private function load_tizinews(){
		$this->load->model("news/v9_news_model");
		$catid = 19;	//分类id
		$announce = $this->v9_news_model->listget(19, 0, 2, "id,title");
		$this->smarty->assign("tizi_announce", $announce);
	}

	public function teacher()
	{
		redirect(redirect_url(Constant::USER_TYPE_TEACHER,'tizi'));
	}

	public function student()
	{
		redirect(redirect_url(Constant::USER_TYPE_STUDENT,'tizi'));
	}

	public function parent()
	{
		redirect(redirect_url(Constant::USER_TYPE_PARENT,'tizi'));
	}

}