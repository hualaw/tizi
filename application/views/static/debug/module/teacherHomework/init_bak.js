// 这是留作业的脚本入口文件
define(function(require){
	var _pageName = $('.mainContainer').attr('pagename');
    var _tabName = $('.mainContainer').attr('tabname');
    switch(_pageName){
        case 'homeworkArchive':
        	seajs.use('module/teacherHomework/homeworkArchive',function(ex){
        		ex.homeworkArchive.init();
				//布置作业
        		ex.setHomework();
				//选择作答方式
        		ex.answerStyle();	
                //选择学生答题顺序
                ex.answerOrder();
        	});	
			// 加载老师端左侧背景高度判断
		    //require('module/common/method/common/height').leftMenuBg();
        break;
    }
	// 加载内容区域高度判断
    require('module/common/basics/common/highlight').highlightMenu();

});