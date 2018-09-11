<?php
include(dirname(__DIR__).'/base_script.php');
init('statistics');

$rebulid = false;
if(isset($argv[1])&&$argv[1]=='rebuild') 
{
	$rebulid = true;
	echo 'data rebuild...';
}

get_statistics($rebulid);

function get_statistics($rebulid=false)
{
	global $db;
	global $redis;

	$question = array('update' => array(), 'insert'=>array());

	$query = "select user_type,count(id) as count from user where user_type > 1 and is_lock = 0 group by user_type";
	
	$result = mysql_query($query);

	//echo '<pre>';
	$user_statistics = array();
	while (!empty($result) && $row = mysql_fetch_assoc($result))
	{
		$user_statistics[$row['user_type']] = $row['count'];
	}

	$query = "select count(*) as count from classes_student_create where user_id=0 and class_id > 0";

	$result = mysql_query($query);

	$student_count = 0;
	while (!empty($result) && $row = mysql_fetch_assoc($result))
	{
		$student_count = $row['count'];
	}
	$user_statistics[2]+=$student_count;

	$query = "select count(DISTINCT school_id) as count from classes where school_id>0 and class_status=0";

	$result = mysql_query($query);

	$school_count = 0;
	while (!empty($result) && $row = mysql_fetch_assoc($result))
	{
		$school_count = $row['count'];
	}
	
	//更新题目数量
	$query = mysql_query("select lesson_total,question_total from user_statistics order by id desc 
		limit 0,1");
	$result = mysql_fetch_assoc($query);
	$lesson_total = $result["lesson_total"] + mt_rand(100, 500);
	$question_total = $result["question_total"] + mt_rand(100, 500);

	$update_count = $insert_count = $update_error = $insert_error = $redis_count = $redis_error = 0;

	$query_insert = "insert into user_statistics (teacher, student, parent, school, lesson_total, 
		question_total) values ($user_statistics[3],$user_statistics[2],$user_statistics[4],$school_count,
		{$lesson_total}, {$question_total});";
	
	$result_insert = mysql_query($query_insert);
	$insert_count = mysql_affected_rows();
	$insert_error = 0;
	echo 'insert:'.$insert_count.':'.$insert_error.'...';

	if($redis)
	{
		$hmset = array(
			'teacher'=>$user_statistics[3],
			'student'=>$user_statistics[2],
			'parent'=>$user_statistics[4],
			'school'=>$school_count,
			'lesson_total' => $lesson_total,
			'question_total' => $question_total
		);
		$return = $redis->hmset('user_statistics',$hmset);
		if($return) $redis_count = count($hmset);
		echo 'redis:'.$redis_count.':'.$redis_error.'...';
	}
	
	local_log(date("Y-m-d H:i:s")."\t新建记录 $insert_count : $insert_error 条，更新记录 $update_count : $update_error 条, Redis记录 $redis_count : $redis_error 条",'user_script_statistics_info');
}
