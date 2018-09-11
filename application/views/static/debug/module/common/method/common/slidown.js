define(function(require, exports) {
    // 头部下拉脚本
    exports.headerSlidown = function(){
        $('#headerSlidown').hover(function(){
            $(this).find('ul.info').removeClass('undis');
        },function(){
            $(this).find('ul.info').addClass('undis');
        });
    };
});