//首页登录框焦点获取和消失脚本
//author shanghongliang
//date 2014-07-31 18:29
define(function(require,exports){
	exports.init=function(){
		$(".loginWrapCon .cInput").focus(function(){
			$(this).addClass("activeInp");
		}).blur(function(){
			$(this).removeClass("activeInp");
		});
	}
});