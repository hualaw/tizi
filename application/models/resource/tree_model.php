<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tree_Model extends MY_Model {
    private $_table = 'cloud_user_directory';
    private $_redis=false;
    public function __construct(){
        parent::__construct();
    }

    //完整的目录结构
    function clean_sorted_cloud_tree($uid,$from_dir=0){
        $sql = "select dir_id,dir_name,depth,p_id from $this->_table where user_id=$uid and is_del=0 and cat_id is null and dir_id>=$from_dir order by dir_id desc";
        $res = $this->db->query($sql)->result_array();
        if(!isset($res[0])){
            return null;
        }
        $this->load->helper('array');
        //获取depth区间
        $max_depth = 0;
        foreach($res as $dps => $d){
            if($d['depth']>$max_depth){
                $max_depth = $d['depth'];
            }
        }
        for($i=$max_depth;$i>=0;$i--){ //处理成树形，子集存在child里,有没有自己看有没有child字段
            foreach($res as $key=>$val){
                if($val['depth']==$i){
                    foreach($res as $k=>$v){
                        if($val['p_id']==$v['dir_id']){
                            $v['child'][] = $val;
                            $res[$k] = $v;
                            unset($res[$key]);
                        }
                    }
                }
            }
        }
        $res = array_merge($res);
        $final_tree = array();
        $index = 0;
        foreach($res as $key=>$val){
            $index ++;
            if(!isset($val['child'])){ //no child , just go head to next index
                $final_tree[] = $val;
            }else{ // with child, maybe grandchild, will go iterately
                $this->sorted_dir($final_tree,$val);
            }
        }
        return $final_tree;
    }
     
    function sorted_dir(&$return,$arr){
        $ori_arr = $arr;
        unset($arr['child']);
        $return[] = $arr;
        if(isset($ori_arr['child'])){
            foreach($ori_arr['child'] as $childs=>$kid){
                $this->sorted_dir($return,$kid);
            }
        }
        return $return;
    }

    //班级空间，从网盘upload
    function from_cloud($user_id){
        $this->load->model('resource/res_dir_model');
        $resource = $this->res_dir_model->get_res_dir($user_id);
        $sub_cat_tree = array();
        $final_html = '';
        $count = $first_dir_id = 0;
        if($resource){
            $first_dir_id = $resource[0]['dir_id'];
            foreach($resource as $key=>$val){
                $tmp = array();
                $val['short_dir_name'] = sub_str($val['dir_name'],0,45);
                $resource[$key] = $val;
                if(isset($val['cat_id']) and $val['cat_id']){
                    $this->_get_category_tree($val['cat_id'],$tmp);
                    if($tmp){
                        $dep_arr[0] = 0;//d[1]是给depth==2的用的
                        $last_depth = 1;
                        foreach($tmp as $ns=>&$n){
                            $n['sub_cat_id'] = $n['id'];
                            $n['dir_id'] = $val['dir_id'];
                            $n['dir_name'] = $n['name'];
                            if($n['depth'] > $last_depth){
                                $dep_arr[$n['depth']-1] = $last_id;   
                            }
                            $n['p_id'] = $dep_arr[$n['depth']-1];
                            $last_depth = $n['depth'];
                            $last_id = $n['id'];
                        }
                        //从cloud_Model扒拉过来的
                        $max_depth = 0;
                        foreach($tmp as $dps => $d){
                            if($d['depth']>$max_depth){
                                $max_depth = $d['depth'];
                            }
                        }
                        for($i=$max_depth;$i>=0;$i--){ //处理成树形，子集存在child里,有没有自己看有没有child字段
                            foreach($tmp as $ts=>$t){
                                if($t['depth']==$i){
                                    foreach($tmp as $k=>$v){
                                        if($t['p_id']==$v['id']){
                                            $v['child'][] = $t;
                                            $tmp[$k] = $v;
                                            unset($tmp[$ts]);
                                        }
                                    }
                                }
                            }
                        }

                        $tmp = array_merge($tmp);
                        $return =  $this->cloud_model->build_dir_tree_with_html($tmp);
                        $html="<ul>
                            <!-- 第一级 -->
                            <li class=''><div class='tree-title";
                        if(!$count){
                            $html.=' tree-title-add';
                        }
                        $plus = $count?'add':'plus';
                        $html.= "' dir-id='{$val['dir_id']}'><a href='javascript:void(0)' class='icon icon-{$plus}'></a><a href='javascript:void(0)' class='shareItem fold unfold'>{$val['short_dir_name']}</a></div>";
                        $html.=$return."</li></ul>";
                        $final_html .= $html;
                        $count++;
                    }
                }
            }
        }
        return array('html'=>$final_html,'first_dir_id'=>$first_dir_id);
    }

    //same as in lesson_prepare.php
    function _get_category_tree($node,&$category_tree)
    {
        $this->load->model('question/question_category_model');
        $category_list=$this->question_category_model->get_node_tree($node);
        $root_depth = $category_list[0]->depth;
        unset($category_list[0]);
        array_values($category_list);
        $i=0;
        foreach($category_list as $c_l)
        { 
            if($c_l->depth-$root_depth>0){
                $category_tree[$i]['id']=$c_l->id;
                $category_tree[$i]['depth']=$c_l->depth-$root_depth;
                //if('course') $category_tree['category'][$i]['depth']--;
                $category_tree[$i]['name']=$c_l->name;
                if($c_l->lft==$c_l->rgt-1) $category_tree[$i]['is_leaf']=1;
                else $category_tree[$i]['is_leaf']=0;
                $i++;
            }
        }
        //print_r($category_list_r);die;
    }
}