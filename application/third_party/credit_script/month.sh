#!/bin/sh
cd /space1/tizi/application/third_party/credit_script/
/usr/bin/php month.php >> ../log/credit_script_month_run.log 2>&1 &
