<?php
/**
 * @author saeed
 * @date   2013-11-13
 * @description 专项练习 - 获取问题 
 */
class Practice_Questions_Model extends MY_Model{

    private $_operators = array('+','-');
    private $_redis;
    private $p_c_id;
    private $pre_pid_group = array();
    private $game_option_num;

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


        $serialize_group = $this->get_pre_question_1();
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
                $addend = rand(1,99);
                $augend = rand(1,99);
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
            $option_group = array();
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
        $serialize_group = $this->get_pre_question_1();
        if(count($serialize_group)>31){
            return array();
        }
        /*method 1*/
        $redis = $this->practice_model->connect_redis('practice');
        $dividend_group = array();
        if($redis&&$redis->exists('division_question')){
            $dividend_group = json_decode($redis->get('division_question'),true);
        }
        if(empty($dividend_group)){
            for($a=2;$a<200;$a++){
                for($b=3;$b<$a;$b++){
                    if($a%$b == 0){ 
                        if(!in_array($a.'÷'.$b,$serialize_group)){
                            $dividend_group[] = $a.','.$b;
                        }
                    }
                }
            }
            $redis->set('division_question',json_encode($dividend_group));
        }
        shuffle($dividend_group);
        $dividend_group_keys = array_rand($dividend_group,$num);
        foreach($dividend_group_keys as $dividend_group_key){
            $option = array();
            $option_group = array();
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
            $option_keys = array_rand($option_group,2);//取三个选项
            foreach($option_keys as $key){
                $option[] = $option_group[$key];
            }
            $option[] = $sum;
            shuffle($option);
            $question[] = array(
                'question'=>$divisor.' ÷ '.$dividend." = ?",
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
        $serialize_group = $this->get_pre_question_1();
        if(count($serialize_group)>99500){
            return array();
        }
        for($m=0;$m<$num;$m++){
            $diff = '';
            $option = array();
            $option_group = array();
            getrand:{
                $multiplier = rand(100,999);
                $multiplicand = rand(10,99);
            }
            if(in_array($multiplier.'*'.$multiplicand,$serialize_group)){
                goto getrand;
            }
            $serialize_group[] = $multiplier.'*'.$multiplicand;
            $sum = $multiplier*$multiplicand;
            $last_char = $sum%10;
            $sum_str = (string)$sum;
            $first_char = $sum_str[0];

            $max_sum = pow(10,strlen($sum)-2);
            $min_sum = pow(10,strlen($sum)-3);
            $diff = $max_sum-$min_sum+1;
            if($diff >200){
                while(count($option)<2){//两个选项
                    $val = rand($min_sum,$max_sum);
                    if(in_array($first_char.$val.$last_char,$option) || $first_char.$val.$last_char == $sum){
                        continue;
                    }else{
                        $option[] = intval($first_char.$val.$last_char);
                    }
                }
            }else{
                for($i=pow(10,strlen($diff)-1);$i<$diff;$i++){
                    $option_group[] = $i;
                }
                $option_keys = array_rand($option_group,2);
                foreach($option_keys as $key){
                    $option[] = (int)($first_char.$option_group[$key].$last_char);
                }
            }
            $option[] = $sum;
            shuffle($option);
            $question[] = array(
                'question'=>$multiplier.' x '.$multiplicand.' = ? ',
                'answer'=>$sum,
                'option'=>$option,
            );
            array_values($multiplicand_group);
            array_values($multiplier_group);
        }
        return $question;
    }
    
    

     /**
     * @info 获取问题 单关游戏调用
     */
    public function get_question_type4($uid,$p_c_id,$num,$option_num = 3,$type=0){
        $this->_get_option_num($p_c_id);
        $option_num = $option_num - 1;
        $pre_practice_group = array();
        $question = array();
        $option = array();
        //$pre_practice = $this->get_pre_question_2();
        $this->p_c_id = $p_c_id;
        $resources = $this->practice_model->get_resources($p_c_id,$type);
        if($resources){
            $f_r = $resources[0]['other_options'];
        }else{
            return array();
        }
        if(!$f_r){
            foreach($resources as $key=>$resources_val){
                $answer_group[$key] = $resources_val['answer'];
                if(!in_array($resources_val['id'],$this->pre_pid_group)){
                    $resource_group[] = $key;
                }
            }
            if(count($resource_group)<10){
                return array();
            }
            if($num !=''){
                if(count($resource_group) < $num) $num = count($resource_group);
                $resource_keys = array_rand($resource_group,$num);
                foreach($resource_keys as $resource_key){
                    $key = $resource_group[$resource_key];
                    $new_resouce_group[] = $key;
                }
                $resource_group = $new_resouce_group;
            }else{
                $resource_group = array_keys($resource_group);
            }
            shuffle($resource_group);
            foreach($resource_group as $key){
                $option = array();
                $current_answer_group = array_values(array_unique(array_diff($answer_group,array($answer_group[$key]))));
                $answer_key = array_rand($current_answer_group,$option_num);
                foreach($answer_key as $answer_key_val){
                    $option[] = trim($current_answer_group[$answer_key_val]); 
                }
                $option[] = trim($resources[$key]['answer']);
                shuffle($option);
                $question[] = array(
                    'question'=>$resources[$key]['question'],
                    'answer'=>trim($resources[$key]['answer']),
                    'option'=>$option,
                );
            }
        }else{
            $question = $this->_resourceProcessing($resources,$num);
        }
        return $question;

    }
   
    /**
     * @info 获取问题
     */
    public function get_question_type5($uid,$p_c_id,$num,$other_option_num=2){
        $this->_get_option_num($p_c_id);
        $option_num = $other_option_num;
        $pre_practice_group = array();
        $question = array();
        $option = array();
        $this->p_c_id = $p_c_id;
        $resources = $this->practice_model->get_resources($p_c_id);

        if($resources){
            $f_r = $resources[0]['other_options'];
        }else{
            return array();
        }
        
        if(!$f_r){
            foreach($resources as $key=>$resources_val){
                $a_key = $this->_get_key($resources_val['question']);
                $answer_group[$a_key][] = $resources_val['answer'];
                if(!in_array($resources_val['id'],$this->pre_pid_group)){
                    $resource_group[] = $key;
                }
            }
            if($num !=''){
                $resource_keys = array_rand($resource_group,$num);
                foreach($resource_keys as $resource_key){
                    $key = $resource_group[$resource_key];
                    $new_resouce_group[] = $key;
                }
                $resource_group = $new_resouce_group;
            }else{
                shuffle($resource_group);
                $resource_group = array_keys($resource_group);
            }
            foreach($resource_group as $key){
                $option = array();
                $temp_answer_group = $answer_group;
                $a_key = $this->_get_key($resources[$key]['question']);
                unset($temp_answer_group[$a_key]);
                $answers = array();
                foreach($temp_answer_group as $temp_answer_group_val){
                    $answers = array_merge($answers,$temp_answer_group_val);  
                }
                $answers_keys = array_rand($answers,$other_option_num);
                foreach($answers_keys as $answers_key){
                    $option[] = trim($answers[$answers_key]);
                }
                $option[] = trim($resources[$key]['answer']);
                shuffle($option);
                $question[] = array(
                    'question'=>$resources[$key]['question'],
                    'answer'=>trim($resources[$key]['answer']),
                    'option'=>$option,
                );

            }
        }else{

            $question = $this->_resourceProcessing($resources,$num);

        }

        return $question;
    }

    //获取问题(从库获取,20以内有余数除法)
    public function get_question_type6($uid,$p_c_id,$num){
        $pre_practice_group = array();
        $question = array();
        $option = array();
        //$pre_practice = $this->get_pre_question_2();
        $this->p_c_id = $p_c_id;
        $resources = $this->practice_model->get_resources($p_c_id);
        foreach($resources as $key=>$resources_val){
            if(!in_array($resources_val['id'],$this->pre_pid_group)){
                $resource_group[] = $key;
            }
        }
        if(count($resource_group)<10){
            return array();
        }
        if($num !=''){
            $resource_keys = array_rand($resource_group,$num);
            foreach($resource_keys as $resource_key){
                $key = $resource_group[$resource_key];
                $new_resouce_group[] = $key;
            }
            $resource_group = $new_resouce_group;
        }else{
            shuffle($resource_group);
            $resource_group = array_keys($resource_group);
        }
        foreach($resource_group as $key){
            $option = array();
            $rand_option = array();
            $option[] = $answer = (int)trim($resources[$key]['answer']);
            if($answer > 1){
                $rand_option = array_merge(array_merge(range(1,$answer-1)),$rand_option,range($answer+1,$answer+4));
            }else{
                $rand_option = array_merge($rand_option,range($answer+1,$answer+7));
            }
            shuffle($rand_option);
            $rand_keys = array_rand($rand_option,2);
            foreach($rand_keys as $rand_key){
                $option[] = $rand_option[$rand_key];
            }
            shuffle($option);
            $question[] = array(
                'question'=>$resources[$key]['question'] ." 的余数是 ?",
                'answer'=>$answer,
                'option'=>$option,
            );
        }
        return $question;
    }

    /**
     * @info 获取问题  3位数x2位数
     */
    public function get_question_type7($uid,$p_c_id,$num){
        $pre_practice_group = array();
        $question = array();
        $option = array();
        $this->p_c_id = $p_c_id;
        $resources = $this->practice_model->get_resources($p_c_id);
        foreach($resources as $key=>$resources_val){
            $a_key = $this->_get_key($resources_val['question']);
            $answer_group[$a_key][] = $resources_val['answer'];
            if(!in_array($resources_val['id'],$this->pre_pid_group)){
                $resource_group[] = $key;
            }
        }
        if($num !=''){
            $resource_keys = array_rand($resource_group,$num);
            foreach($resource_keys as $resource_key){
                $key = $resource_group[$resource_key];
                $new_resouce_group[] = $key;
            }
            $resource_group = $new_resouce_group;
        }else{
            shuffle($resource_group);
            $resource_group = array_keys($resource_group);
        }

        foreach($resource_group as $key){
            $option = array();
            $answer = (int)trim($resources[$key]['answer']);
            $max_sum = strlen($answer)<4?pow(10,strlen($answer)):$answer+500;
            $min_sum = strlen($answer)<4?pow(10,strlen($answer)-1):$answer-500;
            $diff = $max_sum-$min_sum+1;
            if($diff >200){
                while(count($option)<2){//两个选项
                    $val = rand($min_sum,$max_sum);
                    if(in_array($val,$option) || $val == $answer){
                        continue;
                    }else{
                        $option[] = $val;
                    }
                }
            }else{
                for($i=pow(10,strlen($diff)-1);$i<$diff+10;$i++){
                    $option_group[] = $i;
                }
                $option_keys = array_rand($option_group,2);
                foreach($option_keys as $option_key){
                    $option[] = $option_group[$option_key];
                }
            }
            $option[] = $answer;
            shuffle($option);
            $question[] = array(
                'question'=>$resources[$key]['question']." = ?",
                'answer'=>$answer,
                'option'=>$option,
            );
        }
        return $question;
    }

    /**
     * @info 获取问题(20以内加减法)
     * @param num 获取问题数量
     * @practice_list : json , array(array('question'=>'12+2','answer'=>'14','option'=>array()))
     */
    public function get_question_type8($uid,$p_c_id,$num=40){
        $addend_group = array();
        $augend_group = array();
        $current_data = array();
        $serialize_group = array();

        $minus_group = array();
        $plus_group = array();
        $serialize_group = $this->get_pre_question_1();
        for($a=1;$a<21;$a++){
            for($b=1;$b<21;$b++){
                if($a>$b){
                    $minus_group[] = $a.",".$b;
                }
                if(($a+$b)<21){
                    $plus_group[] =  $a.",".$b;
                }
            }
        }
        $half_num = $num/2;
        $minus_keys = array_rand($minus_group,$half_num);
        foreach($minus_keys as $minus_key){
            $t_v = explode(",",$minus_group[$minus_key]);
            $minus_array[] = $t_v[0]."-".$t_v[1];   
        }
        $plus_keys = array_rand($plus_group,$half_num);
        foreach($plus_keys as $plus_key){
            $t_v = explode(",",$plus_group[$plus_key]);
            $plus_array[] = $t_v[0]."+".$t_v[1];   
        }
        $question_group = array_merge($minus_array,$plus_array);
        shuffle($question_group);
        foreach($question_group as $question){
            $option = array();
            $left_group = array();
            $right_group = array();
            if(strpos($question,'+')){
                $operator = '+';
                list($fi_v,$tw_v) = explode("+",$question);
                $q_result = $fi_v+$tw_v;
            }else{
                $operator = '-';
                list($fi_v,$tw_v) = explode("-",$question);
                $q_result = $fi_v-$tw_v;
            }
            if($q_result>1){
                $left_group = range(1,$q_result-1);
                $right_group = range($q_result+1,$q_result+5);
                $option_group = array_merge($left_group,$right_group);
            }else{
                $option_group = range(2,10);
            }
            $option_keys = array_rand($option_group,3);
            foreach($option_keys as $val){
                $option[] = $option_group[$val];
            }
            $option[] = $q_result;
            shuffle($option);
            $new_question[] = array(
                'question'=>$fi_v.$operator.$tw_v,
                'answer'=>$q_result,
                'option'=>$option,
            );


        }

        return $new_question;
    }

    //获取问题
    public function get_question_type9($uid,$p_c_id,$num=30,$type=NULL){
    
        $new_question = array();       
        $resources = $this->practice_model->get_resources($p_c_id,$type);
        foreach($resources as $key=>$resources_val){
            if(!in_array($resources_val['id'],$this->pre_pid_group)){
                $resource_group[] = $key;
            }
        }

        $resource_keys = array_rand($resource_group,$num);
        shuffle($resource_keys);
        foreach($resource_keys as $resource_key){
            $q_key = $resource_group[$resource_key];
            $new_question[] = array(
                'question'=>$resources[$q_key]['question'],
                'answer'=>$resources[$q_key]['answer'],
            );
        }
        return $new_question;
    }

    public function get_question_type10($uid,$p_c_id,$num,$type){//$num为组数 , 3个一组
    
        $resources = $this->practice_model->get_resources($p_c_id,$type);

        foreach($resources as $key=>$resources_val){
            if(!in_array($resources_val['id'],$this->pre_pid_group)){
                $resource_group[trim($resources_val['answer'])][$resources_val['id']] = $resources_val;
            }
        }

        $al_use = array();
        $re_data = array();

        for($i = 0;$i<$num;$i++){

            $per_group = array();
            $gs = array();
            $temp_resources = array();

            while(count($per_group)<3){
                $all_keys = array_keys($resource_group);
                if(empty($gs)){
                    $g_id = mt_rand(0,count($resource_group)-1);
                    $current_key = $all_keys[$g_id];
                    $gs[] = $current_key;
                    $temp_resources = array_slice($resource_group,$g_id,1);
                    $temp_resources = $temp_resources[$current_key];
                }else{
                    $new_group = array_values(array_diff($all_keys,$gs));
                    $g_id = mt_rand(0,count($new_group)-1);
                    $current_key = $new_group[$g_id];
                    $gs[] = $current_key;
                    $temp_resources = $resource_group[$current_key];
                }
                if(!empty($al_use)){
                    foreach($al_use as $q_id){
                        if(isset($temp_resources[$q_id])){
                            unset($temp_resources[$q_id]);
                        }
                    }
                }
                if(!empty($temp_resources)){
                    $question_ids = array_keys($temp_resources);
                    $q_id = $question_ids[mt_rand(0,count($question_ids)-1)];
                    $al_use[] = $q_id;
                    $per_group[] = $temp_resources[$q_id];
                    if(empty($temp_resources)){
                        unset($resource_group[$current_key]);
                    }
                }
            }
            $re_data[] = $per_group;
        }
        return $re_data;
    }

    private function get_pre_question_1(){
        return array();
        $serialize_group = array();
        $this->_redis = $this->practice_model->connect_redis('practice');
        $pre_practice = $this->_redis->keys("game_".$this->p_c_id.'_'.$this->uid."*");
        foreach($pre_practice as $pre_practice_val){
            $question_str = $this->_redis->hget($pre_practice_val,'question');
            $questions = json_decode($question_str,true);
            foreach($questions as $question){
                $serialize_group[] = $question['question'];       
            }
        }
        return $serialize_group;
    }

    public function get_pre_question_2(){
        $serialize_group = array();
        $this->_redis = $this->practice_model->connect_redis('practice');
        $pre_practice = $this->_redis->keys("game_".$this->p_c_id.'_'.$this->uid."*");
        foreach($pre_practice as $pre_practice_val){

            $practice_info = $this->_redis->hgetall($pre_practice_val);
            if($practice_info['status']){
                $question_str = $practice_info['question'];
                $questions = json_decode($question_str,true);
                foreach($questions as $question){
                    $serialize_group[] = $question['question'];       
                    $this->pre_pid_group[] = $question['question']['id'];
                }
            }
        }
        return $serialize_group;
    }

    public function get_resources_num_by_id($p_c_id){
        $num = $this->db
            ->query("select count(*) as num from `practice_resources` where `p_c_id` = {$p_c_id}")
            ->row_array();
        return $num;
    }

    private function _get_key($key){
        $a_key = md5($key);
        $a_key = $a_key[2].$a_key[4].$a_key[6].$a_key[10].$a_key[16];
        return $a_key;
    }

    private function _resourceProcessing($resources,$num){

        $all_options = array();//用于其他选项不足
        if(count($resources)<$num) $num = count($resources);
        foreach($resources as $key=>$resources_val){
            $que = array();
            if(!in_array($resources_val['id'],$this->pre_pid_group)){
                $resource_group[] = $resources_val;
            }
        }
        if($num !=''){
            $resource_keys = array_rand($resource_group,$num);
            foreach($resource_keys as $resource_key){
                $new_resouce_group[] = $resource_group[$resource_key];
                $all_options = array_merge($all_options,
                    explode("|",$resource_group[$resource_key]['other_options']));
                array_push($all_options,$resource_group[$resource_key]['answer']);
            }
            $resource_group = $new_resouce_group;
        }
        array_walk($all_options, function($value, $key)use(&$all_options){
                                    if(empty($value)) unset($all_options[$key]);
                                }
        );
        $all_options = array_unique(array_values($all_options));
        shuffle($resource_group);
        foreach($resource_group as $resources_val){
            $que = array();
            $option = array();
            $que['question'] = $resources_val['question'];
            $que['answer'] = $resources_val['answer'];
            $option = explode("|",$resources_val['other_options']);
            $option = array_filter($option, function($val){
                if(trim($val) == '0' || !empty($val)) return true;
            });
            shuffle($option);
            if(count($option) < $this->game_option_num - 1){
                shuffle($all_options);
                $option = array_merge($option, array_slice(array_values(array_diff($all_options,
                    array_merge($option, array($resources_val['answer'])))), 
                0, $this->game_option_num-1-count($option)));
            }else{
                 $option = array_slice($option, 0, $this->game_option_num - 1);  
            }
            $option[] = $resources_val['answer'];
            shuffle($option);
            $que['option'] = $option;
            $question[] = $que;
        }
        return $question;

    }

    private function _get_option_num($p_c_id){

        $practice = $this->practice_model->get_category_info($p_c_id);
        $p_c_type = $practice['p_c_type'];
        $game_option_num = Constant::practice_option_num($p_c_type);
        $this->game_option_num = $game_option_num;

    }



}   
