<?php
/*新网盘 我的资源库 */
$route['teacher/cloud'] = 'resource/resource/index';

$route['teacher/cloud/res']="resource/resource"; //原来是 res/
$route['teacher/cloud/res/(:any)']="resource/resource/$1";

//原来网盘中的文件预览转到res_file.php下
$route['cloud/cloud/file_detail/(:num)']="resource/res_file/file_detail/$1";
$route['teacher/cloud/share_detail/(:num)']="resource/res_file/share_detail/$1";
$route['cloud/cloud/(:any)']="resource/cloud/$1";



