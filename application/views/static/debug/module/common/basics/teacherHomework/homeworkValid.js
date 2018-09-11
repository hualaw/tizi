define(function(require, exports) {
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    require('validForm');
    var homeworkAjax = require("module/common/ajax/teacherHomework/homeworkAjax");
    // 布置试卷验证
    exports.setHomeworkVid = function(){
        var varFeedbackCheck = $(".setHomeworkForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){            
                
            },
            callback:function(data) {
                homeworkAjax.assign_paper(data);
            }
        });
        varFeedbackCheck.addRule([
            {
                ele:":checkbox:last",
                datatype:"*",
                nullmsg:"请选择班级",
                errormsg:"选择错误！"
            },
            {
                ele:":radio[name=answerStyle]:last",
                datatype:"*",
                nullmsg:"请选择作答方式",
                errormsg:"选择错误！"
            }
            ,
            {
                ele:":radio[name=answerOrder]:last",
                datatype:"*",
                nullmsg:"请选择答题顺序",
                errormsg:"选择错误！"
            }
        ]);            

    };
});