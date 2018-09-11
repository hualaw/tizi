define(function(require, exports) {
    // 请求验证库
    require("validForm");

    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    // 未注册学生 加入班级验证
    exports.addClassNumFrom = function(){
        var _Form = $(".addClassNumFrom").Validform({
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
            beforeSubmit: function(curform) {
                var re=/\s/g;
                var classnum = $('.className').val().replace(re,"");
                curform.attr('action',loginUrlName + 'reg_class/' + classnum);
            }
        });
        _Form.addRule([{
                ele: ".className",
                datatype: sDataType.Classnum.datatype,
                nullmsg: sDataType.Classnum.nullmsg,
                errormsg: sDataType.Classnum.errormsg
            }            
        ]);
    };
});