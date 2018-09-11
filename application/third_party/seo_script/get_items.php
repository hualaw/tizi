<?php
$env = "production";

define("ENVIRONMENT",$env);
include(dirname(__DIR__).'/base_script.php'); 
init('seo', $env);

require(dirname(dirname(__DIR__)).'/'.'libraries/Search.php');
$search = new Search();

$data = array();

$leaf_category = array();

$res = mysql_query("select * from `subject`");

while($arr = mysql_fetch_array($res)){
    
    $leaf_category = array();

    $category_id = $arr['id'];
    echo "get_items:".$category_id."\r\n";

    $items = get_items($category_id,'subject');

    $i = 0;
    foreach($items as $key=>$item){

        $i++;
        if(isset($item->childs)){
            $query_value  = "(".implode(" ",$item->childs).")";
        }else{
            $query_value  = $item->id;
        }
        list($lesson_num,$question_num) = get_doc_num($query_value);
        $item->lesson_num = $lesson_num;
        $item->question_num = $question_num;

        if($redis->lpush('all_subject_list_'.$category_id,$item->id)){
            echo "lpush all_subject_list_".$category_id."\r\n";
        }else{
            echo "lpush all_subject_list_".$category_id." faild"."\r\n";
        }

        if($lesson_num || $question_num){
            if($redis->lpush('subject_list_'.$category_id,$item->id)){
                echo "lpush subject_list_".$category_id."\r\n";
            }else{
                echo "lpush subject_list_".$category_id."\r\n";
            }
        }else{
            echo 'category:'.$category_id." question_num:0\r\n";
        }

        if(!$redis->exists($item->id)){
            $redis->hmset($item->id,(array)$item);
        }

    }

    echo "total_num:$i\r\n";

}

$node_group = array();

$res = mysql_query("select `id`,`depth` from `category` where `type` = 0 and `depth` = 1");
while($arr = mysql_fetch_array($res)){
    $node_group[] = $arr;   
}
$res = mysql_query("select node.* from `category` as node,`category` as parent
    where (node.lft between parent.lft and parent.rgt) and
    (node.depth <= 3) and parent.`depth` = 1  and parent.`type` = 0
    order by node.list_order desc, node.lft");

while($arr = mysql_fetch_array($res)){
    $node_group[] = $arr;   
}

//$res = mysql_query("select * from `category` where `depth` <=3  and (`type` = 0 or `type` is NULL) ");

//while($arr = mysql_fetch_array($res)){
//print_r($node_group);exit;
foreach($node_group as $arr){
    
    $leaf_category = array();

    $category_id = $arr['id'];
    $depth = $arr['depth'];
    echo "get_items:".$category_id."\r\n";
    if($depth == 1){
        
        $items = get_items($category_id,'version');

    }elseif($depth == 2){
    
        $items = get_items_by_grade($category_id);
        
    }elseif($depth == 3){
    
        $items = get_items_by_unit($category_id);
    
    }

    
    echo "category_id:$category_id";

    $i = 0;

    foreach($items as $key=>$item){

        $i++;
        if(isset($item->childs)){
            $query_value  = "(".implode(" ",$item->childs).")";
        }else{
            $query_value  = $item->id;
        }
        list($lesson_num,$question_num) = get_doc_num($query_value);
        $item->lesson_num = $lesson_num;
        $item->question_num = $question_num;

        $redis->lpush('all_list_'.$category_id,$item->id); 
        if($lesson_num || $question_num){
            $redis->lpush('list_'.$category_id,$item->id); 
        }
        if(!$redis->exists($item->id)){
            $redis->hmset($item->id,(array)$item);
        }
    }

    echo "total_num:$i\r\n";

}

function get_items($id,$type){

    $data = array();
    if($type == 'subject'){
        $versions = get_root_id($id);
    }elseif($type == 'version'){
        $version = new stdClass();
        $version->id = $id;
        $versions = array($version);
    }
    foreach($versions as $version){
        $version_id = $version->id;
        $grade_group = get_subtree_node($version_id);
        foreach($grade_group as $grade){
            $grade_id = $grade->id;
            $data = array_merge(get_items_by_grade($grade_id),$data);
        }
    }
    return $data;
}

function get_items_by_grade($grade_id){

    $data = array();
    $units = get_subtree_node($grade_id);
    foreach($units as $key=>$unit){
        if($unit->lft == $unit->rgt-1){
            $data[] = $unit;
            unset($units[$key]);
        }
    }
    foreach($units as $unit){
        $unit_id = $unit->id;
        $data = array_merge(get_items_by_unit($unit_id),$data);
    }
    return $data;
}

function get_items_by_unit($unit_id){

    global $leaf_category;

    $data = array();

    $result = get_subtree_node($unit_id);
    foreach($result as $val){

        $val->childs = get_child_node($val->id);
        $data[] = $val;
        /*
        if($val->lft == $val->rgt-1){
            $leaf_category[] = $val->id;
            $data[] = $val;
        }else{
            $d_result = get_subtree_node($val->id);
            foreach($d_result as $d_result_val){
                $d_result_val->p = $val->id;
                $d_result_val->name .= "({$val->name})"; 
                $leaf_category[] = $d_result_val->id;
                $data[] = $d_result_val;
            }
        }
         */
    }

    return $data;
}

function get_subtree_node($node_id){ $res = mysql_query(
        "select node.* from `category` as node,`category` as parent
        where (node.lft between parent.lft and parent.rgt) and 
        (node.depth = parent.depth + 1) and parent.id = $node_id 
        order by node.list_order desc, node.lft"
    );

    $data = array();

    while($arr = mysql_fetch_object($res)){
        $data[] = $arr;
    }
    return $data;

}

function get_root_id($subject_id)
{

    $res = mysql_query("select `id`,`name`,`lft`,`rgt` from `category` where `subject_id` = {$subject_id} and `depth` = 1 and (`type` = 0 or `type` is NULL)");
    $data = array();
    while($arr = mysql_fetch_object($res)){
        $data[] = $arr;
    }
    return $data;

}


function get_doc_num($category_id){

    echo "\r\ncategory_id:".$category_id."\r\n";
    
    global $search;

    $search_data = $search->init('seolesson')->search( array('category_id'=>$category_id,'doc_type_new'=>2));
    $lesson_num = $search_data['total'];

    $search_data = $search->init('seolesson')->search( array('category_id'=>$category_id,'doc_type_new'=>3));

    $question_num = $search_data['total'];

    echo "[category_id]".'question_num:'.$question_num." ".'lesson_num:'.$lesson_num."\r\n";
    return array($lesson_num,$question_num);


}

function get_child_node($category_id){
    
    $res = mysql_query("select node.id from `category` as node,`category` as parent 
         where (node.lft between parent.lft and parent.rgt) and parent.id = {$category_id}
         order by node.lft");

    $categories = array();
    while($arr = mysql_fetch_array($res)){
        $categories[] = $arr['id'];
    }
    return $categories;

}

