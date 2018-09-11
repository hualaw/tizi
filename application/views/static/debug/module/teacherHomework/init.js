// 这是留作业的脚本入口文件
// author shanhongliang
// date 2014-07-17 11:29
define(function(require){
	//班级列表
	if($(".classList").length){
		//班级列表页面的初始化
		require("module/teacherHomework/homeworkClasslist").init();
	//布置作业
	}else if($(".assignHomework").length){
		//布置作业页面初始化
		require("module/teacherHomework/homeworkAssign").homeworkAssign.init();
	//没有班级或者没有登录
	}else if($(".unlogin").length){
		require.async("module/teacherHomework/homeworkNo",function(ex){
            ex.init();
        });
	}
	//留作业 作业内容 弹框
	//require("module/teacherHomework/homework").homework(); 
	
});