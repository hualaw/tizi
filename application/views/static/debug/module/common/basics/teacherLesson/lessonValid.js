define(function(require, exports) {
    var sDataType = require("tizi_datatype").dataType();
    var lessonAjax = require("module/common/ajax/teacherLesson/lessonAjax");
    //转存文件
    exports.changeFile = function(){
        var _Form=$(".changeFileForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                lessonAjax.changeFile(data);
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
    //移动文件验证
    exports.moveFileTreeValid = function(){
        var _Form=$(".moveFileTreeForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                lessonAjax.moveFileTreeAjax(data);
            }
        });
        _Form.addRule([
            {
                ele:"select",
                datatype:"*",
                nullmsg:"请选择科目、版本和年级",
                errormsg:"请选择科目、版本和年级"
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
               lessonAjax.moveDirTreeAjax(data);
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
});