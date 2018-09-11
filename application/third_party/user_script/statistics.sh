#!/bin/sh
cd /space1/tizi/application/third_party/user_script/
/usr/bin/php statistics.php >> ../log/user_script_statistics_run.log 2>&1 &
