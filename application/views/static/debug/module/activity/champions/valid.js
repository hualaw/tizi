define(function(require,exports){
	// 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
	// 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    // 预约课程验证
    exports.makeAppointValid = function() {
        var _Form = $(".makeAppointForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            ajaxPost: true,
            callback: function(data) {
                exports.makeAppointAjax(data);
            }
        });
        // 验证规则
        _Form.addRule([ {
            ele: ".userName",
            datatype: sDataType.URname.datatype,
            nullmsg: sDataType.URname.nullmsg,
            errormsg: sDataType.URname.errormsg
        }, 
        {
            ele: ".userPhone",
            datatype: sDataType.Phone.datatype,
            nullmsg: sDataType.Phone.nullmsg,
            errormsg: sDataType.Phone.errormsg
        }, 
        {
            ele: ".userEmail",
            datatype: sDataType.Email.datatype,
            nullmsg: sDataType.Email.nullmsg,
            errormsg: sDataType.Email.errormsg
        } ]);
    };
    // 预约课程回调方法
    exports.makeAppointAjax = function(data){
        if(data.status == 1){
			// 请求dialog插件
			require.async("tiziDialog",function(){
				//关闭弹窗
				$.tiziDialog.list['makeAppointDialog'].close();
				$.tiziDialog({
                     icon:"succeed",
                     content:data.error_code,
                     ok:function(){
                        window.location.reload();
                     }
                });
			});
        } else {
            // 请求dialog插件
            require.async("tiziDialog",function(){
                $.tiziDialog({icon:"error",content:data.error_code});
            });
        }
    }
})