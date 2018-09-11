<?php

class Unit_Game_Model extends MY_Model {
    protected $tab_gq = "game_question";
    protected $tab_gt = "game_type";
    protected $tab_zpt = "zuoye_practice_type";
    protected $tab_ga_type_info = "game_type_info";
    protected $tab_ga_type_unit = "game_type_unit";
    // protected $tab_s_unit = "common_unit";

    function __construct(){
        parent::__construct();
    }

    //非小学英语   不会存在zuoye_prac_type    unit_id就是category_id
    function get_games_by_unit_no_practype($unit_id){
        $unit_id = intval($unit_id);
        if(!$unit_id){return null;}
        $game_type = $this->get_gametype_by_unit($unit_id);
        if(!$game_type){return null;}
        $games = array();
        foreach($game_type as $key=>&$val){
            $val['games'] = $this->get_game_by_gametypeunit($val['gtu_id']);//new
        }
        return $game_type;
    } 
     
    //小学英语   数据会存在zuoye_prac_type字段中 
    function get_games_by_unit($unit_id){
        $unit_id = intval($unit_id);
        if(!$unit_id){return null;}
        $game_type = $this->get_gametype_by_unit($unit_id);
        if(!$game_type){return null;}
        $games = array();
        foreach($game_type as $key=>&$val){
            // $val['games'] = $this->get_game_by_gametype($val['game_type']);//old
            $val['games'] = $this->get_game_by_gametypeunit($val['gtu_id']);//new
            //zy_prac_type为key
            $games[$val['zy_prac_type']][] = $val;
        }
        $this->load->model('homework/practice_type_model');
        $all_prac_type = $this->practice_type_model->get_practice_type();
        foreach($all_prac_type as $pt=>&$p){
            if($games){
                foreach($games as $key=>$val)   {
                    if($key == $p['id']){
                        $p['game_types'] = $val;
                    }
                }
            }
        }         
        return $all_prac_type;
    }   

    function get_game_by_gametypeunit($gtu_id){
        // $sql = "select game.* from game_type_info gti 
        $sql = "select game.id,game.game_name,game.game_path,game.game_data_type,game.is_word,game.audio,
                game.is_online,game.option_num,game.image,game.question_num,gtu.game_type_id as game_type 
                from game_type_info gti 
                left join game_type_unit gtu on gtu.id = gti.gtu_id
                left join game on game.id=gti.game_id where gti.gtu_id=$gtu_id and game.is_online=1";
        $g = $this->db->query($sql)->result_array();
        return $g;
    }

    
    //一个unit下面，有哪些作业练习类型 和 游戏类型 
    // game_type_unit
    function get_gametype_by_unit($unit_id,$with_zy_prac_type=true){
        $select = " distinct gtu.id as gtu_id, gtu.game_type_id as game_type, gt.name as game_type_name ";
        if($with_zy_prac_type){
            $select.= " , gt.zy_prac_type, zpt.name as prac_name ";
        }
        $sql = "select $select
                from {$this->tab_ga_type_unit} gtu 
                left join {$this->tab_gt} gt on gt.id = gtu.game_type_id ";
        if($with_zy_prac_type){
            $sql .= " left join {$this->tab_zpt} zpt on zpt.id=gt.zy_prac_type ";
        }
        $sql .= " where unit_id=$unit_id ";
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    //  function get_game_by_gametype($game_type){
    //     $sql = "select * from game where game_type=$game_type and is_online=1";
    //     $g = $this->db->query($sql)->result_array();
    //     return $g;
    // }

    //一个unit下面，有哪些作业练习类型 和 游戏类型
    // function get_gametype_by_unit($unit_id){
    //     $sql = "select gq.game_type,gt.name as game_type_name, gt.zy_prac_type,zpt.name as prac_name from {$this->tab_gq} gq left join {$this->tab_gt} gt on gt.id=gq.game_type left join {$this->tab_zpt} zpt on zpt.id=gt.zy_prac_type where gq.category_id = {$unit_id} GROUP BY gq.game_type";
    //     $res = $this->db->query($sql)->result_array();
    //     return $res;
    // }
}

