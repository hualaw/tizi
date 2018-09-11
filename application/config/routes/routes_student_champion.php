<?php
$route['zhuangyuan'] = 'champions/champions/index';
$route['zhuangyuan/praise'] = 'champions/champions/praise';
$route['zhuangyuan/appoint'] = 'champions/champions/appoint';
$route['zhuangyuan/(:num)'] = 'champions/champions/index/$1';
$route['zhuangyuan/video'] = 'champions/champions/video';
$route['zhuangyuan/video/(:num)'] = 'champions/champions/video/$1';
$route['zhuangyuan/video/(:num)/(:num)'] = 'champions/champions/video/$1/$2';