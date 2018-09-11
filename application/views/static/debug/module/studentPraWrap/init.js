// 这是班级空间脚本入口文件
define(function(require){
	// 加载老师端头部右侧下拉菜单效果
    require('module/common/basics/common/highlight').highlightMenu();

    //自主练习 js
    var _practice = require('module/studentPraWrap/practiceWrap');
    _practice.practice.init();

    //做作业--答案选择
    //_practice.practice.practice_test.get_answer();

    //做作业页面导航滚动
    if($("#course_info_nav").length>0){
        _practice.personal.fellowNav();
    }
    _practice.errorCorrection.errorReport();
    
    //调用做作业
    //这是老师模拟做作业 和学生做作业是同一个页面 但是调用不同
    //_practice.do_homework();
    //_practice.simulate.clock();
    ////模拟做题
    //_practice.simulate.online_question_save();
    //_practice.simulate.offline_question_save();
    //_practice.simulate.set_question_num();
    // $('#course_info_box').css("padding-bottom","50px");
    // $(".exercise a").css("background","#158c71");

    //已经在纸上完成
    _practice.personal.paperComplete();

    //不同作业提交
    if($("div[worktype='simulate']").length){
        _practice.simulate.submit();
        _practice.practice.practice_training.test();
        _practice.practice.practice_test.per_save();
    }
    if($("div[worktype='practiceDo']").length){
        _practice.practice.practice_training.test();
        _practice.practice.practice_training.per_save();
        _practice.practice.practice_training.get_answer();
    }
    if($("div[worktype='practiceTrain']").length){
        _practice.practice.practice_test.per_save(1);
        _practice.practice.practice_test.get_answer(1);
        _practice.practice.practice_training.test();
    }
    if($("div[worktype='practiceSmall']").length){
        _practice.practice.practice_test.per_save(0);
        _practice.practice.practice_test.get_answer(0);
        _practice.practice.practice_training.test();
    }
    if($("div[worktype='homeworkDo']").length){
        _practice.do_homework();   
    }
    if($("div[worktype='homeworkReport']").length){
        _practice.do_paper_work();
    }   

   
    //答案选择
    _practice.personal.answerSelect();
    // 下载分享文件
    var _share = require('module/studentHome/share');
    _share.downloadShare();

    // student download assigned homework
    seajs.use('module/teacherClass/downloadAssignment',function(ex){
        ex.homeworkDownload.init();
    }); 
    
});
