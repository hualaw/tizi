<?php
require_once(dirname(__DIR__).'/base_script.php');
init();

optimize();

//优化表，整理碎片
function optimize(){
	global $db;
	mysql_query("Optimize TABLE paper_question");
	mysql_query("repair table paper_question");
	local_log(date("Y-m-d H:i:s")." 优化表完成", "optimize");
}