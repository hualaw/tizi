#!/bin/sh
cd /space1/tizi/application/third_party/testpaper_script/
/usr/bin/php statistics.php >> ../log/testpaper_script_statistics_run.log 2>&1 &
