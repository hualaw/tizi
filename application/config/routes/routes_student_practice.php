<?php

$route['practice/(:num)']="practice/practice/index/$1";
$route['practice/training/(:num)']="practice/practice_training/index/$1";
$route['practice/training/entry/(:num)']="practice/practice_training/entry/$1";
$route['practice/training/do/(:any)']="practice/practice_training/test/$1";
$route['practice/training/complete/(:any)']="practice/practice_training/complete/$1";

$route['practice/game/(:num)']="practice/game_center/index/$1";
$route['practice/game_question/(:num)']="practice/game_center/get_question/$1";
$route['practice/game/submit']="practice/game_center/submit";


$route['practice/record_sub']="practice/practice/record_sub";
