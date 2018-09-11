<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['secretKey'] = 'SrMSR7qyd_almToVp67ZnW8TXLu9_jXysfabUc4d';
$config['accessKey'] = 'C0F0FEpgVfdPbAtDhe_3tjQGEfjiR9UPqctRCfJt';
// $config['bucket'] = "videoonly"; //测试完视频上传就转回   newkongjian 
$config['bucket'] = "newkongjian"; //测试完视频上传就转回   newkongjian 

// $config['secretKey'] = 'pnlEL16sISdWjJWRAKv5UaJqrfcK38lHee7B09b4';
// $config['accessKey'] = 'Wng4lDFFmffmt5A8QUgMnF_Z603W-6d3v60dyzoW';
// $config['bucket'] = "tizi-test";
$config['domain'] = $config['bucket'].".qiniudn.com";
// $config['domain'] = "nkj-rs1.qiniudn.com";



//tizi_game
$config['tizi_game_bucket'] = "tizi-game";
$config['tizi_game_domain'] = $config['tizi_game_bucket'].".qiniudn.com";


/*教师认证bucket*/
$config['certification_bucket'] = 'cert';
$config['certification_domain'] = $config['certification_bucket'].".qiniudn.com";

/*download bucket*/
$config['aliyundownload_bucket'] = 'aliyundownload';
$config['aliyundownload_domain'] = $config['aliyundownload_bucket'].".qiniudn.com";

/* zhuangyuan bucket */
$config['zhuangyuan_bucket'] = 'http://tizi-dev.qiniudn.com';
$config['video_domain'] = 'qiniu-tdev';

/*tiantianwaijiao bulket*/
$config['fls_bucket'] = "tiantianwaijiao";//这是线上的；    测试的是 'tiantiantest';
$config['fls_domain'] = $config['fls_bucket'].".qiniudn.com";