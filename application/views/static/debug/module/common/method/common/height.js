define(function(require, exports) {
    // 老师端左侧背景高度判断
	exports.leftMenuBg = function() {
        var _wrapContentHeight = $('#wrapContent').height();
        var _slideHeight = $('#slide').height();
        if(_wrapContentHeight > _slideHeight){
          $('#slide').height(_wrapContentHeight);
        }else{
          $('#wrapContent').height(_slideHeight);
        }
    }
	
});