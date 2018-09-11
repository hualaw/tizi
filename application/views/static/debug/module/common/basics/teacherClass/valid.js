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
            // {
            //     ele:"#schoolVal",
            //     datatype:"*",
            //     nullmsg:"请选择学校",
            //     errormsg:"选择错误！"
            // },
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
});