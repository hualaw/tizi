<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."resource_base.php";

class Resource extends Resource_Base {
    protected $_smarty_dir = "teacher/resource/";
    protected $user_id ;
    public function __construct(){
        parent::__construct();
        $this->user_id = $this->session->userdata("user_id");
        if (!$this->user_id){                                                       
            $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
            redirect('login');
        }      
        $user_type = $this->session->userdata("user_type");
        if ($user_type != Constant::USER_TYPE_TEACHER){
            redirect('login');
        }
        $this->load->model('resource/res_dir_model');
        $this->load->model('resource/res_file_model');
        $this->load->model('cloud/cloud_model');
    }

    /*资源库首页*/
    function index(){
        $dir = $this->res_dir_model->get_res_dir($this->user_id);
        $this->course_box(true);
        $this->get_cloud_sta();//获取网盘使用量
        $this->smarty->assign('dir',$dir);
        $this->smarty->display($this->_smarty_dir."resource_index.html");
    }

    /*创建同步目录的弹窗 */
    function course_box($init = false){
        $subject_id = intval($this->input->post('subject_id',true));
        $this->load->model('question/question_subject_model');
        $this->load->model('login/register_model');
        $sub_from_grade = $grade = null;

        if(!$this->question_subject_model->check_subject($subject_id)){ //subject_id不合法
            $grade = intval($this->input->post('grade',true));// 1小 学/ 2 初中/ 3 高中
            $subject_type = intval($this->input->post('s_type',true));
            if($init){
                $sub_from_grade = $this->get_sub_by_grade($grade,2,true);//新的学段对应的学科
            }else{
                $sub_from_grade = $this->get_sub_by_grade($grade,2,false);//新的学段对应的学科
            }
            $refresh_sub = true;//选了新的学段，需要重新渲染 学科（不同学段对应不同学科）
            if($sub_from_grade){
                // foreach($sub_from_grade as $k=>$val){
                //     if($val->type==$subject_type){
                //         $subject_id = $val->id;
                //     }
                // }
                if(!$subject_id){//没有就指定第一个subject为subject_id
                    $subject_id = $sub_from_grade[0]->id;
                }
            }else{ //科目都没获取到，应该自己定个subject_id 和 grade
                $subject_id = $this->register_model->my_subject($this->user_id,''); //容错
            }
        }
        if(!$grade or !$sub_from_grade){ //容错
            $g = $this->question_subject_model->get_grade_by_subject($subject_id);
            if($g){ 
                $grade = $g->grade;
                if($init){
                    $sub_from_grade = $this->get_sub_by_grade($grade,2,true);//新的学段对应的学科
                }else{
                    $sub_from_grade = $this->get_sub_by_grade($grade,2,false);//新的学段对应的学科
                }
                $grade_name = $g->name;
            }
        }
        $this->load->model('question/question_category_model');

        $data['category_root_id'] = $this->question_category_model->get_root_id($subject_id);
        if($data['category_root_id']){
            foreach($data['category_root_id'] as $key=>&$c_r_id){
                if(strpos($c_r_id->name,'信息技术') !== false) {
                    $sub_length = 6;
                }else{
                    $sub_length = 4;
                }
                $data['category_root_id'][$key]->name=mb_substr($c_r_id->name,$sub_length);
                if($c_r_id->type < 2){//子目录
                    $data['category_root_id'][$key]->sub_cat = $this->question_category_model->get_subtree_node($c_r_id->id);
                }
                if($c_r_id->type > 0){ //知识点或冲刺
                    unset($data['category_root_id'][$key]);
                }
            }
        }else{
            echo json_token(array('errorcode'=>false,'error'=>'没有相应同步资源目录'));die;
        }
        $cat_sub_from_grade = $this->get_sub_by_grade($grade,2,true,'cloud_question');//新的学段对应的学科
        $default_cat_id = 0;
        foreach($cat_sub_from_grade as $val){
            if($subject_id == $val->id){
                $default_cat_id = $val->category_id;
            }
        }

        if($init){
            $this->smarty->assign('sub_from_grade',$sub_from_grade);
            $this->smarty->assign('cat_sub_from_grade',$cat_sub_from_grade);
            $this->smarty->assign('subject_id',$subject_id);
            $this->smarty->assign('grade',$grade);
            $this->smarty->assign('default_cat_id',$default_cat_id);
            $this->smarty->assign('grade_name',$grade_name);
            $this->smarty->assign('data',$data);
        }else{
            echo json_token(array('sub_from_grade'=>$sub_from_grade,
                                  // 'cat_sub_from_grade'=>$cat_sub_from_grade,
                                  'subject_id'=>$subject_id,
                                  'grade'=>$grade,
                                  'grade_name'=>$grade,
                                  'data'=>$data
            ));die;
        }
    }

    /*根据学段获取学科 for: course_box() && cat_box()*/
    protected function get_sub_by_grade($grade,$num=2,$need_cat_id=false,$check_subject='cloud'){
        $res = null;
        if($grade){
            $res = $this->question_subject_model->get_subject_by_grade($grade);
            foreach($res as $key=>$val){
                if($need_cat_id){
                    //获取当前科目下的知识点库id  // $val->id is subject_id
                    $data = $this->question_category_model->get_root_id($val->id);
                    if($data){
                        foreach($data as $k=>$v){
                            if($v->type == 1){
                                $val->category_id = $v->id;
                            }
                        }
                    }
                }
                $val->name = mb_substr($val->name,2);
                if(!$this->question_subject_model->check_subject($val->id,$check_subject)){
                    unset($res[$key]);
                }
            }
        }
        return $res;
    }

    /* 公共部分 获取网盘的 (使用量/总容量) */
    protected function get_cloud_sta(){
        $statistics=array();
        $statistics['cloud_storage'] = $this->cloud_model->get_user_cloud_storage($this->user_id,true);
        $this->smarty->assign("statistics", $statistics);//这里需要网盘的统计
    }

    /* 创建知识点目录的弹窗*/
    function cat_box(){
        $grade = intval($this->input->post('grade',true));
        $this->load->model('login/register_model');
        $this->load->model('question/question_subject_model');
        if($grade < 1 or $grade>3){
            $subject_id = $this->register_model->my_subject($this->user_id,'');
            $g = $this->question_subject_model->get_grade_by_subject($subject_id);
            if($g){ 
                $grade = $g->grade;
            }
        }
        $sub_from_grade = $this->get_sub_by_grade($grade,2,true,'cloud_question');//新的学段对应的学科
        
        echo json_token(array('sub_from_grade'=>$sub_from_grade));die;
    }

    /* 创建同步or知识点文件夹 */
    function make_res_dir(){
        $param['cat_type'] = intval($this->input->post('cat_type',true));
        $param['cat_id'] = intval($this->input->post('cat_id',true));//最下面一级cat_id
        $param['dir_name'] = sub_str(strip_tags($this->input->post('dir_name',true)),0,153,'');
        $param['user_id'] = $this->user_id;
        $param['create_time'] = time();
        $param['is_del'] = $param['p_id'] = $param['depth'] = 0;
        // print_r($param);die;
        if(!$param['cat_id'] or !$param['dir_name']){
            $json = array('errorcode'=>false,'error'=>'非法请求');
        }else{
            // 先检查有没有这个cat_id cat_type user_id的文件夹的存在，有就返回false；
            $exist = $this->res_dir_model->check_res_dir_exist($param);
            if($exist){
                echo json_token(array('errorcode'=>false,'error'=>'不能添加重复的文件夹'));die;
            }
            //确保文件夹名字绝对正确
            $this->load->model('question/question_category_model');
            $sure_name = $this->question_category_model->get_single_path($param['cat_id']);
            if(isset($sure_name[0]->name)){
                $ok_name = $sure_name[0]->name;
                if(isset($sure_name[1]->name)){
                    $ok_name .= $sure_name[1]->name;
                }
            }
            $param['dir_name'] = $ok_name?$ok_name:$param['dir_name'];
            $c = $this->res_dir_model->count_res_dir($this->user_id); 
            $res = $this->cloud_model->insert_user_directory($param);
            if($res){
                if(!$c){
                    $json=array('errorcode'=>true,'error'=>'添加成功',
                                'cat_id'=>site_url().'teacher/cloud/res/res_dir/'.$param['cat_id']);
                }else{
                    $json=array('errorcode'=>true,'error'=>'添加成功');
                }
            }else{
                $json = array('errorcode'=>false,'error'=>'系统繁忙，请稍候再试');
            }
        }
        echo json_token($json);die;
    }

    /*同步、知识点  目录详情页*/
    function res_dir($dir_cat_id,$sub_cat_id=0){
        $this->bread_cap($dir_cat_id);
        $dir_cat_id = intval($dir_cat_id);
        $param['cat_id'] = $dir_cat_id;
        $param['user_id'] = $this->user_id;
        if(!$param['cat_id'] or !$this->res_dir_model->check_res_dir_exist($param)){//有无此文件夹
            // echo 'no this dir';die;// will be deleted
            redirect(site_url().'teacher/cloud');
        }
        $category_tree = $list = $count_list = array();
        $this->_get_category_tree($dir_cat_id,$category_tree);//"高中-语文-语文版-必修1"下面的所有单元和课文内容
        if(isset($category_tree[0])){
            $sub_cat_id = $sub_cat_id? $sub_cat_id:$category_tree[0]['id'];//取cat_id下的第一条
        }
        $res_type = Constant::resource_type();
        $all_type_list=$this->res_file_model->res_file_list($this->user_id,$sub_cat_id,array_flip($res_type),1);
        
        if($all_type_list){
            foreach($all_type_list as $key=>$val){
                $list[$val['resource_type']][] = $val;
            }
        }
        $this->load->model('resource/res_file_model');
        if($list){
            foreach($list as $kk=>&$vv){
                if(is_array($vv)){
                    $this->res_file_model->is_pfop_done($vv);
                }
            }
        }
        //每次进入这个subcatid时都会更新redis中的数据
        $this->res_file_model->update_user_res_subcatid($this->user_id,$dir_cat_id,$sub_cat_id);
        //获取 category_tree中每个点的上传文件的count
        $sta = $this->res_file_model->hgetall_user_res($this->user_id,$dir_cat_id);
        //获取dir_id
        $where = array('user_id'=>$this->user_id,'cat_id'=>$dir_cat_id);
        $select = 'dir_id';
        $dir_id = $this->res_dir_model->get_index($select,$where);
        $this->smarty->assign('res_type',$res_type);
        $this->smarty->assign('sta',$sta);
        $this->smarty->assign('dir_id',$dir_id);
        $this->smarty->assign('dir_cat_id',$dir_cat_id);
        $this->smarty->assign('category_tree',$category_tree);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('sub_cat_id',$sub_cat_id);
        $this->smarty->assign('classes',$this->get_my_classes());
        $this->smarty->display($this->_smarty_dir."res_dir.html");   
    }

    /*目录中某个cat中的  资源更多页*/
    function res_list($sub_cat_id,$res_type=1,$page=1,$flip=false){
        if(!$sub_cat_id){//有无此文件夹
            redirect(site_url().'teacher/cloud');
        }
        $this->bread_cap(0,$sub_cat_id);
        $list=$this->res_file_model->res_file_list($this->user_id,$sub_cat_id,array(0=>intval($res_type)),$page,Constant::RES_LIST_PAGESIZE);
        if($res_type < Constant::RESOURCE_TYPE_JXSJ or $res_type > Constant::RESOURCE_TYPE_OTHER){
            $res_type = Constant::RESOURCE_TYPE_JXSJ;
        }
        $res_type_name = Constant::resource_type($res_type);
        $param['user_id'] = $this->user_id;
        $param['sub_cat_id'] = $sub_cat_id;
        $param['resource_type'] = $res_type;
        $param['is_del'] = 0;
        $count = $this->res_file_model->file_sum($param);
        /*分页*/
        $pages =  self::_get_pagination($page,$count,'reslist_page');
        if($list){
            $this->res_file_model->is_pfop_done($list);              
        }
        $this->smarty->assign('list',$list);
        $this->smarty->assign('pages',$pages);
        $this->smarty->assign('count',$count);
        $this->smarty->assign('sub_cat_id',$sub_cat_id);
        $this->smarty->assign('res_type',$res_type);
        $this->smarty->assign('classes',$this->get_my_classes());
        $this->smarty->assign('res_type_name',$res_type_name);
        if($flip){//翻页的话 就是异步加载tpl
            $json['errorcode'] = false;
            if($list){
                $json['errorcode'] = true;
                $json['html'] = $this->smarty->fetch($this->_smarty_dir."res_list_tpl.html");
                $json['pages'] = $pages;
            }
            echo json_token($json);die;
        }else{
            $this->smarty->display($this->_smarty_dir."res_list.html");   
        }
    }

    protected function _get_pagination($page_num,$total,$func){
        $this->load->library('pagination'); 
        $config['total_rows'] = $total; //为页总数
        $config['per_page'] = Constant::RES_LIST_PAGESIZE;
        $config['cur_page'] = $page_num;
        $config['ajax_func'] = $func;
        $config['enable_query_strings'] = false;
        $config['page_query_string'] = false;
        $config['uri_segment'] = 7; //指定page参数是uri的第n个
        //获取分页
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_ajax_links();
        return $pages;
    }

    /*老网盘首页，会删除cookie中的 _mdir 记录,主动进入网盘的第一页*/
    function other(){
        $this->load->model("login/register_model");
        $this->register_model->set_current_cloud_dir(0);//设置当前目录cookie
        redirect('/teacher/cloud/other');
    }

    /*很多页面的 Nav 面包屑 和 容量*/
    protected function bread_cap($dir_cat_id=0,$sub_cat_id=0){
        $this->load->model('question/question_category_model');
        $dir_name = $sub_name = '';
        if($sub_cat_id){
            $sname = $this->question_category_model->get_node($sub_cat_id);
            $sub_name = isset($sname->name)?$sname->name:'';
        }
        if($dir_cat_id){
            $where = array('user_id'=>$this->user_id,'cat_id'=>$dir_cat_id,'is_del'=>0);
            $select = "dir_name";
            $dir_name = $this->res_dir_model->get_index($select,$where);
        }elseif($sub_cat_id){ // 无dir_cat_id , 只有 sub_cat_id
            $dir_name = $this->res_dir_model->get_dir_name_by_subid($this->user_id,$sub_cat_id);
            $dir_cat_id  = $dir_name->cat_id;
            $dir_name = $dir_name->n;
        }
        $this->smarty->assign('dir_name',$dir_name);
        $this->smarty->assign('dir_cat_id',$dir_cat_id);
        $this->smarty->assign('sub_name',$sub_name);
        $this->get_cloud_sta();//获取网盘使用量
    }

    //检查文档是否转换完成 with file_id 
    function doc_process($file_id){
        $file_id = intval($file_id);
        if(!$file_id){redirect("{$site_url}teacher/cloud");}
        $this->load->model('cloud/cloud_model');
        $info = $this->cloud_model->file_info($file_id,'*',0,true);
        if(isset($info['queue_status']) && $info['queue_status']==1){
            echo json_token(array('errorcode'=>true));die;
        }
        echo json_token(array('errorcode'=>false));die;
    }


    function ttt($node){
        // $file['file_ext'] = 'mov';
        // $file['file_type'] = Constant::CLOUD_FILETYPE_VIDEO;
        // $this->smarty->assign('url',"d:/hehe.mov");
        // $this->smarty->assign('file',$file);
        // $this->smarty->display('teacher/resource/media_player.html');
        // die;

        $key = '20140524/3d/7609c26b91c71a9b577e8d49aNDX5PI.wmv';
         
        
        $this->load->helper('qiniu');
        $op = 'mp4/preset/video_440k';
        // $op = 'mp4/vb/512k';
        // $op = 'mp4';
        $path = qiniu_vi_au($key,$op,1);
        echo $path;
        die;

        // $node = '29226';
        // $this->load->model('question/question_category_model');
        // $res = $this->question_category_model->get_parent_id($node);
        // $ress = $this->question_category_model->get_single_path($node);
        // var_dump($res,$ress);die;
        //  $this->load->model("redis/redis_model");
        //  $this->redis_model->connect('cloud_statistics');
        // $user_id = $this->user_id;
        // $dir_cat_id = 29225;
        // $sub_cat_id = 29227;
        // // $this->res_file_model->hgetall_user_res($user_id,$dir_cat_id);   die;
        // $this->res_file_model->update_user_res_subcatid_type($this->user_id,$dir_cat_id,$sub_cat_id,2);
        // $key = 'user_res_'.$user_id.'_'.$dir_cat_id;
        // $count = $this->cache->hget($key,$sub_cat_id); // get all statistics in a dir
        // $count = unserialize($count);
        // var_dump($count);
    }
 
}

