define("module/common/basics/teacherUnLogin/valid",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/Validform/5.3.2/Validform"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/Validform/5.3.2/Validform"),exports.teacherClassNum_add=function(){alert(1);var _Form=$(".addClassNumForm").Validform({tiptype:3,showAllError:!1,beforeSubmit:function(){},ajaxPost:!0,callback:function(data){require.async("module/common/ajax/teacherClass/classAjax",function(ex){ex.common(data)})}});_Form.addRule([{ele:"#set_year",datatype:"*",nullmsg:"请选择年份",errormsg:"选择错误！"},{ele:"#schoolVal",datatype:"*",nullmsg:"请选择学校",errormsg:"选择错误！"},{ele:".classNameInput",datatype:"*1-12",nullmsg:"请输入班级名称",errormsg:"长度1-12个字符"},{ele:".classNumInput",datatype:"*6-8",nullmsg:"请输入班级编号",errormsg:"长度6-8个字符"},{ele:".subjectInput",datatype:"*",nullmsg:"请选择学科",errormsg:"未选择学科"}])},exports.teacherClassNum_have=function(){var _Form=$(".haveClassNumForm").Validform({tiptype:3,showAllError:!1,beforeSubmit:function(){},ajaxPost:!0,callback:function(data){require.async("module/common/ajax/teacherClass/classAjax",function(ex){ex.common(data)})}});_Form.addRule([{ele:".classNumInput",datatype:"*6-8",nullmsg:"请输入班级编号",errormsg:"长度6-8个字符"},{ele:".subjectInput",datatype:"*",nullmsg:"请选择学科",errormsg:"未选择学科"}])}});