define(function(require, exports) {
	// 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();
    // 教师端纠错功能验证
    require('tizi_validform').changeCaptcha('fbquestionBox');
    require('tizi_validform').bindChangeVerify('fbquestionBox');
    exports.addPaperHaveErrorValid = function(){
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
                require("module/common/ajax/feedback/feedbackQuestion/feedbackQuestionAjax").addPaperHaveErrorAjax(data);
            }
        });
        _Form.addRule([
            {
                // 检测反馈内容
                ele:".lite_textarea",
                datatype:"*5-1000",
                nullmsg:"请填写反馈内容！",
                errormsg:"反馈内容5-1000个字符之间！"
            },
            {
                ele:":checkbox:first",
                datatype:"*",
                nullmsg:"错误类型不能为空",
                errormsg:"错误类型不能为空"
            },
            {
                // 检测验证码
                ele:".textCaptcha",
                datatype:sDataType.CaptchaCode.datatype,
                nullmsg:sDataType.CaptchaCode.nullmsg,
                errormsg:sDataType.CaptchaCode.errormsg
            }
        ]);
    }
});