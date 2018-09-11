// 这是班级空间脚本入口文件
define(function(require,exports,module){
    // 加载调查
    // require('module/common/method/common/survey').studentSurvey();

	// 加载高亮设置
    require('module/common/basics/common/highlight').highlightMenu();
    // 加载左侧背景高度判断
    //require('module/common/method/common/height').leftMenuBg();
    // 加入班级验证
    require('module/common/basics/common/valid').studentAddClass();

    var _share = require('module/studentHome/share');    
    _share.downloadShare();// 下载分享文件
    _share.love_share();//点赞分享文件
    _share.check_pfop();//查看是否转换好 分享文件
    
   	//var _practice = require('module/studentPractice/practice');
    //_practice.setTab();
	// 练一练 tab
     var _tab = require('module/common/method/common/tab');
    //学科tab   
     _tab.stuPraticeTab($(".courseNav li.subject_n"),"stuOn",$(".practiceBox"));


    
    
});
