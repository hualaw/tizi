// 这是班级空间脚本入口文件
define(function(require){

    // 邀请加入班级
    if($('.commonInviteJoinTizi').length > 0){
        seajs.use('module/common/basics/common/classInvite',function(e){
            e.accept();
        })
    }

    var _class = require('module/teacherClass/teacherClass');
    /*载入整个班级模块的跳转*/
    _class.simulatDrop();
    
    //添加班级 展开收回
    _class.addWaySlide();
    // 加载调查
    // require('module/common/method/common/survey').teacherSurvey();
    
    // 加载老师端左侧背景高度判断
    require('module/common/method/common/height').leftMenuBg();
    //添加班级
    var _classAdd = require('module/common/basics/common/classAdd');
    _classAdd.addNewClassDialog();
    //设置添加的班级  选择年份、选择学校
    require("tizi_addon_setSchool");
    // var _classSet = require('module/common/basics/common/classSet');
    // _classSet.classSetting();
    //有班级时加载
    if($('.addNewClass').length > 0){
        //修改班级名称
        _class.classManageDialog();
        // 学生管理和班级老师tab切换
        _class.classManageTab();
        //下载shared file
        _class.downloadFile();
    }
    //分享页面预览时加载
    if($('.teacherClassPrev').length > 0){
        //下载shared file
        _class.downloadFile();
    }
    var classList = $('.class_manage_btn li');
    //文件
    if($('.class_manage_btn .fill').hasClass('active')){
        //分享弹出框
        _class.claSharePop();
        //从网盘上传
        _class.fromInterUpFn();
        //移动文件 - 点击收缩
        _class.moveFileFn();
        //取消分享
        _class.shareCancleFn();
        //分享分页
        // _class.class_share_pagination();
        
        //分享文档，在处理中提示弹框
        _class.inprocess();
        _class.check_pfop();
    };
    //试卷
    if($('.class_manage_btn .paper').hasClass('active')){
        //检查试卷
        _class.studentHomework();
        // teacher download assignment
        seajs.use('module/teacherClass/downloadAssignment',function(ex){
            ex.homeworkDownload.init();
        }); 
        //试卷分页
        class_homework_pagination = function (page){
            seajs.use('module/teacherClass/teacherClass', function(c) {
              c.class_homework_pagination(page);
            });
          }
    };
    //作业
    if($('.class_manage_btn .work').hasClass('active')){
        //作业分页
        class_zy_pagination = function (page){
            seajs.use('module/teacherClass/teacherClass', function(c) {
              c.class_zy_pagination(page);
            });
          }
    };
    //学生
    if($('.class_manage_btn .student').hasClass('active')||$('.class_manage_btn .teacher').hasClass('active')){
        // 添加学生账号开始
        _class.addStudent();
        // 班级数量的加减 
        _class.addPlus();
    };
    // 作业预览
    if($('.homeworkPreCon').length > 0){
        //作业预览tab切换
        //_class.homeworkPreTab();
        //查看题目
        _class.subjectDetial();
        _class.personWrongQ();
        //写评语
        _class.comment();
        //点击题目收缩与展开
        _class.click_q();
    }
    // 点击复制调用
    if($('.class_manage_btn .teacher').hasClass("active") || $('.class_manage_btn .set').hasClass("active")){

        seajs.use('tizi_validform',function(ex){
            ex.detectFlashSupport(
                function(){
                    $('#iniInviteId').html('');
                },function(){
                    _class.ZeroClipboard();
                }
            );
        });
        // seajs.use('tizi_validform',function(ex){
        //     ex.detectFlashSupport(
        //         function(){
        //             $('#iniInviteId2').html('');
        //         },function(){
        //             _class.ZeroClipboardSlide();
        //         }
        //     );
        // });
        //保存设置，把"设置"页面分出来之后删掉下面的方法preserveFn();
        _class.preserveFn();
    }
    // 保存设置
    if(classList.eq(5).hasClass("active")){
        _class.preserveFn();
    }
    /* 未登录回调函数 */
    classJoinCallback = function(){
        $(".inVislbleClassForm").Validform().resetStatus();
        $('.inVislbleClassForm').submit();
        return false;
    }

    classJoinClose = function(){
        $(".inVislbleClassForm").Validform().resetStatus();
        return false;
    }
    
    classCreateCallback = function(){
        $(".creatNewClassForm").Validform().resetStatus();
        $('.creatNewClassForm').submit();
        return false;
    }

    classCreateClose = function(){
        $(".creatNewClassForm").Validform().resetStatus();
        return false;
    }

    //检查作业
    if($(".checkHomework").length){
        //检查作业uemian初始化
        require("module/teacherClass/homeworkCheck").homeworkCheck.init();
    }
    //作业汇总报告
    if($(".homeworkReport").length){
        //汇总报告初始化
        require.async("module/teacherClass/homeworkReport",function(ex){
            ex.init();
        });
    }
});
