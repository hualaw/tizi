define(function(require, exports) {
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    require('validForm');
    var resourceAjax = require("module/common/ajax/teacherResource/resourceAjax");
    // 新建同步章节验证
    exports.chapterIndexValid = function(){
        var havenovIndexValid = $(".chapterIndexBoxForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data) {
                resourceAjax.chapterIndexAjax(data);
            }
        });
        havenovIndexValid.addRule([
            {
                ele:".semester",
                datatype:"*",
                nullmsg:"请选择学段",
                errormsg:"请选择学段"
            },
            {
                ele:".subject",
                datatype:"*",
                nullmsg:"请选择学科",
                errormsg:"请选择学科"
            },
            {
                ele:".subversion",
                datatype:"*",
                nullmsg:"请选择版本",
                errormsg:"请选择版本"
            },
            {
                ele:".grade",
                datatype:"*",
                nullmsg:"请选择年级",
                errormsg:"请选择年级"
            }
        ]);
    }
    // 新建同步知识点验证
    exports.loreIndexValid = function(){
        var loreIndexValid = $(".loreIndexBoxForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data) {
                resourceAjax.loreIndexAjax(data);
            }
        });
        loreIndexValid.addRule([
            {
                ele:".semester",
                datatype:"*",
                nullmsg:"请选择学段",
                errormsg:"请选择学段"
            },
            {
                ele:".subject",
                datatype:"*",
                nullmsg:"请选择学科",
                errormsg:"请选择学科"
            }
        ]);
    }
    // 没有我的教材验证
    exports.havenovIndexValid = function(){
        var havenovIndexValid = $(".havenovIndexBoxForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){
                var phone = $(".havenovIndexBox .phone").val();
                var qqCode = $(".havenovIndexBox .qqCode").val();
                if(phone=="" && qqCode==""){
                    $.tiziDialog({content:'请填写QQ号码或联系电话信息'});
                    return false;
                }
            },
            callback:function(data) {
                resourceAjax.havenovIndexAjax(data);
            }
        });
        havenovIndexValid.addRule([
            {
                ele:".version",
                datatype:"*",
                nullmsg:"请填写教材名称",
                errormsg:"请填写教材名称"
            }
        ]);
    }

    //新建文件夹
    exports.creat = function(){
        var _Form=$(".creatNewFileForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                resourceAjax.creat(data);
            }
        });
        _Form.addRule([
            {
                ele:"#create_fileName",
                datatype : "*1-50",
                nullmsg  : "请输入文件夹名称",
                errormsg : "长度1-50字符之间"
            }
        ]
        );
    };
    //分享文件
    exports.shareFile = function(){
        var _Form=$(".shareFliesHasClassForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                resourceAjax.shareFile(data);
            }
        });
        _Form.addRule([
            {
                ele:":checkbox:last",
                datatype:"*",
                nullmsg :"请选择班级",
                errormsg:"选择错误！"
            }
        ]
        );
    };
    //重命名文件
    exports.resetFile = function(){
        var _Form=$(".resetFileNameForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                resourceAjax.resetFile(data);
            }
        });
        _Form.addRule([
            {
                ele:"#resetFileNameTxt",
                datatype : "*1-50",
                nullmsg  : "请输入文件名称",
                errormsg : "长度1-50字符之间"
            }
        ]
        );
    };
});