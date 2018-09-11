<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once "teacher_homework.php";

class Teacher_Assign extends Teacher_Homework {

    private $_smarty_dir="teacher/homework/";

    function __construct() {
        parent::__construct();
        $this->load->model('exercise_plan/hw_classes_model','hcm');
        $this->load->model('homework/zuoye_model');
        $this->load->helper('array');
        $this->load->model('login/register_model');
        $this->load->model('question/question_category_model');
        $this->load->model('homework/zy_allow_edition_model');
        $this->load->model('homework/game_question_model');
        $this->load->model('homework/unit_game_model');
        $this->load->model('homework/homework_package_model');
        $this->user_id = $this->tizi_uid;
        $this->load->model('exercise_plan/teacher_exercise_plan_model','tepm');
        $this->load->model('paper/paper_question_type_model');
        $this->load->model('paper/paper_section_model');
        $this->load->model('paper/paper_save_log');
    }

    //2014-08-06 新版布置页面  学段--科目--版本--年级
    function new_assign($alpha_class_id,$subject_id=0,$banben_select=0){
        $class_id = alpha_id_num($alpha_class_id,true);
        if(!$class_id){redirect(); }
        $this->load->model('question/question_subject_model');
        $g = $this->input->get('g',true,true,0);//学段，小初高
        $subject_type_id = $this->input->get('st',true,true,0);
        if($g){
            if(!in_array($g,array(1,2,3))){//grade不在subject_type中
                redirect(site_url('zuoye/assign/'.$alpha_class_id.'/21'));
            }
            $_sub = $this->question_subject_model->get_subject_by_type($subject_type_id,0,$g);
            if(isset($_sub[0]->id)){
                $data['subject_id'] = $_sub[0]->id;
            }else{
                redirect(site_url('zuoye/assign/'.$alpha_class_id.'/21'));
            }
        }else{
            $data['subject_id'] = $subject_id;
            if(!$data['subject_id']){
                $data['subject_id']=$this->register_model->my_subject($this->tizi_uid,'homework');
                if(!$data['subject_id']){
                    $data['subject_id'] = Constant::DEFAULT_SUBJECT_ID;
                }
            }
        }

        if($data['subject_id']<=9){
            $current_grade = "mid";
        }elseif ($data['subject_id']<=18 and $data['subject_id']>9) {
            $current_grade = 'high';
        }else{
            $current_grade = 'pri';
        }
        if(!$subject_type_id){
            $subject_type_id = $this->question_subject_model->get_subject_type_by_id($data['subject_id']);
        }
        $this->register_model->set_favorate_subject($data['subject_id'],'homework');
        $editions = $this->zy_allow_edition_model->get_by_sid($data['subject_id']);
        if( $editions){//没有对应的教材版本
            if(!$banben_select){
                $banben_select = $editions[0]['category_id'];
            }
            $spec_grade = $this->question_category_model->get_subtree_node($banben_select);//三年级上、五年级下等
        }else{
            // show_error('no legal edition');
            redirect(site_url('zuoye/assign/'.$alpha_class_id.'/21'));
            $spec_grade[0] = (object)null;
            $banben_select = $spec_grade[0]->id = 0;
        }
        
        $this->get_units_data($spec_grade[0]->id,$current_grade); // 小学与否
        $this->smarty->assign('banben_select',$banben_select );
        $this->smarty->assign('subject_id',$data['subject_id'] );
        $this->smarty->assign('subject_type_id',$subject_type_id);
        $this->smarty->assign('cur_grade',$current_grade);
        $this->smarty->assign('editions',$editions );
        $this->smarty->assign('spec_grade',$spec_grade);
        $this->smarty->assign('spec_grade_id',$spec_grade[0]->id);
        
        $c_info = $this->classes->get($class_id);//班级名称
        $this->smarty->assign('class_name',$c_info['classname']);
        $this->smarty->assign('alpha_class_id',$alpha_class_id );
        $this->smarty->display($this->_smarty_dir.'new_assign_homework.html');
    }

    //2014-08-06  点击三年级上  获取其下units和所有视频、游戏、作业试卷包
    function get_units_data($cid,$cur_grade=0){
        $u = $this->get_units($cid);//处理好的单元数据
        $units = $u['entities'];
        $subject_id = $this->question_category_model->get_subject_id($u['entities'][0]->id);
        if(!$cur_grade){
            if($subject_id<=9){
                $cur_grade = "mid";
            }elseif ($subject_id<=18 and $subject_id>9) {
                $cur_grade = 'high';
            }else{
                $cur_grade = 'pri';
            }
            $this->smarty->assign('subject_id',$subject_id);
        }
        $this->smarty->assign('cur_grade',$cur_grade);
        if($subject_id==21){//小学英语才不需要同步章节
            $unit_ids = $u['ids'];
            $games = $this->get_games($unit_ids);//key 是 单元id
            $game_import = $this->get_game_import($unit_ids);//重点词汇  
            $videos = $this->get_videos($unit_ids);
            $videos_important = $this->get_videos_important($unit_ids);
            $this->smarty->assign('all_unit_info',$games); 
            $this->smarty->assign('game_import',$game_import); 
            $this->smarty->assign('videos',$videos); 
            $this->smarty->assign('videos_important',$videos_important); 
        }else{// 是初中就获取同步章节信息
            foreach($units as $k=>$val){
                if($subject_id!=19){
                    $val->chap = $this->question_category_model->get_subtree_node($val->id);
                }else{
                    $val->chap = null;
                }
                if($subject_id==19 or count($val->chap)==0){ //是小学语文 或者 章下面没有节的，就用章的id和name拼出一个节，叫 综合练习
                    $obj = (object)array('id'=>$val->id,'name'=>'综合练习');
                    if(!$val->chap){
                        $val->chap = array($obj);
                    }else{
                        array_unshift($val->chap, $obj);
                    }
                }
            }
            //判断是小学还是  初高中，前者获取游戏，后者获取作业试卷包
            if($cur_grade == 'pri'){ //get游戏
                foreach($units as $k=> $val){
                    foreach($val->chap as $cps=>&$c){
                        $_g = null;
                        if($this->game_question_model->get_q_count($c->id)){//category_id是否对应有题目
                            $_g = $this->unit_game_model->get_games_by_unit_no_practype($c->id);
                        }
                        $c->games = $_g;
                    }
                }
                // print_r($val->chap);
            }else{ //获取资源包
                foreach($units as $k=> $val){
                    foreach($val->chap as $cps=>$c){
                        $_paper = null; $all_nodes = array();
                        //这个小节小面还有节点的话，就一起把子节点都取出来
                        $sub_nodes  = $this->question_category_model->get_subtree_node($c->id);
                        if($sub_nodes){
                            foreach($sub_nodes as $no){
                                $all_nodes[] = $no->id;
                            }
                        }
                        $all_nodes[] = $c->id;
                        $all_nodes = implode(',', $all_nodes);
                        $_paper = $this->homework_package_model->get_package_by_cat($all_nodes);
                        $c->papers = $_paper;
                    }
                }
            }
        }
        $this->smarty->assign('units',$units);
        $arr = array();
        $is_ajax = $this->input->get('ajax',true);
        if($is_ajax){
            $arr['prac_html'] = $this->smarty->fetch('teacher/homework/assign_homework_prac.html');
            $arr['unit_html'] = $this->smarty->fetch('teacher/homework/new_assign_homework_unit.html');
            $arr['error_code'] = true;
            echo json_token($arr);die;
        }
    }

    protected function get_units($cid){
        $unit_entity = $this->question_category_model->get_subtree_node($cid);
        $unit_id = array();
        if($unit_entity){
            foreach($unit_entity as $key=>$val){
                $unit_id[] = $val->id;
            }
        }
        return array('entities'=>$unit_entity,'ids'=>$unit_id);
    }

    protected function get_games($unit_ids){
        if(!$unit_ids or !is_array($unit_ids)){return null; }
        $this->load->model('homework/unit_game_model');
        $games = array();
        foreach($unit_ids as $k=>$val){
            $games[$val] = $this->unit_game_model->get_games_by_unit($val);//key为单元id
        }
        return $games;
    }

    //游戏的 重点词汇 句子 什么的
    protected function get_game_import($unit_ids){
        if(!$unit_ids or !is_array($unit_ids)){return null; }
        $this->load->model('homework/zuoye_import_model');
        $games = array();
        foreach($unit_ids as $k=>$val){
            $im = $this->zuoye_import_model->get_import_word($val);
            if($im ){
                foreach($im as $ims=>&$i){
                    $i['content'] = json_decode($i['content'],true);
                    if($i['important_type']==3){
                        $i['content'] = $i['content'];
                    }else{
                        $i['content'] = $i['content'][0];
                    }
                }
                $games[$val] = $im;//key为单元id
            }
        }
        return $games;
    }

    // 单元下的视频实体
    protected function get_videos($unit_ids){
        if(!$unit_ids or !is_array($unit_ids)){return null; }
        $res = array();
        $this->load->helper('qiniu');
        qiniu_set_bucket('fls_');
        $this->load->model('video/videos_model');
        $this->load->model('homework/unit_model');
        $unit_ids = implode(',',$unit_ids);
        $unit_ids = $this->unit_model->get_units_from_category($unit_ids);
        $unit_ids = $unit_ids['unit_ids'];
        foreach($unit_ids as $k=>$val){
            $i = $this->videos_model->get_lesson_by_unit(0,$val,false);
            if($i){
                foreach($i as $vs=>$video){
                    $video->thumb_uri = qiniu_pub_link($video->thumb_uri);
                    $res[$video->category_id][] = $video;
                }
            }
        }
        qiniu_set_bucket();
        return $res;
    }
    //视频对应的重点词汇
    protected function get_videos_important($unit_ids){
        if(!$unit_ids or !is_array($unit_ids)){return null; }
        $this->load->model('homework/unit_model');
        $unit_ids = implode(',',$unit_ids);
        $unit_ids = $this->unit_model->get_units_from_category($unit_ids);
        $cat_units = $unit_ids['cat_units'];
        $unit_ids = $unit_ids['unit_ids'];
        $res = array();
        foreach($unit_ids as $k=>$val){
            $i = $this->zuoye_model->get_video_word_by_unit($val);
            if($i){
                $res[$val]['word'] = $i;
            }
        }
        foreach($cat_units as $key=>$val){
            if(isset($res[$val])){
                $cat_units[$key]=$res[$val];
            }
        }
        return $cat_units;
    }


/*==========================   以下是 处理提交请求   ================================*/
    
    //插入zuoye_assign表
    function do_assign(){
        $time_chosen = $this->chosen_time();
        if($time_chosen){
            $data['start_time'] = $time_chosen['start_time'];
            $data['end_time'] = $time_chosen['end_time'];
        }else{
            echo json_token(array('errorcode'=>false,'error'=>'作业时间有误'));die;
        }

        $class_id = $this->input->post('alpha_class_id',true);
        $data['class_id'] = alpha_id_num($class_id,true);
        $data['subject_id'] = $this->input->post('subject_id',true);
        $data['status'] = 1;
        $data['has_checked'] = 0;
        $data['user_id'] = $this->tizi_uid;
        $data['assign_time'] = time();
        $unit_games = $this->input->post('games',true);
        $ug = $this->chosen_unit_game_data($unit_games);
        if($ug['count']>5){//每次作业的游戏个数限制
            echo json_token(array('errorcode'=>false,'error'=>'一次作业最多包含5个游戏'));die;
        }
        $video_ids = $this->input->post('videos',true);
        $uv = $this->chosen_video_data($video_ids);//先处理video数据，查查有无对应video
        //作业包
        $package_ids =($this->input->post('papers',true));

        $data['video_ids'] = $uv['video_ids'];
        $data['unit_game_ids'] = $ug['unit_game_ids'];
        
        if(!($ug['unit_ids']) and !($uv['unit_ids']) and !$package_ids){
            echo json_token(array('errorcode'=>false,'error'=>'没有找到相应作业内容'));die;
        }

        if(!$ug['unit_ids']){$ug['unit_ids'] = array(); }
        if(!$uv['unit_ids']){$uv['unit_ids'] = array(); }
        $paper_unit_ids = array();

        if($package_ids) { //如果有选择试卷包，就先插入paper_assign 并返回需要插入paper_ids字段的内容
            $count_paper = explode_to_distinct_and_notempty($package_ids);
            $count_paper = count($count_paper);
            if($count_paper>3){//每次作业的试卷包个数限制
                echo json_token(array('errorcode'=>false,'error'=>'一次作业最多包含3个试卷包'));die;
            }
            $_tmp = $this->invoke_paper_assign($data['class_id'],$data['subject_id'],$package_ids);
            $data['paper_ids'] = json_encode($_tmp['ret_paper']);
            $paper_unit_ids = $_tmp['unit_ids'];
        }
        $data['unit_ids'] = array_merge($ug['unit_ids'],$uv['unit_ids'],$paper_unit_ids);
        $data['unit_ids'] = array_unique($data['unit_ids']);
        $data['unit_ids'] = $data['unit_ids']? implode(',', $data['unit_ids']):'';

        $res = $this->zuoye_model->insert_zuoye($data);
        if($res){
            $this->load->library("credit");//布置作业获得积分
            $score = $this->credit->exec($this->tizi_uid, "make_assign");
            $this->load->model('homework/zuoye_intro_model');
            $this->zuoye_intro_model->change_assign_score($res,$score);

            $arr = array('errorcode'=>true,'error'=>'布置作业成功!','url'=>site_url('zuoye'));
            echo json_token($arr);die;   
        }else{
            echo json_token(array('errorcode'=>false,'error'=>'系统繁忙，请稍后再试'));die;   
        }
    }

    //留作业的 布置试卷包  ，call func assign_homework( ) 插入  paper_assign && student_paper
    function invoke_paper_assign($class_id, $subject_id,$package_ids){
        $time_chosen = $this->chosen_time();
        if($time_chosen){
            $param['start_time'] = $time_chosen['start_time'];
            $param['deadline'] = $time_chosen['end_time'];
        }
        $param['get_answer_way'] = $this->input->post('showTime',true,true,2);
        $param['online'] = 1;// 只有布置线上的试卷，没有线下这种option了
        $param['user_id'] = $this->tizi_uid;
        $is_shuffled = intval($this->input->post('answerOrder',true,true,1));
        if($is_shuffled){
            $param['is_shuffled'] =  $is_shuffled;
        }
        $this->load->helper('array');
        $this->load->model('paper/paper_model');
        $this->load->model('paper/paper_question_model');

        $_data = $unit_ids = array();
        $package_ids = explode(',', $package_ids);
        foreach($package_ids as $key=>$val){
            $ug = explode('-', $val);
            if(isset($ug[0]) and isset($ug[1])){
                $_data[] = $ug[1];
                $unit_ids[] = $ug[0];
            }
        }
        $package_ids =implode(',', $_data);
        $unit_ids = array_unique($unit_ids);
        $unit_ids = array_filter($unit_ids); // 去空
        $package_ids = explode_to_distinct_and_notempty($package_ids);
        // $paper_ass = new paper_assign();
        if(!$package_ids){return null;}
        $ret_paper = array();

        foreach($package_ids as $packs=>$pack){
            $pack_q_sum = 0;
            $pack_info = $this->homework_package_model->get_package_by_id($pack);
            $q_ids = explode_to_distinct_and_notempty($pack_info['question_ids']);
            $new_paper_id = $this->paper_model->init_paper_record($subject_id,$this->tizi_uid,1,Constant::LOCK_TYPE_ASSIGN);
            if($new_paper_id and $q_ids){
                foreach($q_ids as $q){//组成新的卷子
                    $pack_q_sum ++;
                    $addres = $this->paper_question_model->add_question_to_paper($new_paper_id,$q,Constant::QUESTION_ORIGIN_QUESTION, 0,$pack_info['category_id']);
                }
                $param['name'] = $pack_info['name'];
                $param['class_ids'] = array($class_id);
                $param['paper_id'] = $new_paper_id;
                $assign_done = $this->assign_homework($param, $subject_id,0);
                $ret_paper[] = array('assignment_id'=>$assign_done['assignment_id'],'paper_id'=>$new_paper_id,'q_sum'=>$pack_q_sum,'is_package'=>true,'package_id'=>$pack);
            }
        }
        $arr = array('ret_paper'=>$ret_paper,'unit_ids'=>$unit_ids);
        return ($arr);
    }

    //选中的video数据（video  id） 
    protected function chosen_video_data($video_ids=null){
        $video_arr = explode_to_distinct_and_notempty($video_ids);
        if(!$video_arr){return null;}
        $this->load->model('video/videos_model');
        $unit_ids = array();
        foreach($video_arr as $key=>$val){
            // $uid = $this->videos_model->get_lesson_by_id($val,'unit_id');
            $r = $this->videos_model->get_lesson_by_id($val,'fls_video_lesson.*,common_unit.category_id',1,1);
            if($r){
                $unit_ids[] = $r->category_id;
            }else{
                unset($video_arr[$key]);
            }
        }
        $video_ids = implode(',', $video_arr); 
        if(is_array($unit_ids)){
            $unit_ids = array_unique($unit_ids);
            $unit_ids = array_filter($unit_ids); // 去空
        }
        $res = array('unit_ids'=>$unit_ids,'video_ids'=>$video_ids);
        return $res;
    }

    //选中的游戏数据（单元id+游戏id） 
    protected function chosen_unit_game_data($data=null){
        $data = explode_to_distinct_and_notempty($data);
        $count = count($data);
        if(!$data){return null; }
        $_data = $unit_ids = array();
        foreach($data as $key=>$val){
            $ug = explode('-', $val);
            if(isset($ug[0]) and isset($ug[1]) and isset($ug[2])){
                if(intval($ug[2])){
                    $_data[] = array('unit_id'=>$ug[0],'game_id'=>$ug[1],'game_type_id'=>$ug[2]);
                }else{
                    $_data[] = array('unit_id'=>$ug[0],'game_id'=>$ug[1]);
                }
                $unit_ids[] = $ug[0];
            }
        }
        $data = json_encode($_data);
        $unit_ids = array_unique($unit_ids);
        $unit_ids = array_filter($unit_ids); // 去空
        $res = array('unit_ids'=>$unit_ids,'unit_game_ids'=>$data,'count'=>$count);
        return $res;
    }

    protected function chosen_time(){
        $start_day = $this->input->post('start_day',true);
        $start_hour = $this->input->post('start_hour',true);
        $start_min = $this->input->post('start_min',true);
        $start_time = strtotime($start_day);
        $start_time += $start_hour*3600+$start_min*60;

        $end_day = $this->input->post('end_day',true);
        $end_hour = $this->input->post('end_hour',true);
        $end_min = $this->input->post('end_min',true);
        $end_time = strtotime($end_day);
        $end_time += $end_hour*3600+$end_min*60;

        if( time() > $end_time or $start_time>$end_time){
            return false;
        }
        $arr = array('start_time'=>$start_time,'end_time'=>$end_time);
        return $arr;
    }

    function ttt(){
        $unit = 24739;
        $classes_zuoye = $this->zuoye_model->intro_zy_list($this->tizi_uid);

    }

// 从paper_assign移植过来的=====================================================================

    /*tizi4.0 出卷子处的留作业 assign paper*/
    function invoke_assign(){
        $param['user_id'] = $this->user_id;
        $param['get_answer_way'] = $this->input->post('showTime',true,true,2);
        $param['online'] = 1;// 只有布置线上的试卷，没有线下这种option了
        $start = strtotime($this->input->post('start_day',true));
        $start_hour = ($this->input->post('start_hour',true));
        $start_min = ($this->input->post('start_min',true));
         
        $is_shuffled = intval($this->input->post('answerOrder',true,true,1));
        if($is_shuffled){
            $param['is_shuffled'] =  $is_shuffled;
        }else{
            echo json_token(array('errorcode'=>false,'error'=>"答题顺序参数错误"));exit;   
        }
        
        $now = strtotime(date('Y-m-d',time()).' 00:00:00');  // 判断时间 是否合法// 允许 发布当日的作业
        $start += $start_hour*3600+$start_min*60;

        $end = strtotime($this->input->post('end_day',true));
        $end_hour = ($this->input->post('end_hour',true));
        $end_min = ($this->input->post('end_min',true));
        $end += $end_hour*3600+$end_min*60;
        if(!$start){
            echo json_token(array('errorcode'=>false,'error'=>"请选择开始时间"));exit;   
        }
        if($param['online'] && !($end>0)){
            echo json_token(array('errorcode'=>false,'error'=>"请选择结束时间"));exit;   
        }
        if( $param['online'] && ($now > $start || $start>=$end || $end<time())){
            echo json_token(array('errorcode'=>false,'error'=>"做作业时间选择有误"));die;
        }
        $param['start_time'] = $start;
        if($end>0){
            $param['deadline'] = $end;
        }else{
            $param['deadline'] = 0;
        }
        $param['description'] = '';
        $param['name'] = addslashes($this->input->post('ex_name',true));
        $param['difficulty'] = $this->input->post('diff',true);
        if($param['difficulty']>=0 && $param['difficulty']<=1){
            $param['difficulty'] *= 100;
        }else{
            $param['difficulty'] = 0 ;
        }
        //科目id
        $paper_id=intval($this->input->post('paper_id',true));//paper_save_log中的testpaper_id
        $save_id=intval($this->input->post('save_id',true));//paper_save_log中的id
        if(!$paper_id){ echo json_token(array('errorcode'=>false,'error'=>"no paper found!"));die; }
        $this->load->model('exercise_plan/homework_paper_model');
        $subject_id =  $this->homework_paper_model->getSubjetIdByTestPaperId($paper_id);//通过paper_id从库里获取
        $subject_id = $subject_id->subject_id;
        //班级id
        $class_ids =  $this->input->post('classgrade',true);
        if(!$class_ids){
            echo json_token(array('errorcode'=>false,'error'=>"没有选择班级不能发布作业"));
        }

        $param['class_ids'] = $this->tepm->alpha_class_ids($class_ids,true); 
        $this->load->model('exercise_plan/homework_course_question_temp_model','tmp_model');
        $this->load->model('exercise_plan/hw_classes_model','cm');

        $this->load->model('paper/paper_model');
        $new_paper_id = $this->paper_model->save_paper($paper_id,$this->user_id,$name='',true,false,Constant::LOCK_TYPE_ASSIGN,false);
        if($new_paper_id){
            $errorcode = true;
            $param['paper_id'] = $new_paper_id['paper_id'];
            $error=$this->lang->line("success_assign_homework");
        }else{
            $error=$this->lang->line("error_assign_homework");
            echo json_token(array('errorcode'=>false,'error'=>$error));exit();
        }
        $assign=$this->assign_homework($param, $subject_id,$save_id);
        if($assign['errorcode']){
            $presult = $this->insert_paper_zuoye($param['class_ids'],$assign,$param, $subject_id);//插入zuoye_assign表
            if(!$presult){
                $error=$this->lang->line("error_assign_homework");
                echo json_token(array('errorcode'=>false,'error'=>$error));exit();      
            }
        }
        echo json_token(array('errorcode'=>$errorcode,'res'=>$assign,'error'=>'布置作业成功!'));exit();
    } 

    //出卷子中的留作业，在插入paper_assign之后，还要插入zuoye_assign表
    function insert_paper_zuoye($class,$assign_info,$param, $subject_id){
        $data['unit_ids'] = '';
        $data['class_id'] = $class[0];
        $data['user_id'] = $this->tizi_uid;
        $pp[0] = array('assignment_id'=>$assign_info['assignment_id'],'paper_id'=>$param['paper_id'],'q_sum'=>$assign_info['paper_q_count'],'is_package'=>false,'package_id'=>0);
        $data['paper_ids'] = json_encode($pp);
        $data['subject_id'] =  $subject_id;
        $data['status'] = 1;
        $data['has_checked'] = 0;
        $data['assign_time'] =time();
        $data['start_time'] = $param['start_time'];
        $data['end_time'] =  $param['deadline'];

        $res = $this->zuoye_model->insert_zuoye($data);
        if($res){
            $this->load->library("credit");//布置作业获得积分
            $score = $this->credit->exec($this->tizi_uid, "make_assign");
            $this->load->model('homework/zuoye_intro_model');
            $this->zuoye_intro_model->change_assign_score($res,$score);
        }
        return $res;
    }

    /*
        插入 paper_assign && student_paper 表 
        布置作业到[多个]班级或学生
        @param array 需要含有以下字段：
        $param['user_id'] int
        $param['name'] string
        $param['start_time'] int
        $param['deadline'] int
        $param['paper_id'] int
        $param['get_answer_way'] int
        $param['class_ids'] = array() 
        $param['subject_id'],int
    */
    function assign_homework($param, $subject_id,$save_id){
        //传过来的作业信息
        $class_ids = $param['class_ids']; //可能是多个班或没有班. 要么array.要么为空（为空是单独布置给学生）   
        if(empty($class_ids)){
            echo json_token(array('errorcode'=>false,'error'=>'没有选择班级不能发布作业','status'=>'0'));exit;
        }
        unset($param['class_ids']);
        $this->load->model('class/classes_teacher','ct');
        $this->load->model('question/question_subject_model','qsm');
        $subject_type_id = $this->qsm->get_subject_type_by_id($subject_id);//通过subject_id获取type id

        $origin_subject_id = $subject_id; //保留住sid
        $subject_id = $subject_type_id; //新的sid是type id
        // //查询传过来的班级是否都合法
        $legal_classes = $this->ct->get_bt($this->user_id,'class_id'); // 老师的所有班级
        $lc = array();
        foreach($legal_classes as $val){
            $lc[] = $val['class_id'];
        }
        foreach($class_ids as $v){
            if(!in_array($v, $lc)){
                echo json_token(array('errorcode'=>false,'error'=>'包含非法选择的班级'));die;
            }
        }
        //增加非传入部分的通用部分
        $param['is_other'] = false;
        $param['paper_id'] = $param['paper_id'];
        $param['assign_time'] = time();
        $param['is_assigned'] = true;
        $param['online_count'] = 0;//$select_count;
        $param['offline_count'] = 0;//$non_select_count;
        //得到本份作业的题数
        $this->load->model('paper/paper_question_model');
        $param['count']=$this->paper_question_model->count_questions($param['paper_id']); // TODO  可以用sectoin里的data来相加
        //foreach 布置作业到（多个）班级
        $this->load->model("exercise_plan/homework_assign_model",'ham');
        $this->load->model("exercise_plan/student_homework_model",'shm');
        $this->load->model("exercise_plan/student_task_model",'stm');
        $this->load->model("exercise_plan/hw_classes_model");
        $this->load->model("exercise_plan/teacher_exercise_plan_model");
        $result = true;
        $assignment_id = 0;
        if(is_array($class_ids)){
            foreach ($class_ids as $c_id){
                !isset($url_to_class)? $url_to_class = alpha_id_num($c_id):'';
                $current_data = array();
                $param['class_id'] = $c_id;
                $assignment_id = $this->ham->ori_homework_assign($param);
                $uid_list = array();
                if($assignment_id){//assignment_id;取出该class_id下的学生,插入到student_homework表
                    $student_ids = $this->hw_classes_model->get_students($c_id);
                    $data = array();
                    if($student_ids){
                        foreach ($student_ids as $stu_id){
                            $data[] = array('paper_assign_id' => $assignment_id,'user_id' => $stu_id);
                            $uid_list[$stu_id] = array(
                                'uid'=>$stu_id,
                                'score'=>0
                            );
                        }
                        $result = $this->stm->advance_save($data);
                        if($result){//插入消息通知
                            $this->hw_notice($student_ids, $param, $subject_id, $assignment_id);
                        }
                    }
                    if($save_id){
                        $this->paper_save_log->incr_assign_count($save_id);//布置试卷次数加一
                    }
                }else{
                    echo json_token(array('errorcode'=>false,'error'=>'系统繁忙,请重新布置'));die; 
                }
                $this->teacher_exercise_plan_model->teacher_ex_total($param['user_id'],1);
            }
        }else{//when class_ids is null,直接布置给学生
            $students = array('0812800106','0812800023');//接收作业的学生
            $assign_id = $this->ham->ori_homework_assign($paper_val);
            $stu_data = array();
            foreach ($students as $student_id){
                $stu_data[] = array('student_id'=>$student_id,'assignment_id'=>$assign_id);
            }
            $result = $this->stm->advance_save($stu_data);

            if($result !== false){//插入消息通知
            }
        }
        $this->load->library("credit");
        if (isset($param["user_id"])){
            // $this->credit->exec($param["user_id"], "homework_firstsave");
            // $this->credit->exec($param["user_id"], "given_an_assignment");
        }
        $url = site_url()."zuoye";
        return array('errorcode'=>true,'error'=>'操作成功','url'=>$url,'assignment_id'=>$assignment_id,'paper_q_count'=>$param['count']); 
    }

    // tizi 4.0 出卷子 中 布置作业时，展示班级的弹窗
    function assign_box(){
        $this->load->model('class/classes_teacher');
         //获取所有班级
        $all_class_info = parent::get_my_classes();
        $do_have_class = count($all_class_info);
        $this->load->model('homework/zuoye_model');
        $classes_zuoye = $this->zuoye_model->intro_zy_list($this->tizi_uid);
        if($classes_zuoye){
            foreach($classes_zuoye as $cs=>&$c){
                $c['alpha_id'] = alpha_id_num($c['class_id']);
                // foreach($all_class_info as $acs=>$ac){
                //     if($c['alpha_id'] == $ac['alpha_id']){
                //         unset($all_class_info[$acs]);
                //     }
                // }
            }
        }
        if($all_class_info){
            foreach($all_class_info as $acs=>$ac){//一个班级一天最多10次
                if($this->zuoye_model->has_assigned_today(alpha_id_num($ac['alpha_id'],true),$this->tizi_uid)>=Constant::ZUOYE_LIMIT_IN_A_DAY){
                    unset($all_class_info[$acs]);
                }
            }
        }
        if($all_class_info){
            $this->smarty->assign('classes',$all_class_info);
            $return['empty'] = false;
        }else{
            $this->smarty->assign('classes',null);
            $return['empty'] = true;
        }
        $this->smarty->assign('do_have_class',$do_have_class);
        $return['errorcode'] = true;
        $return['box_title'] = $do_have_class?'提示信息':'请先创建班级';
        $return['html']=$this->smarty->fetch('teacher/paper/paper_archive_assign_tpl.html');
        echo json_token($return);
    }

     // 发送作业通知
    public function hw_notice($student_id_arr, $paper_val, $subject_id, $aid){
        if(empty($student_id_arr) || empty($paper_val) || empty($subject_id)){
            return false;
        }
        $this->load->model('login/parent_model');
        $this->load->model('question/question_subject_model');
        $this->load->library("notice");
        $paper_val["name"]  = sub_str($paper_val["name"], 0,90);
        $type_id   = $this->question_subject_model->get_subject_type_by_id($subject_id);//科目类型id
        $typename = $this->question_subject_model->get_subject_type_name($type_id);//科目类型名
        //家长收到消息    
        $count = count($student_id_arr);
        $search_nums = 60;//一次最多查询的记录
        $nums = intval(ceil($count/$search_nums));
        for($i=0; $i<$nums; ++$i){
            $start = $i*$search_nums;
            $page_nums = ($start + $search_nums) >= $count ? ($count-$start) : $search_nums;//本次查询的记录数量
            $student_arr = array_slice($student_id_arr, $start, $page_nums);
            $parents = $this->parent_model->get_kids_parents($student_arr);//获取家长id
            if(!empty($parents)){
                foreach($student_arr as $student_id){
                    if(!empty($parents[$student_id])){
                        $s_name = $this->parent_model->get_info($student_id);
                        $s_name = $s_name[0]['name']?$s_name[0]['name']:'';
                        $msg_data = array("s_name" => $s_name , "subject_name" => $typename);
                        foreach($parents[$student_id] as $ps=>$p){
                            $this->notice->add($p, "kid_get_hw", $msg_data);
                        }
                    }
                }
            }
        }
    }



}
