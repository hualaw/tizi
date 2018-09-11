<?php
include(dirname(__DIR__).'/base_script.php');
//init(false,'development');
init(false);

clear_pq_isdelete();
clear_habits();

/**
 * 清除paper_question is_delete为1的所有垃圾记录，增加访问速度.
 */ 
function clear_pq_isdelete()
{
	global $db;

	$query = mysql_query("SHOW TABLE STATUS LIKE 'paper_question'");
	$row = mysql_fetch_assoc($query);
	$max_id = $row["Auto_increment"];

	$step = 100000;
	$del_rows = 0;
	$del_logs = array();
	for($i = 1; $i <= $max_id; $i = $i + $step)
	{
		echo "id:".$i."-".($i + $step - 1)."...";
		mysql_query("delete from paper_question where id >= ".$i." and id < ".($i + $step)." and is_delete = 1");
		$mysql_affected_rows = mysql_affected_rows();
		$del_rows += $mysql_affected_rows;
		$del_logs[$i."-".($i + $step - 1)] = $mysql_affected_rows;
	}
	local_log(date("Y-m-d H:i:s")."\t删除Papaer isdel记录 ".$del_rows." 条\t数据删除量".json_encode($del_logs),'testpaper_script_clean_info');
}

function clear_hq_isdelete()
{
	global $db;

	$query = mysql_query("SHOW TABLE STATUS LIKE 'homework_question'");
	$row = mysql_fetch_assoc($query);
	$max_id = $row["Auto_increment"];

	$step = 100000;
	$del_rows = 0;
	$del_logs = array();
	for($i = 1; $i <= $max_id; $i = $i + $step)
	{
		echo "id:".$i."-".($i + $step - 1)."...";
		mysql_query("delete from homework_question where id >= ".$i." and id < ".($i + $step)." and is_delete = 1");
		$mysql_affected_rows = mysql_affected_rows();
		$del_rows += $mysql_affected_rows;
		$del_logs[$i."-".($i + $step - 1)] = $mysql_affected_rows;
	}

	local_log(date("Y-m-d H:i:s")."\t删除Homework isdel记录 ".$del_rows." 条\t数据删除量".json_encode($del_logs),'testpaper_script_clean_info');
}

/**
 * 清除垃圾试卷
 */
function clear_habits(){
	global $db;
	$ids = array();
	$sql_str = "SELECT a.id FROM paper_testpaper AS a LEFT JOIN 
			paper_save_log AS b ON a.id=b.testpaper_id LEFT JOIN 
		paper_download_log AS c ON a.id=c.testpaper_id LEFT JOIN 
		paper_assign AS d ON a.id=d.paper_id
			WHERE b.testpaper_id is null and c.testpaper_id is null and d.paper_id is null 
			and a.is_saved=1 and a.is_locked<=0";
	$query = mysql_query($sql_str);
	while ($row = mysql_fetch_assoc($query)){
		$ids[] = $row["id"];
	}
	$remove_total = array(
		"paper_question" => 0,
		"paper_question_type" => 0,
		"paper_section" => 0,
		"paper_testpaper" => 0
	);
	foreach ($ids as $value){
		mysql_query("delete from paper_question where testpaper_id=".$value);
		$remove_total["paper_question"] += mysql_affected_rows();
		
		mysql_query("delete from paper_question_type where testpaper_id=".$value);
		$remove_total["paper_question_type"] += mysql_affected_rows();
		
		mysql_query("delete from paper_section where testpaper_id=".$value);
		$remove_total["paper_section"] += mysql_affected_rows();
		
		mysql_query("delete from paper_testpaper where id=".$value);
		$remove_total["paper_testpaper"] += mysql_affected_rows();
	}
	$log_str = date("Y-m-d H:i:s")."\t删除paper_testpaper ID 共".count($ids)." 个\t数据删除量".json_encode($remove_total);
	local_log($log_str, "clear_habits");
}