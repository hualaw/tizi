#!/bin/sh
cd /space1/tizi/application/third_party/ids2redis_script/
/usr/bin/php ids_into_redis.php >> ../log/ids_into_redis_run.log 2>&1 &
