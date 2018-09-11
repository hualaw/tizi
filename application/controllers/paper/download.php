<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once (dirname(dirname(__FILE__)).'/Controller.php');

class Download extends Controller {
	
	private $_redis=false;
	private $_paper_key='';

    public function __construct(){
        parent::__construct();
        $this->load->model('paper/paper_download_log');
        $this->load->model('paper/paper_model');
		$this->load->model('login/register_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('user_data/teacher_data_model');
        $this->load->model('paper/paper_save_log');
		$this->load->model('redis/redis_model');
        $this->load->helper('array');
        $this->load->helper('download');
        $this->load->library('curl');
        $this->load->config('download');
        $this->load->library('credit');

		if($this->redis_model->connect('download'))
		{
			$this->_redis=true;
			$this->_paper_key=date('Y-m-d').'_paper_'.$this->tizi_uid;
            $this->_paper_month_key=date('Y-m').'_paper_'.$this->tizi_uid;
		}

        $user=$this->register_model->get_user_info($this->tizi_uid);
        /*
        $now=date("H");
        if($user['user']->phone_verified != 1 && ($now >= Constant::AUTH_DOWNLOAD_START || $now <= Constant::AUTH_DOWNLOAD_END))
        {
            $error=str_replace("%s",'<a href="'.site_url('teacher/user/setting').'">，点击验证</a>',$this->lang->line('error_invalid_phone_verified'));
            $response = array('errorcode'=>false, 'error'=>$error, 'status'=>'13');
            echo json_token($response);
            exit();
        }
        else if(!$user['user']->phone_verified)
        {
            $error=str_replace("%s",'',$this->lang->line('error_invalid_phone_verified'));
            $response = array('errorcode'=>false, 'error'=>$error, 'status'=>'13');
            echo json_token($response);
            exit();
        }
        */
    }

    public function paper()
    {
        // if($this->tizi_utype != Constant::USER_TYPE_TEACHER)
        // {
        //     $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_paper_download_utype'), 'status'=>'31');
        //     echo json_token($response);
        //     exit();
        // }

        if(!$this->input->post('captcha_name',true))
        {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_captcha_code'), 'status'=>'21');
            echo json_token($response);
            exit();
        }

		if($this->_redis)
		{
			$count = $this->cache->get($this->_paper_key);
			if(!$count)
			{
				$this->cache->save($this->_paper_key,0,86400);
			}
            $month_count = $this->cache->get($this->_paper_month_key);
            if(!$month_count)
            {
                $this->cache->save($this->_paper_month_key,0,2678400);
            }
            /*
            if($count >= Constant::PAPER_DOWNLOAD_CAPTCHA_LIMIT)
            {
                $captcha=$this->input->post('captcha_word');
                $captcha_name=$this->input->post('captcha_name');
                $check_captcha=$this->captcha->validateCaptcha($captcha,$captcha_name);
                if(!$check_captcha)
                {
                    $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_captcha_code'), 'status'=>'21');
                    echo json_token($response);
                    exit();
                }
            }
			if($count >= Constant::PAPER_DOWNLOAD_LIMIT)
			{
				$response = array('errorcode'=>false, 'error'=>$this->lang->line('error_paper_download_limit'), 'status'=>'15');
                echo json_token($response);
                exit();
			}
            */
            $privilege = $this->credit->userlevel_privilege($this->tizi_uid);
            if($month_count >= $privilege['privilege']['paper_permonth']['value'])
            {
                $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_paper_download_month_limit'), 'status'=>'15');
                echo json_token($response);
                exit();
            }
		}		

        $save_log_id = $this->input->post('save_log_id');

        $paper_obj_expand = $this->load_obj_expand('paper');

        $save_log=$this->{'paper_save_log'}->get_save_log($save_log_id,$this->tizi_uid);
        $paper_id=isset($save_log->paper_id)?$save_log->paper_id:0;
        if(!$paper_id) {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_invalid_paper_id'), 'status'=>'8');
            echo json_token($response); exit;
        }
        $title=$save_log->logname;
        $paper_save=$this->paper_model->save_paper($paper_id,$this->tizi_uid,$title,true,true,Constant::LOCK_TYPE_DOWNLOAD,true);
        $new_paper_id=$paper_save['paper_id'];
        $paper = $this->get_paper($paper_id,false);

        $api_uri = $this->config->item('paper_api_uri');
		$base_api_uri = $this->config->item('base_paper_api_uri');
        $download_api_uri = $this->config->item('download_api_uri');
        
        $download_link=$this->curl_download($api_uri,$paper,$paper_obj_expand);
        $insert_success=$this->paper_download_log->add_download_record($this->tizi_uid, $title, $new_paper_id, $paper_obj_expand['paper_version'], $paper_obj_expand['paper_size'], $paper_obj_expand['paper_type'], $download_link['link']);
		if($insert_success&&$this->_redis) 
        {
            $this->cache->redis->incr($this->_paper_key);
            $this->cache->redis->incr($this->_paper_month_key);
        }
        if($download_link['link']) $this->teacher_data_model->update_teacher_download_default($this->tizi_uid,json_encode($paper_obj_expand));
        echo json_token(array(
            'dtoken'=>$download_link['token'],
            'durl'=>$download_api_uri,
            'dlink'=>urlencode($download_link['link']),
            //'url'=>urlencode(str_replace($base_api_uri,"",$download_link['link'])), 
            'file_name'=>urlencode($title . '.' . $paper_obj_expand['paper_version']), 
            'fname'=>$title, 'errorcode'=>true, 'error'=>'Ok', 'status'=>'0'));
        exit(); 
    }

    public function card() 
    {
        $save_log_id = $this->input->post('save_log_id');
        //$subject_id = $this->input->post('subject_id');

        $paper_obj_expand = $this->load_obj_expand('card');

        $save_log=$this->paper_save_log->get_save_log($save_log_id,$this->tizi_uid);
        $paper_id=isset($save_log->paper_id)?$save_log->paper_id:0;
        if(!$paper_id) {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_invalid_paper_id'), 'status'=>'8');
            echo json_token($response); exit;
        }

        //$paper_id = $this->get_paper_id($subject_id,$this->tizi_uid);
        $paper = $this->get_paper($paper_id);
        $title=$save_log->logname;
        //$logname = $paper['paper_config']->main_title;
        unset($paper['question']);

        $api_uri = $this->config->item('card_api_uri');
        $base_api_uri = $this->config->item('base_card_api_uri');
        $download_api_uri = $this->config->item('download_api_uri');
        
        $download_link=$this->curl_download($api_uri,$paper,$paper_obj_expand);
        if($download_link['link']) $this->teacher_data_model->update_teacher_download_default($this->tizi_uid,json_encode($paper_obj_expand),'card');
        echo json_token(array(
            'dtoken'=>$download_link['token'],
            'durl'=>$download_api_uri,
            'dlink'=>urlencode($download_link['link']),
            //'url'=>urlencode(str_replace($base_api_uri,"",$download_link['link'])), 
            'file_name'=>urlencode($title . '－答题卡' . '.' . $paper_obj_expand['paper_version']), 
            'fname'=>$title . '－答题卡', 'errorcode'=>true, 'error'=>'Ok', 'status'=>'0'));
        exit();
    }

    public function homework() 
    {
		$this->paper('homework');     
    }

    //学生/老师下载布置好的作业
    public function down_assignment($namespace = 'paper') 
    {   
        $redis_key = date('Y-m-d').'_assignment_'.$this->tizi_uid;
        if($this->_redis) {
            $count = $this->cache->get($redis_key);
            if(!$count) {
                $this->cache->save($redis_key,0,86400);
            }
            if($count >= Constant::PAPER_DOWNLOAD_LIMIT) {
                $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_'.$namespace.'_download_limit'), 'status'=>'15');
                echo json_token($response);
                exit();
            }
        }       
        $assid = $this->input->post('assid',true);
        $this->load->model('exercise_plan/homework_assign_model');
        $hwinfo = $this->homework_assign_model->get_assigned_homework_info_by_id($assid);
        $paper_id = $hwinfo['paper_id'];

        if(!$paper_id) {
            $response = array('errorcode'=>false, 'error'=>$this->lang->line('error_invalid_paper_id'), 'status'=>'8');
            echo json_token($response); exit;
        }
        $paper_obj_expand = $this->load_obj_expand('paper');
        $paper = $this->{'get_paper'}($paper_id,false);
        $hw_name = $hwinfo['name'];
        $title = $hw_name?$hw_name:'作业';
        
        $api_uri = $this->config->item($namespace.'_api_uri');
        $base_api_uri = $this->config->item('base_'.$namespace.'_api_uri');
        $download_api_uri = $this->config->item('download_api_uri');

        $download_link=$this->curl_download($api_uri,$paper,$paper_obj_expand,$namespace);
        // var_dump($download_link);die;
        if($download_link['link']) $this->cache->redis->incr($redis_key);
        echo json_token(array(
            'dtoken'=>$download_link['token'],
            'durl'=>$download_api_uri,
            'dlink'=>urlencode($download_link['link']),
            //'url'=>urlencode(str_replace($base_api_uri,"",$download_link['link'])), 
            'file_name'=>urlencode($title . '.' . $paper_obj_expand['paper_version']), 
            'fname'=>$title, 'errorcode'=>true, 'error'=>'Ok', 'status'=>'0'));
        exit();
    }

    public function force_download (){
		$download_type = $this->input->get('download_type');
        $url = $this->input->get('url');
        $redirect_type = $download_type;
		$base_url = $this->config->item('base_'.$download_type.'_api_uri');
		$url=$base_url.urldecode($url);
        $file_name = urldecode($this->input->get('file_name'));
        if(empty($file_name)||empty($url)) die();
        switch ($redirect_type) {
            case 'paper':
            case 'card':$redirect='teacher/paper/center';break;
            case 'homework':$redirect='teacher/homework/center';break;
            default:$redirect=tizi_url();break;
        }
        $file_get_contents=tizi_get_contents($url,$redirect);
        if(stripos($this->input->server('HTTP_USER_AGENT'),'windows'))
        {
            force_download(iconv('utf-8', 'gbk//IGNORE', $file_name), $file_get_contents); 
        }
        else
        {
            force_download($file_name, $file_get_contents);
        }   
    }

    private function curl_download($api_uri,$paper,$paper_obj_expand)
    {
        $paper_id = $paper['paper_config']->id;
        $md5_key = 'enYGxAFbHvo7wuMI';
        $paper_obj = array_get($paper, 'paper_config');
        $paper_questions = array_get($paper, 'paper_question');

        $section_qnum=array(
            'section_one_qnum'=>$paper['question_total'][1],
            'section_two_qnum'=>$paper['question_total'][2]
        );
        if($paper['question_total'][1]+$paper['question_total'][2]==0) {
			$error_lang='error_paper_no_question';
            $response = array('errorcode'=>false,'error'=>$this->lang->line($error_lang), 'status'=>'5');
            echo json_token($response); exit;
        }

        $paper_obj = array_merge((array)$paper_obj, $paper_obj_expand, $section_qnum);
        $paper['paper_config'] = $paper_obj;

        $json_str = json_encode($paper, true);
        $param = array('paper_data'=>$json_str, 'verifycode'=>md5(md5($md5_key) . $paper_id), 'paperid'=>$paper_id);
        set_time_limit(Constant::MAX_DOWNLOAD_TIMEOUT);
        $this->curl->create($api_uri);
        $this->curl->option('connecttimeout',Constant::MAX_CONNECT_TIMEOUT);
        $this->curl->option('timeout',Constant::MAX_DOWNLOAD_TIMEOUT - 5);
        $this->curl->post($param);
        $this->curl->execute();
        $curl_obj = & $this->curl;
        if(property_exists($curl_obj, 'last_response'))
        {
            $curl_response = $curl_obj->last_response;
            $download_info = json_decode($curl_response, true);
            list($status, $msg, $token)= array_get($download_info, array('status', 'msg', 'token'));
            if($status == 'success') 
            {
                $download_link = array('link'=>$msg,'token'=>$token);
                return $download_link;
            } 
            else 
            {
                $response = array('errorcode'=>false,'error'=>$this->lang->line('error_download_failed'), 'status'=>'6', 'msg'=>$msg);
                log_message('error_tizi','1800181:Curl return error',array('status' => json_encode($download_info), 'data' => $param));
                echo json_token($response); exit;
            }
        } 
        else 
        {
            log_message('error_tizi','180018:Curl download error',array('status' => strval($curl_obj->error_code),'data' => $param));
            //Note: get curl error
            $response = array('errorcode'=>false,'error'=>$this->lang->line('default_error'), 'status' => strval($curl_obj->error_code));
            echo json_token($response); exit;
        }
    }

    private function load_obj_expand($mode)
    {
        //$subject_id = $this->input->post('subject_id');       
        $save_log_id = $this->input->post('save_log_id');       
        $download_type = $this->input->post('download_type');

        $paper_type = $this->input->post('paper_type');
        $paper_version = $this->input->post('paper_version');       
        $paper_size = $this->input->post('paper_size');
        $paper_style = $this->input->post('paper_style');

        $require_paper_style = array('default', 'normal', 'test', 'task');
        $require_paper_size = array('A4', 'A4H', 'A3', '16K', '8KH', 'A3H', 'B4');
        $require_paper_type = array('student', 'teacher', 'normal','answer');
        $require_download_type = array('paper', 'card');
        $require_paper_version = array('doc', 'docx');
        if($mode == 'card') {
            $require_paper_type = array('common', 'normal', 'compress');
        }
        if($mode == 'homework')
        {
            $require_download_type = array('homework');
        }

        //only paper
        if($mode=='paper'&&(empty($paper_style) || !in_array($paper_style, $require_paper_style))) {
            $response = array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_paper_style'), 'status'=>'1');
            echo json_token($response); exit;
        }

        //no card
        if($mode!='card'&&(empty($paper_size) || !in_array($paper_size, $require_paper_size))) {
            $response = array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_paper_size'), 'status'=>'3');
            echo json_token($response); exit;
        }

        //all
        if(empty($save_log_id)) {
            $response = array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_paper_save_log'), 'status'=>'9');
            echo json_token($response); exit;
        }
        if(empty($download_type) || !in_array($download_type, $require_download_type)) {
            var_dump($download_type,$require_download_type);die;
            $response = array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_download_type'), 'status'=>'7');
            echo json_token($response); exit;
        }       
        if(empty($paper_version) || !in_array($paper_version, $require_paper_version)) {
            $response = array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_paper_version'), 'status'=>'2');
            echo json_token($response); exit;
        }
        if(empty($paper_type) || !in_array($paper_type, $require_paper_type)) {
                $response = array('errorcode'=>false,'error'=>$this->lang->line('error_'.$mode.'_paper_type'), 'status'=>'4');
                echo json_token($response); exit;
        }
        $paper_obj_expand = array(
            'paper_type'=>$paper_type, 
            'paper_version'=>$paper_version,
            'paper_size'=>$paper_size,
            'paper_style'=>$paper_style,
			'download_type'=>$download_type
        );
        return $paper_obj_expand;
    }

}
