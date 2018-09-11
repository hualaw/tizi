<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once('crm_controller.php');

class Crm_Register extends Crm_Controller {

	private $_table="user";

   	public function __construct()
   	{
    	parent::__construct();
    	$this->load->model('login/register_model');
    	$this->load->model("question/question_subject_model");
    }
	
	//http://www.tizi.com/crm/crm_register/insert_register?phone=18600357911&email=tang@tizi.com&utype=3&rname=eee&ursubject=1&password=123123
    public function insert_register()
    {
    	$phone=$this->input->get_post('phone');
    	$email=$this->input->get_post('email');
    	$rname=$this->input->get_post('rname');
    	$user_type=$this->input->get_post('utype');
    	$register_subject=$this->input->get_post('ursubject');
    	$password=$this->input->get_post('password');
    	
    	$insert=array('errorcode'=>false,'error'=>'','user_id'=>0);

    	$check_phone=$this->register_model->check_phone($phone);
		$check_email=$this->register_model->check_email($email);
		$check_subject=$this->question_subject_model->check_subject($register_subject,'binding');

		if(empty($phone)&&empty($email))
		{
			$insert['error']='error username';
		}
		else if($phone&&!preg_phone($phone))
		{
			$insert['error']='error phone';
		}
		else if($phone&&($check_phone['errorcode']||$check_phone['user_id'] == -127))
		{
			$insert['error']='error exist phone';
			$insert['code']=2;
			if($check_phone['user_id'] > 0) $insert['user_info']=$this->register_model->get_user_info($check_phone['user_id']);
			else if($check_phone['user_id'] == -127) $insert['error']='error thrift';
		}
		else if($email&&!preg_email($email))
		{
			$insert['error']='error email';
		}
		else if($email&&$check_email['errorcode'])
		{
			$insert['error']='error exist email';
			$insert['code']=1;
			if($check_email['user_id'] > 0) $insert['user_info']=$this->register_model->get_user_info($check_email['user_id']);
		}
		else if($user_type!=Constant::USER_TYPE_TEACHER&&$user_type!=Constant::USER_TYPE_PARENT)
		{
			$insert['error']='error user type';
		}
		else if($register_subject&&!$check_subject)
		{
			$insert['error']='error user subject';
		}
		else if(empty($password))
		{
			$insert['error']='error password';
		}
		else
		{
			$this->load->helper('string');
	        $password_salt=random_string('alnum','6');
	        $this->load->helper('encrypt_helper');
	        $password=encrypt_password(md5('ti'.$password.'zi'),$password_salt);

	        $data=array(
	        		'verified'=>1,
					'email'=>$email?$email:NULL,
					'email_verified'=>$email?2:0,
					'phone_verified'=>$phone?2:0,
					'phone_mask'=>$phone?mask_phone($phone):'',
					'password'=>$password,
					'name'=>$rname,
					'user_type'=>$user_type,
					'register_subject'=>$register_subject?$register_subject:NULL,
					'register_time'=>date("Y-m-d H:i:s"),
					'register_ip'=>ip2long(get_remote_ip()),
					'register_origin'=>Constant::REG_ORIGIN_CRM
			);

			$this->db->insert($this->_table,$data);
			$insert['user_id']=$this->db->insert_id();
			if($insert['user_id']>0) 
			{
				/* thrift insert start */
				if($phone)
				{
					$this->load->library("thrift");
					$response = $this->thrift->add_phone($insert['user_id'],$phone);
					if($response==-127)
					{
						$insert['error']='error thrift';
						$this->db->where('id',$insert['user_id']);
						$this->db->update($this->_table,array('phone_verified'=>0,'phone_mask'=>''));
					}
					else if($response!=1)
					{
						$insert['error']='error thrift insert';
						$this->db->where('id',$insert['user_id']);
						$this->db->update($this->_table,array('phone_verified'=>0,'phone_mask'=>''));
					}
					else
					{
						$insert['errorcode']=true;
					}
				}
				/* thrift insert over */
				else
				{
					$insert['errorcode']=true;
				}
			} 
			else 
			{
				$insert['error']='error db insert';
			}
			if($insert['errorcode']) log_message('info_tizi','20001:Register success',array('uid'=>$insert['user_id'],'username'=>$phone.'_'.$email,'register_type'=>'crm','user_type'=>$user_type));
		}
		echo json_encode($insert);
		exit;
    }

    //http://www.tizi.com/crm/crm_register/update_phone?uid=812800987&phone=186003657911
    public function update_phone()
    {
    	$user_id=$this->input->get_post('uid');
    	$phone=$this->input->get_post('phone');

    	$update=array('errorcode'=>false,'error'=>'');

    	$check_user=$this->register_model->get_user_info($user_id);
    	$check_phone=$this->register_model->check_phone($phone);

    	if(empty($user_id)||!$check_user['errorcode'])
		{
			$update['error']='error uid';
		}
    	else if(empty($phone))
		{
			$update['error']='error phone';
		}
		else if($phone&&!preg_phone($phone))
		{
			$update['error']='error phone';
		}
		else if($phone&&($check_phone['errorcode']||$check_phone['user_id']==-127))
		{
			$update['error']='error exist phone';
			if($check_phone['user_id'] > 0) $insert['user_info']=$this->register_model->get_user_info($check_phone['user_id']);
			else if($check_phone['user_id'] == -127) $insert['error']='error thrift';
		}
		else
		{
    		$errorcode=$this->register_model->update_phone($user_id,$phone,2);
    		$update['errorcode']=$errorcode['errorcode'];
    		if(!$errorcode['errorcode']) $update['error']='error thrift insert';
    	}
    	echo json_encode($update);
		exit;
    }

    //http://www.tizi.com/crm/crm_register/update_email?uid=812800987&email=tang@tizi.com
    public function update_email()
    {
    	$user_id=$this->input->get_post('uid');
    	$email=$this->input->get_post('email');

    	$update=array('errorcode'=>false,'error'=>'');

    	$check_user=$this->register_model->get_user_info($user_id);
    	$check_email=$this->register_model->check_email($email);

    	if(empty($user_id)||!$check_user['errorcode'])
		{
			$update['error']='error uid';
		}
    	else if(empty($email))
		{
			$update['error']='error email';
		}
		else if($email&&!preg_email($email))
		{
			$update['error']='error email';
		}
		else if($email&&$check_email['errorcode'])
		{
			$update['error']='error exist email';
		}
		else
		{
    		$errorcode=$this->register_model->update_email($user_id,$email,2);
    		$update['errorcode']=$errorcode['errorcode'];
    	}
    	echo json_encode($update);
		exit;
    }

    //http://www.tizi.com/crm/crm_register/update_password?uid=812800987&password=123123
    public function update_password()
    {
    	$user_id=$this->input->get_post('uid');
    	$password=$this->input->get_post('password');
    	$update=array('errorcode'=>false,'error'=>'');
    	$check_user=$this->register_model->get_user_info($user_id);

    	if(empty($user_id)||!$check_user['errorcode'])
		{
			$update['error']='error uid';
		}
		else
		{
    		$errorcode=$this->register_model->update_password($user_id,md5('ti'.$password.'zi'));
    		$update['errorcode']=$errorcode['errorcode'];
    	}
    	echo json_encode($update);
		exit;
    }

    /**绑定家长和孩子
      http://192.168.11.73:8089/crm/crm_register/parent_bind_kid
      $p_id=812800129
      $kid_id=123; 此为 学号！！！这段程序会通过学号获取user_id !
      $relation='父亲';
    **/
    function parent_bind_kid(){
        $_POST = array_merge($_POST,$_GET);
        $p_id = intval($_POST['p_id']);
        $kid_id = intval($_POST['kid_id']);
        $relation = $_POST['relation'];

        $result = array();
        $result['p_id'] = $p_id;
        $result['kid_id'] = $kid_id;
        $result['relation'] = $relation;
        if(!$p_id || !$kid_id || !$relation){
            $result['status'] = 0;
            $result['msg'] = 'data cannot be empty';
            echo json_encode($result);die;
        }

        // $kid_id = $this->parents_kids->get_user_id_by_student_id($kid_id);
        $this->load->model("login/parent_model");
        $kid_id = $this->parent_model->get_user_id_by_student_id($kid_id);

        if(isset($kid_id[0]['id'])){
            $kid_id = $kid_id[0]['id'];
        }else{
            $result['status'] = 0;
            $result['msg'] = 'No user_id for this student_id !';
            echo json_encode($result);die;
        }
        // $r = $this->parents_kids->add_kid($p_id,$kid_id,$relation);
        $r = $this->parent_model->add_kid($p_id,$kid_id,$relation);
        if($r['status']){
            $result['status'] = 1;
        }else{
            $result['status'] = 0;
        }
        $result['msg'] = $r['msg'];
        echo json_encode($result);die;
    }
    
}		
/* End of file crm_register.php */
/* Location: ./application/controllers/crm/crm_register.php */

