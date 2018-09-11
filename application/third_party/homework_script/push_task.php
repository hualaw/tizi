<?php
include(dirname(__DIR__).'/base_script.php');

$push_task = new Push_Task();

$begin_date = time();

$push_task->_getTodayVideo();

echo "\r\n";

if(!empty($push_task->videos)){
    $push_task->pushTask();
}else{
    echo "没有视频可更新!\r\n";
    local_log(date("Y-m-d H:i:s").' 没有视频可更新!','push_task_info');
}

$used_time = time() - $begin_date;

echo "\r\n".date("Y-m-d h:i:s").'push task completed,used ',$used_time." seconds\r\n";

class Push_Task{

    private $video_update_online_su_num;

    public function __construct(){
        init();
    }

    public function pushTask(){

        echo date("Y-m-d")." 开始获取用户列表...\r\n";
        $user_list = $this->_getAllUsers();
        echo date("Y-m-d")." 用户获取结束.\r\n";
        echo '开始推送...'."\r\n";

        $u_num = 0;
        $faild_u_num = 0;

        foreach($user_list as $user){
            if( $user['register_grade'] && isset($this->videos[$this->_get_grade_video($user['register_grade'])])){
                $video = $this->videos[$this->_get_grade_video($user['register_grade'])];
            }else{
                continue;
            }
            $date = strtotime($video['date']);

            $res = mysql_query("SELECT * FROM `student_task` WHERE `index_value` = {$video['id']} AND `task_type` = 2 AND `uid` = {$user['id']}");
            if(mysql_num_rows($res)) continue;//check repeat

            if(!mysql_query("insert into `student_task`(`index_value`,`task_type`,`uid`,`date`) value({$video['id']},2,{$user['id']},{$date})")){//task type 2
                echo "table:student_video;uid:{$user['id']};video:{$user['id']};insert faild\r\n";
                $faild_u_num++;
            }else{
                $u_num++;
            }
        }
        echo '推送结束,共推送用户'.$u_num.'个, 失败'.$faild_u_num.'个...'."\r\n";

        $this->_updateTodayVideo();

        local_log(date("Y-m-d H:i:s").' 视频推送完成报告: '.'成功推送人数: '.$u_num.' , 失败 '.$faild_u_num.'个;今日需要推送视频数: '.count($this->videos).', 视频 online 状态成功更新'.$this->video_update_online_su_num.' 个','push_task_info');

    }

    private function _getAllUsers(){
        $res = mysql_query("select `id`,`register_grade` from `user` where `user_type` = 2");
        $user_list = array(); 
        while( $data = mysql_fetch_array($res)){
            $user_list[] = $data;  
        }
        return $user_list;
    }

    public function _getTodayVideo($date=''){
        if(!$date){
            $date = date("Y-m-d");
        } 
        $res = mysql_query("select `id`,`date`,`grade_id` from student_video where `date` like '{$date}%' and `online` = 0");
        $videos = array();
        if(mysql_num_rows($res)){
            while($data = mysql_fetch_array($res)){
                $grade = 
                $videos[$data['grade_id']] = $data;
            }
        }
        $this->videos = $videos;
    }

    private function _updateTodayVideo(){

        $i = 0;
        echo '开始更新视频 `online` ...'."\r\n";
        foreach($this->videos as $video){
            if(!mysql_query("update `student_video` set `online` = 1 where `id` = {$video['id']}")){
                echo '视频 id:'.$video['id'].' online update update faild.'."\r\n";    
            }else{
                $i++;
            }
        }
        $this->video_update_online_su_num = $i;
        echo '更新视频 `online` 结束, 总共: '.count($this->videos).'个 , 更新成功: '.$i.'个 .'."\r\n";
        
    }

    private function _get_grade_video($mygrade)                                           
    {                                                                            
        $grade_video=1;                                                          
        switch($mygrade)                                                         
        {                                                                        
        case 1:                                                              
        case 2:                                                              
        case 3: $grade_video=3;break;                                        
        case 4:                                                              
        case 5:                                                              
        case 6: $grade_video=4;break;                                        
        case 7:                                                              
        case 8:                                                              
        case 9: $grade_video=1;break;                                        
        case 10:                                                             
        case 11:                                                             
        case 12:$grade_video=2;break;                                        
        default:$grade_video=1;break;                                        
        }                                                                        
        return $grade_video;                                                     
    }    
   
}

