<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Feedback extends MY_Controller {
 
  private $_user_id=0;
  private $_user_type=0;

  function __construct(){
    parent::__construct();
    $this->load->model('feedback/feedback_model');
    $this->_user_id=$this->session->userdata('user_id');
    $this->_user_type=$this->session->userdata('user_type');
    $this->load->library('user_agent');
  }

  /*反馈错题提交页面*/
  function paper(){
    $question_id = intval($this->input->get('question_id',true));
    //$source = 1;//intval($this->input->get('source',true));

    //$this->smarty->assign('source',$source);
    $this->smarty->assign('question_id',$question_id);
    $this->smarty->display('feedback/feedback_paper.html');
  }

  //题目纠错
  public function correction(){
    $json = array('errorcode'=>false,'error'=>'系统繁忙请稍候再试');
    $this->load->helper('array');
    $pictures = $this->input->post('picture_urls',true);
    $pictures = explode_to_distinct_and_notempty($pictures);
    $question_id = intval($this->input->post('question_id',true));
    if(!$question_id){
      echo json_encode($json);die;
    }
    $wrong = $this->input->post('wrong_type',true);
    $source = intval($this->input->post('source',true,true,Constant::FEEDBACK_SOURCE_PAPER));
    if(!in_array($source, array(Constant::FEEDBACK_SOURCE_PAPER, Constant::FEEDBACK_SOURCE_EXERCISE, Constant::FEEDBACK_SOURCE_PRACTICE))){
      $json['error'] = '题目来源有误';
      echo json_encode($json);die;
    }
    $content = trim($this->input->post('content',true));
    $s = strap($content);
    if(!$s){
      $json['error'] = '请输入有效的反馈内容';
      echo json_encode($json);die; // 没有内容就直接返回true
    }
    $param['content'] = $content;
    $param['question_id'] = $question_id;
    $param['user_id'] = $this->_user_id;
    $param['wrong_type'] = implode(',',$wrong);
    $param['pictures'] = implode(',',$pictures);
    $param['add_time'] = time();
    $param['source'] = $source;
    $res = $this->feedback_model->add_wrong_question($param);
    if($res){
      $json['errorcode'] = true;
      $json['error'] = '成功';
    }
    echo json_encode($json);die;
  }

  //题目纠错  上传图片
  public function upload(){
    $this->load->helper("upload");
    $this->load->helper("json");
    $name = $this->input->get("id",true);

    //set object
    $this->load->config("upload");
    $pathinfo = pathinfo($_FILES[$name]["name"]);
    $ext = isset($pathinfo["extension"]) ? $pathinfo["extension"] : "";
    $object = "feedback/".date("Ym")."/".time()."_".mt_rand(100000000,999999999).".".$ext;
    
    $aq_image_upload = oss_image_upload($name,$object,'feedback');
    switch ($aq_image_upload){
         case -1:json_get(array("code"=>$aq_image_upload, "msg"=>"上传失败"));break;
         case -2:json_get(array("code"=>$aq_image_upload, "msg"=>"格式错误"));break;
         case -3:json_get(array("code"=>$aq_image_upload, "msg"=>"文件过大"));break;
         default:json_get(array("code"=>1, "msg"=>"上传成功", "img_path"=>$aq_image_upload));break;
    }
  }

  /*提供给后台，获取时间戳以后的数据*/
  function get_wrong_q(){
    $time = $this->input->get('time',true);
    $res = $this->feedback_model->get_question($time);
    echo json_token($res);die;
  }

  /*更新错题的status字段*/
  function update_status(){
    $where['question_id'] = $this->input->post('id',true);
    $data['status'] = intval($this->input->post('status',true));
    $res = $this->feedback_model->update_q($where,$data);
    return $res;
  }

  /*反馈建议*/
  public function send_feedback(){
    $user_id = $this->_user_id;
    $return = array('errorcode'=>false,'error'=>$this->lang->line('error_feedback_content'));
    // if(isset($_REQUEST['content'])){ // why cannot use $_post ??
    $param = array();
    $param['content'] = trim($this->input->post('content',true));//strip_tags(trim($_REQUEST['content']));
    $s = strap($param['content']);
    if(!$s){
      echo json_token($return);die; // 没有内容就直接返回true
    }
    if(!$user_id){ // unlogin 
        $param['from_name'] = $this->input->post('name',true);//isset($_REQUEST['name'])?strip_tags(trim($_REQUEST['name'])):null;
        $param['from_phone'] = $this->input->post('phone',true);//isset($_REQUEST['phone'])?strip_tags(trim($_REQUEST['phone'])):null;
        $param['from_email'] = $this->input->post('email',true);//isset($_REQUEST['email'])?strip_tags(trim($_REQUEST['email'])):null;
    }else{
        $param['user_id'] = $user_id;
        // $param['user_type'] = $_user_type; // no need
    }
    $param['from_qq'] = $this->input->post('qq',true);
    if(!$user_id && !preg_qq($param['from_qq'])){
      $return = array('errorcode'=>false,'error'=>$this->lang->line('error_feedback_qq'));
      echo json_token($return);die;
    }
    $param['create_time'] = time();
    $param['user_agent'] = $this->agent->agent_string();
// var_dump($param);die;
    $res = $this->feedback_model->send_feedback($param);
    if($res){
      $return = array('errorcode'=>true,'error'=>$this->lang->line('success_feedback'));
      echo json_token($return);die; 
    }
    echo json_token(array('errorcode'=>false,'error'=>$this->lang->line('default_error')));die;   
  }

}
