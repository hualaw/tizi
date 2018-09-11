<?php

global $db;
global $redis;

function init($redis_db=false, $environment='production')
{
	global $db;
	global $redis;

	set_time_limit(1200);
	error_reporting(E_ALL);
	define('DS', DIRECTORY_SEPARATOR);
	define('BASEPATH', dirname(dirname(__DIR__)).DS);
	define('APPPATH', dirname(__DIR__).DS);
	define('LIBPATH', dirname(dirname(dirname(__DIR__))).DS.'tizi_lib'.DS);
	//$environment = 'production';
	//$environment = 'development';

	if(!file_exists($file_path = APPPATH.'config'.DS.$environment.DS.'database.php'))
	{
		if(!file_exists($file_path = LIBPATH.'library'.DS.'config'.DS.$environment.DS.'database.php'))
		{
			if(!file_exists($file_path = LIBPATH.'library'.DS.'config'.DS.'database.php'))
			{
				echo 'error require db...';exit;
			}
		}
	}
	require($file_path);

	if(!file_exists($file_path = APPPATH.'config'.DS.$environment.DS.'redis.php'))
	{
		if(!file_exists($file_path = LIBPATH.'library'.DS.'config'.DS.$environment.DS.'redis.php'))
		{
			if(!file_exists($file_path = LIBPATH.'library'.DS.'config'.DS.'redis.php'))
			{
				echo 'error require redis...';exit;
			}
		}
	}
	require($file_path);

	echo 'start...';

	$default = $db['tizi'];
	$db = mysql_connect($default["hostname"], $default["username"], $default["password"]);
	mysql_select_db($default["database"]);
	mysql_query("set names utf8");
	echo 'db ready...';

	if($redis_db)
	{
		$default = $config['redis_default'];
		$redis = new Redis();
		$return = $redis->connect($default['host'], $default['port'], $default['timeout']);
		if($return)
		{
			$redis->auth($default['password']);
			$redis->select($config['redis_db'][$redis_db]);
			echo 'redis ready...';
		}
		else
		{
			$redis = false;
			echo 'redis failed...';
		}
	}
}

function local_log($msg,$script_name){
	$fp = fopen(dirname(__FILE__).DS."log".DS.$script_name.".log", "a+");
	fwrite($fp, $msg."\n");
	fclose($fp);

	echo 'end...';
}
