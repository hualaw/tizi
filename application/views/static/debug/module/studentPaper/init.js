// 这是班级空间脚本入口文件
define(function(require){
	// 加载老师端头部右侧下拉菜单效果
    require('module/common/basics/common/highlight').highlightMenu();

    //自主练习 js
    var _studentPaper = require('module/studentPaper/paper');
	if($("div[pagetype='paperReport']").length){
        _studentPaper.report();
    }else{
		_studentPaper.do_homework();   
	}   
});
