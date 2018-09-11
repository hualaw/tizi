define(function(require, exports) {
    var sDataType = require("tizi_datatype").dataType();
    var cloudAjax = require("module/common/ajax/teacherCloud/cloudAjax");
    //新建文件夹
    exports.creat = function(){
        var _Form=$(".creatNewFileForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                cloudAjax.creat(data);
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
                cloudAjax.shareFile(data);
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
                cloudAjax.resetFile(data);
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
    //移动文件验证
    exports.moveFileTreeValid = function(){
        var _Form=$(".moveFileTreeForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                cloudAjax.moveFileTreeAjax(data);
            }
        });
        _Form.addRule([
            {
                ele:".dir_id",
                datatype:"*",
                nullmsg:"请选择文件夹",
                errormsg:"请选择文件夹"
            },
            {
                ele:".to_dir_id",
                datatype:"*",
                nullmsg:"请选择目录",
                errormsg:"请选择目录"
            },
            {
                ele:".to_type",
                datatype:"*",
                nullmsg:"请选择分类",
                errormsg:"请选择分类"
            }
        ]);
    }
    //移动文件夹验证
    exports.moveDirTreeValid = function(){
        var _Form=$(".moveDirTreeForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
               cloudAjax.moveDirTreeAjax(data);
            }
        });
        _Form.addRule([
            {
                ele:".cat_id",
                datatype:"*",
                nullmsg:"请选择文件夹",
                errormsg:"请选择文件夹"
            },
            {
                ele:".to_dir_id",
                datatype:"*",
                nullmsg:"请选择目录",
                errormsg:"请选择目录"
            }
        ]);
    }
    //共享文件
    exports.shareAllValid = function(){
        var _Form=$(".sharAllFileForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){
                if(!$("#checkshare").attr('checked')){
                    $.tiziDialog({content:"请先同意协议"});
                    return false;
                }
            },
            callback:function(data){
                cloudAjax.shareAllAjax(data);
            }
        });
    }
});