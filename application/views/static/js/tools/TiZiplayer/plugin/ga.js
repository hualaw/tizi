(function(TiZiplayer){

var plugin_ga_c0 = plugin_ga_c1 = plugin_ga_c2 = plugin_ga_c3 = false;

var template = function(player, config, div) {

    function get_title(){
        if(config.tcid){
            var title = $('.'+config.tcid).html();
        }else{
            var title=config.title
        }
        if(title == undefined) title = 'Unknown Videos';
        return title;
    }

    function get_category(){
        var category = config.category;
        if(category == undefined) category = 'Videos';
        return category;
    }
    player.onPlaylist(function(){
        plugin_ga_c0 = plugin_ga_c1 = plugin_ga_c2 = plugin_ga_c3 = false;
    });
    player.onPlaylistItem(function(){
        plugin_ga_c0 = plugin_ga_c1 = plugin_ga_c2 = plugin_ga_c3 = false;
    });
    player.onPlay(function(){
        ga('send', 'event', get_category(), 'Play', get_title(), player.getPosition());
    });
    player.onPause(function(){
        ga('send', 'event', get_category(), 'Pause', get_title(), player.getPosition());
    });
    player.onComplete(function(){
        ga('send', 'event', get_category(), 'Complete', get_title(), player.getPosition());
        plugin_ga_c0 = plugin_ga_c1 = plugin_ga_c2 = plugin_ga_c3 = false;
    });
    player.onTime(function(event){
        if(!plugin_ga_c0 && event.duration - event.position <= 20){
            ga('send', 'event', get_category(), 'C_End', $('.video_h1').html(), event.position);
            plugin_ga_c0 = true;
        }

        var dur = event.duration / 3;
        if(!plugin_ga_c1 && event.position > 0 && event.position < dur){
            ga('send', 'event', get_category(), 'C_1', get_title(), event.position);
            plugin_ga_c1 = true;
        }
        if(!plugin_ga_c2 && event.position > dur && event.position < 2 * dur){
            ga('send', 'event', get_category(), 'C_2', get_title(), event.position);
            plugin_ga_c2 = true;
        }
        if(!plugin_ga_c3 && event.position > 2 * dur && event.position < event.duration){
            ga('send', 'event', get_category(), 'C_3', get_title(), event.position);
            plugin_ga_c3 = true;
        }
    });
};
TiZiplayer().registerPlugin('ga', '6.0', template);
})(TiZiplayer);
