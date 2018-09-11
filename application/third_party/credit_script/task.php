<?php
include(dirname(__DIR__)."/base_script.php");

//init(false, "development");
init(false);

$query = mysql_query("select count(*) as num from `user` where user_type=3");
$total = mysql_fetch_assoc($query);
$total = $total["num"];

$step_max = 1000;
$step_total = ceil($total / $step_max);

$task_rule = array();
$query = mysql_query("select * from task_rule");
while ($res = mysql_fetch_assoc($query)){
	$task_rule[$res["name"]] = $res;
}

for ($i = 1; $i <= $step_total; ++$i){
	$offset = ($i - 1) * $step_max;
	$sql_str = "select id,is_lock from `user` where user_type=3 order 
		by id asc limit {$offset},{$step_max}";
	$query = mysql_query($sql_str);
	while ($res = mysql_fetch_assoc($query)){
		if ($res["is_lock"] == 0){
			verify($res["id"]);
			echo "user_id-".$res["id"]." verify complete.\n";
		}
	}
}

function verify($user_id){
	global $db, $task_rule;
	foreach ($task_rule as $value){
		call_user_func($value["name"], $user_id, $value);
	}
}

function use_educloud($user_id, $rules){
	global $conn;
	$query = mysql_query("select id from cloud_user_file where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		a($user_id, $rules["id"]);
	}
}

function archive_paper($user_id, $rules){
	global $db;
	$query = mysql_query("select id from paper_save_log where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		a($user_id, $rules["id"]);
	}
}

function self_cert($user_id, $rules){
	global $db;
	$query = mysql_query("select certification from `user` where id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		if ($res["certification"] == 1){
			a($user_id, $rules["id"]);
		}
	}
}

function use_class($user_id, $rules){
	global $db;
	$query = mysql_query("select id from classes where creator_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		a($user_id, $rules["id"]);
	}
}

function archive_homework($user_id, $rules){
	global $db;
	$query = mysql_query("select id from homework_save_log where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		a($user_id, $rules["id"]);
	}
}

function download_lesson($user_id, $rules){
	global $db;
	$query = mysql_query("select id from lesson_download_log where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		a($user_id, $rules["id"]);
	}
}

function use_invite($user_id, $rules){
	global $db;
	$query = mysql_query("select count(*) as num from `user_invite_register` where reg_invite={$user_id}");
	$res = mysql_fetch_assoc($query);
	if ($res["num"] > 0){
		a($user_id, $rules["id"]);
	}
}

function a($user_id, $task_id){
	global $db;
	$date = date("Y-m-d H:i:s");
	mysql_query("insert ignore into task_rule_logs(user_id,task_id,cyclenum,start_date,last_date) 
		values({$user_id},{$task_id},1,'{$date}','{$date}')");
	return mysql_affected_rows();
}