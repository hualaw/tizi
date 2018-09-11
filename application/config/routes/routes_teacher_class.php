<?php

/*zujuan class manage*/
$route['teacher/class/my']="class/my";
$route['teacher/class/create']="class/create";
$route['teacher/class/applies/(:num)'] = "class/iapply/index/$1";
$route['teacher/class/(:any)']="class/details/index/$1";
$route['teacher/class/(:any)/(:any)']="class/details/index/$1/$2";
$route['teacher/class/(:any)/(:any)/(:any)']="class/details/index/$1/$2/$3";



$route['teacher/class/homework']="class/teacher_homework/index";