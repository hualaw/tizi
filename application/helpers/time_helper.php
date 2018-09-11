<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('convertToHoursMins')) {

    function convertToHoursMins($time, $format = '%s小时%s分钟') {
        settype($time, 'integer');
        if ($time < 0) {
            return;
        }
        $hours = floor($time/3600);
        $minutes = floor(($time%3600)/60);
        return sprintf($format, $hours, $minutes);
    }



}
if ( ! function_exists('convertToMinsSecs')) {

    function convertToMinsSecs($time, $format = '%s分钟%s秒') {
        settype($time, 'integer');
        if ($time < 0) {
            return;
        }
        $minutes = floor($time/60);
        $seconds = $time%60;

        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }

        return sprintf($format, $minutes, $seconds);
    }

}
if ( ! function_exists('convertToHoursMinsSecs')) {

    function convertToHoursMinsSecs($time, $format = '%s:%s:%s') {
        settype($time, 'integer');
        if ($time < 0) {
            return;
        }
        $hours = floor($time/3600);
        $minutes = floor(($time%3600)/60);
        $seconds = $time%3600%60;
        if($hours <10){
            $hours = '0' . $hours;
        }
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }
        return sprintf($format, $hours, $minutes,$seconds);
    }

}
