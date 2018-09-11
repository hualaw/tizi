<?php
/**
 * @author saeed
 * @date   2013-8-30 * @description 学生首页
 */
require(dirname(__FILE__).DIRECTORY_SEPARATOR.'student_base.php');
class Home extends Student_Base{

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $join_class = true;
        if(!$this->get_class_info()){
            $join_class = false;
        }
        $video_grades = Constant::grade_video();

        $this->smarty->assign('join_class',$join_class);
        $this->smarty->assign('video_grades',$video_grades);
        $this->smarty->display($this->_smarty_dir.'index.html');
    }
    /**
     * @info 加入班级
     */
    public function join_class(){
        if($this->user_info->class_name !=''){
			//redirect(site_url() . "student/homework/home");
        }
        if(isset($_POST['classnum'])){
            $invite_code = trim($_POST['classnum']);
            $this->load->model('class/classes_student');
            if($status = $this->classes_student->invite_join($invite_code, $this->uid)){
                $this->load->helper('json');
                json_out(array('code'=>$status));
            }
        }else{
        	/** 是否有邀请码 **/
        	$invite = $this->input->cookie("invite");
	        if ($invite){
				redirect(site_url() . "invite/" . $invite);
			}
        	
            $this->smarty->assign('user_info',$this->user_info);
            $this->smarty->display($this->_smarty_dir.'join_class.html');
        }
    }
    public function qa($subject_id=1){
        $subject_id = (int)$subject_id;
        //$types = $this->_get_subject_type();
        foreach($types as $type){
            if($type->id == $subject_id){
                $subject_name = $type->name;
                break;
            }
        }
        $subjects = $this->_get_subject();
		//是否有错题本
		$invalidSub  = explode(",", Constant::WRONG_NO_SUBJECT_IDS);
		$this->smarty->assign('hasWrongs', !in_array($subject_id, $invalidSub));
        $this->smarty->assign('sid',$subject_id);
        $this->smarty->assign('subjects',$subjects);
        //$this->smarty->assign('types',$types);
        $this->smarty->display($this->_smarty_dir.'qa.html');
    }
    /**
     * @info 获得所有科目
     */
    protected function _get_subject(){
        $subjects =  $this->question_subject_model->get_subjects();
        $new_subjects = array();
        foreach($subjects as $val){
            $new_subjects[$val->id] = $val;
        }
        return $new_subjects;
    }
    

}
