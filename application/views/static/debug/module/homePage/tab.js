//tab标签的切换脚本
//author shanghongliang
//date 2014-7-21 11:23
define(function(require,exports){
	exports.init=function(){
		require('cookies');
		//绑定tab标签页的click事件
		$(".tabLink [data-toggle=tab]").click(function(e){
			if(!$(this).parent().hasClass("active")){
				$.cookies.set('_ln_type', $(this).attr("type"), { hoursToLive : 24 * 365, domain: baseCookieDomain });
			}
			var _target=$(this).attr("url");
			if(!_target){
				return;
			}
			$(this).parents(".tabLink").children().removeClass("active");
			$(this).parent().addClass("active");
			$(_target).parent().children().removeClass("active");
			$(_target).addClass("active");
			e.preventDefault();
		});
	}

});