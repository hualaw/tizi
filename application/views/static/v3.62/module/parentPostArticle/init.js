define("module/parentPostArticle/init",["module/parentPostArticle/vaild","tizi_ajax","validForm","tizi_datatype"],function(require){require("module/parentPostArticle/vaild").submitPost(),seajs.use("kindEditor",function(){KindEditor.ready(function(K){K.create("#postEditor",{items:["fontname","fontsize","|","forecolor","hilitecolor","bold","italic","underline","removeformat","|","justifyleft","justifycenter","justifyright","insertorderedlist","insertunorderedlist","|","emoticons","image","link"],afterChange:function(){K(".word_count2").html(1e4-this.count("text")),$(".word_count1").html(KindEditor.instances[0].html().length)}})})})}),define("module/parentPostArticle/vaild",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/Validform/5.3.2/Validform","lib/tizi_common/0.0.1/tizi_datatype"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/Validform/5.3.2/Validform");var sDataType=require("lib/tizi_common/0.0.1/tizi_datatype").dataType();exports.submitPost=function(){var _Form=$(".submitPost").Validform({tiptype:3,ajaxPost:!0,showAllError:!1,beforeSubmit:function(){return void 0==$(".postEditor").val()?(alert("请输入内容"),!1):void 0},callback:function(){}});_Form.addRule([{ele:".postTitle",datatype:"*2-40",nullmsg:"标题不能为空",errormsg:"请输入标题"},{ele:".postType",datatype:"*",nullmsg:"请选择投稿类型",errormsg:"未选择投稿类型"},{ele:".postName",datatype:sDataType.Uname.datatype,nullmsg:sDataType.Uname.nullmsg,errormsg:sDataType.Uname.errormsg},{ele:".postPhone",datatype:sDataType.Phone.datatype,nullmsg:sDataType.Phone.nullmsg,errormsg:sDataType.Phone.errormsg},{ele:".postEmail",datatype:sDataType.Email.datatype,nullmsg:sDataType.Email.nullmsg,errormsg:sDataType.Email.errormsg},{ele:".qq",datatype:sDataType.QQ.datatype,nullmsg:sDataType.QQ.nullmsg,errormsg:sDataType.QQ.errormsg},{ele:".postEditor",datatype:"*",nullmsg:"内容不能为空",errormsg:"请输入内容"}])}});