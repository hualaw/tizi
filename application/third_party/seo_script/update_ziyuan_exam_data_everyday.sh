#!/bin/sh
cd /space1/tizi/application/third_party/seo_script/
/usr/bin/php update_ziyuan_exam_data_everyday.php >> ../log/credit_script_update_ziyuan_exam_data_everyday_run.log 2>&1 &
