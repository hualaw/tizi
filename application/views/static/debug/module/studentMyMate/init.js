// 这是班级空间脚本入口文件
define(function(require){
	// 加载高亮设置
    require('module/common/basics/common/highlight').highlightMenu();
    // 加载左侧背景高度判断
    require('module/common/method/common/height').leftMenuBg();
    // 加入班级验证
    require('module/common/basics/common/valid').studentAddClass();
});