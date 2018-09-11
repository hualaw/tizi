<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Res_File_Model extends MY_Model {
    private $_table = 'cloud_user_file';
    private $_redis=false;
    public function __construct(){
        parent::__construct();
        $this->load->model("redis/redis_model");
    }

    //通过cat_id 或者 sub_cat_id 来获取文件列表
    function get_list_by_cat($user_id,$cat_id,$sub_cat_id=0,$page=1,$pagesize=20){
        $sql = "select * from {$this->_table} where user_id = $user_id and is_del=0 ";
        $where = " and dir_cat_id = $cat_id ";
        if($sub_cat_id){
            $where .= " and sub_cat_id = $sub_cat_id ";
        }
        $start = ($page-1)*$pagesize;
        $order = " order by upload_time desc ";
        $limit = " limit $start,$pagesize";
        $sql .= $where.$order.$limit;
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    /*  同时获取n资源种类型的文件，每种获取 $pagesize个
        $type_arr  要获取的类型的组合
    */
    function res_file_list($user_id,$sub_cat_id,$type_arr,$page=1,$pagesize=10){
        if(!is_array($type_arr)){
            return null;
        }
        $user_id = intval($user_id);
        $sub_cat_id = intval($sub_cat_id);
        $start = (intval($page)-1)*$pagesize;
        $count = count($type_arr);
        $sql = '';
        if($count>1){
            foreach($type_arr as $k=>$val){
                $sql .= " select * from (select * from $this->_table where user_id=$user_id and sub_cat_id=$sub_cat_id and resource_type=$val and is_del=0 order by upload_time desc limit $start,$pagesize) t{$val}  union all ";
            }
            $sql = rtrim($sql,'union all');
        }elseif($count == 1){
            $type_arr = array_values($type_arr);
            $val = $type_arr[0];
            $sql .= " select * from $this->_table where user_id=$user_id and sub_cat_id=$sub_cat_id and resource_type=$val and is_del=0 order by upload_time desc limit $start,$pagesize ";
        }else{
            return null;
        }
        $res = $this->db->query($sql)->result_array();//echo $this->db->last_query();
        return $res;
    }

    /* 从DB中获取自定义条件下的文件总数    */
    function file_sum($param){
        $data = array_filter($param,'strlen');//过滤数组中的空值
        $this->db->select("count(*) as num ");
        $this->db->where($data);
        $query=$this->db->get($this->_table);   
        return $query->row(0)->num;
    }

    /* 每在同步or知识点文件夹中上传一个文件，就更改redis中的统计值  
        key为 user_res_userid_catid,key为hash类型，hash的key是sub_cat_id(即页面左边的tree中的具体值)
        同样存在库19，cloud_statistics中 
    */
    function update_user_res_subcatid_type($user_id,$dir_cat_id,$sub_cat_id,$res_type=1,$num=1){
        if($res_type<Constant::RESOURCE_TYPE_JXSJ or $res_type>Constant::RESOURCE_TYPE_OTHER){
            return false;
        }
        $count = $this->hget_user_res($user_id,$dir_cat_id,$sub_cat_id);
        if($this->_redis){
            $key = 'user_res_'.$user_id.'_'.$dir_cat_id;
            if($count){
                if(!isset($count[$res_type])){
                    $count[$res_type] = 0;
                }
                $count[$res_type] += $num;
            }else{
                $count = array($res_type=>$num);
            }
            $count = serialize($count);
            $value = $this->cache->hset($key,$sub_cat_id,$count);
        }
    }

    /*  计算最下层的一个同步/知识点目录中的各个类型的文件总数
        return: Array ( 1 => 5 , 2 => 1 ) , 数组下标为 代表资源类型的常量    */
    function count_res_file_by_alltype($user_id,$dir_cat_id,$sub_cat_id){
        $sql = "select resource_type, count(*) as count from cloud_user_file where user_id=? and dir_cat_id = ? and sub_cat_id=? and is_del=0 group by resource_type";
        $c = $this->db->query($sql,array($user_id,$dir_cat_id,$sub_cat_id))->result_array();
        $return = array();
        if($c){
            foreach($c as $key=>$val){
                $return[$val['resource_type']] = $val['count'];
            }
        }
        return $return;
    }

    /* 从DB中获取最新统计，更新sub_cat_id下的所有的类型的文件数目统计 */
    function update_user_res_subcatid($user_id,$dir_cat_id,$sub_cat_id){
        if($this->redis_model->connect('cloud_statistics')){
            $this->_redis=true;
        }
        if($this->_redis){
            $sta = $this->count_res_file_by_alltype($user_id,$dir_cat_id,$sub_cat_id);
            $hash_value = serialize($sta);
            $key = 'user_res_'.$user_id.'_'.$dir_cat_id;
            $value = $this->cache->hset($key,$sub_cat_id,$hash_value);
            return $value;
        }
    }

    /*获取sub_cat_id下的统计数，返回unserialize后的结果*/
    function hget_user_res($user_id,$dir_cat_id,$sub_cat_id){
        if($this->redis_model->connect('cloud_statistics')){
            $this->_redis = true;
        }
        if($this->_redis){
            $key = 'user_res_'.$user_id.'_'.$dir_cat_id;
            $count = $this->cache->hget($key,$sub_cat_id); // get all statistics in a dir
            if($count){
                $count = unserialize($count);
            }
            return $count;
        }
    }

    /*  获取一个同步/知识点目录下的所有 分支的所有类型的文件数统计  */
    function hgetall_user_res($user_id,$dir_cat_id){
        if($this->redis_model->connect('cloud_statistics')){
            $this->_redis = true;
        }
        if($this->_redis){
            $key = 'user_res_'.$user_id.'_'.$dir_cat_id;
            $sum = $this->cache->hgetall($key); // get all statistics in a dir
            $res = array();
            if($sum){
                foreach($sum as $key=>$val){
                    $tmp = unserialize($val);
                    if($tmp){
                        foreach($tmp as $k=>$v){
                            $res[$key][$k] = $v;
                        }
                    }
                    if(!isset($res[$key])){
                        $res[$key] = array();
                    }
                    $res[$key]['total'] = array_sum($res[$key]);
                }
            }
            return $res;
        }
        return null;
    }

    //获取预览音视频文件的七牛的url
    function get_media_url($info){
        $key = $info['file_path'];
        $file_type = $info['file_type'];
        $file_ext = $info['file_ext'];
        $code = $content = $url = null;
        $this->load->helper('qiniu');
        $url = array('low'=>null,'normal'=>null,'origin'=>null);
        if($file_type == Constant::CLOUD_FILETYPE_VIDEO or $file_type == Constant::CLOUD_FILETYPE_AUDIO){
            $to = $file_type == Constant::CLOUD_FILETYPE_VIDEO?'mp4':'mp3';
            if($file_ext != $to){
                $persistent_id = (isset($info['persistent_id']) and $info['persistent_id'])?$info['persistent_id']:0;
                if($persistent_id){
                    //先从redis中获取，没有就请求七牛的api然后写进redis
                    $this->load->model('redis/redis_model');
                    $_redis = $this->redis_model->connect('qiniu_file');
                    if($_redis){ //连得上redis，取的到值就直接返回值
                        $cache_code = $this->cache->redis->get($persistent_id);
                        $cache_code = json_decode($cache_code,true);
                        $code = isset($cache_code['code']) ? $cache_code['code']:null;
                        $to = isset($cache_code['cmd'])?$cache_code['cmd']:$to;
                        $to = str_replace('avthumb/', '', $to);
                    }
                    if($code!==0 and $code!==3 ){//既不是转换成功，也不是转换失败, 就再请求七牛的api
                        $content = $this->curl_prefop_result($persistent_id);
                        if(isset($content['items'])){
                            $code = intval($content['items'][0]['code']);
                            $to = $cmd = $content['items'][0]['cmd'];
                            $to = str_replace('avthumb/', '', $to);
                            $json = json_encode(array('code'=>$code,'cmd'=>$cmd));
                            if($_redis)$this->cache->redis->set($persistent_id,$json);
                        }
                    }
                    $url['normal'] = $code === 0 ? qiniu_vi_au($key,$to,false):qiniu_vi_au($key,$to);
                }else{//没有persistent_id
                    $to = $file_type == Constant::CLOUD_FILETYPE_VIDEO?Constant::PRESET_VIDEO:Constant::PRESET_AUDIO;
                    $url['normal'] = qiniu_vi_au($key,$to);
                }
                if($file_type==Constant::CLOUD_FILETYPE_VIDEO){
                    // $url['low'] = qiniu_vi_au($key,Constant::PRESET_VIDEO_LOW);
                    // $url['origin'] = qiniu_vi_au($key,Constant::PRESET_VIDEO_ORIGIN);
                }
            }else{//如果是mp3/mp4，直接获取下载地址
                if($file_type==Constant::CLOUD_FILETYPE_VIDEO){
                    // $url['low'] = qiniu_vi_au($key,Constant::PRESET_VIDEO_LOW);
                    // $url['origin'] = qiniu_download($key,$to,10800);
                    // $url['normal'] = qiniu_vi_au($key,Constant::PRESET_VIDEO);
                    $url['normal'] = qiniu_download($key,$to,10800);
                }else{
                    $url['normal'] = qiniu_download($key,$to,10800);
                }
            }
        }
        return $url;
    }

    function is_pfop_done(&$data){
        $this->load->model('redis/redis_model');
        $_redis = $this->redis_model->connect('qiniu_file');
        if(!is_array($data)){
            return $data;
        }
        foreach($data as $key=>$val){
            if($_redis){ //连得上redis，取的到值就直接返回值
                $persistent_id = $val['persistent_id'];
                $cache_code = $this->cache->redis->get($persistent_id);
                $cache_code = json_decode($cache_code,true);
                $code = isset($cache_code['code']) ? $cache_code['code']:null;
                $data[$key]['pfop'] = $code===0 ? true:false;
            }
        }
    }

    /*查询某个文件是否pfop完成，先从redis中获取，没有的话请求七牛api*/
    function model_check_pfop($file_id){
        $this->load->model('cloud/cloud_model');
        $info = $this->cloud_model->file_info($file_id,'*',0,true);
        $this->load->helper('qiniu');
        $key = $info['file_path'];
        $persistent_id = (isset($info['persistent_id']) and $info['persistent_id'])?$info['persistent_id']:0;
        $persistent_id = trim($persistent_id);
        $json = array('errorcode'=>false,'error'=>'文件正在转换，请稍候再试');
        $url = '';
        if(!$persistent_id){
            return ($json);
        }
        //先从redis中获取，没有就请求七牛的api然后写进redis
        $this->load->model('redis/redis_model');
        $_redis = $this->redis_model->connect('qiniu_file');
        if($_redis){ //连得上redis，取的到值就直接返回值
            $cache_code = $this->cache->redis->get($persistent_id);
            $cache_code = json_decode($cache_code,true);
            $code = isset($cache_code['code']) ? $cache_code['code']:null;
            if($code === 0){
                $to = $cache_code['cmd'];
                $to = str_replace('avthumb/', '', $to);
                $url = qiniu_vi_au($key,$to,false);
                if($url){
                    return  array('errorcode'=>true,'url'=>$url);
                }
            }elseif($code === 3){
                return array('errorcode'=>false,'error'=>'此文件转换失败');
            }
        }//只会处理code为0和为3的情况，都不是的话，就重新请求
        $content = $this->curl_prefop_result($persistent_id);
        if(isset($content['items'])){
            $code = intval($content['items'][0]['code']);
            $to = $cmd = $content['items'][0]['cmd'];
            $to = str_replace('avthumb/', '', $to);
            $_json = (array('code'=>$code,'cmd'=>$cmd));
            $json_encode = json_encode($_json);
            if($_redis)$this->cache->redis->set($persistent_id,$json_encode);
        }
        if($code === 0 ){
            $url = qiniu_vi_au($key,$to,false);
            $json = array('errorcode'=>true,'url'=>$url);
            return ($json);
        }elseif($code === 3){
            return array('errorcode'=>false,'error'=>'此文件转换失败');
        }
        return ($json);
    }

    /*通过七牛api获取 pfop的转换结果*/
    function curl_prefop_result($persistent_id){
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_URL, 'http://api.qiniu.com/status/get/prefop?id='.$persistent_id);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        curl_close($curl);
        $content = json_decode($content,true);
        return $content;
    }
    /*通过lesson_res_id 获取persistent信息*/
    public function get_persistent_info($lesson_res_id)
    {
        $this->db->select('persistent_id');
        $this->db->limit(1);
        $query = $this->db->get_where($this->_table,array('lesson_res_id'=>$lesson_res_id));
        return  $query->row()->persistent_id;
    }
    
}