// 这是出卷子的脚本入口文件
define(function(require){
    var _pageName = $('.mainContainer').attr('pagename');
    var _tabName = $('.mainContainer').attr('tabname');
    switch(_pageName){
        // 如果是账户设置页面
        case 'myQuestionLibrary':
   			// 加载老师端左侧背景高度判断
    		require('module/common/method/common/height').leftMenuBg();
   			break;
        case 'paperArchive':
        	seajs.use('module/teacherPaper/paperArchive',function(ex){
        		ex.paperArchive.init();
        	});
        break;
        case 'examHall':
          seajs.use('module/teacherPaper/paperExam',function(ex){
            ex.paperExam.init();
          });
        break;
    }

	// 加载老师端头部右侧下拉菜单效果
    require('module/common/basics/common/highlight').highlightMenu();
});