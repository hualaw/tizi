<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lesson_Document extends MY_Controller {
	
	private $_smarty_dir="teacher/lesson/";
	private $_user_id=0;
	private $_user_type=0;
    private $_lesson_doc_key='';
    private $_redis=false;
    public function __construct()
    {
        parent::__construct();
        $this->_user_id=$this->session->userdata('user_id');
        $this->_user_type=$this->session->userdata('user_type');
        $this->load->model('login/register_model');
        $this->load->model('redis/redis_model');

        if($this->redis_model->connect('download'))
        {
            $this->_redis=true;
            $this->_lesson_doc_key=date('Y-m-d').'_lesson_doc_key_'.$this->_user_id;
            $this->_lesson_month_down_key = date('Y-m').'_lesson_doc_key_'.$this->_user_id;
        }

        $this->load->helper('download');
        $this->load->config('download');
        $this->load->model('lesson/document_model');
        $this->load->model('lesson/document_download_model');
    }

	 public function flash_get_json(){
        $doc_num = $this->input->post('id');
        if(empty($doc_num))
        {
            $referer_arr = explode('/', $this->input->server('HTTP_REFERER'));
            $doc_num = $referer_arr[count($referer_arr)-1];
        }

        $doc_id = alpha_id($doc_num,true);
        if(!$this->document_model->is_exist_doc($doc_id))  
        {
            $files_info = array('status'=>1);
        }
        else
        {
            $file_data  = $this->document_model->get_single_doc_info($doc_id);
            /*判断swf文件走网盘的bucket还是备课的*/
            if($file_data->user_id>0)$uri_api=$this->config->item('cloud_preview_api_uri');
            else $uri_api=$this->config->item('lesson_prepare_api_uri');

            $swf_list   = $this->document_model->get_preview_files_new($doc_id,$uri_api);
            
            if($swf_list)
            {
                $files_info = array(
                'status'        =>99,
                'page_total'    =>count($swf_list),
                'files_url'     =>$swf_list,
                'file_id'       =>$doc_num,
                'file_ext'      =>$file_data->file_ext,
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

    public function download_verify()
    {
        if(!$this->input->is_ajax_request()) die;
        self::ajax_verify();
        $error_arr = array();
        $file_id = $this->input->post('file_id',true);
        $is_mine = $this->input->post('is_mine',true,true,false);//下载私有的文件，实体在cloud_user_file中
        if(!isset($file_id) || empty($file_id))
        {
            $error_arr['errorcode'] = false;
            $error_arr['error'] = $this->lang->line('error_down_invalid');
        }
        else
        {
            if($is_mine){
                $this->load->model('cloud/cloud_model');
                $obj = $this->cloud_model->file_info($file_id,'*',0,true);
                $file_data = (object)$obj;
            }else{
                $file_id = alpha_id($file_id, true);
                $file_data = $this->document_model->get_single_doc_info($file_id);
            }
            if($file_data)
            {
                $count = 0;
                $month_cout = 0;
                if($this->_redis)
                {
                    $count = $this->cache->get($this->_lesson_doc_key);
                    $month_cout = $this->cache->get($this->_lesson_month_down_key);
                    if(!$count){
                        $count = $this->document_download_model->get_user_download_cout($this->_user_id);
                        $this->cache->save($this->_lesson_doc_key,$count,86400);
                    }
                    if(!$month_cout){
                        $this->cache->save($this->_lesson_month_down_key,0,86400);
                    }
                }
                else
                {
                    $count = $this->document_download_model->get_user_download_cout($this->_user_id);
                }
                //下载验证码验证
                // if($count >= Constant::DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT)
                // {
                //     $captcha=$this->input->post('captcha_word');
                //     $captcha_name=$this->input->post('captcha_name');
                //     $check_captcha=$this->captcha->validateCaptcha($captcha,$captcha_name);
                //     if(!$check_captcha)
                //     {
                //         $error_arr['errorcode'] = false;
                //         $error_arr['error'] = $this->lang->line('error_captcha_code');
                //         echo json_token($error_arr);
                //         exit;
                //     }
                // }
                $this->load->library('credit');
                $privilege = $this->credit->userlevel_privilege($this->_user_id);
                $lesson_month_down_limit = $privilege['privilege']['lesson_permonth']['value'];
                if($month_cout <= $lesson_month_down_limit)
                {
                    //下载非自己上传的文件才减少下载量
                    if($file_data->user_id!=$this->_user_id){
                        $result = $this->document_download_model->add_download_info($this->_user_id, $file_data);
                        if($result['errorcode'] && $this->_redis){
                            $this->cache->redis->incr($this->_lesson_doc_key);/*当日下载统计*/
                            $this->cache->redis->incr($this->_lesson_month_down_key);/*当月下载统计*/
                        }
                    }
                     
                    $error_arr['errorcode'] = true;
                    $error_arr['error'] = 'Successed';
                    $error_arr['fname'] = $file_data->file_name;
                    $file_info = pathinfo($error_arr['fname']);
                    $error_arr['file_name'] = urlencode($file_data->file_name.'.'.$file_data->file_ext);
                    $error_arr['type'] = $file_data->user_id>0?'user':'lesson';
                    if($file_data->file_ext and strpos(Constant::CLOUD_DOC_TYPES,$file_data->file_ext)!==false){
                        $error_arr['file_path'] = isset($file_info['extension'])?self::make_download_url($file_data->file_path,urlencode($error_arr['fname']),$error_arr['type']):
                        self::make_download_url($file_data->file_path,$error_arr['file_name'],$error_arr['type']);
                        $error_arr['source'] = 'oss';
                    }else{
                        $this->load->helper('qiniu');
                        $error_arr['file_path'] = isset($file_info['extension'])?qiniu_download($file_data->file_path,urlencode($file_data->file_name)):
                        qiniu_download($file_data->file_path,urlencode($file_data->file_name.'.'.$file_data->file_ext));
                        $error_arr['source'] = 'qiniu';
                    }
					
					//任务系统_下载一个文档
					$user_id = $this->session->userdata("user_id");
					$this->load->library("task");
					$this->task->exec($user_id, "download_lesson");
                }
                else
                {
                    $error_arr['errorcode'] = false;
                    $error_arr['error'] = str_replace("%s",$lesson_month_down_limit,$this->lang->line('notice_up_max_down_per_month'));
                }
                
            }
            else
            {
                $error_arr['errorcode'] = false;
                $error_arr['error'] = $this->lang->line('error_file_wrong');
            }
        }
        echo json_token($error_arr);
        exit;
    }

    protected function make_download_url($file_path,$file_name,$type)
    {
        switch ($type) {
            case 'user':
                $this->load->config('upload',true,true);
                $config = $this->config->item('upload');
                $base_url  = $config['domain_document']; 
                $file_path = $base_url.$file_path;
                break;
            case 'lesson':
                $base_url  = $this->config->item('doc_download_api_uri');
                $file_path = $base_url.$file_path;
                break;
            default:
                $base_url  = $this->config->item('doc_download_api_uri');
                $file_path = $base_url.$file_path;
                break;
        }
        if(strpos($file_path, 'http://')===false)$file_path='http://'.$file_path;
        $file_path = urlencode($file_path);
        $salt = $this->config->item('encryption_key');
        $access_key = md5($this->_user_id);
        $time = time();
        $secret_key = sha1($access_key.$salt.$time);
        $download_url = $this->config->item('doc_download_service_uri');
        $return_url = $download_url.'?fileUrl='.$file_path.'&fileName='.$file_name;
        $return_url .= '&secretKey='.$secret_key.'&accessKey='.$access_key.'&time='.$time;
        return $return_url;

    }

    public function download()
    {
        $file_name = $this->input->get('file_name',true);
        $file_path = $this->input->get('url',true);
        $type  = $this->input->get('type',true);
        $type = isset($type)&&!empty($type)?$type:'lesson';
        switch ($type) {
            case 'user':
                $this->load->config('upload',true,true);
                $config = $this->config->item('upload');
                $base_url  = $config['domain_document']; 
                $file_path = $base_url.urldecode($file_path);
                break;
            case 'lesson':
                $base_url  = $this->config->item('doc_download_api_uri');
                $file_path = $base_url.urldecode($file_path);
                break;
            default:
                $base_url  = $this->config->item('doc_download_api_uri');
                $file_path = $base_url.urldecode($file_path);
                break;
        }
        if(strpos($file_path, 'http://')===false)$file_path='http://'.$file_path;
        if(!isset($file_name) || empty($file_name)) die();
        $file_get_contents=tizi_get_contents($file_path,"teacher/lesson/prepare");
        if(stripos($this->input->server('HTTP_USER_AGENT'), 'windows'))
        {
            force_download(iconv('utf-8', 'gbk//IGNORE', $file_name), $file_get_contents); 
        }
        else
        {
            force_download($file_name, $file_get_contents);
        }
    }

    protected function ajax_verify()
    {
        if(!$this->_user_id)
        {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_login'), 'status'=>'10');
            echo json_token($response); exit;
        }
        else if($this->_user_type!=Constant::USER_TYPE_TEACHER)
        {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_user_type_teacher'), 'status'=>'11');
            echo json_token($response); exit;
        }

        $user=$this->register_model->get_user_info($this->_user_id);
        if($user['user']->register_subject == null) {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_invalid_register_subject'), 'status'=>'12');
            echo json_token($response); exit;
        }
    
    }

    public function favorite()
    {
        $doc_id = $this->input->get('id',true);
        $category_id = $this->input->get('category',true);
        $doc_type = $this->input->get('type',true);
        $doc_type = in_array(intval($doc_type),array(3,5,7))?3:intval($doc_type);
        $new_type_arr = Constant::new_doc_type(0,true);
        $res_type = in_array($doc_type, $new_type_arr)?end(array_keys($new_type_arr,$doc_type)):8;
        if($doc_id && $category_id){
            $doc_id=alpha_id($doc_id,true);
            $this->load->model('lesson/document_favorite_model','favorite');
            $exist_info=$this->favorite->is_favorite_exist($this->_user_id,$doc_id);
            $file_owner_id = $this->document_model->get_file_owner($doc_id);
            if($file_owner_id==$this->_user_id){
                $json['errorcode']=false;
                $json['error']='抱歉，不可以收藏自己分享的文件！';
            }elseif($exist_info && $exist_info->is_del==0){
                $json['errorcode']=false;
                $json['error']='您已经收藏过此文档了！';
            }elseif($exist_info && $exist_info->is_del==1){
                $this->favorite->update($this->_user_id,$doc_id,array('is_del'=>0));
                $json['errorcode']=true;
                $json['error']='';
            }else{
                $this->load->model('question/question_category_model');
                $course_list=$this->question_category_model->get_single_path($category_id,'*');
                $dir_cat_id=isset($course_list[1])?$course_list[1]->id:0;
                $sub_cat_id=!empty($course_list)?end($course_list)->id:0; 
                if($dir_cat_id==0 && $sub_cat_id==0){
                    $json['errorcode']=false;
                    $json['error']='很抱歉，该文档无法收藏！';
                }else{
                    $insert_data=array(
                        'user_id'=>$this->_user_id,
                        'doc_id'=>$doc_id,
                        'res_type'=>$res_type,
                        'dir_cat_id'=>$dir_cat_id,
                        'sub_cat_id'=>$sub_cat_id,
                        'add_time'=>time());
                    $status = $this->favorite->insert($insert_data);
                    if($status){
                        $json['errorcode']=true;
                        $json['error']='';
                    }else{
                        $json['errorcode']=false;
                        $json['error']='服务器繁忙，请稍后再试！';
                    }
                }
                
            }
            
        }else{
            $json['errorcode']=false;
            $json['error']='服务器繁忙，请稍后再试！';
        }
        echo json_token($json);die;
    }
	
}
	
/* End of file lesson_document.php */
/* Location: ./application/controllers/lesson/lesson_document.php */

