define("module/teacherCloud/cloud_doc_preview",["lib/artDialog/4.1.7/artDialog","lib/tizi_ajax/0.0.1/tizi_ajax","lib/tizi_common/0.0.1/tizi_download","cookies","tizi_ajax","tiziDialog"],function(require,exports){require("lib/artDialog/4.1.7/artDialog"),require("lib/tizi_ajax/0.0.1/tizi_ajax"),exports.shareDocDown=function(file_id){var source=$("#docnum").data("source"),verify_url="",download_url="";switch(source){case"cloud":verify_url=baseUrlName+"teacher/cloud/download_verify",download_url=baseUrlName+"teacher/cloud/download/?url=";break;case"student":verify_url=baseUrlName+"student/cloud/downverify",download_url=baseUrlName+"student/cloud/download?url=";break;default:return!1}$.tizi_ajax({url:verify_url,type:"POST",data:{file_id:file_id},dataType:"json",success:function(data){if(0==data.errorcode)$.tiziDialog({content:data.error});else{var Teadownload=require("lib/tizi_common/0.0.1/tizi_download"),share_id=$(".download_share").attr("share_id"),down_url=download_url+data.file_path+"&file_name="+data.file_name+"&file_id="+data.file_id+"&share_id="+share_id;Teadownload.force_download(down_url,data.fname,!0)}}})},exports.swfObjectInit=function(swfversion,jsversion){seajs.use(staticBaseUrlName+"flash/cloudFlash/swfobject",function(){var cloudFlashUrl=staticBaseUrlName+"flash/cloudFlash/",swfVersionStr="11.4.0",xiSwfUrlStr=cloudFlashUrl+"playerProductInstall.swf"+jsversion,flashvars={},params={},flash_height=exports.swfObjectHeight(),docExt=$("#docnum").data("ext");switch(docExt){case"doc":flash_height=720;break;case"docx":flash_height=720;break;case"ppt":flash_height=590;break;case"pptx":flash_height=590;break;default:flash_height=720}Teacher.UserCenter.docLib.swfObjectHeight=flash_height,params.quality="high",params.bgcolor="#ffffff",params.wmode="transparent",params.wmode="Opaque",params.allowscriptaccess="sameDomain",params.allowfullscreen="true";var attributes={};attributes.id="TeacherPreview",attributes.name="TeacherPreview",attributes.align="middle";var swfUrl=cloudFlashUrl+"TeacherPreview.swf"+swfversion;swfobject.embedSWF(swfUrl,"flashContent","1000",flash_height,swfVersionStr,xiSwfUrlStr,flashvars,params,attributes),swfobject.createCSS("#flashContent","display:block;text-align:left;")})},exports.swfObjectHeight=function(){var flash_height=$(window).height()-164;return 465>flash_height&&(flash_height=465),$("#flashContent").height(flash_height),flash_height}});