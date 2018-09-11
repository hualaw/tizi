// 这是学生班级空间脚本入口文件
define(function(require){
	var _pageName = $('.mainContainer').attr('pageName');
	// _pageName
	if(!_pageName){return;}
	switch(_pageName){
		// 模块是首页
		case 'share':
		seajs.use("module/studentClass/share",function(_paper){
			_paper.init_sharelist_page.initPage();
			_paper.download_share();
		});
		break;
		case 'paper':
		seajs.use("module/studentClass/paper",function(_paper){
			_paper.lookReviews();
		});
		break;
		case 'noAddClass':
		seajs.use("module/studentClass/vaild",function(_vaild){
			_vaild.studentAddClass();
		});
		break;
		case 'previewPage':
		seajs.use("module/studentClass/share",function(ex){
			ex.download_class_share();//下载 in 文件预览页面
		});
		break;
		case 'stuClassHomeWork':
		seajs.use("module/studentClass/paper",function(_paper){
			_paper.lookReviews();
		});
		seajs.use("module/studentClass/stuClassHomeWork",function(_paper){
			_paper.stuClassHomeWork();
		});
		break;
		

	}

	/* 未登录回调函数 */
    classJoinCallback = function(){
        $(".studentAddClassForm").Validform().resetStatus();
        $('.studentAddClassForm').submit();
        return false;
    }

    classJoinClose = function(){
        $(".studentAddClassForm").Validform().resetStatus();
        return false;
    }

});