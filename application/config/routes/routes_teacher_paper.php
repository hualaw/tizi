<?php

/*zujuan choose question*/
$route['teacher/paper/question']="paper/paper_question";
$route['teacher/paper/question/(:num)']="paper/paper_question/index/$1";
$route['teacher/paper/question/(:num)/(:num)']="paper/paper_question/index/$1/$2";

$route['teacher/paper/course']="paper/paper_question/course";
$route['teacher/paper/course/(:num)']="paper/paper_question/course/$1";
$route['teacher/paper/course/(:num)/(:num)']="paper/paper_question/course/$1/$2";

$route['teacher/paper/cequestion']="paper/paper_question/college_exam";
$route['teacher/paper/cequestion/(:num)']="paper/paper_question/college_exam/$1";
$route['teacher/paper/cequestion/(:num)/(:num)']="paper/paper_question/college_exam/$1/$2";

$route['teacher/paper/new']="paper/paper_question/reset";

/*zujuan choose myquestion*/
$route['teacher/paper/myquestion']="paper/paper_myquestion";
$route['teacher/paper/myquestion/(:num)']="paper/paper_myquestion/index/$1";
$route['teacher/paper/myquestion/(:num)/(:num)']="paper/paper_myquestion/index/$1/$2";

$route['teacher/paper/exam']="paper/paper_exam";
$route['teacher/paper/exam/question/(:num)']="paper/paper_exam/get_question/$1";
$route['teacher/paper/exam/(:num)']="paper/paper_exam/index/$1";
$route['teacher/paper/exam/(:num)/(:num)']="paper/paper_exam/index/$1/$2";

/*zujuan search question*/
$route['teacher/paper/search']="paper/paper_search";
$route['teacher/paper/search/(:num)']="paper/paper_search/index/$1";
$route['teacher/paper/search/(:num)/(:num)']="paper/paper_search/index/$1/$2";

/*zujuan intelligent choose question*/
$route['teacher/paper/intelligent']="paper/paper_intelligent";
$route['teacher/paper/intelligent/(:num)']="paper/paper_intelligent/index/$1";
$route['teacher/paper/intelligent/(:num)/(:num)']="paper/paper_intelligent/index/$1/$2";

/*zujuan paper preview*/
$route['teacher/paper/preview']="paper/paper_preview";
$route['teacher/paper/preview/(:num)']="paper/paper_preview/index/$1";

/*zujuan paper archive*/
$route['teacher/paper/center']="paper/paper_archive";
$route['teacher/paper/archive']="paper/paper_archive";
$route['teacher/paper/archive/(:num)']="paper/paper_archive/index/$1";
$route['teacher/paper/recover/(:num)']="paper/paper_archive/recover_paper/$1";

//feedback
$route['teacher/paper/feedback']="feedback/feedback/paper";
