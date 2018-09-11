#!/bin/sh
cd /space1/tizi/application/third_party/seo_script/
/usr/bin/php update_item_everyday.php >> ../log/credit_script_update_item_everyday_run.log 2>&1 &
