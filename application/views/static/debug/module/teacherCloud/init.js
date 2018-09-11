define(function(require){
	//网盘 操作  buttons
    var _operate_button = require('module/teacherResource/operate_button');
    _operate_button.dropBoxDialog();
	// 网盘js
	var _cloud = require('module/teacherCloud/cloud');
	// 上传文件,选好文件后 触发
	_cloud.dropBoxFieldFn();
    
	//进入文件夹
	_cloud.into_dir();
	//退回上一层目录
	_cloud.go_back_dir();
	//移动文件 - 点击收缩
	_cloud.moveFileFn();
	//返回网盘首页
	_cloud.reCloudHome();
	//翻页
	// _cloud.page();
	//输入框特殊字符限制
	_cloud.specialWordLimt();
	// 新表格样式鼠标经过改变背景色
	$(function(){
		_cloud.tableStyleFn();
	});
    // 新网盘js
    var cloudAdd = require('module/teacherCloud/cloudAdd');
    cloudAdd.hoverOtherFileList();
	// 加载老师端左侧背景高度判断
    require('module/common/method/common/height').leftMenuBg();
})