define(function(require){
	// 加载左侧栏目高亮
	require('module/common/basics/common/highlight').highlightMenu();
	// 加载返回顶部脚本
	require('module/common/method/common/returnTop').cReturnTopfn();
	// 加载左侧背景高度判断
    require('module/common/method/common/height').leftMenuBg();

    //公共调用，反馈
	require('tizi_feedback').feedback();

});