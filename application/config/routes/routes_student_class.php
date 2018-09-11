<?php
$route['student/teacher_share']="student/task/index/3";
// 学生端登录后首页
$route['student/homework/student_index']="student/task/student_index";
$route['student/cloud/download']="class/student_resource/download_share";//下载网盘文件
$route['student/cloud/downverify']="resource/cloud_base/download_verify";//下载网盘文件确认
$route['student/cloud/add_download_count']="student/task/add_download_count";//下载网盘文件+1

// $route['student/cloud/file/(:num)']="student/task/share_detail/$1";
$route['student/cloud/file/(:num)']="class/student_resource/share_detail/$1";
$route['student/cloud/love']="student/task/love_share";//下载网盘文件

//student paper
//$route['student/class/paper/do/(:num)'] = "class/student_paper_do/index/$1";
$route['student/class/paper/get_answer'] = "class/student_paper_do/get_answer";
$route['student/class/paper/pause'] = "class/student_paper_do/pause";
$route['student/class/paper/online'] = "class/student_paper_do/online_question_save";
$route['student/class/paper/submit'] = "class/student_paper_do/submit";
$route['student/class/paper/offline'] = "class/student_paper_do/paperwork_question_submit";
//$route['student/class/paper/report/(:num)/(:num)'] = "class/student_paper_report/index/$1/$2";

//student class
$route['student/class/(:any)'] = "class/student_class/intro/$1";

//student homework

$route['student/homework/paper/report/(:num)/(:num)'] = "class/student_paper_report/index/$1/$2";
$route['student/homework/paper/(:num)/(:num)'] = "class/student_paper_do/index/$1/$2";
$route['student/homework/game/(:num)/(:num)'] = "class/game/index/$1/$2";
$route['homework/game_question/(:num)/(:num)/(:any)'] = "class/game/get_question/$1/$2/$3";
$route['homework/game_question/(:num)/(:num)'] = "class/game/get_question/$1/$2";//原来的链接地址，保留以容错
$route['homework/game_simulate_question/(:num)/(:num)/(:any)'] = "class/game/get_simulate_question/$1/$2/$3";
$route['homework/game_simulate_question/(:num)/(:num)'] = "class/game/get_simulate_question/$1/$2"; //原来的链接地址，保留以容错
$route['homework/game_submit'] = "class/game/submit";
$route['homework/video_submit'] = "class/student_homework/video_submit";
