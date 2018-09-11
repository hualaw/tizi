<?php
/* cloud */
$route['teacher/cloud/index/(:num)'] = 'resource/cloud/index/$1';
$route['teacher/cloud/other'] = 'resource/cloud/index';
$route['teacher/cloud/(:any)'] = 'resource/cloud/$1';
$route['teacher/cloud/download_verify'] = 'resource/cloud_base/download_verify';

/*user document*/
$route['teacher/user/mydocument'] = "user/user_document/index";
$route['teacher/user/mydocument/(:num)'] = "user/user_document/index/$1";
$route['teacher/user/mydocument/perfect'] = "user/user_document/file_perfect";
$route['teacher/user/mydocument/edit/(:num)'] = "user/user_document/file_edit/$1";
$route['teacher/user/mydocument/upload'] = "user/user_document/file_upload";
/*user question group*/
$route['teacher/user/myquestion/g'] = "user/user_question/myquestion_index";
$route['teacher/user/myquestion/g/(:num)'] = "user/user_question/myquestion_index/$1";
/*user question*/
$route['teacher/user/myquestion'] = "user/user_question/index";
$route['teacher/user/myquestion/(:num)'] = "user/user_question/index/$1";
$route['teacher/user/myquestion/edit/(:num)'] = "user/user_question/edit_question/$1";
$route['teacher/user/myquestion/new'] = "user/user_question/new_question";
/*zujuan lesson prepare*/
$route['teacher/lesson/prepare']="lesson/lesson_prepare";
$route['teacher/lesson/prepare/(:num)']="lesson/lesson_prepare/index/$1";
$route['teacher/lesson/prepare/(:num)/(:num)']="lesson/lesson_prepare/index/$1/$2";
$route['teacher/lesson/view/(:any)']="lesson/lesson_prepare/preview/$1";
$route['teacher/lesson/search/(:num)']="lesson/lesson_prepare/search_index/$1";
$route['teacher/lesson/mydocument'] = "user/user_document/my_document_index";
$route['teacher/lesson/mydocument/(:num)'] = "user/user_document/my_document_index/$1";
$route['teacher/lesson/mydocument/view/(:any)']="user/user_document/preview/$1";
$route['teacher/lesson/search']="lesson/lesson_prepare/search_index";
/*new lesson_cloud*/
$route['teacher/lesson/upload']="lesson/lesson_cloud/file_upload_index";
$route['teacher/lesson/upload/(:num)/(:num)/(:num)']="lesson/lesson_cloud/file_upload_index/$1/$2/$3";
$route['teacher/lesson/upload/(:num)/(:num)']="lesson/lesson_cloud/file_upload_index/$1/$2";
$route['lesson/check_api']="lesson/lesson_check_api/check";
//我的文件
$route['teacher/lesson/prepare/mine'] = "lesson/lesson_prep_my/mine";
$route['teacher/lesson/prepare/mine/(:num)'] = "lesson/lesson_prep_my/mine/$1";
$route['teacher/lesson/prepare/mine/(:num)/(:num)'] = "lesson/lesson_prep_my/mine/$1/$2";
$route['teacher/lesson/file_detail/(:num)']="lesson/lesson_prep_my/file_detail/$1";
//我的收藏
$route['teacher/lesson/prepare/fav'] = "lesson/less_my_fav/mine";
$route['teacher/lesson/prepare/fav/(:num)'] = "lesson/less_my_fav/mine/$1";
$route['teacher/lesson/prepare/fav/(:num)/(:num)'] = "lesson/less_my_fav/mine/$1/$2";