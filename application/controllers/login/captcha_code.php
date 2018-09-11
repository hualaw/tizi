<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once LIBPATH."controllers/tizi_captcha.php";

class Captcha_Code extends Tizi_Captcha {

    public function __construct()
    {
        parent::__construct();
    }

    // protected function captcha_rule($captcha_name)
    // {
    //     $need_check=true;

    //     //download paper
    //     if($captcha_name=='PaperDownBox'&&$this->redis_model->connect('download'))
    //     {
    //         $need_check=false;
    //         $paper_key=date('Y-m-d').'_paper_'.$this->tizi_uid;
    //         $download_paper_count=$this->cache->get($paper_key);
    //         if($download_paper_count>1&&strpos((string)sqrt($download_paper_count),'.')===false) $need_check=true;
    //     }
    //     //download homework
    //     if($captcha_name=='HomeworkDownBox'&&$this->redis_model->connect('download'))
    //     {
    //         $need_check=false;
    //         $paper_key=date('Y-m-d').'_homework_'.$this->tizi_uid;
    //         $download_paper_count=$this->cache->get($paper_key);
    //         if($download_paper_count>1&&strpos((string)sqrt($download_paper_count),'.')===false) $need_check=true;
    //     }
    //     //lesson doc down
    //     if($captcha_name=='DocDownBox'&&$this->redis_model->connect('download'))
    //     {
    //         $need_check=false;
    //         $doc_key=date('Y-m-d').'_lesson_doc_key_'.$this->tizi_uid;
    //         $download_doc_count=$this->cache->get($doc_key);
    //         if($download_doc_count>1&&strpos((string)sqrt($download_doc_count),'.')===false) $need_check=true;
    //     }

    //     return $need_check;
    // }
}
