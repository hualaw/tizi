/**
 * Created by 91waijiao on 14-5-7.
 */
define(function(require, exports) {
    /**
     * 所有操作高亮   共享，班级分享，等等
     * @constructor
     */
    exports.hoverOtherFileList = function(){
        $(".otherFileList .md_bd li").hover(function(){
            if($(this).hasClass("nav_tab")){return;}
            $(".otherFileList .md_bd li").removeClass("active");
            $(this).addClass("active");
        },function(){
            $(this).removeClass("active");
        });
    }
});