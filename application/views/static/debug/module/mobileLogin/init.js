define(function(require, exports) {

    var _mobileLogin = require("module/mobileLogin/mobileLogin");
	// 加载手机端登录脚本
    _mobileLogin.mobileLogin();
    // 加载直接PC登录脚本
    _mobileLogin.loginInPC();
    // 登录检测
    _mobileLogin.loginRefresh();
    //未注册学生加入班级 验证
    _mobileLogin.addClassValid();
});