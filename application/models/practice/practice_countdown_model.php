<?php
/**
 * @author saeed
 * @date   2013-10-16
 * @description 专项练习 - (小学数学)
 */
class Practice_Countdown_Model extends MY_Model{

    private $_operators = array('+','-');
    private $_redis;
    function __construct()
    {
        parent::__construct();
        $this->load->model('practice/practice_model');
    }
    
    /**
     * @info 获取问题(100以内加减法)
     * @param num 获取问题数量
     * @practice_list : json , array(array('question'=>'12+2','answer'=>'14','option'=>array()))
     */
    public function get_question_type1($uid,$p_c_id,$num){
        $addend_group = array();
        $augend_group = array();
        $current_data = array();
        $serialize_group = array();


        $serialize_group = $this->get_pre_question();
        if(count($serialize_group)>9000){
            return array();
        }
        /*
        for($a=0;$a<100;$a++){
            for($b=0;$b<100;$b++){
                if(!in_array($a.','.$b,$current_data)){
                    $all_group[] =  $a.",".$b;
                }
            }
        }
        if(!(count($all_group)<$num)){
            array_rand($all_group,$num);
        }
         */
        for($i=0;$i<100;$i++){
            $addend_group[] = $i;
            $augend_group[] = $i;
        }
        for($m=0;$m<$num;$m++){
            $option = array();
            getrand:{
                $addend = rand(0,99);
                $augend = rand(0,99);
                $operator = rand(0,1);
            }
            //echo $addend."|".$augend;
            if($operator == 1){
                if($addend < $augend){
                    list($addend,$augend) = array($augend,$addend);
                }
            }
            //echo '['.$addend.'|'.$augend.']';
            //echo '|'.$operator;
            $serialize_str = $addend.$this->_operators[$operator].$augend;
            if(in_array($serialize_str,$serialize_group)){
                goto getrand;
            }
            $sum = $operator == 0 ? ($addend + $augend):($addend - $augend);
            //echo '|'.$sum;
            $serialize_group[] = $serialize_str;
            if($sum>10){
                $min_sum = $sum-10;
                $max_sum = $sum+10;
            }else{
                $min_sum = 0;
                $max_sum = 20;
            }
            for($i=$min_sum;$i<$max_sum+1;$i++){
                if($i != $sum){
                    $option_group[] = $i;
                }
            }
            $option_keys = array_rand($option_group,3);
            foreach($option_keys as $val){
                $option[] = $option_group[$val];
            }
            $option[] = $sum;
            $option = array_unique($option);
            if(count($option)<4){
                $option[] = rand($max_sum+1,$max_sum+10);
            }
            shuffle($option);
            $question[] = array(
                'question'=>$addend.$this->_operators[$operator].$augend,
                'answer'=>$sum,
                'option'=>$option,
            );
        }

        return $question;
    }
    /**
     * @info 获取问题(有余数的除法)
     * @param num 获取问题数量
     */
    public function get_question_type2($uid,$p_c_id,$num){

        $question = array();
        $divisor_group = array();
        $dividend_option_group = array();
        $current = array();
        $option_group = array();
        $serialize_group = $this->get_pre_question();
        if(count($serialize_group)>31){
            return array();
        }
        /*method 1*/
        for($a=2;$a<20;$a++){
            for($b=1;$b<$a;$b++){
                if($a%$b == 0){ 
                    if(!in_array($a.'÷'.$b,$serialize_group)){
                        $dividend_group[] = $a.','.$b;
                    }
                }
            }
        }
        $dividend_group_keys = array_rand($dividend_group,$num);

        foreach($dividend_group_keys as $dividend_group_key){
            $option = array();
            $dividend_str = $dividend_group[$dividend_group_key];
            list($divisor,$dividend) = explode(",",$dividend_str);
            $sum = $divisor/$dividend;
            
            if($sum>3){
                $min_sum = $sum-3;
                $max_sum = $sum+3;
            }else{
                $min_sum = 1;
                $max_sum = 7;
            }
            for($i=$min_sum;$i<$max_sum+1;$i++){
                if($i != $sum){
                    $option_group[] = $i;
                }
            }
            $option_keys = array_rand($option_group,3);//取三个选项
            foreach($option_keys as $key){
                $option[] = $option_group[$key];
            }
            $option[] = $sum;
            shuffle($option);
            $question[] = array(
                'question'=>$divisor.'÷'.$dividend,
                'answer'=>$sum,
                'option'=>$option,
            );
        }
        /* method 2
        for($i=2;$i<20;$i++){
            $divisor_group[] = $i;
            $dividend_option_group[] = $i;
        }
        for($m = 0;$m<$num;$m++){
            $order = rand(0,count($divisor_group)-1);
            $divisor = $divisor_group[$order];
            $option = array();
            $dividend_group = array();
            for($i=1;$i<$divisor;$i++){
                if($divisor%$i == 0){ 
                    $dividend_group[] = $i;
                }
            }
            $dividend = $dividend_group[rand(0,count($dividend_group)-1)];
            foreach($dividend_option_group as $key=>$val){
                if($val==$divisor){
                    unset($dividend_option_group[$key]);
                    break;
                }
            }
            $option_keys = array_rand($dividend_option_group,3);
            foreach($option_keys as $key){
                $option[] = $dividend_option_group[$key];
            }
            $question[] = array(
                'divisor'=>$divisor,
                'dividend'=>$dividend,
                'answer'=>$divisor+$dividend,
                'option'=>$option,
            );
            unset($divisor_group[$order]);
            $divisor_group = array_values($divisor_group);
        }
         */
        return $question;
    }

    /**
     * @info 获取问题(三位数乘两位数)
     * @param num 获取问题数量
     */
    public function get_question_type3($uid,$p_c_id,$num){

        $multiplier_group = array();
        $multiplicand_group[] = array();
        $question = array();
        $serialize_group = $this->get_pre_question();
        if(count($serialize_group)>99500){
            return array();
        }
        for($m=0;$m<$num;$m++){
            $diff = '';
            $option = array();
            $option_group = array();
            getrand:{
                $multiplier = rand(1,999);
                $multiplicand = rand(1,99);
            }
            if(in_array($multiplier.'*'.$multiplicand,$serialize_group)){
                goto getrand;
            }
            $serialize_group[] = $multiplier.'*'.$multiplicand;
            $sum = $multiplier*$multiplicand;
            $max_sum = pow(10,strlen($sum-1))+10;

            $min_sum = $sum>10 ?pow(10,strlen($sum-1)-1)-10:pow(10,strlen($sum));
            $diff = $max_sum-$min_sum+1;
            if($diff >200){
                while(count($option_group)<4){
                    $val = rand(0,$diff);
                    if(in_array($val,$option_group)){
                        continue;
                    }else{
                        $option_group[] = $val;
                    }
                }
            }else{
                for($i=0;$i<$diff;$i++){
                    $option_group[] = $i;
                }
            }
            $option_keys = array_rand($option_group,3);
            foreach($option_keys as $key){
                $option[] = $option_group[$key];
            }
            $option[] = $sum;
            shuffle($option);
            $question[] = array(
                'question'=>$multiplier.'*'.$multiplicand,
                'answer'=>$sum,
                'option'=>$option,
            );
            array_values($multiplicand_group);
            array_values($multiplier_group);
        }
        return $question;
    }
    private function get_pre_question(){
        $serialize_group = array();
        $this->_redis = $this->practice_model->connect_redis('practice');
        $pre_practice = $this->_redis->keys("countdown_".$this->uid."*");
        foreach($pre_practice as $pre_practice_val){
            $question_str = $this->_redis->hget($pre_practice_val,'question');
            $questions = json_decode($question_str,true);
            foreach($questions as $question){
                $serialize_group[] = $question['question'];       
            }
        }
        return $serialize_group;
    }

}
