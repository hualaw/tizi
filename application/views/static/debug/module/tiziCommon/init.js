define(function(require,exports){
	// 公共调用，错误信息提示
    require('tizi_msg').errormsg();
	//公共调用，我的消息
	require('tizi_notice').getNotice();
	//公共调用，登录检测
	require('tizi_login_form').init();
	// 公共select模拟
	require('tizi_select');
	// $('select').jqTransSelect();
	//公共cookie检测
	require('tizi_cookie');
	// 头部下拉脚本调用
	require('module/common/method/common/slidown').headerSlidown();
});