// 这是出卷子的脚本入口文件
define(function(require){
    var _pageName = $('.mainContainer').attr('pagename');
    var _tabName = $('.mainContainer').attr('tabname');
    switch(_pageName){
        // 如果是账户设置页面
        case 'myQuestionLibrary':
            seajs.use('module/teacherTestLibrary/myQuestions',function(ex){
                // 加载答案显示效果
                ex.showAnswer();
                // 加载添加分组效果
                ex.addNewGroup();
                // 加载编辑分组效果
                ex.editNewGroup();
                // 加载删除分组效果
                ex.delNewGroup();
				// 加载进入分组效果
				//ex.GroupInto();
				//删除试题
				ex.delQues();
                // 加载题目分组效果
                ex.groupQues();
				// 根据学科加载分组
				ex.ajaxGetGroups();
            });
            // 加载老师端左侧背景高度判断
            require('module/common/method/common/height').leftMenuBg();
            break;
//        // 如果是上传新题设置页面
//        case 'myQuestionUpload':
//            require('module/common/method/common/height').leftMenuBg();
//            seajs.use('module/teacherTestLibrary/myQuestionUpload',function(ex){
//                ex.UploadAq.appletUrl = 'http://192.168.11.196:8084/application/views/static/';
//                ex.UploadAq.init();
//                window.onerror=function(){return true;}
//            });
//            seajs.use('module/common/basics/teacherTestLibrary/valid',function(ex){
//                ex.uploadQuesValid.myQuestion();
//            });
//            // 加载老师端左侧背景高度判断
//            break;
    }

    // 加载老师端头部右侧下拉菜单效果
    require('module/common/basics/common/highlight').highlightMenu();
});