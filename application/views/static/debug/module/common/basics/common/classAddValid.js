define(function(require, exports) {
    require('validForm');
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    var classAjax = require("module/common/ajax/teacherClass/classAjax");
    var teacherMd5 = require("module/common/basics/common/md5");
    exports.Validform_checktip = function(msg,o,cssctl){
        if(!o.obj.is("form")){
            var objtip=o.obj.next().find(".Validform_checktip");
            objtip.text(msg);
            o.obj.next().show();

            var objtip=o.obj.next().find(".Validform_checktip");
            objtip.text(msg);
            
            var infoObj=o.obj.next('.ValidformInfo');
            // 判断验证成功
            if(o.type==2){
                infoObj.show();
                o.obj.next().hide();
            }
        }
    };
    var ValidformTip = null;
    $('.join_class').length > 0 ? ValidformTip = 3 : ValidformTip = exports.Validform_checktip;
    // 创建新班级验证
    exports.creat = function(){
        // 鼠标离开输入框的时候恢复默认状态
        $('.creatNewClassForm input').each(function(){
            var _this = $(this);
            $(this).blur(function(){
                if(_this.val() == ''){
                    $('.ValidformInfo').hide();
                    _this.removeClass('Validform_error');
                    _this.next('.Validform_checktip').hide();
                }
            });
        });
        var _Form=$(".creatNewClassForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:function(msg,o,cssctl){
                if(!o.obj.is("form")){
                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();

                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    
                    var infoObj=o.obj.next('.ValidformInfo');
                    // 判断验证成功
                    if(o.type==2){
                        infoObj.show();
                        o.obj.next().hide();
                    }
                }
            },
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(curform){
                if(curform.find('.classNameInput').val() == ''){
                    curform.find('.classNameInput').next('.ValidformInfo').show().find('.Validform_checktip').html(sDataType.Classname.nullmsg);
                    curform.find('.classNameInput').addClass('Validform_error').focus();
                    return false;
                };
                // if (!$(".theGenusScholl_n").hasClass("undis")) {                
                //     if ($(".schoolsType_beta").val()==0) {
                //         $.tiziDialog({
                //              content:"请选择学校类型"
                //         });
                //         return false
                //     }
                //     if ($(".schollName").attr("value")=="") {
                //         $.tiziDialog({
                //              content:"请输入学校名称"
                //         });
                //         return false
                //     };                    
                //    $("#schoolVal").attr("value",-1) 
                // }
            },         

            callback:function(data){
                classAjax.common(data);
            }
        });
        _Form.addRule([
                // {
                //     ele:".selectSchool",
                //     datatype:"*",
                //     nullmsg:"请选择学校",
                //     errormsg:"选择错误！"
                // },
                // {
                //     ele:".selectClass",
                //     datatype:"*",
                //     nullmsg:"请选择年级",
                //     errormsg:"选择错误！"
                // },
                {
                    ele:".classNameInput",
                    ignore:"ignore",
                    datatype: sDataType.Classname.datatype,
                    nullmsg: sDataType.Classname.nullmsg,
                    errormsg: sDataType.Classname.errormsg
                }
                // ,
                // {
                //     ele:".teacher_subject",
                //     datatype:"*",
                //     nullmsg:"请选择授课科目",
                //     errormsg:"选择错误！"
                // }
            ]
        );
    };
    // 加入已有班级
    exports.invite = function(){
        // 鼠标离开输入框的时候恢复默认状态
        $('.inVislbleClassForm input').each(function(){
            var _this = $(this);
            $(this).blur(function(){
                if(_this.val() == ''){
                    $('.ValidformInfo').hide();
                    _this.removeClass('Validform_error');
                    _this.next('.Validform_checktip').hide();
                }
            });
        });
        var _Form=$(".inVislbleClassForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:function(msg,o,cssctl){
                if(!o.obj.is("form")){
                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();

                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    
                    var infoObj=o.obj.next('.ValidformInfo');
                    // 判断验证成功
                    if(o.type==2){
                        infoObj.show();
                        o.obj.next().hide();
                    }
                }
            },
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(curform){
                if(curform.find('.in_vislble_class_text').val() == ''){
                    curform.find('.in_vislble_class_text').next('.ValidformInfo').show().find('.Validform_checktip').html(sDataType.Classname.nullmsg);
                    curform.find('.in_vislble_class_text').addClass('Validform_error').focus();
                    return false;
                }
            },
            callback:function(data){
                classAjax.invite(data);
            }
        });
        _Form.addRule([
                {
                    ele:".in_vislble_class_text",
                    ignore:"ignore",
                    datatype: sDataType.Classnum.datatype,
                    nullmsg: sDataType.Classnum.nullmsg,
                    errormsg: sDataType.Classnum.errormsg
                }
                // ,
                // {
                //     ele:".teacher_subject",
                //     datatype:"*",
                //     nullmsg:"请选择授课科目",
                //     errormsg:"选择错误！"
                // }
            ]
        );
    };
});
