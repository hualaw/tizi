define(function(require){
	// 加载首页幻灯片
	require('module/common/method/common/focus').LeftRightScroll();
	// 加载返回顶部脚本
	require('module/common/method/common/returnTop').cReturnTopfn();
	// 加载统一登录脚本
	require("module/common/basics/common/login").commonLogin();
	//加载标签页的点击切换脚本
	require("module/homePage/tab").init();
	//二维码的下拉事件绑定
	require("module/homePage/dropDown").dropDown();
	//首页有班级账号学校联动
	if($('.classAccountForm').length > 0){
		seajs.use('module/homePage/school',function(_s){
			_s.init();
		});
	}
	//登录输入框的焦点
	require("module/homePage/focus").init();
});