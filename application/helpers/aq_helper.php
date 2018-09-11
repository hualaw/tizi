<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('add_editor_css')) {
    function add_editor_css($str) {
        $pre = "<html><head><link href='".site_url()."application/views/static/js/tools/jmeditor/mathquill-0.9.1/mathquill.css' rel='stylesheet' type='text/css'/></head><body><div style='word-break:break-all;'>";
        $rear = "</div></body></html>";
        return $pre.$str.$rear;
    }   
}
