define(function(require, exports) {
	//学生首次登录
	exports.stuSubmit = function (json){
        if (json.code == 1){
            window.location.href = json.redirect;
        } else {
        	require("tiziDialog");
            $.tiziDialog({content:json.msg});
        }
	}
	//researcher首次登录
	exports.resSubmit = function (json){
        if (json.code == 1){
            window.location.href = json.redirect;
        } else {
        	require("tiziDialog");
            $.tiziDialog({content:json.msg});
        }
	}
	//老师首次登录
	exports.teaSubmit = function (json){
        if (json.code == 1){
            window.location.href = json.redirect;
        } else {
        	require("tiziDialog");
            $.tiziDialog({content:json.msg});
        }
	}
	//家长首次登录
	exports.parSubmit = function (json){
        if (json.code == 1){
            window.location.href = json.redirect;
        } else {
        	require("tiziDialog");
            $.tiziDialog({content:json.msg});
        }
	}
    //用户名首次登录
    exports.unameSubmit = function (json){
        if (json.code == 1){
            window.location.href = json.redirect;
        } else {
            require("tiziDialog");
            $.tiziDialog({content:json.msg});
        }
    }
});