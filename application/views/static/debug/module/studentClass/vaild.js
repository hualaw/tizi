define(function(require, exports) {
	// 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    // 加入班级验证
    exports.studentAddClass = function(callback_login){
        var _Form = $(".studentAddClassForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: function(msg, o, cssctl) {
                if (!o.obj.is("form")) {
                    var objtip = o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();
                    var objtip = o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    var infoObj = o.obj.next(".ValidformTips");
                    // 判断验证成功
                    if (o.type == 2) {
                        infoObj.show();
                        o.obj.next().hide();
                    }
                }
            },
            showAllError: false,
            beforeSubmit: function(curform) {
                // 加载MD5加密
                require.async("tizi_validform",function(ex){
                    ex.md5(curform);
                });
            },
            ajaxPost: true,
            callback: function(data) {    
                require.async("module/common/ajax/studentCommon/ajax",function(ex){
                    ex.studentAddClass(data);
                });
            }
        });
        _Form.addRule([{
               ele:".classNumInput",
                    datatype:"*6-8",
                    nullmsg:"请输入班级编号",
                    errormsg:"长度6-8个字符"
            }
        ]);
    };
});