define("module/teacherCloud/init",["module/teacherCloud/cloud","validForm","tizi_ajax","json2","mustache","module/common/basics/teacherCloud/cloudValid","tizi_download","module/common/method/common/height"],function(require){var _cloud=require("module/teacherCloud/cloud");_cloud.dropBoxFieldFn(),_cloud.dropBoxDialog(),_cloud.into_dir(),_cloud.go_back_dir(),_cloud.moveFileFn(),_cloud.reCloudHome(),_cloud.specialWordLimt(),$(function(){_cloud.tableStyleFn()}),require("module/common/method/common/height").leftMenuBg()}),define("module/teacherCloud/cloud",["lib/Validform/5.3.2/Validform","lib/tizi_ajax/0.0.1/tizi_ajax","lib/json2/0.0.1/json2","lib/mustache/0.5.0/mustache","module/common/basics/teacherCloud/cloudValid","tizi_datatype","module/common/ajax/teacherCloud/cloudAjax","lib/tizi_common/0.0.1/tizi_download","cookies","tizi_ajax","tiziDialog"],function(require,exports){require("lib/Validform/5.3.2/Validform"),require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/json2/0.0.1/json2"),require("lib/mustache/0.5.0/mustache");var cloudValid=require("module/common/basics/teacherCloud/cloudValid"),Teadownload=require("lib/tizi_common/0.0.1/tizi_download");exports.dropBoxFieldFn=function(){seajs.use(["flashUploader","cookies"],function(){var statusDialog,cFileObj=$(".current_dir_id"),cur_dir_id=cFileObj.val(),flag=!1,queueJsonStr="",queueNum=0,docExt="*.doc;*.docx;*.ppt;*.pptx;*.xls;*.xlsx;*.wps;*.et;*.dps;*.pdf;*.txt;",allowType="*.*";$("#shareFileUp").uploadify({swf:staticBaseUrlName+staticVersion+"lib/uploadify/2.2/uploadify.swf",uploader:"http://up.qiniu.com",buttonClass:"choseFileBtn",button_image_url:baseUrlName+"quit",buttonText:"上传文件",fileTypeExts:allowType,fileSizeLimit:"200MB",fileObjName:"file",multi:!0,width:110,height:35,preventCaching:!0,successTimeout:7200,uploadLimit:10,overrideEvents:["onSelectError","onDialogClose","onUploadProgress","onCancel","onUploadSuccess"],onSWFReady:function(){},onDialogClose:function(queueData){queueNum=queueData.queueLength},onSelect:function(file){$("#shareFileUp-queue").hide();var cFileName=cFileObj.data("fname");cFileName||(cFileName="");var praseName=file.name;praseName.length>30&&(praseName=praseName.substring(0,30)+"...");var praseSize=exports.praseFileSize(file.size);queueJsonStr+='{"queue_id":'+file.index+',"file_id":"'+file.id+'","file_name":"'+praseName+'","size":"'+praseSize+'","cdir":"'+cFileName+'","status":'+file.filestatus+"},"},onFallback:function(){$(".choseFile").html(noflash),$("#upDropFile").hide()},onSelectError:function(file,errorCode){switch(errorCode){case-100:$(".choseFile").find(".error").remove(),$.tiziDialog({content:"每次最多上传10份文档"});break;case-110:$(".choseFile").find(".error").remove(),$.tiziDialog({content:"文件 ["+file.name+"] 过大！每份文档不能超过200M"});break;case-120:$(".choseFile").find(".error").remove(),$.tiziDialog({content:"文件 ["+file.name+"] 大小异常！不可以上传大小为0的文件"});break;case-130:$(".choseFile").find(".error").remove(),$.tiziDialog({content:"文件 ["+file.name+"] 类型不正确！不可以上传错误的文件格式"})}return!1},onUploadStart:function(file){if(-1!=docExt.indexOf(file.type)){var formData=$.tizi_token({cur_dir_id:cur_dir_id,session_id:$.cookies.get(baseSessID)},"post");$("#shareFileUp").uploadify("settings","formData",formData),$("#shareFileUp").uploadify("settings","uploader",baseUrlName+"upload/cloud"),$("#shareFileUp").uploadify("settings","fileObjName","shareFileUp")}else{var oldFileName=file.name,fileSize=file.size,fileKey="",fileToken="";$.tizi_ajax({url:baseUrlName+"cloud/cloud/ajax_get_token_key",type:"POST",dataType:"json",data:{file_ext:file.type,file_size:file.size},async:!1,success:function(data){return 1!=data.error_code?($.tiziDialog({content:data.error}),!1):(fileKey=data.file_key,fileToken=data.file_token,void 0)},error:function(){$.tiziDialog({content:"系统忙，请稍后再试"})}}),$("#shareFileUp").uploadify("settings","fileObjName","file"),$("#shareFileUp").uploadify("settings","uploader","http://up.qiniu.com");var formData={token:fileToken,key:fileKey,cur_dir_id:cur_dir_id,old_name:oldFileName,file_size:fileSize};$("#shareFileUp").uploadify("settings","formData",formData)}if(flag===!1&&""!=queueJsonStr){queueJsonStr=queueJsonStr.substring(0,queueJsonStr.length-1),queueJsonStr='{"files":['+queueJsonStr+"]}";var myobj=JSON.parse(queueJsonStr),listTemp=$("#upFilePop").html(),alertContent=Mustache.to_html(listTemp,myobj);statusDialog=$.tiziDialog({id:"",title:"上传文件",content:alertContent,icon:null,width:600,ok:!1,cancel:!1}),flag=!0}},onUploadProgress:function(file,bytesUploaded,bytesTotal){var cFileIndex=file.index,cFileObj=$("#file_num_"+cFileIndex);if(bytesUploaded>0&&bytesTotal>bytesUploaded){var percentage=bytesUploaded/bytesTotal*100;percentage=percentage.toFixed(1),95>percentage&&(cFileObj.find("strong").css({width:percentage+"%"}),cFileObj.find(".complete").html(percentage+"%"),cFileObj.find(".UploadCancel").html("x"))}bytesUploaded==bytesTotal&&(cFileObj.find(".UploadCancel").html(""),cFileObj.find(".UploadCancel").attr("href","javascript:;"))},onCancel:function(file){var cFileIndex=file.index,cFileObj=$("#file_num_"+cFileIndex);cFileObj.remove(),1==queueNum&&(flag=!1,queueJsonStr="",statusDialog.close())},onUploadSuccess:function(file,data){queueJsonStr="";var cFileIndex=file.index,cFileObj=$("#file_num_"+cFileIndex),json=JSON.parse(data);json.key?$.tizi_ajax({url:baseUrlName+"cloud/cloud/qiniu_upload",type:"POST",dataType:"json",data:{cur_dir_id:cur_dir_id,key:json.key,file_name:file.name,file_size:file.size},success:function(data){1==data.error_code?(cFileObj.find(".complete").html("100%"),cFileObj.find("strong").css({width:"100%"}),1==queueNum?(flag=!1,setTimeout(function(){window.top.location.reload()},2e3)):queueNum>1&&cFileIndex==queueNum-1&&(flag=!1,setTimeout(function(){window.top.location.reload()},2e3))):(cFileObj.find(".complete").css("color","red"),cFileObj.find(".complete").html(data.error))},error:function(){$.tiziDialog({content:"系统忙，请稍后再试"})}}):json.code?1==json.code?(cFileObj.find(".complete").html("100%"),cFileObj.find("strong").css({width:"100%"}),1==queueNum?(flag=!1,setTimeout(function(){window.top.location.reload()},2e3)):queueNum>1&&cFileIndex==queueNum-1&&(flag=!1,setTimeout(function(){window.top.location.reload()},2e3))):-6==json.code?$.tiziDialog({content:json.msg,time:3}):(cFileObj.find(".complete").css("color","red"),cFileObj.find(".complete").html("上传失败")):(cFileObj.find(".complete").css("color","red"),cFileObj.find(".complete").html("上传失败"))},onUploadError:function(file,errorCode,errorMsg,errorString){exports.uploadError(file,errorCode,errorMsg,errorString,"cloud_upload")},onQueueComplete:function(queueData){queueData.uploadsSuccessful<queueData.filesSelected&&1!=queueData.filesSelected&&(flag=!1,setTimeout(function(){window.top.location.reload()},2e3))}}),$("#shareFileUp object").css({left:"0px"})})},exports.uploadError=function(file,errorCode,errorMsg,errorString,source){$.tizi_ajax({url:baseUrlName+"cloud/cloud/upload_error",type:"POST",dataType:"json",data:{file_index:file.index,file_name:file.name,errorCode:errorCode,errorMsg:errorMsg,errorString:errorString,source:source},success:function(){}})},exports.praseFileSize=function(size){for(var mod=1024,units=["B","KB","MB"],i=0;size>mod;i++)size/=mod;return size.toFixed(2)+" "+units[i]},exports.dropBoxDialog=function(){seajs.use(["tiziDialog","tizi_ajax"],function(){$("#createNewFileBtn").live("click",function(){var cur_dir_id=$(".current_dir_id").val();$(".create_folder").val(cur_dir_id),$.tiziDialog({id:"create_new_fileId",title:"新建文件夹",content:$("#creatNewFlie").html().replace("creatNewFileForm_beta","creatNewFileForm"),icon:null,width:600,ok:function(){return $(".creatNewFileForm").submit(),!1},cancel:!0}),cloudValid.creat()}),$(".downloadFile").live("click",function(){var file_name=$(this).attr("file_name"),file_id=$(this).attr("file_id");$.tizi_ajax({url:baseUrlName+"teacher/cloud/download_verify",type:"POST",data:{file_id:file_id,file_name:file_name},dataType:"json",success:function(data){if(0==data.errorcode)$.tiziDialog({content:data.error});else if(1==data.file_type){var down_url=baseUrlName+"teacher/cloud/download/?url="+data.file_path+"&file_name="+data.file_name+"&file_id="+data.file_id;Teadownload.force_download(down_url,data.fname,!0)}else Teadownload.force_download(data.url,data.fname,!0,!0)}})}),$(".shareFile").live("click",function(){$(".has_class").val();$(".shareFliesHasClassForm_beta").length>0?($(".get_file_id").val($(this).attr("file_id")),$.tiziDialog({id:"shareFile_id",title:"分享文件",content:$("#shareFliesBox_hasClass").html().replace("shareFliesHasClassForm_beta","shareFliesHasClassForm"),icon:null,width:600,ok:function(){return $(".shareFliesHasClassForm").submit(),!1},cancel:!0}),cloudValid.shareFile()):$.tiziDialog({id:"",title:"分享文件",content:$("#shareFliesBox_noClass").html(),icon:null,width:600,ok:function(){},cancel:!0})}),$(".moveFlie").live("click",function(){var resource_id=$(this).attr("moved-resource-id"),is_file=$(this).attr("is_file");$(".tree-title").each(function(){0==$(this).attr("dir-id")&&($(".folderList").eq(0).show(),$(this).addClass("tree-title-add"))}),$.tiziDialog({id:"",title:"请选择目标文件夹",content:$("#moveFilePop").html().replace("moveFileForm_beta","moveFileForm"),icon:null,width:600,ok:function(){var to_dir_id=$(".tree-title-add").attr("dir-id");$.tizi_ajax({url:baseUrlName+"teacher/cloud/move",type:"POST",dataType:"json",data:{resource_id:resource_id,to_dir_id:to_dir_id,is_file:is_file},success:function(json){1==json.errorcode?window.location.reload():$.tiziDialog({content:json.error})},error:function(){}})},close:function(){},cancel:!0})}),$(".resetFlieName").live("click",function(){$("#resetFileNameTxt").attr("value",$(this).attr("file_name")),$(".is_file").val($(this).attr("is_file")),$(".rename_id").val($(this).attr("file_id")),$.tiziDialog({id:"resetFile_id",title:"重命名文件",content:$("#resetFileNamePop").html().replace("resetFileNameForm_beta","resetFileNameForm"),icon:null,width:600,ok:function(){return $(".resetFileNameForm").submit(),!1},cancel:!0}),cloudValid.resetFile()}),$(".deleteFile").live("click",function(){var file_id=$(this).attr("file_id"),is_file=$(this).attr("is_file"),node=$(this);if(1==is_file)var content="是否删除该文件?",title="删除文件";else var content="是否删除该文件夹?（注意！文件夹下的文件也会被删除！）",title="删除文件夹";$.tiziDialog({title:title,content:content,icon:"question",width:400,ok:function(){$.tizi_ajax({url:baseUrlName+"teacher/cloud/del",type:"POST",dataType:"json",data:{is_file:is_file,file_id:file_id},success:function(json){1==json.errorcode?$.tiziDialog({content:json.error,icon:"succeed",ok:function(){node.parent().parent().remove();var student_total=$("#student_total").html();$("#student_total").html(student_total-1)}}):$.tiziDialog({content:json.error})},error:function(){$.tiziDialog({content:"系统忙，请稍后再试"})}})},cancel:!0})}),$("#upFileBeta").live("click",function(){$.tiziDialog({id:"",title:"上传文件（0/4）",content:$("#upFilePop").html().replace("upFilePopForm_beta","upFilePopForm"),icon:null,width:600,ok:function(){return $(".upFilePopForm").submit(),!1},cancel:!0})})})},exports.reHome=function(ele){var toUrl=$(ele).data("tourl");$.cookies.get("_mdir")&&$.cookies.set("_mdir",0,{domain:".tizi.com",path:"/"}),window.top.location.href=toUrl},exports.showKeyPress=function(evt){return evt=evt?evt:window.event,exports.checkSpecificKey(evt.keyCode)},exports.checkSpecificKey=function(keyCode){var specialKey='<>/:*?|\\"^+',realkey=String.fromCharCode(keyCode),flg=!1;return flg=specialKey.indexOf(realkey)>=0,flg?!1:!0},exports.into_dir=function(){$(".into_dir").live("click",function(){var current_dir_id=$(this).attr("data-dir-id");$.tizi_ajax({url:baseUrlName+"teacher/cloud/dir/"+current_dir_id,type:"GET",dataType:"json",success:function(json){1==json.errorcode?($(".cloud_file_list").html(json.html),exports.dropBoxFieldFn(),exports.tableStyleFn()):void 0!==typeof json.href_to?window.top.location.href=baseUrlName+json.href_to:$.tiziDialog({content:"系统忙，请稍后再试"})},error:function(){$.tiziDialog({content:"系统忙，请稍后再试"})}})})},exports.go_back_dir=function(){$(".go_back_dir").live("click",function(){var current_dir_id=$(this).attr("data-dir-id");$.tizi_ajax({url:baseUrlName+"teacher/cloud/dir/"+current_dir_id,type:"GET",dataType:"json",data:{back:!0},success:function(json){1==json.errorcode?($(".cloud_file_list").html(json.html),exports.dropBoxFieldFn(),exports.tableStyleFn()):$.tiziDialog({content:"系统忙，请稍后再试"})},error:function(){window.location.href=baseUrlName+"teacher/cloud"}})})},exports.moveFileFn=function(){$(".tree-title").live("click",function(){{var _nxt=$(this).next("ul"),_chd1=$(this).children("a").eq(0),_chd2=$(this).children("a").eq(1);$(this)}if($(".tree-title").removeClass("tree-title-add"),$(this).addClass("tree-title-add"),$(".shareItem").removeClass("unfold"),_nxt.length>0&&"block"==_nxt.css("display")?(_nxt.hide(),_chd1.removeClass("icon-plus").addClass("icon-add"),_chd2.removeClass("unfold").addClass("fold")):(_nxt.show(),_chd2.addClass("unfold"),$(this).find(".icon").hasClass("icon-add")?_chd1.removeClass("icon-add").addClass("icon-plus"):_chd1.removeClass("icon-add").removeClass("icon-plus")),$(".class_share_choose_file").val()){var dirid=$(this).attr("dir-id");exports.get_file_in_a_dir(dirid)}})},exports.get_file_in_a_dir=function(dirid){var filetype=$("#fromInterPop").attr("chosen-type");$.tizi_ajax({url:baseUrlName+"teacher/cloud/get_files_render",type:"GET",dataType:"json",data:{dir_id:dirid,filetype:filetype},success:function(json){1==json.errorcode&&$(".DisRight").html(json.html)},error:function(){$.tiziDialog({content:"服务器繁忙，请稍候再试"})}})},exports.get_files=function(getData){getData.ver=(new Date).valueOf(),$.tizi_ajax({url:baseUrlName+"cloud/cloud/get_files",type:"GET",dataType:"json",data:getData,success:function(data){1==data.errorcode?$(".ajax_file_list").html(data.html):$.tiziDialog({content:"系统忙，请稍后再试"})},error:function(){$.tiziDialog({content:"系统忙，请稍后再试"})}})},exports.typeActive=$("#file_type_list").find(".active"),exports.getUrlData=function(ele){ele=ele||1;var cTypeVal=exports.typeActive.data("ctype")||0,cDir=$(".current_dir_id"),finalData=$.extend({page:ele},{ctype:cTypeVal},{cdir:cDir.val()});return finalData},exports.page=function(page){exports.get_files(exports.getUrlData(page))},exports.reCloudHome=function(){$(".ico_workCenter").live("click",function(){exports.reHome(this)})},exports.specialWordLimt=function(){document.onkeypress=exports.showKeyPress},exports.tableStyleFn=function(){$(".tableStyle tr").hover(function(){$(this).addClass("tdf1").siblings().removeClass("tdf1")},function(){$(".tableStyle tr").removeClass("tdf1")})}}),define("module/common/basics/teacherCloud/cloudValid",["lib/tizi_common/0.0.1/tizi_datatype","module/common/ajax/teacherCloud/cloudAjax"],function(require,exports){var cloudAjax=(require("lib/tizi_common/0.0.1/tizi_datatype").dataType(),require("module/common/ajax/teacherCloud/cloudAjax"));exports.creat=function(){var _Form=$(".creatNewFileForm").Validform({tiptype:3,showAllError:!1,ajaxPost:!0,callback:function(data){cloudAjax.creat(data)}});_Form.addRule([{ele:"#create_fileName",datatype:"*1-50",nullmsg:"请输入文件夹名称",errormsg:"长度1-50字符之间"}])},exports.shareFile=function(){var _Form=$(".shareFliesHasClassForm").Validform({tiptype:3,showAllError:!1,ajaxPost:!0,callback:function(data){cloudAjax.shareFile(data)}});_Form.addRule([{ele:":checkbox:last",datatype:"*",nullmsg:"请选择班级",errormsg:"选择错误！"}])},exports.resetFile=function(){var _Form=$(".resetFileNameForm").Validform({tiptype:3,showAllError:!1,ajaxPost:!0,callback:function(data){cloudAjax.resetFile(data)}});_Form.addRule([{ele:"#resetFileNameTxt",datatype:"*1-50",nullmsg:"请输入文件名称",errormsg:"长度1-50字符之间"}])}}),define("module/common/method/common/height",[],function(require,exports){exports.leftMenuBg=function(){var _wrapContentHeight=$("#wrapContent").height(),_slideHeight=$("#slide").height();_wrapContentHeight>_slideHeight?$("#slide").height(_wrapContentHeight):$("#wrapContent").height(_slideHeight)}});