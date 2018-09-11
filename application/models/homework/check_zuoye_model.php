<?php

class Check_Zuoye_Model extends MY_Model {
    protected $tab = "zuoye_assign";
    protected $tab_z_stu = "zuoye_student";
    protected $tab_com = "zuoye_comment";

    function __construct(){
        parent::__construct();
    }

    //全班的整份作业完成情况
    function finish_situation($ass_id){
        $select = " za.end_time as za_end_time, zs.* ,zc.content as comment";
        $sql = "select $select from {$this->tab} za left join {$this->tab_z_stu} zs on zs.zy_assign_id=za.id left join zuoye_comment zc on zc.zy_assign_id=za.id and zs.user_id=zc.user_id where za.id = $ass_id ";
        $info = $this->db->query($sql)->result_array();

        if(!$info or !$info[0]['user_id'] )return null;
        $stat = $this->zuoye_class_avg($info);

        return array('stu_info'=>$info,'class_info'=>$stat);
    }

    //获取zuoye_student中的完成情况 
    function zuoye_class_avg(&$data){
        if(!is_array($data))return null;
        $count = count($data); //人数count
        $game_time = $avg_score = $complet_sum = $part_sum  = $q_sum = $sum_score =$game_count= 0;
        $this->load->model('login/register_model');
        foreach($data as $students=>&$stu){
            if($stu['is_complete'] == 1){
                $part_sum ++; //部分完成人数
            }elseif($stu['is_complete'] ==2 ){
                $complet_sum ++; //全部完成人数
            }
            $stu['score'] = $stu['question_num']?$stu['correct_num']/$stu['question_num']*100:0;
            $sum_score += $stu['score'];
            // $stu['over_time'] = false;
            $done_game_data = json_decode($stu['zuoye_info'],true);
            $stu['done_game_data'] = $done_game_data;
            $stu['person_avg_game_cost'] = 0;
            if(isset($done_game_data['game'])){
                $gdata = $done_game_data['game'];
                if(!$game_count) $game_count = count($gdata);
                foreach($gdata as $gs=>$gg){//一个人的每款游戏的平均用时
                    $stu['person_avg_game_cost'] += $gg['game_time'][0];
                }
                //此人这次作业，每个游戏的平均时间
                $stu['person_avg_game_cost'] = $stu['person_avg_game_cost']/$game_count;
            }
            //此人这次作业，所有游戏的总时间
            $stu['person_expend_all_game_time'] = $stu['person_avg_game_cost']*$game_count;
            // var_dump($game_data);
            $q_sum += $stu['question_num'];//总题数
            // $game_time += $stu['game_time'];//总时间开销
            $game_time += $stu['person_expend_all_game_time']?$stu['person_expend_all_game_time']:0  ;
            //获取姓名和学号
            $stui = $this->register_model->get_user_info($stu['user_id']);
            $stu['stu_name'] = $stui['user']->name; 
            $stu['stu_number'] = $stui['user']->student_id; 
        }
        $arr = array('avg_time'=>$count?$game_time/$count:0,
                     'avg_score'=>$count?$sum_score/$count:0,
                     'complet_sum'=>$complet_sum,
                     'part_sum'=>$part_sum,
            );
        // var_dump($arr);
        return $arr;
    }

    //某次作业的完成人数    
    function complete_zy_stu($ass_id){
        $sql = "select count(1) as num from {$this->tab_z_stu} where zy_assign_id=$ass_id ";
        $stu_sum = $this->db->query($sql)->row(0)->num;
        $sql_com  =$sql." and is_complete=2";//2是完成
        $stu_com_sum = $this->db->query($sql_com)->row(0)->num;
        $sql_part_com  =$sql." and is_complete=1";//1是部分完成
        $stu_part = $this->db->query($sql_part_com)->row(0)->num;
        return array('stu_sum'=>$stu_sum,'stu_comp'=>$stu_com_sum,'stu_part'=>$stu_part);
    }

        //检查作业，置位为 【已检查】
    function checking_zuoye($ass_id,$teacher_id){
        $this->load->model('homework/zuoye_model');
        $res = $this->zuoye_model->get_assignment($ass_id);
        if(!$res){return false; }
        if($res['has_checked']){ return true ;}
        $end_time = $res['end_time'];
        $this->load->model('homework/check_zuoye_model');
        $complete = $this->check_zuoye_model->complete_zy_stu($ass_id);
        $complete = $complete['stu_sum']==$complete['stu_comp'] ? true:false;
        if($end_time < time() or $complete){ // 作业截止了 或者  所有人都完成了
            $sql = "update {$this->tab} set has_checked=1 where id=$ass_id and user_id=$teacher_id ";
            $this->db->query($sql);
            return  ($this->db->affected_rows() === 1)? true:false;
        }
        return false;
    }

    
}

