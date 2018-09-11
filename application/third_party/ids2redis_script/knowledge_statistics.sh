#!/bin/sh
cd /space1/tizi/application/third_party/ids2redis_script/
/usr/bin/php knowledge_statistics.php >> ../log/knowledge_statistics.log 2>&1 &
