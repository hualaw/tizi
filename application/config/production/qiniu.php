<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['secretKey'] = 'pnlEL16sISdWjJWRAKv5UaJqrfcK38lHee7B09b4';
$config['accessKey'] = 'Wng4lDFFmffmt5A8QUgMnF_Z603W-6d3v60dyzoW';
$config['bucket'] = "production";
$config['domain'] = $config['bucket'].".qiniudn.com";
$config['domain'] = "cloud-rs1.tizi.com";

$config['certification_bucket'] = 'certification';
$config['certification_domain'] = $config['certification_bucket'].".qiniudn.com";

//tizi_game
$config['tizi_game_bucket'] = "tizi-game";
$config['tizi_game_domain'] = $config['tizi_game_bucket'].".qiniudn.com";

/* zhuangyuan bucket */
$config['zhuangyuan_bucket'] = 'http://tizi.qiniudn.com';
$config['video_domain'] = 'qiniu-tizi';

/*tiantianwaijiao bulket*/
$config['fls_bucket'] = 'tiantianwaijiao';
$config['fls_domain'] = $config['fls_bucket'].".qiniudn.com/";
