define("module/studentMyTeacher/init",["module/common/basics/common/highlight","module/common/method/common/height","module/common/basics/common/valid","tizi_ajax","validForm"],function(require){require("module/common/basics/common/highlight").highlightMenu(),require("module/common/method/common/height").leftMenuBg(),require("module/common/basics/common/valid").studentAddClass()}),define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}}),define("module/common/method/common/height",[],function(require,exports){exports.leftMenuBg=function(){var _wrapContentHeight=$("#wrapContent").height(),_slideHeight=$("#slide").height();_wrapContentHeight>_slideHeight?$("#slide").height(_wrapContentHeight):$("#wrapContent").height(_slideHeight)}}),define("module/common/basics/common/valid",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/Validform/5.3.2/Validform"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/Validform/5.3.2/Validform"),exports.teacherClassNum_add=function(){var _Form=$(".addClassNumForm").Validform({tiptype:3,showAllError:!1,beforeSubmit:function(){},ajaxPost:!0,callback:function(data){require.async("module/common/ajax/teacherClass/classAjax",function(ex){ex.common(data)})}});_Form.addRule([{ele:".classNumInput",datatype:"*6-8",nullmsg:"请输入班级编号",errormsg:"长度6-8个字符"},{ele:".subjectInput",datatype:"*",nullmsg:"请选择学科",errormsg:"未选择学科"}])},exports.teacherClassNum_have=function(){var _Form=$(".haveClassNumForm").Validform({tiptype:3,showAllError:!1,beforeSubmit:function(){},ajaxPost:!0,callback:function(data){require.async("module/common/ajax/teacherClass/classAjax",function(ex){ex.common(data)})}});_Form.addRule([{ele:".classNumInput",datatype:"*6-8",nullmsg:"请输入班级编号",errormsg:"长度6-8个字符"},{ele:".subjectInput",datatype:"*",nullmsg:"请选择学科",errormsg:"未选择学科"}])},exports.studentAddClass=function(){var _Form=$(".studentAddClassForm").Validform({tiptype:function(msg,o){if(!o.obj.is("form")){var objtip=o.obj.next().find(".Validform_checktip");objtip.text(msg),o.obj.next().show();var objtip=o.obj.next().find(".Validform_checktip");objtip.text(msg);var infoObj=o.obj.next(".ValidformTips");2==o.type&&(infoObj.show(),o.obj.next().hide())}},showAllError:!1,beforeSubmit:function(curform){require.async("tizi_validform",function(ex){ex.md5(curform)})},ajaxPost:!0,callback:function(data){require.async("module/common/ajax/studentCommon/ajax",function(ex){ex.studentAddClass(data)})}});_Form.addRule([{ele:".classNum",datatype:"*6-8",nullmsg:"请输入班级编号",errormsg:"长度6-8个字符"}])}});