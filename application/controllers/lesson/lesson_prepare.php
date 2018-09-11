<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lesson_Prepare extends MY_Controller {

    private $_smarty_dir = 'teacher/lesson/';
    private $_islogin = true;
    public function __construct()
    {
        parent::__construct();
        $this->_user_id=$this->session->userdata('user_id');
        $this->_user_type=$this->session->userdata('user_type');
        $this->load->model('lesson/document_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('question/question_category_model');
        $this->load->model("lesson/document_type_model");
        $this->load->model("login/register_model");
        $this->load->library('search');
        $this->load->config('search');
        $this->load->model('redis/redis_model');
        $this->load->model('space/space_user_model');
        $this->load->model('resource/res_file_model');
        
        if(!$this->_user_id) $this->_islogin = false;
        $this->smarty->assign('is_login',$this->_islogin);
    }

    public function index($subject_id=0,$category_select=0)
    {
        $template=$this->_smarty_dir.'lesson_prepare.html';
        $cache_id="beike_s".$subject_id."_c".$category_select;
        $data = array();

        /*公共数据 no database,no cache*/
     	self::_init_param($subject_id,$category_select,$data);

     	/*判断是读缓存还是数据库*/
        if(!$this->smarty->isCached($template,$cache_id))
        {	
            self::_init_data($data);/*涉及较多数据库查询*/
            $this->smarty->assign('sj_url',site_url().'teacher/lesson/prepare');
        }
        $param = $this->input->get('id',true);
        $this->smarty->assign('param',$param?"?id=$param":'');
        $this->smarty->assign('focusid',$param?$param:'');     
        $this->smarty->assign('tab_name','public');//贡献文件tab
        $this->smarty->assign('data',$data);
        $this->smarty->display($template, $cache_id);

    }

    /*初始化公共参数*/
    protected function _init_param($subject_id,$category_select,&$data)
    {
    	/*http get and params initalize*/
    	$data['subject_id']=$this->input->get('sid',true);
        $data['category_select']=$this->input->get("cselect",true);
        $data['doctype']=$this->input->get('doctype',true);
        /*$data['node_select']=$this->input->get("nselect",true);*/
        $data['node_id'] = $this->input->get("id",true);

        /*初始化并检测学科*/
        if(!$data['subject_id']) $data['subject_id']=$subject_id;
        if(!$data['subject_id']) $data['subject_id']=$this->register_model->my_subject($this->_user_id,'doc');
        if(!$this->question_subject_model->check_subject($data['subject_id'],'lesson')) $data['subject_id']=Constant::DEFAULT_SUBJECT_ID;
       	
       	/*设置学科cookies*/
        $this->register_model->set_favorate_subject($data['subject_id'],'doc');
        $data['subject_name']=$this->question_subject_model->get_subject_name($data['subject_id']);
        
        if(!$data['category_select']) $data['category_select']=$category_select;
        if($data['doctype']=="") $data['doctype']=0;
    }

    /*共享文件 和 我的文件 ， 共同调用的获取目录树的方法*/
    protected function _init_data(&$data){
  
        /*get document type*/
        $data['type_list']=$this->document_type_model->get_type_list();

        /*get category depth 1*/
        $data['category_root_id']=$this->question_category_model->get_root_id($data['subject_id']);
        foreach ($data['category_root_id'] as $key => &$value) 
        {
            if($value->type > 0) unset($data['category_root_id'][$key]);
            /*临时增加过滤20140603 begin*/
            if($value->category_type!=0)unset($data['category_root_id'][$key]);
            /*临时增加过滤20140603 end*/
        }
        $data['category_root_id'] = array_values($data['category_root_id']);
        if($data['category_select'])
        {
            $select_parent_id=$this->question_category_model->get_parent_id($data['category_select']);
            if($select_parent_id) $category_root_select=$select_parent_id;
            else $category_root_select=$data['category_select'];
        }   
        else
        {
            $data['category_select']=$category_root_select=$data['category_root_id'][0]->id;
        }

        $data['category_root_select']=$category_root_select;
        $check_second_root=false;
        foreach($data['category_root_id'] as $key=>$c_r_id)
        {
            if(strpos($c_r_id->name,'信息技术') !== false) $sub_length = 6;
            else $sub_length = 4;
            $data['category_root_id'][$key]->name=mb_substr($c_r_id->name,$sub_length);
            if($category_root_select==$c_r_id->id)
            {
                $data['category_root_name']=$c_r_id->name;
                $check_second_root=true;
            }
        }   
        if(!isset($data['category_root_name']))
        {
            $data['category_root_name']=$data['category_root_id'][0]->name;
        }

        /*get category depth 2 */
        $data['category_second_root_id']=array();
        $data['category_second_root_name']='';
        if($check_second_root)
        {
            $data['category_second_root_id']=$this->question_category_model->get_subtree_node($category_root_select);
            if(!empty($data['category_second_root_id']))
            {
                if($data['category_select']==$category_root_select) $data['category_select']=$data['category_second_root_id'][0]->id;

                foreach($data['category_second_root_id'] as $key=>$c_s_r_id)
                {
                    $data['category_second_root_id'][$key]->name=$c_s_r_id->name;
                    if($data['category_select']==$c_s_r_id->id)
                    {
                        $data['category_second_root_name']=$c_s_r_id->name;
                    }
                }
                if(!isset($data['category_second_root_name']))
                {
                    $data['category_second_root_name']=$data['category_second_root_id'][0]->name;
                }
            }
        }

        /*当前学科，年级信息*/
        $data['current_subjects'] = $this->question_subject_model->get_subjects_by_sid($data['subject_id']);
        if($data['current_subjects']) $data['current_grade'] = $data['current_subjects'][0]->grade;
        foreach ($data['current_subjects'] as &$value) 
        {
            $value->name = mb_substr($value->name, 2);
        }

        /*同步章节结构树信息*/
        $data['category_tree'] = array();
        self::_get_category_tree($data['category_select'],$data['category_tree']);
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
                //if('course') $category_tree['category'][$i]['depth']--;
                $category_tree[$i]['name']=$c_l->name;
                if($c_l->lft==$c_l->rgt-1) $category_tree[$i]['is_leaf']=1;
                else $category_tree[$i]['is_leaf']=0;
                $i++;
            }
        }
        //print_r($category_list_r);die;
    }

    public function get_category()
    {
        $category_node_select=$this->input->get("cnselect");    
        if($category_node_select<=0)
        {
            $category_node_list['errorcode']=false;
            $category_node_list['error']=$this->lang->line("error_get_category");
        }
        else
        {
            $category_list=$this->question_category_model->get_subtree_node($category_node_select);
            $category_node_list=array();
            $i=0;

            foreach($category_list as $c_l)
            {
                $category_node_list['category'][$i]['id']=$c_l->id;
                $category_node_list['category'][$i]['depth']=$c_l->depth;
                if('course') $category_node_list['category'][$i]['depth']--;
                $category_node_list['category'][$i]['name']=$c_l->name;
                if($c_l->lft==$c_l->rgt-1) $category_node_list['category'][$i]['is_leaf']=1;
                else $category_node_list['category'][$i]['is_leaf']=0;
                $i++;

            }
            $category_node_list['errorcode']=true;
        }   
        echo json_encode($category_node_list);
        exit();
    }

    public function get_document()
    {
        $doc_type=$this->input->get("doctype");
        $node_select=$this->input->get("nselect");
        $cate_select=$this->input->get("cselect");
        $page_select=$this->input->get("page");
        $subject_id=$this->input->get("sid");
        $order_id = $this->input->get('order'); 
        $get_order = $this->input->get("otype");
        $is_desc = $get_order=='1'?'desc':'asc';
        switch ($order_id) {
            case '0':
                $order_type = 'upload_time';
                break;
            case '1':
                $order_type = 'page_count';
                break;
            case '2':
                $order_type = 'file_size';
                break;      
            default:
                $order_type = 'upload_time';
                break;
        }
        //$order_type = !$order_type ? 'upload_time' : 'page_count' ;     
        $documents = array();
        if($page_select<=0) $page_select=1;
        if($doc_type<0) $doc_type=0;
        if($page_select>Constant::DOC_PAGES_LIMIT)
        {
            exit();
        }
        if($node_select<=0)
        {
            $data = array('document'=>array(),'total'=>0);
            $documents['errorcode']=true;
            $this->smarty->assign('pages','');
            $this->smarty->assign('data',$data);
            $documents['html']=$this->smarty->fetch($this->_smarty_dir.'lesson_prepare_tpl.html');
        }
        else
        {
            $category_list=$this->question_category_model->get_node_tree($node_select);
            $category_id_list=array();
            foreach($category_list as $c_l)
            {
                $category_id_list[]=$c_l->id;
            }
            $breadcrumb=$this->question_category_model->get_single_path($node_select,'id,parent.name');        
            $i=0;
            $url_param = array();
            foreach($breadcrumb as $b)
            {
                $documents['breadcrumb'][$i]=$b->name;
                $url_param[$i]=$b->id;
                $i++;
            }
            // print_r($breadcrumb);die;
            /*from search data*/
            $document_list = false;
            if($this->config->item('solr_search_lesson'))
            {
                $new_doc_type = intval($doc_type)==3?array(3,5,7):$doc_type;
                $this->load->library('Search');
                $search_data = $this->search->init('lesson')->search(array(
                    'category_id' => $node_select,
                    'subject_id'=> $subject_id,
                    'doc_type'  => $new_doc_type),
                    $page_select,Constant::LESSON_PER_PAGE,$order_type.' '.$is_desc);
                $document_list = isset($search_data['result'])?$search_data['result']:false;    
                if($document_list !== false)
                {   
                    $data['total']       = $search_data['total'];
                    $data['page_total']  = ceil($data['total']/Constant::LESSON_PER_PAGE);
                    $data['page_num']    = $page_select;
                    $pages =  self::get_pagination($data['page_num'],$data['total'],'lessonPage');
                }
            }
            /*from database*/
            if($document_list === false)
            {
                //get pagination
                $data['total']       = $this->document_model->get_docs_list($category_id_list,$page_select,$doc_type,$subject_id,true);
                $data['page_total']  = ceil($data['total']/Constant::LESSON_PER_PAGE);
                $data['page_num']    = $page_select;
                $pages =  self::get_pagination($data['page_num'],$data['total'],'lessonPage');
                //get question
                $document_list=$this->document_model->get_docs_list($category_id_list,$page_select,$doc_type,$subject_id,false,$order_type,$is_desc);
            }
            $i=0;
            foreach($document_list as $key=>$q_l)
            {
                /*获取用户空间信息*/
                $space_info = false;
                if($q_l->user_id>0)
                $space_info = $this->space_user_model->open_space_by_user_id($q_l->user_id);
                $documents['document'][$i]['owner'] = $space_info['space_data'];
                /*格式化文档数据信息*/
                $documents['document'][$i]['file_id']=$q_l->id;
                $documents['document'][$i]['id']=alpha_id($q_l->id);
                $documents['document'][$i]['page_count']=$q_l->page_count;
                $documents['document'][$i]['file_name']=$q_l->file_name;
                $documents['document'][$i]['file_ext']=self::_get_file_type($q_l->file_ext);
                $documents['document'][$i]['extension']=$q_l->file_ext;
                if($q_l->user_id>0){
                    $documents['document'][$i]['hits']=$q_l->hits;
                }else{$documents['document'][$i]['hits']=($q_l->rand_score*10)>48?$q_l->rand_num+$q_l->hits+1200:$q_l->rand_num+$q_l->hits+200;}
                
                $documents['document'][$i]['upload_time']=date("Y-m-d",$q_l->upload_time);
                $documents['document'][$i]['file_size']=self::prase_file_size($q_l->file_size);
                //$documents['document'][$i]['downloads']= $q_l->downloads>0?$q_l->rand_num+$q_l->downloads:$q_l->downloads;
                if($q_l->user_id>0){
                    $documents['document'][$i]['downloads']=$q_l->downloads;
                }else{$documents['document'][$i]['downloads']= ($q_l->rand_score*10)>48?$q_l->rand_num+$q_l->downloads+1000:$q_l->rand_num+$q_l->downloads+100;}

                $assess_info = $this->document_model->get_doc_assess_log($q_l->id,$q_l->score);
                $rand_count = $q_l->rand_num;
                if($rand_count>=100)$rand_count = $rand_count-80;
                if($rand_count>=50 and $rand_count<100)$rand_count = $rand_count-30;
                if($rand_count>=20 and $rand_count<50)$rand_count = $rand_count-10;
                $documents['document'][$i]['assess_count']= $assess_info['count']+$rand_count;
                $assess_avg_score = 0.0;
                //if($assess_info['count']>0){
                    $assess_avg_score = (double)round(($assess_info['score']+$q_l->rand_score)/($assess_info['count']+1),1);
                //}
                $star_level = array(1,1,1,1,1);
                self::prase_star_level($assess_avg_score,$star_level);
                $documents['document'][$i]['star_level'] = $star_level;
                $i++;
            }
            $data['order_sel']=$order_id;
            $data['order_desc']=$get_order;
            $data['document']=&$documents['document'];
            $documents['errorcode']=true;
            $documents['total_num']=$data['total'];
            if(!$this->input->get("focus_id",true)){
                $documents['tab_url']=$subject_id.'/'.$cate_select;
                if($cate_select!=$node_select)$documents['tab_url'].='?id='.$node_select;
            }
            $cloud_doc_type = Constant::new_doc_type(0,true);
            if($doc_type>0)$doc_type = end(array_keys($cloud_doc_type,$doc_type));
            $this->smarty->assign('pages',$pages);
            $this->smarty->assign('data',$data);
            $this->smarty->assign('nselect',$node_select);
            $this->smarty->assign('doc_type',$doc_type);
            $this->smarty->assign('sid',$subject_id);
            $documents['html']=$this->smarty->fetch($this->_smarty_dir.'lesson_prepare_tpl.html');
        }
        unset($documents['document']);
        echo json_encode($documents);
        exit();
    }

    protected function prase_star_level($assess_avg_score,&$star_level_arr)
    {
        $assess_avg_score = $assess_avg_score>5.0?5.0:$assess_avg_score;
        $int_val = floor($assess_avg_score);
        for ($i=0; $i < $int_val; $i++) { 
            $star_level_arr[$i]=3;
        }
        if($int_val!=$assess_avg_score){
            $star_level_arr[$int_val] = 2;
        }
    }

    public function assess_star()
    {
        $score = $this->input->get('score');
        $doc_id = $this->input->get('doc_id');
        if($score and $doc_id){
            $doc_id = alpha_id($doc_id,true);
            $status = $this->document_model->set_doc_assess_log($this->_user_id,$doc_id,intval($score));
        }else{
            $status = -3;
        }   
        $errorcode = array();
        switch ($status) {
            case -1:
                $errorcode['error_code'] = false;
                $errorcode['error']="您已经评价过该文档!";
                break;
            case -2:
                $errorcode['error_code'] = false;
                $errorcode['error']="天哪，评价失败了!";
                break;
            case -3:
                $errorcode['error_code'] = false;
                $errorcode['error']="无效的评星!";
                break;    
            default:
                $errorcode['error_code'] = true;
                $errorcode['error']="评价成功!";
                break;
        }
        echo json_encode($errorcode);die;
    }

    public function preview($doc_id = NULL)
    {
        if(!is_null($doc_id))
        {
            $doc_id = alpha_id($doc_id,true);
            $data = $this->document_model->get_single_doc_info($doc_id);
            $course_list = $this->question_category_model->get_single_path($data->category_id,'*');
            if(empty($data))
            {
                tizi_404('teacher/lesson/prepare');
                exit;
            }
            $ico_class = array(1=>'ico_doc',2=>'ico_pic',3=>'ico_video',4=>'ico_audio',5=>'ico_other');
            $unable_view = false;
            $status = $this->document_model->update_statistics($doc_id, 'hits');
            $data->upload_time = date("Y-m-d",$data->upload_time);
            $data->file_size = self::prase_file_size($data->file_size);
            $data->id = alpha_id(intval($data->id));
            $data->extension = $data->file_ext;
            $data->file_ext = Constant::document_icon($data->file_ext);
            if($data->user_id<=0){
                $data->hits=($data->rand_score*10)>48?$data->rand_num+$data->hits+1200:$data->rand_num+$data->hits+200;
                $data->downloads = ($data->rand_score*10)>48?$data->rand_num+$data->downloads+1000:$data->rand_num+$data->downloads+100;
            }   

            /*获取用户空间信息*/
            $space_info = false;
            if($data->user_id>0)
            $space_info = $this->space_user_model->open_space_by_user_id($data->user_id);
            $data->owner = $space_info['space_data'];
            /*获取用户空间信息-end*/
            $data->type = Constant::CLOUD_FILETYPE_OTHER;
            if(!$data->extension){
                  $unable_view = true;  
            }
            elseif(strpos(Constant::CLOUD_DOC_TYPES,$data->extension)!==false){
                $data->type = Constant::CLOUD_FILETYPE_DOC;
            }elseif(strpos(Constant::CLOUD_PIC_TYPES,$data->extension)!==false){
                $data->type = Constant::CLOUD_FILETYPE_PIC;
                $this->load->helper('qiniu');
                $data->file_path = qiniu_img($data->file_path,0,699);//最长边为699
            }elseif(strpos(Constant::CLOUD_VIDEO_TYPES,$data->extension)!==false or strpos(Constant::CLOUD_AUDIO_TYPES,$data->extension)!==false){
                $data->type = strpos(Constant::CLOUD_AUDIO_TYPES,$data->extension)!==false?Constant::CLOUD_FILETYPE_AUDIO:Constant::CLOUD_FILETYPE_VIDEO;
                if($data->extension == 'swf'){
                    $this->load->helper('qiniu'); 
                    $data->file_path = qiniu_download($data->file_path,'swf',10800,false);
                    $this->smarty->assign('url',$data->file_path);
                }else{
                    $media_file_info = array('file_path'=>$data->file_path,'file_ext'=>$data->extension,'file_type'=>$data->type);
                    $persistent_id = $this->res_file_model->get_persistent_info($doc_id);
                    if($persistent_id)$media_file_info['persistent_id'] = $persistent_id;
                    $this->mediatype($doc_id,false,$media_file_info);
                }
            }else{//不支持预览
                $unable_view = true;
            }
            $data->ico_class = $ico_class[$data->type];
            $data1 = array();
            $data1['preview'] = $data;

            $this->load->library('credit');
            $privilege = $this->credit->userlevel_privilege($this->_user_id);
            $lesson_month_down_limit = $privilege['privilege']['lesson_permonth']['value'];
            if($this->redis_model->connect('download'))
            {
                $lesson_doc_key=date('Y-m-d').'_lesson_doc_key_'.$this->_user_id;
                $month_down_key = date('Y-m').'_lesson_doc_key_'.$this->_user_id;
                $surplus_count = $lesson_month_down_limit-$this->cache->get($month_down_key);
                $data1['surplus_download_count'] = $surplus_count>=0?$surplus_count:0;
                $data1['download_doc_count'] = $this->cache->get($lesson_doc_key);
                $data1['download_doc_limit'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
            }
            else
            {
                $data1['surplus_download_count'] = $lesson_month_down_limit;
                $data1['download_doc_count'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
                $data1['download_doc_limit'] = Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT;
            }
            $data1['related_docs'] = $this->document_model->get_relation_doc_by_course($data->category_id,$doc_id);
            foreach ($data1['related_docs'] as $key => $value) {
                $value->id = alpha_id($value->id);
                //$value->file_ext = Constant::document_icon($value->file_ext);
                $value->hits=$value->rand_num+$value->hits;
                $file_type_id = self::_get_file_type($value->file_ext,false);
                $value->ico_class = $ico_class[$file_type_id];
            }
            $assess_info = $this->document_model->get_doc_assess_log($doc_id,$data->score);
            $assess_avg_score = 0.0;
            //if($assess_info['count']>0){
                //$assess_avg_score = (double)round($assess_info['score']/$assess_info['count'],1);
                $assess_avg_score = (double)round(($assess_info['score']+$data->rand_score)/($assess_info['count']+1),1);
            //}
            $rand_count = $data->rand_num;
            if($rand_count>=100)$rand_count = $rand_count-80;
            if($rand_count>=50 and $rand_count<100)$rand_count = $rand_count-30;
            if($rand_count>=20 and $rand_count<50)$rand_count = $rand_count-10;
            $star_level = array(1,1,1,1,1);
            self::prase_star_level($assess_avg_score,$star_level);
            $data1['star_level'] = $star_level;
            $data1['assess_count'] = $assess_info['count']+$rand_count;
            //是否收藏过
            $favorite_exist = false;
            if($this->_user_id){
                $this->load->model('lesson/document_favorite_model','favorite');
                $favorite_info = $this->favorite->is_favorite_exist($this->_user_id,$doc_id);
                if($favorite_info && $favorite_info->is_del==0)$favorite_exist = true;
            }
            $this->smarty->assign('favorite_exist',$favorite_exist);
            $this->smarty->assign('data',$data1);
            $this->smarty->assign('res_id',$doc_id);
            $this->smarty->assign('unable_view',$unable_view);//该文件不支持预览
            $this->smarty->assign('breadcrumb',self::_remove_first_node($course_list));
            $this->smarty->assign('space_static_url',static_url('space'));
            $this->smarty->assign('file',array('file_ext'=>$data->extension,'file_type'=>$data->type));
            $this->smarty->display($this->_smarty_dir.'lesson_preview.html');
        }
        else
        {
            tizi_404('teacher/lesson/prepare');
            exit;
        }
    }

    protected function _remove_first_node($arr){
        if(empty($arr)) return false;
        $new_arr = array();
        if(strpos('信息技术',$arr[0]->name)!==false){
            $subject_name = mb_substr($arr[0]->name, 0, 6);
            $version_str = str_replace($subject_name,'',$arr[0]->name); 
        }else{
            $subject_name = mb_substr($arr[0]->name, 0, 4);
            $version_str = str_replace($subject_name,'',$arr[0]->name); 
        }
        $new_arr['subject'] = array('id'=>$arr[0]->subject_id,'name'=>$subject_name);
        $new_arr['version'] = array('id'=>$arr[0]->id,'name'=>$version_str);
        if(isset($arr[1])){
            $new_arr['grade'] = array('id'=>$arr[1]->id,'name'=>$arr[1]->name);
        }
        return $new_arr;
    }

    public function search_index()
    {   
        $data = array();
        $data['page'] = $this->input->get('page',true)?$this->input->get('page',true):1;
        $data['order'] = $this->input->get('order',true)?$this->input->get('order',true):'upload_time';
        $data['order_type'] = $this->input->get('type',true)?$this->input->get('type',true):'desc';
        $data['keyword'] = $this->input->get('keyword',true)?$this->input->get('keyword',true):'';
        $data['total'] = 0;
        $base_url = site_url('teacher/lesson/search').
            "?keyword={$data['keyword']}&order={$data['order']}&order_type={$data['order_type']}";
        if(strap($data['keyword'])){
            
            $document_list = $this->search->init('lesson')->search(array(
                'keyword'=>$data['keyword']),
                $data['page'],Constant::LESSON_SEARCH_PER_PAGE,"score desc, {$data['order']} {$data['order_type']}");
            if($document_list['total'] > 0)
            {
                $i=0;
                foreach($document_list['result'] as $q_l)
                {
                    $data['document'][$i]['file_id']=$q_l->id;
                    $data['document'][$i]['id']=alpha_id($q_l->id);
                    $data['document'][$i]['category_text'] = '';
                    if(isset($q_l->category_text) && is_array($q_l->category_text)){
                        $search_cate_arr = explode(',', $q_l->category_text[0]);
                        if($search_cate_arr){
                            $data['document'][$i]['category_text'] .= $search_cate_arr[0];
                            if(isset($search_cate_arr[1]))
                            $data['document'][$i]['category_text'] .= $search_cate_arr[1];
                        }
                    }
                    /*获取用户空间信息*/
                    $space_info = false;
                    if($q_l->user_id>0)$space_info = $this->space_user_model->open_space_by_user_id($q_l->user_id);
                    $data['document'][$i]['owner'] = $space_info['space_data'];
                    $data['document'][$i]['page_count']=$q_l->page_count;
                    $data['document'][$i]['file_name']=$q_l->file_name;
                    $data['document'][$i]['file_ext']=self::_get_file_type($q_l->file_ext);
                    $data['document'][$i]['extension']=$q_l->file_ext;

                    if($q_l->user_id>0){
                        $data['document'][$i]['hits']=$q_l->hits;
                    }else{$data['document'][$i]['hits']=($q_l->rand_score*10)>48?$q_l->rand_num+$q_l->hits+1200:$q_l->rand_num+$q_l->hits+200;}
                    $data['document'][$i]['upload_time']=date("Y-m-d",$q_l->upload_time);
                    $data['document'][$i]['file_size']=self::prase_file_size($q_l->file_size);
                    $data['document'][$i]['downloads']=$q_l->downloads>0?$q_l->rand_num+$q_l->downloads:$q_l->downloads;

                    $assess_info = $this->document_model->get_doc_assess_log($q_l->id,$q_l->score);
                    $rand_count = $q_l->rand_num;
                    if($rand_count>=100)$rand_count = $rand_count-80;
                    if($rand_count>=50 and $rand_count<100)$rand_count = $rand_count-30;
                    if($rand_count>=20 and $rand_count<50)$rand_count = $rand_count-10;
                    $data['document'][$i]['assess_count']= $assess_info['count']+$rand_count;
                    $assess_avg_score = 0.0;
                    $assess_avg_score = (double)round(($assess_info['score']+$q_l->rand_score)/($assess_info['count']+1),1);
                    $star_level = array(1,1,1,1,1);
                    self::prase_star_level($assess_avg_score,$star_level);
                    $data['document'][$i]['star_level'] = $star_level;
                    $i++;
                }
            }
            
        }
        $data['tab_order'] = $data['order_type']=='desc'?'asc':'desc';
        $data['total']=isset($document_list['total'])?$document_list['total']:0;
        $this->smarty->assign('pages',self::get_pagination($data['page'],$data['total'],'',array('base_url'=>$base_url),false));
        $this->smarty->assign('data',$data);
        $this->smarty->assign('search_url',site_url('teacher/lesson/search'));
        $this->smarty->display($this->_smarty_dir.'lesson_search.html');
    }

    protected function prase_file_size($file_size)
    {
        $mod = 1024;
        $units = explode(' ','B KB MB');
        for ($i = 0; $file_size > $mod; $i++) 
        {
            $file_size /= $mod;
        }
        return round($file_size, 2) . ' ' . $units[$i];
    } 

    protected function get_pagination($page_num,$total,$func,$conf=array(),$ajax=true)
    {
        $this->load->library('pagination'); 
        if($ajax){
            $config['total_rows']       = $total; //为页总数
            $config['cur_page']         = $page_num;
            $config['ajax_func']        = $func;
            $config['per_page']       = Constant::LESSON_PER_PAGE;
            $config['common_func'] = '';
            $config = array_merge($config,$conf);
            //获取分页
            $this->pagination->initialize($config);
            $pages = $this->pagination->create_ajax_links(); 
        }else{
            $config['use_page_numbers'] = TRUE;
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'page';
            $config['total_rows'] = $total;
            $config['per_page'] = Constant::LESSON_PER_PAGE;
            $config = array_merge($config,$conf);
            $this->pagination->initialize($config);
            $pages = $this->pagination->create_links(); 
        }
        return $pages;
    }

    /*预览 视频/音频 页面*/
    protected function mediatype($file_id,$is_cloud=true,$file_info=null){
        if($is_cloud && is_null($file_info)){//from cloud
            $this->load->model('cloud/cloud_model');
            $info = $this->cloud_model->file_info($file_id,'*',0,true);
            if(!$info){
                $this->smarty->assign('url','');
                return false;
            }
            $url = $this->res_file_model->get_media_url($info);
        }else{//from lesson_prepare
            $url = $this->res_file_model->get_media_url($file_info);
        }
        $this->smarty->assign('url',$url);
    }

    protected function _get_file_type($file_ext,$return_text=true)
    {
        $file_type=Constant::CLOUD_FILETYPE_OTHER;
        if(!$file_ext){

            $file_type=Constant::CLOUD_FILETYPE_OTHER;
        }
        elseif(strpos(Constant::CLOUD_DOC_TYPES,$file_ext)!==false){
            $file_type = Constant::CLOUD_FILETYPE_DOC;
        }elseif(strpos(Constant::CLOUD_PIC_TYPES,$file_ext)!==false){
            $file_type = Constant::CLOUD_FILETYPE_PIC;
        }elseif(strpos(Constant::CLOUD_VIDEO_TYPES,$file_ext)!==false){
            $file_type = Constant::CLOUD_FILETYPE_VIDEO ;
        }elseif(strpos(Constant::CLOUD_AUDIO_TYPES,$file_ext)!==false){
            $file_type = Constant::CLOUD_FILETYPE_AUDIO;
        }else{
            $file_type=Constant::CLOUD_FILETYPE_OTHER;
        }
        return  $return_text?Constant::cloud_filetype($file_type):$file_type;
    }

}
    
/* End of file lesson_prepare.php */
/* Location: ./application/controllers/lesson/lesson_prepare.php */
