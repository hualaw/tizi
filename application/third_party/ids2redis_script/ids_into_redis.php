<?php
//http://xue.tizi.cn/application/third_party/ids2redis_script/ids_into_redis.php
include(dirname(__DIR__).'/base_script.php');
//初始化信息
init('study_statistics');
//init('study_statistics', 'development');

$knowledge_arr = array();

echo date("Y-m-d H:i:s")."clean redis task begin\r\n";
//清除数据
clear_knowledge_stat_key($knowledge_arr);

echo date("Y-m-d H:i:s")."into redis task begin\r\n";
$j = 0;
//print_r($knowledge_arr);exit;
foreach($knowledge_arr as $val){
	add_ids_into_redis($val["knowledge_id"]);
	$j++;
}
echo date("Y-m-d H:i:s")."into redis task total:{$j}end\r\n";

/**
 * 根据知识点统计表clear_knowledge_stat_key清除相应的redis-key
 */
function clear_knowledge_stat_key(&$knowledge_arr)
{
	global $db;
	global $redis;
	
	$sql_str = "SELECT knowledge_id FROM study_knowledge_stat WHERE question_num >0";
	$query = mysql_query($sql_str);
	$i = 0;
	//$knowledge_arr = array();
	while (!empty($query) && $row = mysql_fetch_array($query)){
		$knowledge_arr[] = $row;
		if($redis){
			$redis->delete('k_question_ids_'.$row["knowledge_id"]);
			$i++;
		}
	}
	local_log(date("Y-m-d H:i:s")."\t tizi_study清除知识点试题统计redis-key ".$i." 条",'study_knowledge_question_ids_log');
	echo date("Y-m-d H:i:s")."clean redis count: ".$i."\r\n";
	echo date("Y-m-d H:i:s")."clean redis task end\r\n";
}

/**
 * 根据知识点统计表study_category_stat生成相应的redis-key
 */ 
function add_ids_into_redis($knowledge_id) {
	global $db;
	global $redis;
	//获得一级以及二级知识点的id
//	$sql_str1 = "SELECT id FROM study_knowledge WHERE parentId = {$knowledge_id} UNION SELECT id from study_knowledge WHERE id = {$knowledge_id}";
//	$query = mysql_query($sql_str1);
//
//	$knowledge_id_list = array();
//	while(!empty($query) and $data = mysql_fetch_array($query)){
//		$knowledge_id_list[] = $data['id'];
//	}

//	$knowledge_str = implode(',', $knowledge_id_list);
	//TODO 状态没有加 数据太少 上线加上状态判断
//	$query2 = mysql_query("SELECT sq.id FROM study_questions sq LEFT JOIN study_knowledge_question_rel sk ON sq.id = sk.qId WHERE sk.kId IN ({$knowledge_str})");
	$query2 = mysql_query("SELECT sq.id FROM tiku_base_questions sq LEFT JOIN study_knowledge_question_rel sk ON sq.id = sk.qId WHERE sk.kId = {$knowledge_id}  AND sq.status = 2");
	$i = 0;
	while(!empty($query2) && $row = mysql_fetch_array($query2)){
		if($redis){
			$redis->sadd('k_question_ids_' . $knowledge_id, strval($row['id']));
			$i++;
		}
	}
	echo date("Y-m-d H:i:s")."knowledge_id:{$knowledge_id}-num:{$i} \r\n";
	local_log(date("Y-m-d H:i:s")."\t tizi_study创建知识点:".$knowledge_id."QID共 ".$i." 条",'study_knowledge_question_ids_log');
 }
