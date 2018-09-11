define(function(require){
	//  我的资源库js
    var _resource = require('module/teacherResource/teacherResource');
	
    // 新建同步章节
    _resource.chapter();
    // 新建同步知识点
    _resource.lore();
    // 添加请选择教材方法
    _resource.chooseVersion();
    // 添加请选择目录方法
    _resource.chooseCatalog();
    // 绑定没有教材版本
    _resource.bindHavenov();
    // 删除文件夹
    _resource.delCommonFolder();
    // 班级分享高亮
    _resource.hoverFileText();
    // 文件上传高亮
    _resource.hoverOtherFileList();
    /*reslist  分页*/
    _resource.init_reslist_page.initPage();
    seajs.use('module/teacherResource/opt_upload.uncompress',function(do_upload){
        // 同步章节文件上传
        do_upload.dropBoxFileList();
    });
    //网盘 操作  buttons
    var _operate_button = require('module/teacherResource/operate_button');
    _operate_button.dropBoxDialog();
	// 加载老师端左侧背景高度判断
    require('module/common/method/common/height').leftMenuBg();
})