<?php

//tizi 4.0 
$route['zuoye/score/(:num)'] = 'homework/teacher_score/score/$1';//分数明细
$route['zuoye/class/report/(:any)'] = 'homework/teacher_report/class_report/$1';//班级所有作业 汇总报告
$route['zuoye/check/(:num)'] = 'homework/teacher_check/check/$1';//检查作业
// $route['zuoye/assign/(:num)'] = 'homework/teacher_assign/assign/$1';//班级所有作业 汇总报告
// $route['zuoye/assign/(:num)'] = 'homework/teacher_assign/new_assign/$1';//班级所有作业 汇总报告
$route['zuoye/assign/(:num)/(:num)/(:num)'] = "homework/teacher_assign/new_assign/$1/$2/$3";
$route['zuoye/assign/(:num)/(:num)'] = "homework/teacher_assign/new_assign/$1/$2";
$route['zuoye/assign/(:num)'] = "homework/teacher_assign/new_assign/$1";

$route['zuoye/packages/(:num)'] = "homework/teacher_homework/view_zuoye_package/$1";
$route['zuoye/wrongq/(:num)'] = "homework/teacher_report/wrong_q_report/$1";//试卷包错题报告
$route['zuoye/stu_record/(:num)'] = "homework/teacher_report/student_record/$1";//试卷包 作答记录


/*homework center and check class assigned homework*/
// $route['teacher/homework/class/(:num)']="homework/homework/check_class_homework/$1";
// $route['teacher/homework/review/(:num)']="homework/homework/review_homework/$1";
// $route['teacher/homework/class/(:num)']='exercise_plan/exercise_plan_controller/check_class/$1';
$route['teacher/homework/class/(:num)']='class/details/old_check_class/$1';
$route['ep/ep/ck/(:num)']='exercise_plan/exercise_plan_controller/check_class/$1';

//留作业  2.0
// $route['ep/ep/ck/(:num)'] = 'exercise_plan/exercise_plan_controller/check_class/$1';
$route['ep/epi/(:any)'] = 'exercise_plan/exercise_plan_intelligent/$1';
$route['ep/ep/index'] = 'exercise_plan/exercise_plan_controller/index';

//查看某个学生的作业情况
$route['teacher/homework/stu/(:num)/(:num)'] = 'exercise_plan/exercise_plan_controller/check_person/$1/$2';
$route['ep/ep/ps/(:num)/(:num)'] = 'exercise_plan/exercise_plan_controller/check_person/$1/$2';

//老师查看一份作业的所有题目
$route['teacher/homework/review/(:num)'] = 'exercise_plan/exercise_plan_controller/new_review/$1';
$route['ep/ep/rv/(:num)'] = 'exercise_plan/exercise_plan_controller/review/$1';

$route['ep/ep/(:any)'] = 'exercise_plan/exercise_plan_controller/$1';

$route['ep/ep/ata'] = 'exercise_plan/exercise_plan_controller/about_to_assign';
$route['teacher/homework/pre_assign'] = 'exercise_plan/exercise_plan_controller/about_to_assign';
$route['teacher/homework/preview2'] = 'exercise_plan/exercise_plan_controller/preview';
$route['teacher/homework/preview2/(:num)'] = 'exercise_plan/exercise_plan_controller/preview/$1';

$route['ep/ep/ct'] = 'exercise_plan/exercise_plan_controller/choose_textbook';
