// 这是班级空间脚本入口文件
define(function(require){
	// 加载老师端头部右侧下拉菜单效果
    require('module/common/basics/common/highlight').highlightMenu();
    // 自主练习 tab
    var _tab = require('module/common/method/common/tab');
    //学校tab
     _tab.stuPraticeTab($(".changeTab a"),"active",$(".practiceBox"));
  

    //返回的时候控制当前选项卡的
    _tab.gradeTab();
    _tab.subjectTab();
});