<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-4-21
 * Time: 上午10:26
 * To change this template use File | Settings | File Templates.
 */
set_time_limit(0);
include(dirname(__DIR__)."/base_script.php");

//init('notice', "development");
init('notice');

$user_query = mysql_query("SELECT id FROM user WHERE is_lock = 0 AND user_type = 3");

$now_date = date('Y-m-d H:i:s', time());
$pre_thirty_days = date('Y-m-d H:i:s', time()- 30 * 86400);

$login_medal_type = 2;
$user_count = 0;
global $redis;

echo date("Y-m-d")." 开始处理老师登录次数数据...\r\n";

while($user_id = mysql_fetch_assoc($user_query)){
	$i = 0;
	$query = mysql_query("SELECT generate_time FROM `session` WHERE
			user_id = {$user_id['id']} and generate_time >= '{$pre_thirty_days}' and generate_time <= '{$now_date}'
			group by dayofyear(generate_time)");

	$session_data = array();
	while ($tmp = mysql_fetch_assoc($query)){
		$session_data = $tmp;
		++$i;
	}
	if (empty($session_data)) continue;

	$now_level = medal_login_count_level($i, 'level');

	//用户登录的数据
	$user_login_statistics = get_user_medal_info($user_id['id']);

	$param = array();
	$param['user_id'] = $user_id['id'];

	if (empty($user_login_statistics[$login_medal_type])) {
		//不存在 新增
		$param['medal_type'] = $login_medal_type;
		$param['level'] = $now_level;
		$param['get_date'] = strtotime($session_data['generate_time']);
		$param['upgrade_msg'] = upgrade_msg($param['level'], $i);

		if ($redis) {
			$data = json_encode(array("_mt" => "login_master"));
			$redis->zadd($user_id['id'], time(), $data);
		}
		if (insert_user_medal($param)){
			echo $user_id['id'] . " level=" . $now_level . " insert complete!\r\n";
			++$user_count;
		}
	} else {
		//存在就更新数据,是否更新等级
		$old_level = $user_login_statistics[$login_medal_type]['level'];

		if ($now_level == $old_level) {
			//等级没有变化，但是登录天数有变化，那么只更新upgrade_msg
			$param['upgrade_msg'] = upgrade_msg($now_level, $i);
		} else {
			$param['level'] = $now_level;
			$param['get_date'] = strtotime($session_data['generate_time']);
			$param['upgrade_msg'] = upgrade_msg($param['level'], $i);

			$msg_type = ($now_level > $old_level) ? 'login_master_up' : 'login_master_down';

			if ($redis) {
				$data = json_encode(array("_mt" => $msg_type, "level"=> $now_level));
				$redis->zadd($user_id['id'], time(), $data);
			}
		}

		if (update_user_medal($user_id['id'], $login_medal_type, $param)) {
			echo $user_id['id'] . " level=" . $now_level . " update complete!\r\n";
			++$user_count;
		}
	}
}
echo $user_count . "个老师登录达人勋章信息更新成功\n";
local_log(date("Y-m-d H:i:s")." " . $user_count . "个老师登录达人勋章信息更新成功\n", "medal_script_info");

/** 获取用户过去30天内，登录天数相对应的等级
 * @static
 * @param $login_count_level
 * @return int|string
 */
function medal_login_count_level($login_count_level, $flag = 'level') {
	$level = array(
		1 => 4,
		2 => 8,
		3 => 12,
		4 => 16,
		5 => 20,
		6 => 24,
		7 => 28,
		8 => 30
	);
	if ($flag == 'level') {
		if ($login_count_level >= 30) return 8;

		foreach ($level as $kl => $vl) {
			if ($login_count_level <= $vl) {
				return $kl;
			}
		}
	} elseif ($flag == 'count') {
		if ($login_count_level <= 1) return 4;
		if ($login_count_level >= 8) return 30;

		foreach ($level as $kl => $vl) {
			if ($login_count_level == $kl) {
				return $vl;
			}
		}
	}
}

/** 获取用户登录统计表信息
 * @param $user_id
 * @return mixed
 */
function get_user_medal_info($user_id) {
	$query = mysql_query("SELECT * FROM user_medal WHERE user_id = {$user_id}");

	$login_statistics = array();

	while($query_info = mysql_fetch_assoc($query)) {
		$login_statistics[$query_info['medal_type']] = $query_info;
	}

	return $login_statistics;
}

/** 向用户登录统计表中插入数据
 * @param $param
 * @return bool
 */
function insert_user_medal($param) {
	mysql_query("INSERT INTO user_medal(user_id,medal_type,upgrade_msg,get_date, level)
					VALUES (
						{$param['user_id']},{$param['medal_type']},'{$param['upgrade_msg']}',{$param['get_date']},{$param['level']}
					)");

	if (mysql_affected_rows() === 1) {
		return true;
	}
	return false;
}

/** 更新用户登录统计表
 * @param $user_id
 * @param $param
 * @return bool
 */
function update_user_medal($user_id, $medal_type, $param) {
	$sql = "UPDATE user_medal SET ";
	foreach ($param as $kp => $vp) {
		$sql .= $kp . "='" . $vp . "',";
	}
	mysql_query(trim($sql, ',') . " WHERE user_id = {$user_id} AND medal_type = {$medal_type}");

	if (mysql_affected_rows() === 1){
		return true;
	}
	return false;
}

/** 用户登录等级 升级的提示
 * @param $login_level
 * @param $login_count
 * @return string
 */
function upgrade_msg ($login_level, $login_count) {
	if ($login_level == 8) return '明天继续登录就能保持。';

	$next_login_count = medal_login_count_level($login_level, 'count') + 1;

	switch($next_login_count - $login_count) {
		case 1:
			return '明天继续登录就能升级。';
		case 0:
			return '明天继续登录就能保持。';
		default :
			return '明天继续登录就能保持。';
	}
}
