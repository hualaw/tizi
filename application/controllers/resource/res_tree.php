<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");
require_once dirname(__FILE__).DIRECTORY_SEPARATOR."resource_base.php";

class Res_Tree extends Resource_Base {
    protected $_smarty_dir = "teacher/resource/";
    public function __construct(){
        parent::__construct();
        if (!$this->user_id){                                                       
            $this->session->set_flashdata('errormsg',$this->lang->line('error_user_type_teacher'));
            redirect('login');
        }      
        $user_type = $this->session->userdata("user_type");
        if ($user_type != Constant::USER_TYPE_TEACHER){
            redirect('login');
        }
        $this->load->model('resource/res_dir_model');
        $this->load->model('resource/res_file_model');
        $this->load->model('cloud/cloud_model');
        $this->load->model('resource/tree_model');
    }

    /*移动文件/文件夹时弹出的树*/
    function tree(){
        $resource = null;//$this->res_dir_model->get_res_dir($this->user_id);
        $sub_cat_tree = array();
        // if($resource){
        //     foreach($resource as $key=>$val){
        //         $tmp = array();
        //         $val['short_dir_name'] = sub_str($val['dir_name'],0,45);
        //         $resource[$key] = $val;
        //         if(isset($val['cat_id']) and $val['cat_id']){
        //             $this->_get_category_tree($val['cat_id'],$tmp);
        //             if($tmp){
        //                 $sub_cat_tree[$val['cat_id']] = $tmp;
        //             }
        //         }
                
        //     }
        // }
        // foreach($sub_cat_tree as $k=>$v){
        //     foreach($v as $key=>$val){
        //         $val['short_name'] = sub_str($val['name'],0,36);
        //         $sub_cat_tree[$k][$key] = $val;
        //     }
        // }
        $cloud_tree = $this->tree_model->clean_sorted_cloud_tree($this->user_id);
        if($cloud_tree){
            foreach($cloud_tree as $key=>$val){
                $val['short_dir_name'] = sub_str($val['dir_name'],0,36);
                $cloud_tree[$key] = $val;
            }     
        }
        $res_type = null;//Constant::resource_type();
        // $res_type = array_flip($res_type);

        // var_dump($res_type);die;
        $this->smarty->assign('res_type',$res_type);
        $this->smarty->assign('cloud',$cloud_tree);
        $this->smarty->assign('resource',$resource);//同步/知识点文件夹
        $this->smarty->assign('sub_cat_tree',$sub_cat_tree);//每个同/知文件夹下的目录树
        $json['html'] = $this->smarty->fetch($this->_smarty_dir.'move_tree.html');
        $json['dir_html'] = $this->smarty->fetch($this->_smarty_dir.'move_dir_tree.html');
        echo json_token($json);die;
    }
 
}

