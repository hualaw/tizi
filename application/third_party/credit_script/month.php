<?php
include(dirname(__DIR__)."/base_script.php");

//init(false, "development");
init(false);

$query = mysql_query("select count(*) as num from `user` where user_type=3");
$total = mysql_fetch_assoc($query);
$total = $total["num"];

$step_max = 1000;
$step_total = ceil($total / $step_max);
$succ_mopay = 0;
$cu_timestamp = strtotime(date("Y-m-d"));
$year_sec = 86400 * 365;

$credit_rule = array();
$query = mysql_query("select * from credit_rule where group_by=4");
while ($res = mysql_fetch_assoc($query)){
	$credit_rule[$res["action"]] = $res;
}

for ($i = 1; $i <= $step_total; ++$i){
	$offset = ($i - 1) * $step_max;
	$sql_str = "select id,register_time,certification,is_lock from `user` where user_type=3 order 
		by id asc limit {$offset},{$step_max}";
	$query = mysql_query($sql_str);
	while ($res = mysql_fetch_assoc($query)){
		if ($res["is_lock"] == 0){
			$mopay = mopay($res["id"], strtotime($res["register_time"]), $res["certification"]);
			if (true === $mopay){
				$succ_mopay++;
			}
		}
	}
}

local_log(date("Y-m-d H:i:s")." 发积分成功{$succ_mopay}人\n", "credit_month");
echo "发积分完成";



function mopay($user_id, $register_timestamp, $certification){
	global $db, $credit_rule, $cu_timestamp, $year_sec;
	$register_sec = $cu_timestamp - $register_timestamp;
	
	if ($register_sec < $year_sec){
		$rulekey = "month_credit_1";
	} else if ($register_sec >= $year_sec && $register_sec < ($year_sec * 2)){
		$rulekey = "month_credit_2";
	} else if ($register_sec >= ($year_sec * 2) && $register_sec < ($year_sec * 3)){
		$rulekey = "month_credit_3";
	} else {
		$rulekey = "month_credit_4";
	}
	
	if ($certification == 1){
		$credit_change = $credit_rule[$rulekey]["certificate_credit"];
	} else {
		$credit_change = $credit_rule[$rulekey]["general_credit"];
	}
	$foreign_id = $credit_rule[$rulekey]["id"];
	$msg = $credit_rule[$rulekey]["statement"];
	
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
		mysql_query("insert into credit_logs(user_id,foreign_id,credit_change,total,msg) 
			values({$user_id},{$foreign_id},{$credit_change},{$total},'{$msg}')");
		if (mysql_affected_rows() === 1){
			return true;
		}
	}
	return false;
}