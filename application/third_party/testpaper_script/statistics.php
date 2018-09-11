<?php
include(dirname(__DIR__).'/base_script.php');
init('pq_count');

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

	date_default_timezone_set('PRC');
	$from = date("Y-m-d H:i:s", strtotime("yesterday"));
	$to = date("Y-m-d H:i:s", strtotime("today"));

	$question = array('update' => array(), 'insert'=>array());

	$query = "select testpaper_id from paper_save_log where save_time >= '$from' and save_time < '$to'";
	if($rebulid) $query = "select testpaper_id from paper_save_log";

	$result = mysql_query($query);

	//echo '<pre>';
	$paper_ids = array();
	while (!empty($result) && $row = mysql_fetch_assoc($result))
	{
		$paper_ids[] = $row['testpaper_id'];
	}

	$update_count = $insert_count = $update_error = $insert_error = $redis_count = $redis_error = 0;
	if(!empty($paper_ids))
	{
		$paper_ids = implode(",",$paper_ids);
		$query1 = "select question_id, count(id) as count from paper_question where testpaper_id in ($paper_ids) and is_delete = 0 and (question_origin = 0 or question_origin is NULL) group by question_id";
		$result1 = mysql_query($query1);

		$question_ids = array();
		while (!empty($result1) && $row = mysql_fetch_assoc($result1))
		{
			$question_count['insert'][$row['question_id']] = $row['count'];
			$question_ids[] = $row['question_id'];
		}

		$question_ids = implode(",",$question_ids);
		$query2 = "select question_id, count from question_statistics where question_id in ($question_ids)";
		$result2 = mysql_query($query2);

		while (!empty($result2) && $row = mysql_fetch_assoc($result2))
		{
			$question_count['update'][$row['question_id']] = $question_count['insert'][$row['question_id']] + $row['count'];
			unset($question_count['insert'][$row['question_id']]);
		}

		//var_dump($question_count);

		if(!empty($question_count['update']))
		{
			foreach($question_count['update'] as $qk => $qu)
			{
				$query_update = "update question_statistics set count = $qu where question_id = $qk and update_time < '$to'";
				$result_update = mysql_query($query_update);
				if(mysql_affected_rows()>0) 
				{
					$update_count ++;
					if(!$rebulid && $redis)
					{
						$return = $redis->set($qk,$qu);
						if($return) $redis_count++;
						else $redis_error++;
					}
				}
				else 
				{
					$update_error ++;
				}
			}
			echo 'update:'.$update_count.':'.$update_error.'...';
		}
		
		if(!empty($question_count['insert']))
		{
			$query_insert = "insert into question_statistics (question_id, count) values ";
			foreach($question_count['insert'] as $qk => $qi)
			{
				$query_insert .= "($qk,$qi),";
			}
			$query_insert = substr($query_insert,0,-1).';';
			//echo $query_insert;
			$result_insert = mysql_query($query_insert);
			$insert_count = mysql_affected_rows();
			$insert_error = count($question_count['insert']) - $insert_count;
			echo 'insert:'.$insert_count.':'.$insert_error.'...';

			if(!$rebulid && $redis)
			{
				//$query = "select question_id, count from question_statistics where update_time >= '$to'";
				//$result = mysql_query($query);

				$mset=array();
				$i=$j=0;
				//while (!empty($result) && $row = mysql_fetch_assoc($result))
				foreach($question_count['insert'] as $qk => $qi)
				{
					//$mset[$row['question_id']]=$row['count'];
					$mset[$qk]=$qi;
					$j++;
					if($j >= 50)
					{
						$i++;
						$j=0;
						$return = $redis->mset($mset);
						if($return) $redis_count+=count($mset);
						else $redis_error+=count($mset);
						$mset=array();
					}
				}
				if(!empty($mset))
				{
					$return = $redis->mset($mset);
					if($return) $redis_count+=count($mset);
					else $redis_error+=count($mset);
				}
			}
		}
		
		if($rebulid && $redis)
		{
			$query = "select question_id, count from question_statistics where update_time >= '$to'";
			$result = mysql_query($query);

			$mset=array();
			$i=$j=0;
			while (!empty($result) && $row = mysql_fetch_assoc($result))
			{
				$mset[$row['question_id']]=$row['count'];
				$j++;
				if($j >= 50)
				{
					$i++;
					$j=0;
					$return = $redis->mset($mset);
					if($return) $redis_count+=count($mset);
					else $redis_error+=count($mset);
					$mset=array();
				}
			}
			if(!empty($mset))
			{
				$return = $redis->mset($mset);
				if($return) $redis_count+=count($mset);
				else $redis_error+=count($mset);
			}
		}

		echo 'redis:'.$redis_count.':'.$redis_error.'...';
	}

	local_log(date("Y-m-d H:i:s")."\t新建记录 $insert_count : $insert_error 条，更新记录 $update_count : $update_error 条, Redis记录 $redis_count : $redis_error 条",'testpaper_script_statistics_info');
}
