//家长端状元讲座模块开始
define(function(require){
    var _pageName = $('.mainContainer').attr('pagename');
    switch(_pageName){
        case 'index':
            seajs.use('module/activity/champions/index_champions', function(_video) {
                _video.init();
            });
            break;
        case 'video':
            //获取视频列表
            seajs.use('module/activity/champions/video_champions', function(_videolist) {
                _videolist.init();
            });
            break;
    }


});