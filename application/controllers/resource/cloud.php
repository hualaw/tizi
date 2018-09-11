<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."cloud_base.php";
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."../resource/resource_base.php";

class Cloud extends Cloud_Base {
    protected $_smarty_dir = "cloud/";
    protected $user_id ;
    public function __construct(){
        parent::__construct();
        $this->user_id = $this->session->userdata("user_id");
        if (!$this->user_id){                                                       
            $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
            redirect('login');
        }      
        $user_type = $this->session->userdata("user_type");
        // if ($user_type != Constant::USER_TYPE_TEACHER){
            // redirect('login');
        // }    
        $this->load->model("login/register_model");
        $this->load->model('cloud/cloud_model');
        $this->smarty->assign('chosen_type', 0);
        $this->smarty->assign('filetypes', Constant::cloud_filetype(0,true));
    }

    //老网盘首页
    function index($chosen_type=0){
        $chosen_type = intval($chosen_type);
        $file_type_name = Constant::cloud_filetype($chosen_type);
        $breadcrumb_str = $file_type_name?$file_type_name:'';
        $current_dir_id = 0;
        if(!$chosen_type){
            $current_dir_id = $this->register_model->my_current_cloud_dir()?
            $this->register_model->my_current_cloud_dir():0;
            $total = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$current_dir_id,1,0,true);
            $data = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$current_dir_id);
            /*分页*/
            $pages =  self::get_pagination(1,$total['total_page'],'cloud_page');
            $this->smarty->assign('total_count', $total['all_total']);
        }elseif(in_array($chosen_type, array_keys(Constant::cloud_filetype(0,true)))){
            $total = $this->cloud_model->get_file_by_type($this->user_id,$chosen_type,true,1,true);
            $data['file'] = $this->cloud_model->get_file_by_type($this->user_id,$chosen_type,true);
            /*分页*/
            $pages =  self::get_pagination(1,$total,'cloud_page');
            $this->smarty->assign('total_count', $total);
        }else{
            redirect('/teacher/cloud');
        }
        if($data['file']){
            $this->load->model('resource/res_file_model');
            $this->res_file_model->is_pfop_done($data['file']);
        }
        //$total_count = $this->cloud_model->file_sum_by_dir_id($this->user_id,$current_dir_id);
        //获取所有班级
        $resource = new resource_base();
        $all_class_info = $resource->get_my_classes();
        
        //目录tree
        $tree = '';//$this->cloud_model->get_dir_tree($this->user_id);
        $dir_name = '';
        // $breadcrumb_str = '';
        if(!$breadcrumb_str){
            if($current_dir_id > 0){
                $dir_info = $this->cloud_model->get_dir_info($current_dir_id);
                $dir_name = $dir_info['dir_name'];
                $this->breadcrumb($dir_info['p_id'],$breadcrumb_str);
            }
        }
        $storage_info = $this->cloud_model->get_user_cloud_storage($this->user_id,true);
        $this->smarty->assign('data', $data);
        $this->smarty->assign('tree', $tree);
        $this->smarty->assign('storage_info', $storage_info);
        $this->smarty->assign('classes', $all_class_info);
        $this->smarty->assign('chosen_type', $chosen_type);
        $this->smarty->assign('dir_id', $current_dir_id);//初始化用的,无意义
        $this->smarty->assign('dir_name', $dir_name);
        $this->smarty->assign('breadcrumb', $breadcrumb_str);
        $this->smarty->assign('pages',$pages);
        $this->smarty->display($this->_smarty_dir."cloud_index.html");
    }

    public function flash_get_json(){
        $doc_id = $this->input->post('id');
        if(empty($doc_id))
        {
            $referer_arr = explode('/', $this->input->server('HTTP_REFERER'));
            $doc_id = $referer_arr[count($referer_arr)-1];
        }
        $preview_data = $this->cloud_model->get_single_doc_preview($doc_id,$this->user_id,true,1);//要能查看源文件被删除的分享文件
        // $preview_data = $this->cloud_model->get_single_doc_preview($doc_id,$this->user_id,true);

        if(empty($preview_data))  
        {
            $files_info = array('status'=>1);
        }
        else
        {
            $uri_api    =  'http://tizi-zujuan-thumb.oss.aliyuncs.com/';
            $swf_list   = array();
            $i = 0;
            while ( $i < $preview_data->page_count) {
                $page = $i+1;
                $swf_list[$i] = $uri_api.$preview_data->swf_folder_path."/preview_{$page}.swf";
                $i++;
            }
            if($swf_list)
            {
                $files_info = array(
                'status'        =>99,
                'page_total'    =>$preview_data->page_count,
                'files_url'     =>$swf_list,
                'file_id'       =>$doc_id,
                'file_ext'      =>$preview_data->file_ext,
                'goto_next_num' =>Constant::PAGE_NUM_LOAD_NEXT
                );
            }
            else
            {
                $files_info = array('status'=>1);
            }
        }
        echo json_encode($files_info);exit;
    }

    function get_files()
    {
        $type_select=$this->input->get("ctype");
        $dir_select =$this->input->get("cdir");
        $page_select=$this->input->get("page");
        if($page_select<=0) $page_select=1;
        if(!isset($dir_select)) $dir_select=0;
        if($type_select and in_array($type_select, array_keys(Constant::cloud_filetype(0,true)))){
            $total = $this->cloud_model->get_file_by_type($this->user_id,$type_select,true,$page_select,true);
            $data['file'] = $this->cloud_model->get_file_by_type($this->user_id,$type_select,true,$page_select);
            /*分页*/
            $pages =  self::get_pagination($page_select,$total,'cloud_page');
            $this->smarty->assign('total_count', $total);
        }else{
            $total = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$dir_select,$page_select,0,true);
            //$data = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$dir_select,$page_select,$file_offset);

            $perpage = Constant::CLOUD_FILE_PER_PAGE_NUM;
            $dir_total_page = ceil($total['dir_total']/$perpage);
            
            if($total['dir_total']==$dir_total_page*$perpage){
                $first_file_on_page = $dir_total_page+1;//文件出现的第一页
            }else{
                $first_file_on_page = $dir_total_page;
            }
            $count_first_file_page = $perpage-$total['dir_total']%$perpage;//文件出现的第一页中，文件的个数
            if($page_select>$dir_total_page){
                
                $start = ($page_select-$first_file_on_page-1)*$perpage+$count_first_file_page;//文件的limit的起始
                $data = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$dir_select,$page_select,$start);            
            }else{
                 $data = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$dir_select,$page_select);            
            }
            /*分页*/
            $pages =  self::get_pagination($page_select,$total['total_page'],'cloud_page');
            $this->smarty->assign('total_count', $total['all_total']);
        }
        if($data['file']){
            $this->load->model('resource/res_file_model');
            $this->res_file_model->is_pfop_done($data['file']);
        }
        $this->smarty->assign('dir_id',$dir_select);
        $this->smarty->assign('data',$data);
        $this->smarty->assign('pages',$pages);
        $json['errorcode'] = true;
        $json['html']= $this->smarty->fetch($this->_smarty_dir.'cloud_file_list_ajax_tpl.html');
        echo json_token($json);die;

    }

    function ajax_get_token_key(){
        $ext = $this->input->post('file_ext',true);//带着.的，如:  .mp4; .flv
        if(!$ext){//如果没有后缀的，名字就是后缀
            $ext = $this->input->post('file_name',true);
            $rpos = strrpos($ext, '.');
            if($rpos !== false){
                $ext = substr($ext,$rpos+1);
            }
        }
        $size = $this->input->post('file_size',true);
        /*判断容量*/
        $user_cloud_storage = $this->cloud_model->get_user_cloud_storage($this->user_id);
        $user_cloud_storage += $size;

        //获取我的网盘的总容量
        $this->load->library('credit');
        $privilege = $this->credit->userlevel_privilege($this->user_id);
        $my_cloud_size = $privilege['privilege']['cloud_sizem']['value']; //单位是M
        $my_cloud_size *= 1024*1024; //单位是byte

        if($user_cloud_storage > $my_cloud_size){
            $error_code['error_code'] = false;
            $error_code['error'] = '您的网盘容量已用完，暂无法上传';
            json_get($error_code);
        }
        $ext_with_no_point = ltrim($ext,'.');//去掉最左边的点
        if(isset($ext) and !empty($ext)){
            $this->load->library('qiniu');
            if(strpos(Constant::CLOUD_VIDEO_TYPES_JWPLAYER, $ext_with_no_point)!==false and $ext!='.mp4'){
                $token = $this->qiniu->make_token(7200,'avthumb/'.Constant::PRESET_VIDEO);//非mp4格式的视频，会申请转换成mp4格式
            }elseif (strpos(Constant::CLOUD_AUDIO_TYPES, $ext_with_no_point)!==false and $ext!='.mp3') {
                $token = $this->qiniu->make_token(7200,'avthumb/'.Constant::PRESET_AUDIO);//非mp3格式的视频，会申请转换成mp3格式
            }else{
                $token = $this->qiniu->make_token();
            }
            $md5 = md5(uniqid());
            $filename = alpha_id(mt_rand(1000000, 9999999)) . $ext;
            $key = date("Ymd") . "/" . substr($md5, 3, 2) . "/" . substr($md5, 7,26).$filename;
            if($token){
                $error_code['error_code'] = true;
                $error_code['file_token'] = $token;
                $error_code['file_key'] = $key;
            }else{
                $error_code['error_code'] = false;
                $error_code['error'] = '服务器繁忙,请稍后重试';
            }
        }else{
            $error_code['error_code'] = false;
            $error_code['error'] = '服务器繁忙,请稍后重试';
        }
        echo json_token($error_code);die;
    }

    /*记录上传错误日志*/
    function upload_error(){
        $post_data = $this->input->post();
        if(isset($post_data['ver'])){
            unset($post_data['ver']);
        }
        if(isset($post_data['token'])){
            unset($post_data['token']);
        }
        if(isset($post_data['page_name'])){
            unset($post_data['page_name']);
        }
        $post_data['user_id'] = $this->user_id;
        log_message('error_tizi', $post_data['source'].'_error:'.json_encode($post_data));
        die;
    }

    /*上传至七牛后，将数据回写我们的数据库*/
    function qiniu_upload(){
        $file_key   = $this->input->post('key',true);
        $file_size  = $this->input->post('file_size',true);
        $file_name  = $this->input->post('file_name',true);
        $persistent_id  = $this->input->post('persistent_id',true);//fop的id,任何文件轉換都會有這個id
        //2014-05-10 新的参数，如果是在
        $dir_cat_id = intval($this->input->post('dir_cat_id',true));
        $sub_cat_id = intval($this->input->post('sub_cat_id',true));
        $dir_id = intval($this->input->post('dir_id',true));
        $res_type = intval($this->input->post('res_type',true));
        $cur_dir_id = intval($this->input->post('cur_dir_id',true))?intval($this->input->post('cur_dir_id',true)):0;
        $show_place = $this->input->post('show_place',true)?intval($this->input->post('show_place',true)):0;
        if($dir_cat_id and $sub_cat_id and $dir_id){//如果是传到同步目录
            $cur_dir_id = $dir_id;
            if(!$res_type){//容错，如果没有取到这个值，就默认是第一种类型
                    $res_type = Constant::RESOURCE_TYPE_JXSJ;
            }
        }
        $file_info = pathinfo($file_name);
        $ext = isset($file_info["extension"]) ? $file_info["extension"] : "";
        $ext = strtolower($ext);
        $this->load->helper("upload");
        $file_type = cloud_upload_file_type_check($ext);
        $insert['user_id'] = $this->user_id;
        $insert['file_name'] = str_replace(strrchr($file_name, '.'),'',$file_name);
        $insert['file_name'] = $this->cloud_model->check_dir_name_exist($cur_dir_id,$insert['file_name'],$this->user_id,true,$ext,$dir_cat_id);
        $insert['file_path'] = $file_key; 
        $insert['dir_id'] = $cur_dir_id;
        $insert['file_type'] = $file_type;
        $insert['upload_time'] = time();
        $insert['file_ext'] = $ext;
        $insert['file_size'] = $file_size;
        $insert['upload_ip'] = ip2long(get_remote_ip());
        //2014-05-12 增加新的3个参数
        $insert['dir_cat_id'] = $dir_cat_id;
        $insert['sub_cat_id'] = $sub_cat_id;
        $insert['resource_type'] = $res_type;
        $insert['persistent_id'] = $persistent_id;
        $insert['show_place'] = $show_place;
        $result = $this->cloud_model->insert_upload_file($insert);
        if($result){
            $error_code['error_code'] = true;
            $error_code['new_file_id'] = $result;
        }else{
            $error_code['error_code'] = false;
            $error_code['error'] = '上传失败';
        }
        echo  json_token($error_code);die;
    }

    //上传文档文件，至阿里云oss (其中的七牛已经不会走了)
    function upload(){
        ini_set("max_execution_time", "7200");
        $this->load->helper("json");
        $error_code = array();
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $name = 'shareFileUp';
            $cur_dir_id = intval($this->input->post('cur_dir_id',true))?intval($this->input->post('cur_dir_id',true)):0;
            //2014-05-10 新的参数，如果是在
            $dir_cat_id = intval($this->input->post('dir_cat_id',true));
            $sub_cat_id = intval($this->input->post('sub_cat_id',true));
            $dir_id = intval($this->input->post('dir_id',true));
            $res_type = intval($this->input->post('res_type',true));
            $show_place = $this->input->post('show_place',true)?intval($this->input->post('show_place',true)):0;
            if($dir_cat_id and $sub_cat_id and $dir_id){//如果是传到同步目录
                $cur_dir_id = $dir_id;
                if(!$res_type){//容错，如果没有取到这个值，就默认是第一种类型
                    $res_type = Constant::RESOURCE_TYPE_JXSJ;
                }
            }
            if(isset($_FILES[$name]['error'])&&$_FILES[$name]['error'] == 0){
                $user_cloud_storage = $this->cloud_model->get_user_cloud_storage($this->user_id);
                $user_cloud_storage += $_FILES[$name]['size'];
                //获取我的网盘的总容量
                $this->load->library('credit');
                $privilege = $this->credit->userlevel_privilege($this->user_id);
                $my_cloud_size = $privilege['privilege']['cloud_sizem']['value']; //单位是M
                $my_cloud_size *= 1024*1024; //单位是byte
                if($user_cloud_storage > $my_cloud_size){
                    $error_code['code'] = -6;
                    $error_code['msg']  = '您的网盘容量已用完，暂无法上传';
                    json_get($error_code);
                }
                $this->load->helper("upload");
                $pathinfo = pathinfo($_FILES[$name]["name"]);
                $ext = isset($pathinfo["extension"]) ? strtolower($pathinfo["extension"]): "";
                $file_type = cloud_upload_file_type_check($ext);
                if($file_type==Constant::CLOUD_FILETYPE_DOC){
                    //文档、预览文件走阿里云
                    $status = cloud_doc_file_upload($name);//'teacher_doc/123/123/123.'.$ext;//
                    if(strpos($status,'teacher_doc')!==false){
                        $file_info = pathinfo($_FILES[$name]['name']);
                        $insert['user_id'] = $this->user_id;
                        $insert['file_name'] = str_replace(strrchr($_FILES[$name]['name'], '.'),'',$_FILES[$name]['name']);
                        $insert['file_name'] = $this->cloud_model->check_dir_name_exist($cur_dir_id,$insert['file_name'],$this->user_id,true,$file_info['extension']);
                        $insert['file_path'] = $status;
                        $insert['dir_id'] = $cur_dir_id;
                        $insert['file_type'] = $file_type;
                        $insert['upload_time'] = time();
                        $insert['file_ext'] = strtolower($file_info['extension']);
                        $insert['file_size'] = $_FILES[$name]['size'];
                        $insert['upload_ip'] = ip2long(get_remote_ip());
                        $insert['queue_status'] = 2;
                        //2014-05-12 增加新的3个参数
                        $insert['dir_cat_id'] = $dir_cat_id;
                        $insert['sub_cat_id'] = $sub_cat_id;
                        $insert['resource_type'] = $res_type;
                        $insert['show_place'] = $show_place;

                        // var_dump($insert);die;
                        $result = $this->cloud_model->insert_upload_file($insert);
                        if($result){
                            //加入转换队列
                            $this->load->model('user_data/user_document_model');
                            $this->user_document_model->task_lpush_queue($result,2);
                            $error_code['code'] = 1;
                            $error_code['new_file_id'] = $result;
                            $error_code['msg']  = '上传成功!';
                            json_get($error_code);
                        }else{
                            $error_code['code'] = -3;
                            $error_code['msg']  = '失败,请重试!';
                            json_get($error_code);
                        }
                    }else{
                        $error_code['code'] = -2;
                        $error_code['msg']  = '失败,请重试!';
                        json_get($error_code);
                    }
                }elseif($file_type>Constant::CLOUD_FILETYPE_DOC && $file_type<=Constant::CLOUD_FILETYPE_OTHER){
                    if(!isset($file_info['extension'])){
                        $error_code['code'] = -6;
                        $error_code['msg']  = '不允许上传无后缀文件!';
                        json_get($error_code);
                    }
                    //引入七牛的类库，上传至七牛
                    $this->load->library('qiniu');
                    $res = $this->qiniu->qiniu_upload($name);
                    if($res['errorcode'] && isset($res['ret']['key'])){
                        $file_info = pathinfo($_FILES[$name]['name']);
                        $insert['user_id'] = $this->user_id;
                        $insert['file_name'] = str_replace(strrchr($_FILES[$name]['name'], '.'),'',$_FILES[$name]['name']);
                        $insert['file_name'] = $this->cloud_model->check_dir_name_exist($cur_dir_id,$insert['file_name'],$this->user_id,true,$file_info['extension']);
                        $insert['file_path'] = $res['ret']['key']; 
                        $insert['dir_id'] = $cur_dir_id;
                        $insert['file_type'] = $file_type;
                        $insert['upload_time'] = time();
                        $insert['file_ext'] = $file_info['extension'];
                        $insert['file_size'] = $_FILES[$name]['size'];
                        $insert['upload_ip'] = ip2long(get_remote_ip());
                        $result = $this->cloud_model->insert_upload_file($insert);
                        if($result){
                            $error_code['code'] = 1;
                            $error_code['msg']  = '上传成功!';
                            $error_code['new_file_id'] = $result;
                            json_get($error_code);
                        }else{
                            $error_code['code'] = -3;
                            $error_code['msg']  = '失败,请重试!';
                            json_get($error_code);
                        }
                    }else{
                        $error_code['code'] = -4;
                        $error_code['msg']  = '失败,请重试!';
                        json_get($error_code);
                    }
                }else{
                    $error_code['code'] = -5;
                    $error_code['msg']  = '失败,请重试!';
                    json_get($error_code);
                }
            }else{
                $error_code['code'] = -1;
                $error_code['msg']  = '上传失败!';
                json_get($error_code);
            }
        }else{
            tizi_404('teacher/cloud');
            exit;
        }
    }

    //移动文件夹or文件
    // 文件夹只能在原来的网盘中移动，文件可以在全局
    function move(){
        $resource_id = intval($this->input->post('resource_id',true));
        // $sub_cat_id只是移动文件到资源库中才有用
        $sub_cat_id = $to_dir_id = intval($this->input->post('to_dir_id',true));
        $is_file = intval($this->input->post('is_file',true));
        $json = array('errorcode'=>false,'error'=>'操作失败');
        $dir_cat_id = intval($this->input->post('cat_id',true));//同步/知识点文件夹的cat_id
        if($dir_cat_id){
            $resource_type = intval($this->input->post('type',true));//文件的资源类型，1～8
            if(!in_array($resource_type,array_flip(Constant::resource_type()))){
                echo json_token(array('errorcode'=>false,'error'=>'资源类型不合法'));die;
            }
        }
        if($dir_cat_id){
            $to_dir_id = intval($this->input->post('dir_id',true));//同步/知识点文件夹的id 
        }
        if(!$resource_id){
            echo json_token($json);die;
        }
        if($is_file==false){ //判断文件夹 之间的移动是否合法
            if($resource_id==$to_dir_id){
                $json = array('errorcode'=>false,'error'=>'不能将文件夹移动到自己里！');
                echo json_token($json);die;
            }
            $this->load->helper('array');
            $tree = $this->cloud_model->get_all_child_dir_id_string($this->user_id,$resource_id);
            $tree = explode_to_distinct_and_notempty($tree);//所有的子文件夹id数组
            if(in_array($to_dir_id,$tree)){
                $json = array('errorcode'=>false,'error'=>'不能将文件夹移动到自己的子文件夹中！');
                echo json_token($json);die;
            }
        } 
        $ext = 0;
        $under_dir_id = 0;
        if($is_file){
            $info = $this->cloud_model->file_info($resource_id);
            if(!$info){
                $name = 'unknown';
            }else{
                $name = $info['file_name'];
                $ext = $info['file_ext'];
                $under_dir_id = $info['dir_id'];
                $retrieve_restype = $info['resource_type'];
                $from_dir_cat_id = $info['dir_cat_id'];//文件原来是否存在 同步/知识点目录 下
                $from_sub_cat_id = $info['sub_cat_id'];//文件原来是否存在 同步/知识点目录 下
            }
        }else{
            $info = $this->cloud_model->get_dir_info($resource_id);
            if(!$info){
                $name = 'unknown';
            }else{
                $name = $info['dir_name'];
                $under_dir_id = $info['p_id'];
            }
        }
        // if($under_dir_id == $to_dir_id and $retrieve_restype==$resource_type){
        //     $json = array('errorcode'=>true,'error'=>'操作成功');//移动到原来的文件夹下，直接报成功
        //     echo json_token($json);die;
        // }
        if($is_file and $dir_cat_id){//是  移动文件到同步目录 中么？
            $file_to_cat_dir = true;
        }else{
            $file_to_cat_dir = false;
            $sub_cat_id = $resource_type = $dir_cat_id = null;
        }
        $new_name = $this->cloud_model->check_dir_name_exist($to_dir_id,$name,$this->user_id,$is_file,$ext,$file_to_cat_dir);//检查是否有重名
        // var_dump($file_to_cat_dir,$resource_id,$to_dir_id,$dir_cat_id,$sub_cat_id,$resource_type);die;
        if($new_name != $name){
            $this->cloud_model->rename_dir_or_file($is_file,$resource_id,$new_name);
        }
        $res = $this->cloud_model->move_dir_or_file($is_file,$resource_id,$to_dir_id,$this->user_id,$dir_cat_id,$sub_cat_id,$resource_type);
        if(!$res){
            echo json_token($json);die;
        }
        $this->load->model('resource/res_file_model');
        if($is_file and $dir_cat_id){//是  移动文件到同步目录 中,这个目录的redis加1；
            $this->res_file_model->update_user_res_subcatid_type($this->user_id,$dir_cat_id,$sub_cat_id,$retrieve_restype,1);
        } 
        if($is_file and $from_dir_cat_id){//是同步/知识点文件夹下的文件的话要更新redis -1 
            $this->res_file_model->update_user_res_subcatid_type($this->user_id,$from_dir_cat_id,$from_sub_cat_id,$retrieve_restype,-1);
        }
        $json = array('errorcode'=>true,'error'=>'操作成功');
        echo json_token($json);die;
    }

    // //上传的文件的详细页面  $id is file_id
    // function file_detail($file_id){ 
    //     $file_id = intval($file_id);
    //     $is_file = true;
    //     $belonging = $this->cloud_model->check_belonging($this->user_id,$file_id,$is_file);
    //     if(!$belonging || !$file_id){redirect('/teacher/cloud');}
    //     $file = $this->cloud_model->file_info($file_id,'*',0,true);
    //     // var_dump($file);die;
    //     $tpl_file = 'share_preview';
    //     if($file['file_type']==Constant::CLOUD_FILETYPE_DOC){
    //         $tpl_file = 'share_document_preview';
    //     }else{
    //         // $this->load->library('qiniu');
    //         // $file['file_path'] = $this->qiniu->qiniu_get_image($file['file_path'],0,0,600);
    //         $this->load->helper('qiniu');
    //         $file['file_path'] = qiniu_img($file['file_path']);
    //     }
    //     $this->load->helper('number');
    //     $file['file_id'] = $file_id;
    //     // $this->cloud_model->add_hit_count($file['id']);//老师访问不会加一
    //     $this->smarty->assign('file',$file);
    //     $this->smarty->assign('file_detail',true);
    //     $this->smarty->display($this->_smarty_dir.$tpl_file.'.html');
    // }

    //检查文档是否转换完成
    function doc_process($share_id){
        $share_id = intval($share_id);
        if(!$share_id){redirect("{$site_url}teacher/cloud");}
        $share_info = $this->cloud_model->get_file_by_share_id($share_id);
        if(isset($share_info[0]['queue_status']) && $share_info[0]['queue_status']==1){
            echo json_token(array('errorcode'=>true));die;
        }
        echo json_token(array('errorcode'=>false));die;
    }

    //分享内容的详细页面  $id is file_id
    // function share_detail($share_id){ 
    //     $share_id = intval($share_id);
    //     if(!$share_id){redirect("/teacher/cloud");}
    //     $is_file = true;
    //     $share_info = $this->cloud_model->get_file_by_share_id($share_id);
    //     if(!isset($share_info[0])){//没有找到该分享
    //         redirect("/teacher/cloud");;
    //     }
    //     $id = $share_info[0]['file_id'];
    //     $belonging = $this->cloud_model->check_belonging($this->user_id,$id,$is_file);
    //     if(!$belonging || !$id){redirect('/teacher/cloud');}
    //     $file = $share_info[0];//$this->cloud_model->file_info($id,'*',$class_id);
    //     $tpl_file = 'share_preview';
    //     if($file['file_type']==Constant::CLOUD_FILETYPE_DOC){
    //         $tpl_file = 'share_document_preview';
    //     }else{
    //         // $this->load->library('qiniu');
    //         // $file['file_path'] = $this->qiniu->qiniu_get_image($file['file_path'],0,0,600);
    //         $this->load->helper('qiniu'); 
    //         $file['file_path'] = qiniu_img($file['file_path']);
    //     }
    //     $this->load->helper('number');
    //     // $this->cloud_model->add_hit_count($file['id']);//老师访问不会加一
    //     $this->smarty->assign('file',$file);
    //     $this->smarty->assign('file_detail',false);
    //     $this->smarty->display($this->_smarty_dir.$tpl_file.'.html');
    // }

    //取消分享
    function del_share(){
        $id = intval($this->input->post('file_id',true));
        $share_id = intval($this->input->post('share_id',true));
        $class_id = $this->input->post('class_id',true);
        $json = array('errorcode'=>false,'error'=>'操作失败');
        if(!$share_id){
            echo json_token($json);die;
        }
        $class_id = alpha_id_num($class_id,true);
        $is_file = true;
        //班级创建者有权取消他人的分享
        $this->load->model('class/class_model');
        $c = $this->class_model->g_classinfo($class_id,'creator_id');
        $allow_del = false;
        if(isset($c['creator_id']) and $c['creator_id']==$this->tizi_uid){
            $allow_del = true;
        }else{
            $belonging = $this->cloud_model->check_belonging($this->user_id,$id,$is_file);
            $allow_del = $belonging?true:false;
        }
        if(!$allow_del){
            $json['error'] = '您只能取消分享自己的文件';
            echo json_token($json);die;
        }
        $res = $this->cloud_model->del_share($share_id);
        if($res){
            echo json_token(array('errorcode'=>true,'error'=>'操作成功'));die;
        }
        echo json_token($json);die;
    }

    /*面包屑*/
    protected function breadcrumb($p_id,&$breadcrumb_str)
    {
        if($p_id > 0){
            $p_dir_info = $this->cloud_model->get_parent_dir($p_id);
            if(isset($p_dir_info->p_id)){
                $breadcrumb_str = '<a class="cgr into_dir" data-dir-id="'.$p_dir_info->dir_id.'" href="javascript:;">'.$p_dir_info->dir_name.'</a>'.' &gt; '.$breadcrumb_str;
                $this->breadcrumb($p_dir_info->p_id,$breadcrumb_str);
            }
        }
    }

    //重命名 文件夹或文件
    function rename(){
        $is_file = intval($this->input->post('is_file',true));
        $id = intval($this->input->post('id',true));
        // $name = sub_str(trim(strip_tags($this->input->post('name',true))), 0,153,'');
        $name = sub_str(trim(($this->input->post('name',true))), 0,153,'');
        $name = filter_file_name($name);//不允许特殊字符的存在

        if(strlen($name)<1){
            $json = array('errorcode'=>false,'error'=>'请输入有效的名称');            
            echo json_token($json);die;
        }
        $belonging = $this->cloud_model->check_belonging($this->user_id,$id,$is_file);
        $json = array('errorcode'=>false,'error'=>'操作失败');
        if(!$belonging){
            echo json_token($json);die;
        }
        $ext = 0;
        $sub_cat_id = 0;
        if($is_file){
            $info = $this->cloud_model->file_info($id);
            if(!$info){
                echo json_token($json);die;
            }else{
                $pid = $info['dir_id'];
                $ext = $info['file_ext'];
                $old_name = $info['file_name'];
                $sub_cat_id = $info['sub_cat_id'];
            }
        }else{
            $info = $this->cloud_model->get_dir_info($id);
            if(!$info){
                echo json_token($json);die;
            }else{
                $pid = $info['p_id'];
                $old_name = $info['dir_name'];
            }
        }
        if($name == $old_name){
            $json = array('errorcode'=>true,'error'=>'操作成功');
            echo json_token($json);die;
        }
        $name = $this->cloud_model->check_dir_name_exist($pid,$name,$this->user_id,$is_file,$ext,$sub_cat_id);
        $res = $this->cloud_model->rename_dir_or_file($is_file,$id,$name);
        if(!$res){
            echo json_token($json);die;
        }
        $json = array('errorcode'=>true,'error'=>'操作成功','file_id'=>$id,'name'=>$name);
        echo json_token($json);die;
    }

    //分享到班级
    function share(){
        //判断有没有选择至少一个班级
        $classes = $this->input->post('class',true);
        $json = array('errorcode'=>false,'error'=>'请至少选择一个班级');
        if(empty($classes)){
            echo json_token($json);die;
        }
        if(!is_array($classes)){ //如果不是数组
            $classes = array($classes);
        }
        $this->load->model('class/classes_teacher');
        $_my_classes = $this->classes_teacher->get_bt($this->user_id,'class_id');
        $my_classes = array();
        foreach($_my_classes as $v){
            $my_classes[] = $v['class_id'];
        }
        foreach($classes as $cs=>$c){
            $_id = alpha_id_num($c,true);
            if(!in_array($_id,$my_classes)){
                unset($classes[$cs]); //清除不是我的班级
            }
            $classes[$cs] = $_id;
        }
        // $content = sub_str(strip_tags(trim($this->input->post('desc',true))),0,1500,'');
        $file_id = ($this->input->post('file_id',true));
        $this->load->helper('array');
        $file_ids = explode_to_distinct_and_notempty($file_id);
        // var_dump($file_ids,$share_from,$classes);die;
        if(!($file_id)){
            $json['error'] = '没有找到文件';
            echo json_token($json);die;
        }
        foreach($file_ids as $k=>$val){
            $param = array();
            $count = count($classes);
            for($i=0;$i<$count;$i++){
                $param[$i]['file_id'] =$val;
                $param[$i]['content'] = '';//$content;
                $param[$i]['user_id'] = $this->user_id;
                $param[$i]['class_id'] = $classes[$i];
                $param[$i]['create_time'] = time();
                $param[$i]['love_count'] = 0;
                $param[$i]['download_count'] = 0;
                $param[$i]['is_del'] = 0;
            }
            $res = $this->cloud_model->share_to_classes($param);
        }
        
        if(!$res){
            $json['error']='操作失败';
            echo json_token($json);die;
        }
        //如果是从我的备课里的收藏来的，就会减去相应的下载次数
        // $from_my_beike = $this->input->post('from_my_beike',true,true,false);
        // if($from_my_beike){
        //     $this->load->model('redis/redis_model');
        //     if($this->redis_model->connect('download')){
        //         foreach($file_ids as $k=>$val){
        //             $_lesson_doc_key=date('Y-m-d').'_lesson_doc_key_'.$this->tizi_uid;
        //             $_lesson_month_down_key = date('Y-m').'_lesson_doc_key_'.$this->tizi_uid;
        //             $this->cache->redis->incr($_lesson_doc_key);/*当日下载统计*/
        //             $this->cache->redis->incr($_lesson_month_down_key);/*当月下载统计*/
        //         }
        //     }
        // }

        $al_c = alpha_id_num($classes[0]);
        $site_url = site_url();
        $url = "{$site_url}teacher/class/{$al_c}/share";
        $json = array('error' => '操作成功','errorcode'=>true,'url'=>$url);
        echo json_token($json);die;
    }

    //进入某个文件夹
    function dir($dir_id){
        $dir_id = intval($dir_id);
        if(!$dir_id || !$this->cloud_model->check_belonging($this->user_id,$dir_id)){
            $this->register_model->set_current_cloud_dir(0);//设置当前目录cookie
            echo json_token(array('errorcode'=>false,'href_to'=>'teacher/cloud'));die;
            //redirect(site_url('teacher/cloud'));
        }
        $go_back =$this->input->get('back',true);
        $dir_info = $this->cloud_model->get_dir_info($dir_id);
        if($go_back){ //获取父节点id
            if(isset($dir_info['p_id']) && $dir_info['p_id']){
                $dir_id = $dir_info['p_id'];
            }else{
                $dir_id = 0;
            }
        }
        $dir_name = '';
        $breadcrumb_str = '';
        if($dir_id > 0){
            $p_dir_info = $this->cloud_model->get_dir_info($dir_id);
            $dir_name = $p_dir_info['dir_name'];
            $this->breadcrumb($p_dir_info['p_id'],$breadcrumb_str);
        }
        //echo $breadcrumb_str;die;
        $this->register_model->set_current_cloud_dir($dir_id);//设置当前目录cookie
        
        $total = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$dir_id,1,0,true);
        $data = $this->cloud_model->get_dir_child_by_p_id($this->user_id,$dir_id);
        if($data['file']){
            $this->load->model('resource/res_file_model');
            $this->res_file_model->is_pfop_done($data['file']);
        }
        /*分页*/
        $pages =  self::get_pagination(1,$total['total_page'],'cloud_page');
        $this->smarty->assign('total_count', $total['all_total']);
        $this->smarty->assign('data',$data);
        $this->smarty->assign('dir_id',$dir_id);
        $this->smarty->assign('dir_name',$dir_name);
        $this->smarty->assign('breadcrumb', $breadcrumb_str);
        $this->smarty->assign('pages',$pages);
        $json['errorcode'] = true;
        $json['html']= $this->smarty->fetch($this->_smarty_dir.'cloud_file_list.html');
        $json['old_cloud_breadcrumbs']= $this->smarty->fetch($this->_smarty_dir.'old_cloud_breadcrumbs.html');
        echo json_token($json);die;
    }

    protected function get_pagination($page_num,$total,$func)
    {
        $this->load->library('pagination'); 
        $config['total_rows']       = $total; //为页总数
        $config['per_page']       = Constant::CLOUD_FILE_PER_PAGE_NUM;
        $config['cur_page']         = $page_num;
        $config['ajax_func']        = $func;
        //获取分页
        $this->pagination->initialize($config);
        $pages = $this->pagination->create_ajax_links();
        return $pages;
    }

    //新建文件夹
    function mkdir(){
        $num = $this->cloud_model->dir_count($this->user_id);
        if($num>=Constant::CLOUD_DIR_NUM_MAX){
            echo json_token(array('errorcode'=>false,'error'=>"最多只能创建".Constant::CLOUD_DIR_NUM_MAX.'个文件夹'));die;
        }
        $current_dir_id = intval($this->input->post('cur_dir_id',true));
        if($current_dir_id != 0 ){
            $param['depth'] = $this->cloud_model->get_dir_info($current_dir_id,'depth'); //获取该id对应的depth，然后加一
            $param['depth'] = $param['depth']['depth']+1;
        }else{
            $param['depth'] = 0;
        }
        $is_file = false;
        $param['dir_name'] = sub_str(trim(($this->input->post('dir_name',true))), 0,153,'');
        $param['dir_name'] = filter_file_name($param['dir_name']);//不允许特殊字符的存在
        if(strlen($param['dir_name'])<1){
            $json = array('errorcode'=>false,'error'=>'请输入有效的名称');            
            echo json_token($json);die;
        }
        $param['dir_name'] = $this->cloud_model->check_dir_name_exist($current_dir_id,$param['dir_name'],$this->user_id,$is_file);
        $param['create_time'] = time();
        $param['user_id'] =$this->session->userdata("user_id");
        $param['p_id'] = $current_dir_id;
        
        $res = $this->cloud_model->insert_user_directory($param);
        if($res){
            echo json_token(array('errorcode'=>true,'error'=>'创建成功','dir_name'=>$param['dir_name']));die;
        }
        echo json_token(array('errorcode'=>false,'error'=>'系统繁忙，请稍候再试'));die;
    }

    //删除上传的文件
    function del(){
        $is_file = intval($this->input->post('is_file',true));
        $id = intval($this->input->post('file_id',true));
        $belonging = $this->cloud_model->check_belonging($this->user_id,$id,$is_file);
        $json = array('errorcode'=>false,'error'=>'操作失败');
        if(!$belonging){
            echo json_token($json);die;
        }
        $is_cat_file = false;
        if(!$is_file){//如果是文件夹，要判断是否是同步/知识点文件夹，是的话要更新redis
            $cat_id = $this->cloud_model->get_dir_info($id,'cat_id');
            $is_cat_dir = isset($cat_id['cat_id'])?$cat_id['cat_id']:false;
        }else{//如果是文件，要判断是否在同步/知识点文件夹下，是的话另外更新redis
            $info = $this->cloud_model->file_info($id,'*',0,true);
            if(isset($info['dir_cat_id']) and $info['dir_cat_id'] and 
                isset($info['sub_cat_id']) and $info['sub_cat_id'] and
                isset($info['resource_type']) and $info['resource_type'] ){
                $is_cat_file = true;
            }
        }
        $res = $this->cloud_model->del_dir_or_file($this->user_id,$id,$is_file);
        if(!$res){
            //删除七牛上的资源，暂时先不删
            // $file_info =$this->cloud_model->file_info($id);
            // if(!$file_path){
            //     $file_key = $file_info['file_path'];
            //     $this->load->library('qiniu');
            //     $this->qiniu->qiniu_del($file_path);
            // }
            echo json_token($json);die;
        }
        if(!$is_file and $is_cat_dir){//是同步/知识点文件夹的话要更新redis
            $this->load->model('resource/res_dir_model');
            $this->res_dir_model->del_cat_in_redis($this->user_id,$is_cat_dir);
        }
        if($is_file and $is_cat_file){//是同步/知识点文件夹下的文件的话要更新redis
            $this->load->model('resource/res_file_model');
            $this->res_file_model->update_user_res_subcatid_type($this->user_id,$info['dir_cat_id'],$info['sub_cat_id'],$info['resource_type'],-1);
        }
        $json = array('errorcode'=>true,'error'=>'操作成功','file_id'=>$id,'is_file'=>$is_file);
        echo json_token($json);die;
    }

    //获取一个文件夹下的所有该类型的文件
    function get_files_render(){
        $dir_id = intval($this->input->get('dir_id',true));
        $filetype=intval($this->input->get('filetype',true));
        $sub_cat_id=intval($this->input->get('sub_cat_id',true));
        if($filetype==Constant::CLOUD_FILETYPE_OTHER){
            $filetype = 0;
        }
        $files = $this->cloud_model->get_files_in_a_dir($this->user_id,$dir_id,$filetype,$sub_cat_id);

        foreach($files as $k=>$v){
            $v['file_full_name'] = $v['file_name'];
            $v['file_name'] = sub_str($v['file_name'].'.'.$v['file_ext'],0,60);
            $files[$k] = $v;
        }
        $this->smarty->assign('files',$files);
        $html = '';
        $html = $this->smarty->fetch($this->_smarty_dir.'class_share_file_table.html');
        echo json_token(array('errorcode'=>true,'error'=>'','html'=>$html));
    }

    /*先下载到我们的server*/
    public function download(){
        $this->load->helper('download');
        $file_name = $this->input->get('file_name');
        if(!isset($file_name) || empty($file_name)) die();
        $file_id = intval($this->input->get('file_id'));
        if(!$file_id)die;
        $file_path = $this->input->get('url');
        $file_path = $this->cloud_model->get_download_file_path($file_id,$file_path);
        $red = "teacher/cloud";
        if ($this->tizi_utype != Constant::USER_TYPE_TEACHER){
            $red = 'ban';
        }   
        $file_get_contents=tizi_get_contents($file_path,$red,5,7200);
        
        if(stripos($this->input->server('HTTP_USER_AGENT'), 'windows'))
        {
            force_download(iconv('utf-8', 'gbk//IGNORE', $file_name), $file_get_contents); 
        }
        else
        {
            force_download($file_name, $file_get_contents);
        }
    }
}

