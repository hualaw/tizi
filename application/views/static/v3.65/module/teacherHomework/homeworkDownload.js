define("module/teacherHomework/homeworkDownload",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/artDialog/4.1.7/artDialog","lib/tizi_common/0.0.1/tizi_validform","tizi_ajax","md5","tiziDialog","cookies","lib/tizi_common/0.0.1/tizi_download"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/artDialog/4.1.7/artDialog"),exports.homeworkDownload={downWord:$(".down-word-btn"),init:function(){var _this=this;$(".down-word-btn").click(function(){_this.open_downword($(this).attr("slid"))}),$(".del-log-btn").click(function(){var slid=$(this).attr("slid"),page=$(".page").find(".active").html(),type=$("#filter_type").attr("data_type");$.tiziDialog({content:"确定删除当前文档？",cancel:!0,ok:function(){seajs.use("module/teacherHomework/homeworkArchive",function(ex){ex.homeworkArchive.delete_log(page,type,slid)})}})})},open_downword:function(slid){return"disabled"==this.downWord.attr("disabled")?!1:($.tiziDialog({title:"下载作业",content:$("#down-paper-content").html().replace("downWin_tpl","downWin").replace("PaperDownBox_tpl","HomeworkDownBox").replace("PaperDownBoxWord_tpl","HomeworkDownBoxWord"),icon:null,width:787,init:function(){require("lib/tizi_common/0.0.1/tizi_validform").changeCaptcha("HomeworkDownBox"),require("lib/tizi_common/0.0.1/tizi_validform").bindChangeVerify("HomeworkDownBox")},okVal:"立即下载本作业",ok:function(){var checkcode=require("lib/tizi_common/0.0.1/tizi_validform").checkCaptcha("HomeworkDownBox",1,1);if(!checkcode)return!1;this_a=exports.homeworkDownload;var downWord=$("a[slid='"+slid+"'][class='down-word-btn']");downWord.html("下载中"),downWord.attr("disabled","disabled"),this_a.download(slid,"paper",function(){this_a=exports.homeworkDownload,downWord.html("下载"),downWord.removeAttr("disabled")})},cancel:!0}),$(".teacherHomework p.in").hover(function(){$(".teacherHomework p.in").removeClass("active"),$(this).addClass("active").siblings().removeClass("active")},function(){$(".teacherHomework p.in").removeClass("active")}).click(function(){$(this).find("input").addClass("windInput").attr("checked","checked")}),void 0)},setDownloadChecked:function(download_type,name,value){for(var len=$(".downWin_tpl").find("input[type=radio][name="+download_type+"_"+name+"]").length,i=0;len>i;i++){var obj=$(".downWin_tpl").find("input[type=radio][name="+download_type+"_"+name+"]").eq(i);obj.val()==value?obj.attr("checked",!0):obj.attr("checked",!1)}},download:function(slid,download_type,unlock){var paper_version=$(".downWin").find("input[type=radio][name="+download_type+"_version]:checked").val();this.setDownloadChecked(download_type,"version",paper_version);var paper_type=$(".downWin").find("input[type=radio][name="+download_type+"_type]:checked").val();this.setDownloadChecked(download_type,"type",paper_type);var paper_size=$(".downWin").find("input[type=radio][name=paper_size]:checked").val();this.setDownloadChecked(download_type,"size",paper_size),download_type="homework";var post_data={paper_version:paper_version,paper_type:paper_type,save_log_id:slid,download_type:download_type};post_data.captcha_name="HomeworkDownBox",post_data.captcha_word=$(".HomeworkDownBoxWord").val(),post_data.paper_style="default",post_data.paper_size=paper_size,baseuri=baseUrlName+"paper/download/homework",$.tizi_ajax({url:baseuri,type:"POST",data:post_data,dataType:"json",success:function(data){if(unlock(),0==data.errorcode)$.tiziDialog({content:data.error});else{var url=baseUrlName+"download/paper?url="+data.url+"&file_name="+data.file_name+"&download_type="+download_type;ga("send","event","Download-Paper-"+download_type,"Download",data.fname),require("lib/tizi_common/0.0.1/tizi_download").force_download(url,data.fname)}}})}}}),define("lib/tizi_common/0.0.1/tizi_validform",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/md5/0.0.1/md5","lib/artDialog/4.1.7/artDialog","lib/cookies/0.0.1/jquery.cookies"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),exports.md5=function(element){require("lib/md5/0.0.1/md5");var i=0;element.find("input").each(function(){if("password"==$(this).attr("type")){var input_name=$(this).attr("name");void 0==input_name&&(input_name=$(this).attr("md5_name")),input_name&&(i+=1);var input_id=input_name+i,pass_hidden='<input class="text-input" id="'+input_id+'_md5" name="'+input_name+'" type="hidden">';$("#"+input_id+"_md5").remove(),$(this).parent().append(pass_hidden),$("#"+input_id+"_md5").val(tizi_md5($(this).val())),$(this).attr("md5_name",input_name).removeAttr("name")}})},exports.reset_md5=function(element){var i=0;$(element).find("input").each(function(){if("password"==$(this).attr("type")){input_name=$(this).attr("md5_name"),input_name&&(i+=1);var input_id=input_name+i;$("#"+input_id+"_md5").remove(),$(this).attr("name",input_name).removeAttr("md5_name")}})},exports.changeCaptcha=function(captcha_name){if(void 0==captcha_name)return!1;var img=$("."+captcha_name).siblings("img"),type=((new Date).valueOf(),"base64");$.browser.msie&&"6.0"==$.browser.version&&(type="normal"),$.tizi_ajax({url:baseUrlName+"captcha",type:"get",dataType:"json",data:{captcha_name:captcha_name,captcha_type:type,ver:(new Date).valueOf()},success:function(data){data.errorcode?(img.attr("src",data.image),data.word?($("."+captcha_name).parent().addClass("undis"),$("."+captcha_name+"Word").val(data.word)):$("."+captcha_name).parent().removeClass("undis")):require.async("tiziDialog",function(){$.tiziDialog({icon:"error",content:data.error,time:3})})}})},exports.bindChangeVerify=function(captcha_name){$(".commonCaptcha").find("."+captcha_name)&&$("."+captcha_name).siblings(".changeCaptcha").each(function(){$(this).live("click",function(event){event.preventDefault(),exports.changeCaptcha(captcha_name)})})},exports.checkCaptcha=function(captcha_name,keep_code,show_dialog,checkcode){var check=!1;return void 0==captcha_name&&(captcha_name=basePageName),void 0==checkcode&&(checkcode=$("."+captcha_name+"Word").val()),void 0==keep_code&&(keep_code=1),$.tizi_ajax({url:baseUrlName+"check_captcha",type:"get",dataType:"json",async:!1,data:{check_code:checkcode,keep_code:keep_code,captcha_name:captcha_name,ver:(new Date).valueOf()},success:function(data){data.errorcode?($(".textCaptcha").siblings(".Validform_checktip").text(data.error).attr("class","Validform_checktip Validform_right"),check=!0):(show_dialog&&require.async("tiziDialog",function(){$.tiziDialog({icon:"error",content:data.error,time:3})}),$(".commonCaptcha .Validform_checktip").text(data.error).attr("class","Validform_checktip Validform_wrong"),$("."+captcha_name).parent().hasClass("undis")&&($("."+captcha_name+"Word").val(""),exports.changeCaptcha(captcha_name)),check=!1)}}),check},exports.sendPhoneCode=function(phone,code_type,fn,errfn){var url=baseUrlName+"send_phone_code";require("lib/artDialog/4.1.7/artDialog"),$.tizi_ajax({url:url,type:"post",dataType:"json",data:{phone:phone,code_type:code_type,ver:(new Date).valueOf()},success:function(data){data.errorcode?fn(data):errfn(data)}})},exports.sendEmailCode=function(email,code_type,fn,errfn){var url=baseUrlName+"send_email_code";$.tizi_ajax({url:url,type:"post",dataType:"json",data:{email:email,code_type:code_type,ver:(new Date).valueOf()},success:function(data){data.errorcode?fn():errfn(data)}})},exports.checkPhoneCode=function(checkcode,phone,code_type){var url=baseUrlName+"check_code",check=!1;return require("lib/tizi_ajax/0.0.1/tizi_ajax"),$.tizi_ajax({url:url,type:"post",dataType:"json",async:!1,data:{phone:phone,check_code:checkcode,code_type:code_type,ver:(new Date).valueOf()},success:function(data){data.errorcode?($(".phoneCode").siblings(".Validform_checktip").text(data.error).attr("class","Validform_checktip Validform_right"),check=!0):($(".phoneCode").siblings(".Validform_checktip").text(data.error).attr("class","Validform_checktip Validform_wrong"),check=!1)}}),check},exports.detectFlashSupport=function(fn_noflash,fn_flash){void 0==fn_noflash&&(fn_noflash=function(){}),void 0==fn_flash&&(fn_flash=function(){});var hasFlash=!1;if("function"==typeof ActiveXObject)try{new ActiveXObject("ShockwaveFlash.ShockwaveFlash")&&(hasFlash=!0)}catch(error){}!hasFlash&&navigator.mimeTypes["application/x-shockwave-flash"]&&(hasFlash=!0),hasFlash?fn_flash():fn_noflash()},exports.checkLogin=function(){require("lib/cookies/0.0.1/jquery.cookies");var username=$.cookies.get(baseUnID);username&&$.tizi_ajax({url:baseUrlName+"login/login/check_login",type:"get",dataType:"json",data:{},success:function(data){data.errorcode&&(window.location.href=baseUrlName)}})}}),define("lib/tizi_common/0.0.1/tizi_download",["lib/cookies/0.0.1/jquery.cookies","lib/tizi_ajax/0.0.1/tizi_ajax","lib/artDialog/4.1.7/artDialog"],function(require,exports){require("lib/cookies/0.0.1/jquery.cookies"),require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/artDialog/4.1.7/artDialog"),exports.force_download=function(url,fname,openbox,noxunlei){noxunlei||(url=url+"&session_id="+$.cookies.get(baseSessID));var ie_ver=exports.ie_version();return 1==openbox||6==ie_ver||7==ie_ver||8==ie_ver?(fname=""==fname||void 0==fname?"请点击下载":"请点击下载《"+fname+"》",$.tiziDialog({content:fname,ok:!1,cancel:!1,dblclick:!1,icon:null,button:[{name:"点击下载",href:url,className:"aui_state_highlight",target:"_self"}]}),!1):(window.location.href=url,void 0)},exports.ie_version=function(){var ie=$.browser.msie,version=$.browser.version;return ie?version:!1},exports.down_confirm_box=function(url,fname,noxunlei){noxunlei||(url=url+"&session_id="+$.cookies.get(baseSessID)),$.tiziDialog({content:"是否下载文件《"+fname+"》？",ok:!1,cancel:!0,icon:null,button:[{name:"点击下载",href:url,className:"aui_state_highlight",target:"_self"}]})}});