define(function(require, exports) {
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    var classAjax = require("module/common/ajax/teacherClass/classAjax");
    var teacherMd5 = require("module/common/basics/common/md5");
    // 修改班级名称
    exports.alterClassGrade = function(){
        var _Form=$(".alterClassGradePopForm").Validform({
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
            beforeSubmit:function(){
                $('.classId').val($('#class_grade').val());
            },
            callback:function(data){
                classAjax.alterClassGrade(data);
            }
        });
        _Form.addRule([
                {
                    ele:".classNameInput",
                    datatype: sDataType.Classname.datatype,
                    nullmsg: sDataType.Classname.nullmsg,
                    errormsg: sDataType.Classname.errormsg
                }
            ]
        );
    };
    // 创建新班级验证
    exports.creat = function(){
        var _Form=$(".creatNewClassForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,

            callback:function(data){
                classAjax.common(data);
                $.tiziDialog.list['addClassGradeId'].close();
                alert('close');
            }
        });
        _Form.addRule([
                {
                    ele:".selectSchool",
                    datatype:"*",
                    nullmsg:"请选择学校",
                    errormsg:"选择错误！"
                },
                {
                    ele:".selectClass",
                    datatype:"*",
                    nullmsg:"请选择年级",
                    errormsg:"选择错误！"
                },
                {
                    ele:".classNameInput",
                    datatype: sDataType.Classname.datatype,
                    nullmsg: sDataType.Classname.nullmsg,
                    errormsg: sDataType.Classname.errormsg
                },
                {
                    ele:"#teacher_subject",
                    datatype:"*",
                    nullmsg:"请选择授课科目",
                    errormsg:"选择错误！"
                }
            ]
        );
    };
    // 加入已有班级
    exports.invite = function(){
        var _Form=$(".inVislbleClassForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                classAjax.invite(data);
            }
        });
        _Form.addRule([
                {
                    ele:".in_vislble_class_text",
                    datatype: sDataType.Classnum.datatype,
                    nullmsg: sDataType.Classnum.nullmsg,
                    errormsg: sDataType.Classnum.errormsg
                },
                {
                    ele:"select",
                    datatype:"*",
                    nullmsg:"请选择授课科目",
                    errormsg:"选择错误！"
                }
            ]
        );
    };
    // 创建学生帐号
    exports.createStudent_new = function(class_id){
        var _Form=$(".createStudentName").Validform({
            // 3说明是在输入框右侧显示
            tiptype:function(msg,o,cssctl){
                if(!o.obj.is("form")){
                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();

                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    
                    var infoObj=o.obj.next('.ValidformTips');
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
                $('.classId').val($('#class_id').val());
                teacherMd5.md5(curform);
            },
            callback:function(data){
                classAjax.createStudent(data);
            }
        });
        _Form.addRule([
                {
                    ele:".studentNames_new",
                    datatype:"*",
                    nullmsg:"请输入姓名",
                    errormsg:"至少需要输入一个姓名！"
                }
            ]
        );
    };
    // 创建学生帐号
    exports.createStudentHasCount = function(class_id){
        var _Form = $(".createStudentHasCount").Validform({
            // 3说明是在输入框右侧显示
            tiptype:function(msg,o,cssctl){
                if(!o.obj.is("form")){
                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();

                    var objtip=o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    
                    var infoObj=o.obj.next('.ValidformTips');
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
                $('.classId').val($('#class_id').val());
                Common.comValidform.md5(curform);
            },
            callback:function(data){
                classAjax.createStudent(data);
            }
        });
        _Form.addRule([
                {
                    ele:".stuAccName",
                    datatype:"*",
                    nullmsg:"请输入账号",
                    errormsg:"请输入账号！"
                }
            ]
        );
    };
    // 解散班级验证
    exports.deleteClass = function(class_id){
        var _Form=$(".deleteClassPopForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(curform){
                $('.classId').val(class_id);
                teacherMd5.md5(curform);
            },
            callback:function(data){
                classAjax.common(data);
            }
        });
        _Form.addRule([
                {
                    ele:".in_vislble_class_text",
                    datatype:sDataType.Passwd.datatype,
                    nullmsg :sDataType.Passwd.nullmsg,
                    errormsg:sDataType.Passwd.errormsg
                }
            ]
        );
    };

});
