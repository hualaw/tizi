<?php
/**
 * @date 2013-10-8
 * @description notice
 */
//online作业截止后老师获通知
$lang['notice_assign_teach'] = "(%s)同步作业已经截止，您可以去<a href='/teacher/homework/class/%d'>检查作业</a>。";
//offline作业截止后老师获通知
$lang['notice_offline_assign_teach'] = "(%s)同步作业已经截止。";
///布置作业后学生获通知 @deprecated
$lang['notice_assign_stu'] = "收到<a href='/student/homework/%d'>%s</a>电脑前作业(<a href='/student/homework/do/%d'>%s</a>)";


//布置作业后学生获通知,线下作业 @deprecated
$lang['notice_offline_assign_stu'] = "收到<a href='/student/homework/%d'>%s</a>非电脑前作业(<a href='/student/browse_homework/%d'>%s</a>)";
//学生提交作业后家长获通知
$lang['notice_assign_submit'] = "(<a href='/parents/homework/analyze/%d/%d'>%s</a>)作业%s已经完成,请<a href='/parents/homework/%d?subject_id=%d'>检查作业</a>";
//新的留作业:  $1:科目，$2:作业名
$lang['student_receive_exercise_plan'] = "收到%s作业(<a href='/student/homework/do/%d'>%s</a>)";
$lang['parent_receive_exercise_plan'] = "%s收到%s作业(<a href='/student/homework/do/%d'>%s</a>)";


//老师移除学生动作该学生获取的消息
$lang['notice_shot_student']  = "你被老师从%s%s级%s中移除";	
//老师移除学生动作该学生的家长和老师获取的消息
$lang['notice_shot_student2'] = "%s被老师从%s%s级%s中移除";	

//学生退出班级该学生收到的消息
$lang['notice_exit_student']  = "你退出了%s%s级%s";	
//学生退出班级老师和家长收到的消息
$lang['notice_exit_student2'] = "%s%s级%s%s退出了班级";	


