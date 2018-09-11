define(function(require, exports) {
    // 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    //cookie
    require('cookies');
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    
    exports.mobileLogin = function(callback_login){
        // 加载验证
        require("tizi_valid").indexLogin();
    }

    // 绑定直接进入方法
    exports.loginInPC = function(){
        //设置cookie
        $(".loginInPC").on("click", function(){
            var dataUrl = $(this).data("url");
            $.cookies.set("_mobile","0");
            if($.cookies.get("_mobile")==0){
                //location.href = dataUrl;
                window.location.reload();
            }
        });
    }

    exports.loginRefresh = function(){
        // if($('.loginAreaBefore').length > 0){
        //  require("tizi_login_form").checkLogin();
        // }
        if($('.loginAreaBefore').length > 0){
            if($.cookies.get(baseUnID) == null){
                $('.loginAreaBefore').removeClass('undis');
            }else{
                $('.loginAreaAfter').removeClass('undis');
            }
        }
    }

    //未注册学生加入班级 验证
    exports.addClassValid = function (){
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
            showAllError: false,
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
    }
});