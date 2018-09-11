<?php
require_once dirname(__FILE__) . "/../question/question_intelligent.php";
/**
 *  智能选作业模块
 */
class Exercise_Plan_Intelligent_Model extends Question_Intelligent{
    public $_table = "exercise";
    public $_course = "course";
     
    public function __construct(){
        parent::__construct();      
    }

    public function getRandomDataRecords($randId, $qtype, $area, $big=true){
        $randWhere = $big ? " AND a.id>=" . $randId : " AND a.id<" . $randId;//获取题目id搜寻条件
        $where    = ' a.qtype_id =' . $qtype . " AND a.online = 1 AND b.{$this->_course}_id in (" . $this->_categorys . ')';
        if(count($this->_excludeQuestions) > 1){
            $where .= ' AND a.id NOT IN("'.implode('","',$this->_excludeQuestions).'")';
        }else if(count($this->_excludeQuestions) == 1){
            $where .= ' AND a.id <> '.$this->_excludeQuestions[0];
        }
        $field    = 'a.*, c.name, exercise_text.body as body_text,exercise_text.answer as answer_text,exercise_text.analysis as analysis_text ';
        $sql      = "SELECT " . $field
                    ." FROM `$this->_table` AS a LEFT JOIN `{$this->_table}_{$this->_course}` AS b ON a.id = b.question_id "
                    ." LEFT JOIN `{$this->_course}` AS c ON b.{$this->_course}_id = c.id "
                    ." LEFT JOIN `exercise_text` ON exercise_text.id = a.id "
                    ." WHERE " . $where;
        $unionSql = '';
        if($area['l'] == $area['r']){//难度区间仅有一个难度
            if($this->_result[$area['l']] > 0){
                $unionSql .= $sql . " AND a.level_id = " . $this->_difficultLevelIdArr[$area['l']] . $randWhere . " order by rand() limit " . $this->_result[$area['l']];
            }
        }else{
            $lLimit = $this->_result[$area['l']] + 1;//解决1条记录的偏移
            $rLimit = $this->_result[$area['l']] + 1;
            $rows = $lLimit+$rLimit;
            // $unionSql = $sql." AND a.level_id in (" . $this->_difficultLevelIdArr[$area['l']] .','.$this->_difficultLevelIdArr[$area['r']].") order by rand()";
            $unionSql = $sql." AND a.level_id in (1,2,3,4,5) order by rand()";
            //限制选题数量
            $unionSql .= " limit 10000";
        }
        $query  = $this->db->query($unionSql);
        $results  = $query->result_array();
        foreach($results as $result){
            $dif = $this->getKeyByLevelId($result["level_id"]);
            $result['qtype']      = $qtype;                             //增加题型id
            $result['level']      = $this->_difficultLevelArr[$dif];    //增加题型level 
            $this->_resultData[]  = $result;
            $this->_resultLevels[$dif]  += 1;
        }       
    }  

    public function getWiseRangeIds($qtype){
        $where = '';
        $qtype = intval($qtype);//过滤
        if(empty($qtype) || empty($this->_categorys)){//考察范围和题型不能为空
            return Constant::ERROR_WISE_MAXID_PARAM_EMPTY;
        }               
        
        $where .= ' a.qtype_id =' . $qtype . " AND b.{$this->_course}_id in (" . $this->_categorys . ')';
        $sql    = "SELECT min(a.id) as minWiseId,max(a.id) as maxWiseId FROM `{$this->_table}` AS a JOIN `{$this->_table}_{$this->_course}` AS b ON a.id = b.question_id WHERE " . $where;
        $query  = $this->db->query($sql);

        if($row = $query->row()){
            $maxWiseId = intval($row->maxWiseId);
            if($maxWiseId <= 0) return true;
            $minWiseId = intval($row->minWiseId);
            return array('minWiseId'=>$minWiseId, "maxWiseId"=>$maxWiseId);
        }
        return true;
     }

     public function getResult($records){
        $firstArea       = $this->getFirstArea();                   //获取难度边界值不等的第一个难度区间
        $count = count($this->_resultData);
        if(!empty($this->_resultData)){
            foreach($this->_resultData as $val){    
                if($records>0){
                    $this->_resultsArr[] = $val;
                    $records --;
                }
            }    
        }
        // var_dump($this->_resultsArr);die;
    }

    // public function getAllResults(){   return $this->_resultsArr; }

    // public function getDataRecords($records, $randId, $qtype){
    //     $difficultCounts = $records;         //分配难度区间的初始化数量
    //     $this->initResult();                //初始化结果数组
    //     foreach($this->_difficultAreas as $area){//生成数据 
    //         if($difficultCounts <= 0){ break; }
    //         $this->getAreaRecords($area['l'], $area['r'], $difficultCounts);//分配该难度区间边界难度值应获取的记录数
    //         $this->getRandomDataRecords($randId, $qtype, $area);//选取比随机id大的记录数组
    //         if($this->_resultLevels[$area['l']] < $this->_result[$area['l']] || $this->_resultLevels[$area['r']] < $this->_result[$area['r']]){
    //             $this->getRandomDataRecords($randId, $qtype, $area, false); //若数据没有取满则选取比随机id小的记录数组
    //         }
            
    //         if($this->filterDataRecords($area) === true){                   //数量已经取满
    //             break;
    //         }else{  //扣除已经获取的合法数量后进入下一难度区间
    //             $difficultCounts -= $this->_resultLevels[$area['l']];//扣除该难度区域左边界难度值已经获取的合法数量
    //             if($area['l'] != $area['r']){
    //                 $difficultCounts -= $this->_resultLevels[$area['r']];   //若难度区域左右边界值不等则扣除该难度区域右边界难度值已经获取的合法数量
    //             }
    //         }
    //     }
    //     $this->getResult($records);         
    // }
    
    // private function getKeyByLevelId($id){ return array_search($id, $this->_difficultLevelIdArr); }
    // private function getFirstArea(){
    //     $firstArea = array();
    //     if($this->_totalDifficult!=$this->_difficultArr[0] && $this->_totalDifficult!=end($this->_difficultArr)){
    //         $firstArea = $this->_difficultAreas[0];
    //         if($firstArea['l'] == $firstArea['r'] ){
    //             $firstArea = $this->_difficultAreas[1];
    //         }
    //     }
    //     return $firstArea;
    //  }
    // private function filterDataRecords($area){
    //     if($this->_resultLevels[$area['l']]>=$this->_result[$area['l']] && $this->_resultLevels[$area['r']]>=$this->_result[$area['r']]){//取满数据
    //         $this->_resultLevels[$area['l']] = $this->_result[$area['l']];
    //         $this->_resultLevels[$area['r']] = $this->_result[$area['r']];
    //         return true;
    //     }else{
    //         $rtemp      = intval(ceil($this->getScale($area) * $this->_resultLevels[$area['l']]));
    //         if($rtemp > $this->_resultLevels[$area['r']]){                        //按区域右边界难度值数量计算该难度区域合法数量
    //             $temp   = intval(ceil($this->_resultLevels[$area['r']] / $this->getScale($area)));
    //             $this->_resultLevels[$area['l']] = $temp;
    //         }else if($rtemp < $this->_resultLevels[$area['r']]){                  //按区域左边界难度值数量计算该难度区域合法数量
    //             $this->_resultLevels[$area['r']] = $rtemp ;
    //         }
    //         return false;
    //     }
    //  }
    // private  function getScale($area){
    //     if($this->_difficultArr[$area['r']] != $this->_difficultArr[$area['l']]){
    //         return ($this->_totalDifficult - $this->_difficultArr[$area['l']]) / ($this->_difficultArr[$area['r']] - $this->_totalDifficult);
    //     }else{          return 1;    }
    // }
    
    // private function getAreaRecords($start, $end, $counts){
    //     $difficultArr   = $this->_difficultArr;
    //     if($difficultArr[$end] != $difficultArr[$start]){//保证值偏小,直线近似计算
    //         $temp = intval(ceil(($difficultArr[$end] - $this->_totalDifficult) * $counts / ($difficultArr[$end] - $difficultArr[$start])));
    //         $this->_result[$start]  = $temp;        
    //         $this->_result[$end]    = $counts - $temp;  
    //     }else{//难度区间只有一个难度值
    //         $this->_result[$start]  = $counts;
    //     }
    //  }
    // private function initResult(){
    //     $this->_resultData = array();  //每个题型都初始化从数据库获取的结果数据数组为空数组 
    //     foreach($this->_difficultArr as $key=>$val){
    //         $this->_result[$key]       = 0;
    //         $this->_resultLevels[$key] = 0;
    //     }   
    // }

    

    // public function init($totalDifficult, $categorys, $questionLevels = array()){       
    //     $this->_resultsArr = array();                             //初始化最终结果数据数组为空数组 
    //     $this->_total      = array('nums'=>0, 'difficult'=>0);    //初始化统计信息数值都为0
    //     $this->setQuestionLevels($questionLevels);                //设置题目所有难度的相关数组

    //     if($totalDifficult < $this->_difficultArr[0]){           //小于最小难度值则取最小难度值
    //         $totalDifficult    = $this->_difficultArr[0];
    //     }else if($totalDifficult > end($this->_difficultArr)){   //大于最大难度值则取最大难度值
    //         $totalDifficult    = end($this->_difficultArr);
    //     }       
    //     $this->_totalDifficult = $totalDifficult;                //设置总体计划难度     
        
    //     $currentKey = $this->getDifficultKey();
    //     $l          = $currentKey;      
    //     $r          = in_array($totalDifficult, $this->_difficultArr) ? $currentKey : ($currentKey+1);//刚好为难度区域边界值则右边界和左边相等，否则取最近的难度区域  
    //     while($l >= 0 && $r < count($this->_difficultArr)){      //碰到边界即停止
    //         $this->_difficultAreas[] = array("l"=>$l, "r"=>$r);  //设置总体计划难度对应的难度区域
    //         $l--;
    //         $r++;           
    //     }       
        
    //     if(is_array($categorys)){//转化非空数组为按,分割的字符串
    //         $categorys = array_map(function ($var) {return intval($var);}, $categorys);//过滤
    //         $categorys = implode(',', array_unique($categorys)); //设置选取范围           
    //     }
    //     $this->_categorys = $categorys;     
    // }


    // private function setQuestionLevels($questionLevels = array()){
    //     if(!empty($questionLevels)){                              //参数非空则设置难度数组
    //         $countLevels = count($questionLevels);
    //         $this->_difficultArr = $this->_difficultLevelArr = $this->_difficultLevelIdArr = array();
    //         foreach($questionLevels as $levels){
    //             $this->_difficultArr[]        = $levels->level / $countLevels;
    //             $this->_difficultLevelArr[]   = $levels->level;
    //             $this->_difficultLevelIdArr[] = $levels->id;
    //         }
    //     }
    // }
    // private function getDifficultKey(){
    //     foreach($this->_difficultArr as $key=>$val){
    //         if($this->_totalDifficult < $val){
    //             return $key-1;
    //         }else if($this->_totalDifficult == $val){
    //             return $key;
    //         }
    //     }
    //  }
}