//  我的题库
define(function(require, exports) {
	// 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    var _validAjax = require("module/common/ajax/teacherTestLibrary/validAjax");
    // 添加分组验证
    exports.addNewGroupValid = function(){
        var _Form=$(".addDocForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){},
            callback:function(data){
                //调用ajax处理
                _validAjax.addNewGroupValidAjax(data);
            }
        });
        _Form.addRule([
            {
                ele:".group_name",
                datatype:"*2-20",
                nullmsg:"分组名称不能为空",
                errormsg:"请输入2-20个字符"
            }
        ]);
    };
    // 编辑分组验证
    exports.EditNewGroupValid=function(that){
        //添加表单验证
        var _Form=$(".editDocForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){},
            callback:function(data){
                //调用ajax处理
                _validAjax.EditNewGroupValidAjax(data,that);
            }
        });
        _Form.addRule([
            {
                ele:".group_name",
                datatype:"*2-20",
                nullmsg:"分组名称不能为空",
                errormsg:"请输入2-20个字符"
            }
        ]);
    }
    // 添加上传新题验证
    exports.uploadQuesValid = {
        init:function(){
            this.myQuestion();
        },
        myQuestion:function(){
            var _Form=$(".form_qList").Validform({
                // 3说明是在输入框右侧显示
                tiptype:3,
                showAllError:false,
                ajaxPost:true,
                beforeSubmit:function(curform){

                },
                callback:function(data){
                    _validAjax.uploadQuesAjax.myQuestion(data);
                }
            });
            _Form.addRule(
                [
                    {
                        ele:".s_grade",
                        datatype:"*",
                        nullmsg:"请选择学段"
                    },
                    {
                        ele:".s_subject",
                        datatype:"*",
                        nullmsg:"请选择学科"
                    },
                    {
                        ele:".s_qtype",
                        datatype:"*",
                        nullmsg:"请选择题型"
                    },
                    {
                        ele:".source",
                        datatype:"*",
                        nullmsg:"请输入题目/来源"
                    }
                ]
            );
        },
        //  老师端个人中心上传试题 添加分组验证v20140114
        myGroupTipForm:function(obj){
            var _Form=$(".myGroupTipForm").Validform({
                // 3说明是在输入框右侧显示
                tiptype:3,
                showAllError:false,
                ajaxPost:true,
                beforeSubmit:function(curform){

                },
                callback:function(data){
                    _validAjax.uploadQuesAjax.myGroupTipForm(data,obj);

                }
            });
            _Form.config({
                tiptype:3
            });
            _Form.addRule(
                [
                    {
                        ele:".s_newSubject",
                        datatype:"*",
                        nullmsg:"请选择学科"
                    },
                    {
                        ele:".s_GroupName",
                        datatype:"*1-20",
                        nullmsg:"请选择分组名称",
                        errormsg:"长度1-20字符之间"
                    }
                ]
            );
        }
    }
    // 我的题库题目分组v20140512
    exports.GroupQuesValid = function(){
        var _Form=$(".GroupQuesForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){},
            callback:function(data){
                //调用ajax处理
                _validAjax.GroupQuesValidAjax(data);
            }
        });
        _Form.addRule([
            {
                ele:".csub-list",
                datatype:"*",
                nullmsg:"请选择学科"
            },
            {
                ele:".s_newGroup",
                datatype:"*",
                nullmsg:"请选择分组",
                errormsg:"请选择分组"
            }
        ]);
    };

});