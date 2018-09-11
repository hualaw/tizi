define(function(require, exports) {
	// 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    
    // 首页--登录验证
    exports.commonLogin = function(){
        // 加载验证
        require("tizi_valid").indexLogin();

    };

});