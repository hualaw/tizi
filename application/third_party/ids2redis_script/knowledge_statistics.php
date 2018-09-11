<?php
//http://xue.tizi.cn/application/third_party/ids2redis_script/konwledge_statistics.php
//知识点和知识点下题目数量的统计
include(dirname(__DIR__).'/base_script.php');
//init(false, 'development');
init();

//先清除数据后插入
mysql_query("TRUNCATE study_knowledge_stat");
echo date("Y-m-d H:i:s") . '  study_knowledge_stat 表数据清除成功';

$sql = "SELECT sk.kId, COUNT(*) k_num FROM study_knowledge_question_rel sk LEFT JOIN tiku_base_questions sq ON sk.qId = sq.id WHERE sq.materialId = 0 AND sq.status = 2 GROUP BY sk.kId ";
$query = mysql_query($sql);

$k = 0;
while (!empty($query) && $row = mysql_fetch_array($query)){
	//循环插入数据
	$k++;
	mysql_query("INSERT INTO study_knowledge_stat (knowledge_id, question_num) VALUES ({$row['kId']}, {$row['k_num']})");
}
local_log(date("Y-m-d H:i:s")."\t " . $k . "个知识点下面的题目数量统计成功 ",'study_knowledge_stat_log');
echo date("Y-m-d H:i:s") . ' ' . $k . ' 个知识点下面的题目数量统计成功';