define(function(require, exports) {
	// 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    // 老师登录前加入班级验证
    exports.teacherClassNum_add = function(){
        var _Form = $(".addClassNumForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            beforeSubmit: function(curform) {
                // 加载MD5加密
                // require.async("tizi_validform",function(ex){
                //     ex.md5(curform);
                // });
            },
            ajaxPost: true,

            callback: function(data) {
                // 异步提交
                require.async("module/common/ajax/teacherClass/classAjax",function(ex){
                    ex.common(data);
                });
            }
        });
        _Form.addRule([
            {
                ele:"#set_year",
                datatype:"*",
                nullmsg:"请选择年份",
                errormsg:"选择错误！"
            },
            {
                ele:"#schoolVal",
                datatype:"*",
                nullmsg:"请选择学校",
                errormsg:"选择错误！"
            },
            {
                ele: ".classNameInput",
                datatype:"*1-12",
                nullmsg:"请输入班级名称",
                errormsg:"长度1-12个字符"
            },
            {
                ele: ".classNumInput",
                datatype:"*6-8",
                nullmsg:"请输入班级编号",
                errormsg:"长度6-8个字符"
            },
            {	
               	 ele:".subjectInput",
               	 datatype: "*",
     		  	 nullmsg: "请选择学科",
       			 errormsg: "未选择学科"

            }
            
        ]);
    };
    exports.teacherClassNum_have = function(){
        var _Form = $(".haveClassNumForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            beforeSubmit: function(curform) {
                // 加载MD5加密
                // require.async("tizi_validform",function(ex){
                //     ex.md5(curform);
                // });
            },
            ajaxPost: true,

            callback: function(data) {
                // 异步提交
                require.async("module/common/ajax/teacherClass/classAjax",function(ex){
                    ex.common(data);
                });
            }
        });
        _Form.addRule([{
                ele: ".classNumInput",
                datatype:"*6-8",
                nullmsg:"请输入班级编号",
                errormsg:"长度6-8个字符"
            },
            {	
               	 ele:".subjectInput",
               	 datatype: "*",
     		  	 nullmsg: "请选择学科",
       			 errormsg: "未选择学科"

            }
            
        ]);
    };
    // 教师端纠错功能验证
    // 页面打开加载验证码
    require('tizi_validform').changeCaptcha('fbquestionBox');
    require('tizi_validform').bindChangeVerify('fbquestionBox');
    exports.addPaperHaveErrorValid = function(){
        var that = this;
        //添加表单验证
        var _Form=$(".haveErrorForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){
                var picture_urls = '';
                //处理图片链接
                $(".picture_urls").each(function(){
                    picture_urls += picture_urls === '' ? '' : ',';
                    picture_urls += $(this).attr('src');
                });
                $("#picture_urls").val(picture_urls);
                /*调用验证码验证服务端信息*/
                return require('tizi_validform').checkCaptcha('fbquestionBox',1);
            },
            callback:function(data){
                //调用ajax处理
                require.async("module/common/ajax/feedbackQuestion/feedbackQuestionAjax",function(ex){
                    ex.addPaperHaveErrorAjax(data);
                });
            }
        });
        _Form.addRule([
            {
                ele:".lite_textarea",
                datatype:"*",
                nullmsg:"错误描述不能为空",
                errormsg:"错误描述不能为空"
            },
            {
                ele:":checkbox:first",
                datatype:"*",
                nullmsg:"错误类型不能为空",
                errormsg:"错误类型不能为空"
            }
        ]);
    }
});