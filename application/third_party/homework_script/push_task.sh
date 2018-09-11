#!/bin/sh
cd /space1/tizi/application/third_party/homework_script/
/usr/bin/php push_task.php >> ../log/push_task_run.log 2>&1 &
