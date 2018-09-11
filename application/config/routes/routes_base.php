<?php
$route['forgot']='login/redirect/forgot';
$route['register']='login/redirect/register';
$route['gk_shuati'] = 'login/redirect/gk_shuati';

/*zujuan about*/
$route['about/school/download']='login/about/download_school';
$route['about/newsdetails/(:num)'] = "login/about/newsdetails/$1";
$route['about/newslist/(:num)/(:num)'] = "login/about/newslist/$1/$2";
$route['about/report'] = "login/about/report";
$route['about/report/(:num)'] = "login/about/report/$1";
$route['about/witness'] = "login/about/witness";
$route['about/join'] = "login/about/join";
$route['about/links'] = "login/about/links";
$route['about/advisor'] = "login/about/advisor";
$route['about/(:any)']='login/about/show_about/$1';

$route['sitemap.html']='login/about/sitemap';
$route['link.html']='login/about/link';

//upload
$route['upload/csxls'] = 'class/create_students/xls';
$route['upload/aqask'] = 'student/aq_ask/upload';
$route['upload/udoc'] = 'user/user_document/file_upload';
$route['upload/uques'] = 'user/user_question/word_img_upload';
$route['upload/taqask'] = 'aq_teacher/aq_teacher/upload';
$route['upload/feedback'] = 'feedback/feedback/upload';//题目纠错
$route['upload/cloud'] = 'resource/cloud/upload';//网盘上传文件

//download
$route['download/paper'] = 'paper/download/force_download';
$route['download/doc'] = 'lesson/lesson_document/download';
$route['download/udoc'] = 'user/user_document/download';
$route['download/slist'] = 'class/create_students/dl';

//invite
$route['invite/(:any)'] = "class/invite/index/$1";

//feedback
$route['send_feedback'] = 'feedback/feedback/send_feedback';

