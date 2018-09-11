<?php
// 默认回答问题时间：60 mins.超过此时间还未能回答问题的，被回收答题权.

set_time_limit(0);
$config = array(
    'mysql' => array(
        'host' =>'localhost',
        'user' => 'root',
        'password' => 'root',
        'dbname' => 'new_zujuan',
    ),
    //  'mysql' => array(
    //     'host' =>'192.168.11.12',
    //     'user' => 'tizi',
    //     'password' => 'tizi',
    //     'dbname' => 'new_zujuan',
    // ),
);

$mysql_config = $config['mysql'];
//mysql init
$db = new Db($mysql_config);

$res = $db->patrol();

class Db{
    public function __construct($mysql_config){
        $con = mysql_connect($mysql_config['host'],
        $mysql_config['user'],$mysql_config['password']);
        mysql_select_db($mysql_config['dbname']);
        mysql_query("set names utf8");
    }

    public function patrol(){
        $max_interval = 60*60; // 默认回答问题时间：60 mins.超过此时间还未能回答问题的，被回收答题权
        $select_sql = "select id, teacher_id,start_date from aq_question where is_resolved=2 and `specific`=0 and is_del=0";
        $rs= mysql_query($select_sql);
        while($r = mysql_fetch_array($rs)){
              echo $r['id'] . " " . date('Y-m-d H:i:s',$r['start_date']);
              echo "<br />";
              $interval = time()-$r['start_date'];
              // update DB, revoke the right of answering the question
              if($interval>$max_interval){
                $t_id = $r['teacher_id'];
                $q_id  = $r['id'];

                //同时更新aq_question表(题目的相关状态)和aq_teacher表（update 回收次数）
                $update_q_sql = "UPDATE aq_question AS q , aq_teacher AS t SET q.teacher_id=0,q.start_date=0,q.is_resolved=1,t.aq_revoke_times = t.aq_revoke_times+1 where q.id = $q_id and t.id=$t_id";
                mysql_query($update_q_sql);
              }
        }
    }

}