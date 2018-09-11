<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tips extends MY_Controller {

    private $_redis=false;

    function __construct()
    {
        parent::__construct();
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('tips'))   
        {
            $this->_redis=true;
        }
    }
	
    function get_tips()
    {
        $tips=array('errorcode'=>true,'tips'=>'','status'=>'');
        $tips_name=$this->input->get('tips_name');
        $with_cookie=$this->input->get('with_cookie');
        if($tips_name)
        {
            if($with_cookie)
            {
                $tips['status']=$this->input->cookie(Constant::COOKIE_TZTIPS.$tips_name);
                if(empty($tips['status']))
                {
                    $tips['status']=false;
                    $tips['tips']=$this->tips($tips_name);
                }
            }
            else if($this->tizi_uid && $this->_redis)
            {
                $tips['status']=$this->cache->redis->hget($tips_name,$this->tizi_uid);
                if(empty($tips['status']))
                {
                    $tips['status']=false;
                    $tips['tips']=$this->tips($tips_name);
                }
            }
        }
        echo json_token($tips);
        exit;
    }

    function close_tips()
    {
        $tips=array('errorcode'=>true,'tips'=>'','status'=>'');
        $tips_name=$this->input->post('tips_name');
        $with_cookie=$this->input->post('with_cookie');
        if($tips_name)
        {
            if($with_cookie)
            {
                $this->input->set_cookie(Constant::COOKIE_TZTIPS.$tips_name,date('Y-m-d H:i:s'),Constant::COOKIE_TIPS_EXPIRE_TIME);
            }
            else if($this->tizi_uid && $this->_redis)
            {
                $tips['status']=$this->cache->redis->hset($tips_name,$this->tizi_uid,date('Y-m-d H:i:s'));
            }
        }
        echo json_token($tips);
        exit;
    }

    function tips()
    {
        return 'tip';
    }

}
/* End of file tip.php */
/* Location: ./application/controllers/login/tip.php */
