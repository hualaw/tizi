//tab标签的切换脚本
//author shanghongliang
//date 2014-7-24 11:11
define(function(require,exports){
	exports.dropDown=function(){
		//绑定微信二维码click事件
		$(".codeWrap,.weixinLink").click(function(e){
			$(".codeWrap").toggleClass("active");
			e.stopPropagation();
		});
		//点击其他地方二维码消失
		$(document).click(function(e){
			$(".codeWrap").removeClass("active")
		});
	}

});