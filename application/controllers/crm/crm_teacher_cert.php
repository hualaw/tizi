<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once("crm_controller.php");

class Crm_Teacher_Cert extends Crm_Controller{
    
    public function __construct(){
        parent::__construct();
    }

   // CRM  审核老师的申请   id为表中的primary key
    function verify_apply(){
        $data['id'] = intval($this->input->post('id',true));
        $uid = $data['user_id'] = intval($this->input->post('user_id',true));
        $data['apply_status'] = intval($this->input->post('status',true)); //2.pass, 3.reject
        $data['reject_msg'] = $this->input->post('reject_msg',true); //新加字段，不通过的原因说明
        $data['cert_type'] = intval($this->input->post('cert_type',true)); //新加字段，不通过的原因说明
        $data['cert_num'] = $this->input->post('cert_num',true); //新加字段，不通过的原因说明

        if(!$data['id'] or !$data['apply_status'] or !$data['user_id']){
            echo json_token(array('errorcode'=>false,'error'=>"缺少参数"));die;
        }
        if($data['apply_status'] == Constant::APPLY_STATUS_SUCC && (!$data['cert_type'] or !$data['cert_num'])){
            echo json_token(array('errorcode'=>false,'error'=>"缺少证件类型或编号"));die;   
        } 
        
        if($data['apply_status'] == Constant::APPLY_STATUS_FAIL && !$data['reject_msg']){
            echo json_token(array('errorcode'=>false,'error'=>"缺少不通过的原因说明"));die;   
        }
        $this->load->model('user_data/cert_model');
        $res = $this->cert_model->edit_apply_status($data);

        if($res){
            if($data['apply_status'] == Constant::APPLY_STATUS_SUCC ){//发送通知
                $this->certification_notice(array('user_id'=>$uid,'apply_status'=>$data['apply_status']), 't_cert_succ');

				$this->load->model('medal/user_medal_model');

				$teacher_certification = $this->cert_model->get_apply_status($uid);
				$teacher_medal = $this->user_medal_model->get_user_medal_info($uid, false);

				if (!isset($teacher_medal[Constant::TEACHER_AUTHENTICATION_MEDAL])) {
					$param['user_id'] = $uid;
					$param['medal_type'] = Constant::TEACHER_AUTHENTICATION_MEDAL;
					$param['upgrade_msg'] = '';
					$param['get_date'] = $teacher_certification[0]['verify_time'];
					$param['level'] = 1;

					$this->certification_notice(array('user_id'=>$uid,'apply_status'=>$data['apply_status']), 'teacher_medal');

					$this->user_medal_model->insert_user_medal($param);
				}
            }
            echo json_token(array('errorcode'=>true,'error'=>"操作成功"));die;    
        }
        echo json_token(array('errorcode'=>false,'error'=>"操作失败"));die;
    }

	/*通过认证的老师 接收通知*/
	private function certification_notice($data, $msg_type){
		$user_id = $data['user_id'];
		$status = $data['apply_status'];
		if($status == Constant::APPLY_STATUS_SUCC){
			$this->load->library("notice");
			$this->notice->add($user_id, $msg_type);
		}
	}
    function search_cert(){
        $data['is_del'] = 0;
        $data['cert_type'] = intval($this->input->post('cert_type',true)); //新加字段，不通过的原因说明
        $data['cert_num'] = $this->input->post('cert_num',true); //新加字段，不通过的原因说明
        $data['apply_status'] = Constant::APPLY_STATUS_SUCC;
        $this->load->model('user_data/cert_model');
        $res = $this->cert_model->search($data);
        if($res){
            echo json_token(array('errorcode'=>true,'error'=>"此证件号码已存在",'count'=>$res));die;   
        }
        echo json_token(array('errorcode'=>false,'error'=>"不存在",'count'=>$res));die;   
    }


    //CRM访问七牛图片
    function get_pic(){
        $key = $this->input->get_post('pic',true);
        if(!$key){
            echo json_token(array('errorcode'=>false,'error'=>"缺少参数"));die;       
        }
        $this->load->library('qiniu');
        $this->qiniu->change_bucket();
        // $path = $this->qiniu->qiniu_get_image($key,0,0,600);//上传好后的图片路径
        $this->load->helper('qiniu');
        $path = qiniu_img($key);
        $this->qiniu->change_bucket('');
        if($path){
            echo json_token(array('img'=>$path,'error'=>"成功",'errorcode'=>true));die;       
        }else{
            echo json_token(array('errorcode'=>false,'error'=>"获取token失败"));die;          
        }
    }
}
/* end of Crm_Teacher_Cert.php */
