<?php
include(dirname(__DIR__)."/base_script.php");

//init(false, "development");
init(false);

$query = mysql_query("select count(*) as num from `user` where user_type=3");
$total = mysql_fetch_assoc($query);
$total = $total["num"];

$step_max = 1000;
$step_total = ceil($total / $step_max);

$credit_rule = array();
$query = mysql_query("select * from credit_rule where group_by=3");
while ($res = mysql_fetch_assoc($query)){
	$credit_rule[$res["action"]] = $res;
}

for ($i = 1; $i <= $step_total; ++$i){
	$offset = ($i - 1) * $step_max;
	$sql_str = "select id,certification,is_lock from `user` where user_type=3 order 
		by id asc limit {$offset},{$step_max}";
	$query = mysql_query($sql_str);
	while ($res = mysql_fetch_assoc($query)){
		if ($res["is_lock"] == 0){
			verify($res["id"], $res["certification"]);
			echo $res["id"]." verify complete.\n";
		}
	}
}

function verify($user_id, $certification){
	global $db, $credit_rule;
	$rule_logs = array();
	$query = mysql_query("select * from credit_rule_logs where user_id={$user_id}");
	while ($res = mysql_fetch_assoc($query)){
		$rule_logs[$res["rule_id"]] = $res;
	}
	foreach ($credit_rule as $value){
		if (!isset($rule_logs[$value["id"]])){
			call_user_func($value["action"], $user_id, $value, $certification);
		}
	}
}

function certificate_phone($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select phone_verified from `user` where id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		if ($res["phone_verified"] == 1){
			absolute_insert($user_id, $rule, $certification);
		}
	}
	return false;
}

function certificate_email($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select email_verified from `user` where id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		if ($res["email_verified"] == 1){
			absolute_insert($user_id, $rule, $certification);
		}
	}
}

function cloud_first_uploaded($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select id from cloud_user_file where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		absolute_insert($user_id, $rule, $certification);
	}
}

function class_first_create($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select id from classes where creator_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		absolute_insert($user_id, $rule, $certification);
	}
}

function homework_firstsave($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select id from homework_assign where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		absolute_insert($user_id, $rule, $certification);
	}
}

function paper_firstsave($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select id from paper_save_log where user_id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		absolute_insert($user_id, $rule, $certification);
	}
}

function certificate_teacher($user_id, $rule, $certification){
	global $db;
	$query = mysql_query("select certification from `user` where id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		if ($res["certification"] == 1){
			absolute_insert($user_id, $rule, $certification);
		}
	}
}

function absolute_insert($user_id, $rule, $certification){
	if ($certification == 1){
		$credit_change = $rule["certificate_credit"];
	} else {
		$credit_change = $rule["general_credit"];
	}
	$foreign_id = $rule["id"];
	$msg = $rule["statement"];
	
	$query = mysql_query("select * from credit where id={$user_id}");
	if ($res = mysql_fetch_assoc($query)){
		$balance = $res["balance"] + $credit_change;
		$total = $res["total"] + $credit_change;
		mysql_query("update credit set balance={$balance},total={$total} where id={$user_id}");
	} else {
		$balance = $credit_change;
		$total = $credit_change;
		mysql_query("insert ignore into credit(id,balance,total) values({$user_id},{$balance},{$total})");
	}
	if (mysql_affected_rows() === 1){
		$date = date("Y-m-d H:i:s");
		mysql_query("insert into credit_logs(user_id,foreign_id,credit_change,total,msg,cyclenum) 
			values({$user_id},{$foreign_id},{$credit_change},{$total},'{$msg}',1)");
		mysql_query("insert into credit_rule_logs(rule_id,user_id,cyclenum,start_date,last_date) 
			values({$foreign_id},{$user_id},1,'{$date}','{$date}')");
		if (mysql_affected_rows() === 1){
			return true;
		}
	}
}