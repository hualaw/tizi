Teacher.UserCenter={UploadAq:{appletUrl:"",init:function(){if($.browser.msie&&$.browser.version<8)$(".ckEd").prepend('<p class="ieTip">温馨提示：您的浏览器版本较低，将无法上传图片或者公式，建议您安装并使用谷歌、火狐或ie8以上浏览器，若使用360浏览器请切换为“极速模式”</p>'),$("#question_content").addClass("ieTxtArea"),$("#analysis").addClass("ieTxtArea"),$("#option_answer").addClass("ieTxtArea");else{$("#question_content").ckeditor(),$("#analysis").ckeditor(),$("#option_answer").ckeditor();var isWindows=this.checkOS();isWindows&&($(".Tip").show(),this.addPlugin())}Teacher.UserCenter.my_question_ajax.init(),this.openGroupInfo()},addPlugin:function(){var serverUrl=baseUrlName+"upload/uques",uploader=new WordImageUploader(serverUrl,this.appletUrl);CKEDITOR.instances.question_content.on("change",function(){uploader.uploadWordImagesFromCKEditor(CKEDITOR.instances.question_content)}),CKEDITOR.instances.analysis.on("change",function(){uploader.uploadWordImagesFromCKEditor(CKEDITOR.instances.analysis)}),CKEDITOR.instances.option_answer.on("change",function(){uploader.uploadWordImagesFromCKEditor(CKEDITOR.instances.option_answer)})},checkOS:function(){return windows=-1!=navigator.userAgent.indexOf("Windows",0)?1:0,windows?1:0},openGroupInfo:function(){for(var openGroupInfo=$(".openGroupInfo"),tmpGroupTip="",size=$(".s_newSubject option").size(),i=0;size>i;i++)$(".dataStore").data($(".s_newSubject option").eq(i).text(),$(".s_newSubject option").eq(i).val());openGroupInfo.on("click",function(){var _that=$(this);$(".s_newSubject option").removeAttr("selected");var subject_text=_that.parents("ul").find(".s_subject option:selected").text(),index=$(".dataStore").data(subject_text);$(".s_newSubject option").eq(index).attr("selected",!0),index&&$(".s_newSubject").attr("disabled",!0),tmpGroupTip=$(".tmpGroupTip").html(),$.tiziDialog({id:"tmpGroupTipId",content:tmpGroupTip,icon:!1,ok:function(){return $(".myGroupTipForm").submit(),!1},cancel:!0,init:function(){Teacher.UserCenter.uploadQuesValid.myGroupTipForm(_that)}})})}},UploadING:{init:function(){$("#file_upload").uploadify({formData:$.tizi_token({session_id:$.cookies.get(baseSessID)},"post"),swf:staticBaseUrlName+staticVersion+"lib/uploadify/2.2/uploadify.swf",uploader:baseUrlName+"upload/udoc",buttonImage:staticBaseUrlName+staticVersion+"lib/uploadify/2.2/icon_org.png",buttonClass:"uploadify_oragne",fileTypeExts:"*.doc; *.docx; *.ppt; *.pptx; *.xls; *.xlsx; *.wps; *.et; *.dps; *.pdf; *.txt",fileSizeLimit:"20MB",fileObjName:"documents",width:94,"text-indent":10,uploadLimit:5,overrideEvents:["onSelectError","onDialogClose","onUploadError"],onSelectError:function(file,errorCode){switch(errorCode){case-100:$.tiziDialog({content:"每次最多上传5份文档"});break;case-110:$.tiziDialog({content:"文件 ["+file.name+"] 过大！每份文档不能超过20M"});break;case-120:$.tiziDialog({content:"文件 ["+file.name+"] 大小异常！不可以上传大小为0的文件"});break;case-130:$.tiziDialog({content:"文件 ["+file.name+"] 类型不正确！不可以上传错误的文件格式"})}return!1},onSWFReady:function(){},onFallback:function(){$(".uploadDoc").html(noflash)},onUploadError:function(file,errorCode){return-280!=errorCode&&-290!=errorCode?($.tiziDialog({content:"文件 ["+file.name+"] 上传失败"}),!1):void 0},onQueueComplete:function(){top.location.href=baseUrlName+"teacher/user/mydocument/perfect"}})}},UploadOver:{init:function(){this.addOverInfo(),this.delOverInfo(),this.delSynInfo()},addOverInfo:function(){var addBtn_message=$(".addBtn_message");addBtn_message.eq(0).on("click",function(){var add_knows=$(".add_knows").eq(0).clone();$(".add_knows").parent().append(add_knows)}),addBtn_message.eq(1).on("click",function(){var add_ansyInfo=$(".add_ansyInfo").eq(0).clone();$(".add_ansyInfo").parent().append(add_ansyInfo)})},delOverInfo:function(){var del_message=$(".del_message");del_message.on("click",function(){$(this).index();$(this).parent().remove()})},delSynInfo:function(){var del_syncInfo=$(".del_syncInfo");del_syncInfo.on("click",function(){var index=$(this).index();$(".md_filesList").eq(index).slideUp("slow",function(){$(this).remove()})})}},my_question_ajax:{init:function(){this.subjecType(),this.getVersion(),$(".add_course").on("click",function(){objConfirm=$(this),Teacher.UserCenter.my_question_ajax.confirmCourseMsg(objConfirm)}),$(".add_category").on("click",function(){objConfirm=$(this),Teacher.UserCenter.my_question_ajax.confirmCategoryMsg(objConfirm)}),this.add_option(),this.del_option(),this.answer_panel()},add_option:function(){$("#myadd_option").on("click",function(){var next_option=Teacher.UserCenter.my_question_ajax.getNextOption();next_option&&(str='<label class="labelr"> <input class="radior" type="checkbox" name="option_answer[]" ',str+='value="'+next_option+'">',str+=next_option+"</label>",$("#answer_row").append(str)),Teacher.UserCenter.my_question_ajax.setLastOption()})},del_option:function(){$("#mydel_option").on("click",function(){var le=$(".pickList").find("input").last();if("A"==le.val())$.tiziDialog({content:"不能再减少了"});else{var p=le.closest("label");p.remove()}Teacher.UserCenter.my_question_ajax.setLastOption()})},getNextOption:function(){var last_val=this.getLastOption(),option_list="ABCDEFGHIJKLMNOPQRSTUVWXYZ",pos=option_list.indexOf(last_val);if(pos==option_list.length-1)return $.tiziDialog({content:"选项不能再多了"}),!1;var next_pos=0;return next_pos=pos>parseInt(option_list.length)?option_list.length:pos+1,option_list[next_pos]},setLastOption:function(){var le=$(".pickList").find("input").last(),last_val=le.val();$("#last_option").attr("value",last_val)},getLastOption:function(){var le=$(".pickList").find("input").last(),last_val=le.val();return last_val},answer_panel:function(){$(".s_qtype").on("change",function(){var obj_ele=$(this),values=obj_ele.val();values>=2&&7>=values?($("#qestion_unselect").hide(),$("#qestion_select").show()):($("#qestion_unselect").show(),$("#qestion_select").hide())})},getBaseUrl:function(){return"user/user_question/"},unCheck:function(str){return"请选择"==str||"请选择版本"==str?($.tiziDialog({content:"您有未选择的项！"}),!0):!1},delCourseSelect:function(id,ele){$(ele);$("#category_id").val(""),$("p#"+id).remove()},delCategorySelect:function(id){var knowledge_id_obj=$("#knowledge_id"),kid_list_obj=knowledge_id_obj.val(),kid_list=kid_list_obj.split("|"),kpos=kid_list.indexOf(id);-1!=kpos&&kid_list.splice(kpos,1);try{kid_str=kid_list.join("|")}catch(e){}knowledge_id_obj.attr("value",kid_str),$("p#"+id).remove()},confirmCategoryMsg:function(ele){var check_res,objBtn=ele,str="",sid="",categoryid_storage=$("#knowledge_id"),category_name_obj=$(".knowlage_name"),storedval=categoryid_storage.val();if(objBtn.prev().find(".tree-item").each(function(){var id=$(this).find("option:selected").val(),name=$(this).find("option:selected").html();return(check_res=Teacher.UserCenter.my_question_ajax.unCheck(name))?!1:(""!=name&&(str+=name+"--"),""!=id&&(sid+=id+"-"),void 0)}),check_res)return check_res;var val_arr=storedval.split("|"),pos=val_arr.indexOf(sid);return-1!=pos?($.tiziDialog({content:"您已经添加了该知识点信息"}),void 0):(sid+="|",storedval?categoryid_storage.attr("value",storedval+sid):categoryid_storage.attr("value",sid),sid=sid.substr(0,sid.length-1),category_name_obj.append('<p id="'+sid+'">'+str+'<a href="javascript:void(0);" onclick="Teacher.UserCenter.my_question_ajax.delCategorySelect(\''+sid+'\',this);" class="del_message">删除</a></p>'),void 0)},confirmCourseMsg:function(ele){var objBtn=ele,str="",sid="",categoryid_storage=$("#category_id"),category_name_obj=$(".category_name"),storedval=categoryid_storage.val();if(""!=storedval)return $.tiziDialog({content:"您已经添加了教材同步信息！"}),!1;var check_res;return objBtn.prev().find(".tree-item").each(function(){var id=$(this).find("option:selected").val(),name=$(this).find("option:selected").html();return(check_res=Teacher.UserCenter.my_question_ajax.unCheck(name))?!1:(""!=name&&(str+=name+"--"),""!=id&&(sid+=id+"-"),void 0)}),check_res?check_res:(categoryid_storage.attr("value",sid),sid=sid.substr(0,sid.length-1),category_name_obj.append('<p id="'+sid+'">'+str+'<a href="javascript:void(0);" onclick="Teacher.UserCenter.my_question_ajax.delCourseSelect(\''+sid+'\',this);" class="del_message">删除</a></p>'),void 0)},subjecType:function(){$(".s_grade").on("change",function(){var obj_ele=$(this),_this=Teacher.UserCenter.my_question_ajax,values=obj_ele.val(),base_uri=baseUrlName+_this.getBaseUrl()+"ajax_subject",getData={grade_id:values,ver:(new Date).valueOf()};$.tizi_ajax({url:base_uri,type:"GET",data:getData,dataType:"json",success:function(data){1==data.error_code?$(".s_subject").html(data.error):$.tiziDialog({content:data.error})}})})},getVersion:function(){$(".s_subject").on("change",function(){var obj_ele=$(this),_this=Teacher.UserCenter.my_question_ajax,values=obj_ele.val(),base_uri=baseUrlName+_this.getBaseUrl()+"subject_ajax_select",getData={subject_id:values,ver:(new Date).valueOf()};$.tizi_ajax({url:base_uri,type:"GET",data:getData,dataType:"json",success:function(data){1==data.error_code?($(".s_qtype").html(data.qtype),$(".s-knowlage").html(data.category),$(".s-version").html(data.course),$(".s_Group").html(data.groups)):$.tiziDialog({content:data.error})}})})},delAfter:function(ele){var _curSel=ele;_curSel.nextAll(".tree-item").remove()},treeItemClick:function(this_a){var thisId=$(this_a).val();if(""==thisId)return this.delAfter($(this_a)),!1;var selType=$(this_a).find("option:selected").data("type");"selplus"==selType?this.ajax_node_data(thisId,$(this_a)):this.delAfter($(this_a))},ajax_node_data:function(cnselectVal,element){var base_url=this.getBaseUrl();$.get(baseUrlName+base_url+"ajax_cate_node",{cnselect:cnselectVal,ver:(new Date).valueOf()},function(data){1==data.error_code?element&&(Teacher.UserCenter.my_question_ajax.delAfter(element),element.after(data.error)):$.tiziDialog({content:data.error})},"json")}},ajax_select:{getBaseUrl:function(){return"user/user_question/"},unCheck:function(str){return"请选择"==str||"请选择版本"==str?($.tiziDialog({content:"您有未选择的项！"}),!0):!1},delSelect:function(id,ele){var this_obj=$(ele);this_obj.parents(".md_syncInfo").find("input[type='hidden']").val(""),$("span#"+id).remove()},confirmMsg:function(ele){var objBtn=ele,str="",sid="",categoryid_storage=objBtn.next("input[type='hidden']"),category_name_obj=objBtn.parents(".md_syncInfo").find(".category_name"),storedval=categoryid_storage.val();if(""!=storedval)return $.tiziDialog({content:"您已经添加了教材同步信息！"}),!1;var check_res;return objBtn.prev().find(".tree-item").each(function(){var id=$(this).find("option:selected").val(),name=$(this).find("option:selected").html();return(check_res=Teacher.UserCenter.ajax_select.unCheck(name))?!1:(""!=name&&(str+=name+"--"),""!=id&&(sid+=id+"-"),void 0)}),check_res?check_res:(categoryid_storage.attr("value",sid),sid=sid.substr(0,sid.length-1),category_name_obj.append('<span id="'+sid+'">'+str+'<a href="javascript:void(0);" onclick="Teacher.UserCenter.ajax_select.delSelect(\''+sid+'\',this);" class="del_message">删除</a></span>'),void 0)},delAfter:function(ele){var _curSel=ele;_curSel.nextAll(".tree-item").remove()},treeItemClick:function(this_a){var thisId=$(this_a).val();if(""==thisId)return this.delAfter($(this_a)),!1;var selType=$(this_a).find("option:selected").data("type");"selplus"==selType?this.ajax_node_data(thisId,$(this_a)):this.delAfter($(this_a))},ajax_node_data:function(cnselectVal,element){var base_url=this.getBaseUrl();$.get(baseUrlName+base_url+"ajax_node_select",{cnselect:cnselectVal,ver:(new Date).valueOf()},function(data){1==data.error_code?element&&(Teacher.UserCenter.ajax_select.delAfter(element),element.after(data.error)):$.tiziDialog({content:data.error})},"json")}},uploadQuesValid:{init:function(){this.myQuestion()},myQuestion:function(){var _Form=$(".form_qList").Validform({tiptype:3,showAllError:!1,ajaxPost:!0,beforeSubmit:function(){},callback:function(data){Teacher.UserCenter.uploadQuesValidAjax.myQuestion(data)}});_Form.addRule([{ele:".s_grade",datatype:"*",nullmsg:"请选择学段"},{ele:".s_subject",datatype:"*",nullmsg:"请选择学科"},{ele:".s_qtype",datatype:"*",nullmsg:"请选择题型"},{ele:".source",datatype:"*",nullmsg:"请输入题目/来源"}])},myGroupTipForm:function(obj){var _Form=$(".myGroupTipForm").Validform({tiptype:3,showAllError:!1,ajaxPost:!0,beforeSubmit:function(){},callback:function(data){Teacher.UserCenter.uploadQuesValidAjax.myGroupTipForm(data,obj)}});_Form.config({tiptype:3}),_Form.addRule([{ele:".s_newSubject",datatype:"*",nullmsg:"请选择学科"},{ele:".s_GroupName",datatype:"*1-20",nullmsg:"请选择分组名称",errormsg:"长度1-20字符之间"}])}},uploadQuesValidAjax:{myQuestion:function(data){if(data.error_code){var type=data.type,title="insert"==type?"继续上传":"继续编辑";$.tiziDialog({content:data.error,okVal:title,cancelVal:"查看我的试题",ok:function(){this.close(),"insert"==type&&(window.location.href=baseUrlName+"teacher/user/myquestion/new")},cancel:function(){var csid=$(".subNavBox").data("subject"),sid=$(".s_subject option:selected").val(),gid=$(".s_Group option:selected").val();window.location.href=csid==sid?"0"==gid?baseUrlName+"teacher/user/myquestion/g":baseUrlName+"teacher/user/myquestion/g/"+gid:baseUrlName+"teacher/user/myquestion/"+sid}})}else $.tiziDialog({content:data.error})},myGroupTipForm:function(data,obj){var oldGroup=obj.parent().find(".s_Group");if(data.error_code){var newGroupName=data.new_name,newGroupID=data.error,newOption='<option value ="'+newGroupID+'" selected="selected">'+newGroupName+"</option>";oldGroup.removeAttr("selected"),oldGroup.append(newOption),$.tiziDialog.list.tmpGroupTipId.close()}else $.tiziDialog({content:data.error})}}};