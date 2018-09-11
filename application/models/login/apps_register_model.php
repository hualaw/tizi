<?php
class Apps_Register_Model extends MY_Model{
	
	
	public function email_sign_student($email, $name, $password, $origin){
		if (true === $this->email_unique($email)){
			return -1;
		}
		
		$this->load->model('login/register_model');
		$student_id = $this->register_model->get_student_id();
		
		$this->load->helper('string');
		$this->load->helper('encrypt_helper');
		$password_salt = random_string('alnum','6');
		$password = encrypt_password($password, $password_salt);
		$student_data = array(
			'student_id' => $student_id,
			'verified' => 1,
			'email_verified' => 0,
			'phone_verified' => 0,
			'name' => $name,
			'password' => $password,
			'user_type' => Constant::USER_TYPE_STUDENT,
			'register_time' => date("Y-m-d H:i:s"),
			'register_ip'=>ip2long(get_remote_ip()),
			'register_origin'=>$origin
		);
		
		$this->db->insert('user', $student_data);
		$user_id = $this->db->insert_id();
		
		if ($user_id > 0){
			$user = array(
				'user_id' => $user_id,
				'student_id' => $student_id
			);
			return $user;
		} else {
			return false;
		}
	}
	
	public function email_unique($email){
		$sql_str = 'select id from `user` where email=?';
		$result = $this->db->query($sql_str, array($email))->result_array();
		return isset($result[0]['id']) && $result[0]['id'] > 0 ? true : false;
	}
	
	/**
	 * 手机app答疑注册接口model(通过手机注册)
	 * 流程：手机提交注册=>手机接受验证码=>输入手机验证码=>验证码正确注册成功
	 */
	public function phone_sign_student($phone, $password, $origin){
		$this->load->model('login/register_model');
		$student_id = $this->register_model->get_student_id();
		
		$this->load->helper('string');
		$this->load->helper('encrypt_helper');
		$password_salt = random_string('alnum','6');
		$password = encrypt_password($password, $password_salt);
		$student_data = array(
			'student_id' => $student_id,
			'verified' => 1,
			'email_verified' => 0,
			'phone_verified' => 1,
			'phone_mask' => mask_phone($phone),
			'name' => mask_phone($phone),
			'password' => $password,
			'user_type' => Constant::USER_TYPE_STUDENT,
			'register_time' => date("Y-m-d H:i:s"),
			'register_ip'=>ip2long(get_remote_ip()),
			'register_origin'=>$origin
		);
		$this->db->insert('user', $student_data);
		$user_id = $this->db->insert_id();
		
		if ($user_id > 0){
			$this->load->library("thrift");
			$response = $this->thrift->add_phone($user_id, $phone);
			if ($response == 1){
				$user = array(
					'user_id' => $user_id,
					'student_id' => $student_id
				);
				return $user;
			}
			return false;
		} else {
			return false;
		}
	}
}