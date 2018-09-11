define("module/studentPraWrap/practiceWrap",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/artDialog/4.1.7/artDialog","lib/tizi_common/0.0.1/tizi_download","cookies","tizi_ajax","tiziDialog"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/artDialog/4.1.7/artDialog");var Teadownload=require("lib/tizi_common/0.0.1/tizi_download"),practice={vnum:$(".vnum"),subMark:$(".exercise_subject .sub_mark"),progress:$(".count table").find(".progress"),type_choose:$(".type_choose"),analysisBtn:$(".analysis_btn"),subjectCards:$(".subject_answer_card_btn span"),answerCardBox:$(".subject_answer_card_box"),subjectDelete:$(".exercise_subject .delete"),specialTestTitle:$(".special_test_title"),exercise_subject:$(".exercise_subject"),exerciseSubjectBox:$(".exercise_subject_box"),itemProgressBar:function(){{var td2=this.progress;this.progress.next()}td2.each(function(){var count=$(this).attr("count"),countAll=$(this).attr("countAll");$(this).find("span").html(count+"/"+countAll),$(this).find("strong").width(count/countAll*$(this).width())})},graspLever:function(){},tabs:function(tabTit,on,tabCon){$(tabCon).each(function(){$(this).children().eq(0).show()}),$(tabTit).children().click(function(){$(this).addClass(on).siblings().removeClass(on);var index=$(tabTit).children().index(this);$(tabCon).children().eq(index).show().siblings().hide()})},changeBackColor:function(table){$(table).find("tr:even").addClass("gray")},analysis:function(){this.analysisBtn.click(function(){$(this).parent().siblings(".detial").toggle(),$(this).parent().prev().toggle(),$(this).toggleClass("analysis_btn_click"),$(this).hasClass("analysis_btn_click")?$(this).html("收起解析"):$(this).html("展开解析")})},deleteSort:function(){var subject_num=this.exerciseSubjectBox.find(".sub-title-nu");subject_num.each(function(index,element){$(element).text(index+1)})},deleteSubject:function(){var _index=$(this).index();this.subjectDelete.click(function(){practice.exerciseSubjectBox.find(".exercise_subject").eq(_index).remove(),practice.deleteSort()})},subjectMark:function(){this.subMark.click(function(){$(this).toggleClass("active");var pid=$(this).parents(".exercise_subject").find("input[name='pid']").val(),order=$(this).parents(".exercise_subject").find(".sub-title-nu").html();void 0!=pid&&""!=pid&&$.tizi_ajax({url:baseUrlName+"student/practice/mark",data:{pid:pid},type:"POST",success:function(){$(".subject_answer_card_box .type_choose").find("li:eq("+(order-1)+")").find(".card_mark").length<1?$(".subject_answer_card_box .type_choose").find("li:eq("+(order-1)+")").append("<span class='card_mark'></span>"):$(".subject_answer_card_box .type_choose").find("li:eq("+(order-1)+")").find(".card_mark").remove()}})})},subjectCard:function(){this.subjectCards.click(function(){$(this).toggleClass("card_show"),practice.answerCardBox.toggle()})},subjecToTop:function(){this.type_choose.find("li").each(function(){var _index=$(this).index();$(this).click(function(){$(".exercise_subject_box").eq(0).is(":hidden")&&$(".subject_type li").eq(0).click();var _top=practice.exercise_subject.eq(_index).offset().top;$(document).scrollTop(_top)})})},ListShow:function(){function a(k){b=k.id,g=k.percent,j=k.width,styleData=h(),bindItems=d()}function d(){var o=[];m=$(".vote-item-wrap");for(var n=0,k=m.length;k>n;n++)o.push(m[n].children[1]);return o}function h(){for(var o=[],n=["#ff9900"],q=n.slice(),p=0,l=g.length;l>p;p++){var k=Math.floor(Math.random()*q.length);o.push(q[k]),q.splice(k,1),0==q.length&&(q=n.slice())}return o}function f(l,k){$(l.children[0]).css("background-color",k.color),$(l.children[1]).css({"background-color":k.color,width:"0px"}),$(l.children[2]).css("background-color",k.color)}function i(){for(var n=[],l=[],m=0,k=g.length;k>m;m++)f(bindItems[m],{color:styleData[m]}),n.push(bindItems[m].children[1]),l.push(Math.round(g[m]*j));e(n,0,l,c)}function e(p,o,l){for(var r=0,q=g.length;q>r;r++)$(p[r]).animate({width:l[r]},"slow")}var b,c,g,j;return{init:a,go:i}}(),percentsBar:function(){var all_item=parseInt($(".vnum").eq(0).attr("itemCount"))+parseInt($(".vnum").eq(1).attr("itemCount"))+parseInt($(".vnum").eq(2).attr("itemCount"))+parseInt($(".vnum").eq(3).attr("itemCount"))+parseInt($(".vnum").eq(4).attr("itemCount")),percent_1=$(".vnum").eq(0).attr("itemCount")/all_item,percent_2=$(".vnum").eq(1).attr("itemCount")/all_item,percent_3=$(".vnum").eq(2).attr("itemCount")/all_item,percent_4=$(".vnum").eq(3).attr("itemCount")/all_item,percent_5=$(".vnum").eq(4).attr("itemCount")/all_item,percents=[percent_1,percent_2,percent_3,percent_4,percent_5];this.ListShow.init({id:"appVoteBox",percent:percents,width:128})},percentsFn:function(){this.vnum.each(function(){var all_item=parseInt($(".vnum").eq(0).attr("itemCount"))+parseInt($(".vnum").eq(1).attr("itemCount"))+parseInt($(".vnum").eq(2).attr("itemCount"))+parseInt($(".vnum").eq(3).attr("itemCount"))+parseInt($(".vnum").eq(4).attr("itemCount")),this_item=$(this).attr("itemCount");0==all_item&&(all_item=1);var this_item_p=parseInt(this_item)/all_item;$(this).html(this_item+"（"+(100*this_item_p).toFixed(2)+"%）")})},countdown:{flash_import:function(){var id=$("#p_c_id").val(),path=$("#path").val(),source_url=staticBaseUrlName+"flash/practice_flash/"+path+"/",data_url=baseUrlName+"student/game1/get_question/"+id,option=["A","B","C","D"].slice(0,$(".option_num").val()),game_help=$(".game_help").val(),subject_id=$(".subject_id").val(),data=[data_url,source_url,option,game_help,subject_id];return data},flash_submit:function(id,result,selected_option){var submit_url=baseUrlName+"student/game1/submit";$.tizi_ajax({url:submit_url,data:{id:id,result:result,selected_option:selected_option},type:"POST",success:function(){}})}},challenge:{flash_import:function(){var id=$("#p_c_id").val(),path=$("#path").val(),source_url=staticBaseUrlName+"flash/practice_flash/"+path+"/",data_url=baseUrlName+"student/game2/get_question/"+id,option=["A","B","C","D"].slice(0,$(".option_num").val()),game_help=$(".game_help").val(),subject_id=$(".subject_id").val(),data=[data_url,source_url,option,game_help,subject_id];return data},flash_submit:function(id,result,selected_option){var submit_url=baseUrlName+"student/game2/submit";$.tizi_ajax({url:submit_url,data:{id:id,result:result,selected_option:selected_option},type:"POST",success:function(){}})}},game_v3:{flash_import:function(){var id=$("#p_c_id").val(),path=$("#path").val(),token=$(".token").attr("id"),pname=$(".pname").attr("id"),source_url=staticBaseUrlName+"flash/practice_flash/"+path+"/",data_url=baseUrlName+"student/game3/get_question/"+id,submit_url=baseUrlName+"student/game3/submit",data=[data_url,source_url,token,pname,submit_url,10,5,5];return data},flash_submit:function(id,result,selected_option){var submit_url=baseUrlName+"practice/game3/submit";$.tizi_send({url:submit_url,data:"id="+id+"&result="+result+"&selected_option="+selected_option,type:"POST",success:function(){}})}},favorites:{mark:function(){$(".exercise_subject .sub_mark").click(function(){$(this).parents(".exercise_subject").fadeOut(500)})}},getPracticeAjax:function(){var p_c_id=$("input[name='p_c_id']").val();void 0!==p_c_id&&$.ajax({url:baseUrlName+"student/practice_test/report/"+p_c_id,type:"GET",success:function(data){for(data=eval("("+data+")"),$(".all_num").html(data.total_num),$(".average_num").html(data.total_num),$(".report_rank").html(data.rank),$(".report_user_num").html(data.user_num),mastery=data.mastery,$.each(mastery,function(key,val){var tpl="<tr> <td class='indent'>"+val.name+"</td> <td class='progress' count='"+val.correct_num+"' countAll='"+val.total_num+"'><span>"+val.correct_num+"/"+val.total_num+"</span><strong></strong></td> <td><span class='level star_"+val.mastery+"'></span></td></tr>";$(".mastery").append(tpl)}),i=0;5>i;i++){var di=data.difficulty[i];$("#voteItem"+i).find(".vnum").attr("itemcount",di)}}})},getPracticeData:function(){var p_c_id=$("input[name='p_c_id']").val();$(".tab_list li:eq(1)").click(function(){practice.percentsBar(),practice.ListShow.go(),practice.percentsFn(),practice.itemProgressBar(),$(".mainContainer").removeAttr("style")}),$(".tab_list li:eq(2)").click(function(){$.ajax({url:baseUrlName+"student/practice_test/record/"+p_c_id,type:"GET",success:function(data){data=eval("("+data+")"),nav_tpl="<tr> <td class='gray w130'>时间</td> <td class='gray w120'>类型</td> <td class='gray w130'>题目总数</td> <td class='gray w120'>正确数量</td> <td class='gray w120'>解析</td> <td class='gray'>报告</td></tr>",$(".record").html(nav_tpl),$.each(data,function(key,val){1==val.status?(a_u="<a href='"+val.a_u+"'>查看解析</a>",r_u="<a href='"+val.r_u+"'>查看报告</a>"):(a_u="未完成",r_u="<a href='"+val.r_u+"'>继续完成</a>");var tpl="<tr> <td>"+val.start_time+"</td> <td>"+val.s_p_type+"</td> <td>"+val.question_num+"</td><td>"+val.correct_num+"</td> <td>"+a_u+"</td> <td>"+r_u+"</td> </tr>";$(".record").append(tpl)}),$(".mainContainer").removeAttr("style")}})}),$(".tab_list li:eq(3)").click(function(){$.ajax({url:baseUrlName+"student/practice_test/wrong_questions/"+p_c_id,type:"GET",success:function(data){data=eval("("+data+")");var nav_tpl="<tr> <td class='gray w430 indent'>知识点名称</td> <td class='gray w100'>错题数</td> <td class='gray w100'>查看题目</td> <td class='gray'>练习</td> </tr>";$(".wrong_questions").html(nav_tpl),$.each(data,function(key,val){var tpl="<tr><td class='indent'>"+val.name+"</td> <td>"+val.num+"</td> <td><a href='"+baseUrlName+"student/practice/wrong_questions_show/"+p_c_id+"/"+key+"'>查看题目</a></td> <td><a href='"+baseUrlName+"student/practice/training/"+p_c_id+"?c="+key+"'>练习5道题</a></td> </tr>";$(".wrong_questions").append(tpl)}),$(".mainContainer").removeAttr("style")}})}),$(".tab_list li:eq(4)").click(function(){$.ajax({url:baseUrlName+"student/practice_test/favorites/"+p_c_id,type:"GET",success:function(data){data=eval("("+data+")");var nav_tpl="<tr> <td class='gray w530 indent'>知识点名称</td> <td class='gray w100'>错题数</td> <td class='gray'>查看题目</td></tr>";$(".favorites").html(nav_tpl),$.each(data,function(key,val){var tpl="<tr><td class='indent'><span>"+val.name+"</span></td> <td>"+val.wrong_num+"</td> <td><a href='"+baseUrlName+"student/practice/favorites_show/"+p_c_id+"/"+val.id+"'>查看题目</a></td></tr>";$(".favorites").append(tpl)}),$(".mainContainer").removeAttr("style")}})})},practice_training:{v_num:"v="+(new Date).getTime(),per_save:function(){$(".question_option").find("li").click(function(){if($(this).hasClass("active"))return!1;var ac_len=$(this).parents("ul").find("li.active").length;if(!ac_len){var c_num=parseInt($(".c_num").text())+1;$(".c_num").text(c_num)}var s_p_id=$("input[name='s_p_id']").val(),question_id=$(this).parents(".homeworkBlock").find(".q_id").val(),input=$(this).text(),order=$(this).parents(".homeworkBlock").find(".order").val();$.tizi_ajax({url:baseUrlName+"student/practice_training/per_save",data:{s_p_id:s_p_id,pid:question_id,input:input,order:order},type:"POST",dataType:"json",success:function(){}})})},get_answer:function(){var id=$("input[name='s_p_id']").val();$.ajax({url:baseUrlName+"student/practice_training/get_answer?"+this.v_num,type:"GET",data:"id="+id,dataType:"json",success:function(data){data=eval("("+data.msg+")"),""!=data&&$.each(data,function(key,val){$(".question_"+val.order).find(".question_option").find("li:eq("+(Number(val.index)-1)+")").addClass("active")}),$("#course_content_online").find(".c_num").text($("#course_content_online").find("li.active").length),practice.set_question_num()}})},test:function(){function startclock(){se=setInterval("time_update()",1e3)}var next_continue_tpl="<p>确认要离开此页面?</p>",url=$(".backhome").attr("href"),s_p_id=$("input[name='s_p_id']").val();$(".nextDo").click(function(){$.tiziDialog({content:next_continue_tpl,ok:function(){$("#p_c_id").val();$.tizi_ajax({url:baseUrlName+"student/practice_training/pause",data:{s_p_id:s_p_id},dataType:"json",type:"POST",success:function(data){if(2==data.status){var tip_msg='您需要帮助您的孩子创建一个帐号后才可以进行专项练习,请点击页面顶部右侧的 "学生专区" 进行创建',back_url=$(".backhome").attr("href");$.tiziDialog({content:tip_msg,ok:function(){window.location.href=back_url},cancel:!0})}else window.location.href=url}})},cancel:!0})});var se,m=Number($(".minute").val()),s=Number($(".second").val());time_update=function(){s>0&&s%60==0&&(m+=1,s=0),10>s&&(s="0"+s),10>m&&(m="0"+m),t=m+":"+s,s=Number(s),m=Number(m),$(".showtime").html(t),s+=1},$(".over").click(function(){submit_tpl="确认要提交吗?",$.tiziDialog({okVal:"确认提交",cancel:!0,content:submit_tpl,ok:function(){var id=$("input[name='s_p_id']").val();$.tizi_ajax({url:baseUrlName+"student/practice_training/submit",data:{id:id},type:"POST",success:function(data){if(99==data.status)id=data.msg,window.location.href=baseUrlName+"student/practice/training/report/"+id;else if(2==data.status){var tip_msg='您需要帮助您的孩子创建一个帐号后才可以进行专项练习,请点击页面顶部右侧的 "学生专区" 进行创建',back_url=$(".backhome").attr("href");$.tiziDialog({content:tip_msg,ok:function(){window.location.href=back_url},cancel:!0})}}})}})}),startclock()}},initHover:function(){void 0!=$(".mainContainer").attr("pagename")&&$("#memberMenu li").each(function(i){$(".mainContainer").attr("pagename")==$("#memberMenu li").eq(i).attr("pagename")&&$("#memberMenu li").eq(i).attr("class","active")})},categoryTab:function(){var url=window.location.href,map=["/record/","/wrong_questions/"];$(".PraRight").find("a:eq(0)").addClass("titleActive");for(var i=0;i<map.length;i++)-1!=url.indexOf(map[i])&&($(".PraRight").find("a").removeClass("titleActive"),$(".PraRight").find("a:eq("+(i+1)+")").addClass("titleActive"))},set_question_num:function(){var n=$(".homeworkBlock").length;$(".a_num").text(n);var c=$(".course_info_item").find("li.active").length;$(".c_num").text(c)},init:function(){practice.getPracticeData(),practice.tabs(".tab_list","active",".tab-bd"),practice.tabs(".subject_type","active",".practice_version"),practice.tabs(".nav_ul","active",".tab-bd-3"),practice.changeBackColor(".table1"),practice.changeBackColor(".table2"),practice.deleteSort(),practice.analysis(),practice.subjectMark(),practice.deleteSubject(),practice.subjectCard(),practice.subjecToTop(),practice.getPracticeAjax(),practice.initHover(),practice.categoryTab()}};exports.practice=practice;var errorCorrection={haveErrorUploadImages:function(){var that=this,fbupload=$(".haveErrorForm").find(".fbupload");fbupload.each(function(){var node=$(this),loading=staticUrlName+"image/answerquestion/loading.gif",upload_id=node.attr("id");$("#"+upload_id).uploadify({formData:$.tizi_token({session_id:$.cookies.get(baseSessID)},"post"),swf:staticBaseUrlName+staticVersion+"lib/uploadify/2.2/uploadify.swf",uploader:baseUrlName+"upload/feedback?id="+upload_id,multi:!1,buttonClass:"choseFileBtn",buttonText:"上传图片",fileTypeExts:"*.jpg; *.png;*.gif;*.bmp",fileSizeLimit:"2048KB",fileObjName:upload_id,button_image_url:baseUrlName,width:102,height:28,overrideEvents:["onSelectError","onDialogClose"],onUploadStart:function(){$(".haveErrorForm ."+upload_id).html('<img src="'+loading+'" class="imgloading"/>')},onSWFReady:function(){},onFallback:function(){$(".imgTips").html(noflash)},onSelectError:function(file,errorCode){switch(errorCode){case-110:$.tiziDialog({content:"文件 ["+file.name+"] 过大！每张图片不能超过2M"});break;case-120:$.tiziDialog({content:"文件 ["+file.name+"] 大小异常！不可以上传大小为0的文件"});break;case-130:$.tiziDialog({content:"文件 ["+file.name+"] 类型不正确！不可以上传错误的文件格式"})}return!1},onUploadSuccess:function(file,data){var json=JSON.parse(data),upload_id_img=$(".haveErrorForm ."+upload_id);if(1==json.code){var img_path=json.img_path;that.drawImage(img_path,92,71,upload_id),upload_id_img.removeClass("red"),upload_id_img.addClass("upladpicSpan").removeClass("upladpic")}else upload_id_img.html("<b>"+json.msg+"</b>"),upload_id_img.addClass("red");upload_id_img.siblings(".clearpic").show(),upload_id_img.siblings(".clearpic").on("click",function(){upload_id_img.find("img").remove(),upload_id_img.siblings(".clearpic").hide(),upload_id_img.removeClass("red"),upload_id_img.removeClass("upladpicSpan").addClass("upladpic")})},onUploadComplete:function(){}})})},errorReport:function(){var paperTip=$(".paperTip"),question_id="",category_id="";paperTip.live("click",function(){require.async(upload_flash),question_id=$(this).attr("data-question-id"),category_id=$(this).attr("data-category-id"),$(".haveErrorQId").val(question_id),$(".haveErrorQIdSpan").html(question_id),$.tiziDialog({id:"createHaveError",title:"题目纠错",content:$("#haveErrorPop").html().replace("haveErrorForm_beta","haveErrorForm"),icon:null,width:400,init:function(){Student.errorCorrection.haveErrorUploadImages()},ok:function(){return $(".haveErrorForm").submit(),!1},cancel:!0,close:function(){$(".haveErrorForm").find(".aqupload").each(function(){var upload_id=$(this).attr("id");$("#"+upload_id).uploadify("destroy")}),$(".choseFile .error").remove()}}),Common.valid.paperHaveError.addPaperHaveError()})},drawImage:function(src,width,height,upload_id){var image=new Image,Img=new Image;image.src=src,image.onload=function(){image.width>width||image.height>height?(w=image.width/width,h=image.height/height,w>h?(Img.width=width,Img.height=image.height/w):(Img.height=height,Img.width=image.width/h)):(h=image.height/height,Img.width=image.width/h,Img.height=height),$(".haveErrorForm ."+upload_id).html('<img src="'+src+'" class="picture_urls" width="'+Img.width+'" height="'+Img.height+'"/>')}}};exports.errorCorrection=errorCorrection;var personal={commentWin:function(){var ele=$(".comment span"),close=$(".commentClose");ele.each(function(){$(this).live("click",function(){var l=$(this).position().left-200,t=$(this).position().top-100,win=$(this).parent().next().children();win.show().css({left:l+"px",top:t+"px"})})}),close.each(function(){$(this).live("click",function(){$(this).parent().hide()})})},homeworkTab:function(tabTit,on,tabCon){$(tabCon).each(function(){$(this).children().eq(0).show().siblings().hide()}),$(tabTit).each(function(){$(this).children().eq(0).addClass(on)}),$(tabTit).children().click(function(){$(this).addClass(on).siblings().removeClass(on);var index=$(tabTit).children().index(this);$(tabCon).children().eq(index).show().siblings().hide()})},fellowNav:function(){function setCourseTabPositon(top,winTop){var _t=winTop||$(window).scrollTop(),_box=$("#course_info_nav");if(_t>top){_box.addClass("tab_scoll");var isIE6=/MSIE 6\./.test(navigator.userAgent)&&!window.opera;isIE6&&_box.animate({top:_t},{duration:600,queue:!1})}else _box.removeClass("tab_scoll")}function courseInfo(id){var _box=$(id),_top="#course_content"==id?39:39,_top=_box.offset().top-_top;$(window).scrollTop(_top)}function setCourseHandle(top){{var _top=top||$(window).scrollTop(),_tabs=$("#course_info_nav li.detail"),_item=$("#course_info_box .course_info_item");_item.length}_item.each(function(i){var _thisTop=$(this).offset().top-40,_next=$(this).next(".course_info_item"),_tab=_tabs.eq(i);if(_thisTop>_top&&0==i)return setCourseHandleCurrent(_tabs.first()),void 0;if((_top>_thisTop||_top==_thisTop)&&0==_next.length)return setCourseHandleCurrent(_tabs.last()),void 0;if(_next.length>0){var _nextTop=_next.offset().top-40;if((_top>_thisTop||_top==_thisTop)&&_nextTop>_top)return setCourseHandleCurrent(_tab),void 0}})}function setCourseHandleCurrent(d){d.addClass("current").siblings(".detail").removeClass("current")}{var _box=$("#course_info_nav"),_top=_box.offset().top;$("#course_comment_courseID").val()}setCourseTabPositon(_top),$(window).scroll(function(){var _winTop=$(window).scrollTop();setCourseTabPositon(_top,_winTop),setCourseHandle(_winTop)}),$("#course_info_nav li.detail").click(function(){var _id=$(this).attr("con");_id&&(setCourseHandleCurrent($(this)),courseInfo("#"+_id))})},paperComplete:function(){$(".allDone").live("click",function(){$(this).addClass("clicked")})},answerSelect:function(){$(".result").find("li").each(function(){$(this).live("click",function(){$(this).addClass("active").siblings().removeClass("active")})})},addNewClass:function(){$("#addNewClass").live("click",function(){Common.comValidform.checkStudent(function(){$.tiziDialog({id:"addNewClassWinID",title:"加入班级",content:$("#addNewClassWin").html().replace("addNewClassWinForm_beta","addNewClassWinForm"),icon:null,width:500,ok:function(){return $(".addNewClassWinForm").submit(),!1},cancel:!0}),Common.valid.student.addNewClassWin()})})},setShowHideInput:function(){var btn_set=$(".btn_set");btn_set.on("click",function(){$(this).index();$(this).parents(".md_stuAccount").find("form").toggle()})},setShowHideForm:function(){var act_reset=$(".act_reset");act_reset.on("click",function(){var form=($(this).index(),$(this).parents(".md_stuAccount").find("form"));form.hide()})},love_share:function(){$(".zan").live("click",function(){var share_id=$(this).attr("share_id"),that=$(this);$.tizi_ajax({url:baseUrlName+"student/cloud/love",type:"POST",dataType:"json",data:{share_id:share_id},success:function(json){if(1==json.errorcode){var love=Number(that.parents().find(".get_love").attr("value"))+1;that.parents().find(".get_love").text("获赞("+love+")"),that.remove()}else $.tiziDialog({content:json.error})},error:function(){$.tiziDialog({content:"系统繁忙，请稍后再试"})}})})},stu_force_download:function(url,fname,openbox,is_qiniu,share_id){"undefined"==is_qiniu&&(url=url+"&session_id="+$.cookies.get(baseSessID)),1==is_qiniu&&$.tizi_ajax({url:baseUrlName+"student/cloud/add_download_count",type:"POST",data:{share_id:share_id},dataType:"json",success:function(){}});var ie_ver=Common.Download.ie_version();return 1==openbox||6==ie_ver||7==ie_ver||8==ie_ver?(fname=""==fname||void 0==fname?"是否下载？":"是否下载《"+fname+"》？",$.tiziDialog({content:fname,ok:!1,icon:null,button:[{name:"点击下载",href:url,className:"aui_state_highlight",target:"_self"}]}),!1):(window.location.href=url,void 0)},download_share:function(){$(".download_share").live("click",function(){var file_id=$(this).attr("file_id"),share_id=$(this).attr("share_id");$.tizi_ajax({url:baseUrlName+"student/cloud/downverify",type:"POST",data:{file_id:file_id,share_id:share_id},dataType:"json",success:function(data){if(0==data.errorcode)$.tiziDialog({content:data.error});else if(1==data.file_type){var down_url=baseUrlName+"student/cloud/download?url="+data.file_path+"&file_name="+data.file_name+"&file_id="+data.file_id+"&share_id="+share_id;Common.Download.force_download(down_url,data.fname,!0)}else Student.personal.stu_force_download(data.url,data.fname,!0,!0,share_id)}})})}};exports.personal=personal,exports.do_homework=function(){function update_break_status(sta){var aid=$("input[name='aid']").val();$.tizi_ajax({url:baseUrlName+"student/do_homework/"+sta,data:{aid:aid},type:"POST",dataType:"json",success:function(){}})}function countDown(){var date_arr=$(".deadline").val().split("-"),s1=Date.parse(date_arr[1]+"/"+date_arr[2]+"/"+date_arr[0]+" "+date_arr[3]+":"+date_arr[4]+":"+date_arr[5]),s2=parseInt((new Date).getTime()),s=parseInt((s1-s2)/1e3),days=parseInt(s/86400);s%=86400;var hours=parseInt(s/3600);s%=3600;var mins=parseInt(s/60);s%=60;var timeLeft=days+"天"+hours+"小时"+mins+"分钟"+s+"秒";0>=days&&0>=hours&&0>=mins&&0>=s?(clearInterval(timer),oSpan.html("已到时"),submit_work()):oSpan.html(timeLeft)}function submit_work(){var aid=$("input[name='aid']").val();$.tizi_ajax({url:baseUrlName+"student/do_homework/submit",data:{aid:aid},type:"POST",dataType:"json",success:function(data){99==data.status?window.location.href=baseUrlName+"student/homework/report/"+data.msg:window.location.reload()}})}var back_pause_tpl="<p>暂停啦.</p>",pause_tpl="<p>暂停啦.</p>",next_continue_tpl="确认要离开此页面?",aid=$("input[name='aid']").val();$.ajax({url:baseUrlName+"student/do_homework/get_answer?"+(new Date).getTime(),data:"aid="+aid,type:"GET",dataType:"json",success:function(data){data=eval("("+data.msg+")");var online=data.online,offline=data.offline;if(""!=online&&$.each(online,function(key,val){$(".question_"+val.question_id).find(".question_option").find("li:eq("+(Number(val.index)-1)+")").addClass("active")}),""!=offline)for(var i=0;i<offline.length;i++)$(".question_"+offline[i]).find(".allDone").addClass("clicked");simulate.set_question_num()}}),$(".nextDo").click(function(){$.tiziDialog({content:next_continue_tpl,ok:function(){var aid=$("input[name='aid']").val();$.tizi_ajax({url:baseUrlName+"student/do_homework/pause",data:{aid:aid},type:"POST",success:function(){window.location.href=baseUrlName+"student/home"}})},cancel:!0})}),$(".question").find(".answer_option").click(function(){$(this).parents(".question").find(".answer_option").attr("checked",!1),$(this).attr("checked","checked")}),$(".over").click(function(){var submit_tpl="确认要提交作业吗";$.tiziDialog({content:submit_tpl,ok:function(){submit_work()},cancel:!0})}),$(".question_option").find("li").click(function(){if($(this).hasClass("active"))return!1;var aid=$("input[name='aid']").val(),ac_len=$(this).parents("ul").find("li.active").length;if(!ac_len){var c_num=parseInt($(".c_num").text())+1;$(".c_num").text(c_num)}var question_id=$(this).parents(".homeworkBlock").find(".q_id").val(),input=$(this).text();$.tizi_ajax({url:baseUrlName+"student/do_homework/online_question_save",data:{aid:aid,q_id:question_id,input:input},type:"POST",dataType:"json",success:function(){}})}),$(".allDone").click(function(){if($(this).hasClass("clicked"))return!1;var c_num=parseInt($(".c_num").text())+1;$(".c_num").text(c_num);var aid=$("input[name='aid']").val(),question_id=$(this).parents(".homeworkBlock").find(".q_id").val();$.tizi_ajax({url:baseUrlName+"student/do_homework/paperwork_question_save",data:{aid:aid,q_id:question_id},type:"POST",dataType:"json",success:function(){}})});var oSpan=$(".clock"),timer=null;countDown(),timer=setInterval(countDown,1e3)};var simulate={online_question_save:function(){$(".question_option").find("li").click(function(){if($(this).hasClass("active"))return!1;var ac_len=($("input[name='aid']").val(),$(this).parents("ul").find("li.active").length);if(!ac_len){var c_num=parseInt($(".c_num").text())+1;$(".c_num").text(c_num)}})},offline_question_save:function(){$(".allDone").click(function(){if($(this).hasClass("clicked"))return!1;var c_num=parseInt($(".c_num").text())+1;$(".c_num").text(c_num)})},clock:function(){function startclock(){se=setInterval("time_update()",1e3)}var se,m=Number($(".minute").val()),s=Number($(".second").val());time_update=function(){return 999==m&&59==s?(clearInterval(se),void 0):(s>0&&s%60==0&&(m+=1,s=0),10>s&&(s="0"+s),10>m&&(m="0"+m),t=m+":"+s,s=Number(s),m=Number(m),$(".showtime").html(t),s+=1,void 0)},startclock()},submit:function(){$(".over").click(function(){submit_tpl="确认要提交吗?",$.tiziDialog({okVal:"确认提交",cancel:!0,content:submit_tpl,ok:function(){var id=$("input[name='aid']").val(),start_time=$("input[name='start_time']").val(),online_data=new Array,offline_data=new Array;$(".homeworkBlock").find("li").each(function(){var question_id,input;$(this).hasClass("active")&&(question_id=$(this).parents(".homeworkBlock").find(".q_id").val(),input=parseInt($(this).parents("ul").find("li").index($(this)))+1,online_data.push(question_id+","+input))}),$(".homeworkBlock").find(".allDone").each(function(){if($(this).hasClass("clicked")){var question_id=$(this).parents(".homeworkBlock").find(".q_id").val();offline_data.push(question_id)}}),$.tizi_ajax({url:baseUrlName+"student/simulate/submit",data:{id:id,online_data:online_data,offline_data:offline_data,start_time:start_time},type:"POST",success:function(data){99==data.status?window.location.href=baseUrlName+"teacher/homework/demo/report/"+id:2==data.status&&$.tiziDialog({okVal:"确定",time:2,content:data.msg})}})}})})},set_question_num:function(){var n=$(".homeworkBlock").length;$(".a_num").text(n);var c=$(".course_info_item").find("li.active").length;$(".c_num").text(c)}};exports.simulate=simulate,exports.do_paper_work=function(){$(".allDone").click(function(){if($(this).hasClass("clicked"))return!1;var _this=$(this),aid=$("input[name='aid']").val(),question_id=$(this).parents(".homeworkBlock").find(".q_id").val();$.tizi_ajax({url:baseUrlName+"student/do_homework/paperwork_question_submit",data:{aid:aid,q_id:question_id},type:"POST",dataType:"json",success:function(){_this.parent().find(".notice").remove()}})})}}),define("lib/tizi_common/0.0.1/tizi_download",["lib/cookies/0.0.1/jquery.cookies","lib/tizi_ajax/0.0.1/tizi_ajax","lib/artDialog/4.1.7/artDialog"],function(require,exports){require("lib/cookies/0.0.1/jquery.cookies"),require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/artDialog/4.1.7/artDialog"),exports.force_download=function(url,fname,openbox,noxunlei){noxunlei||(url=url+"&session_id="+$.cookies.get(baseSessID));var ie_ver=exports.ie_version();return 1==openbox||6==ie_ver||7==ie_ver||8==ie_ver?(fname=""==fname||void 0==fname?"请点击下载":"请点击下载《"+fname+"》",$.tiziDialog({content:fname,ok:!1,cancel:!1,dblclick:!1,icon:null,button:[{name:"点击下载",href:url,className:"aui_state_highlight",target:"_self"}]}),!1):(window.location.href=url,void 0)},exports.ie_version=function(){var ie=$.browser.msie,version=$.browser.version;return ie?version:!1},exports.down_confirm_box=function(url,fname,noxunlei){noxunlei||(url=url+"&session_id="+$.cookies.get(baseSessID)),$.tiziDialog({content:"是否下载文件《"+fname+"》？",ok:!1,cancel:!0,icon:null,button:[{name:"点击下载",href:url,className:"aui_state_highlight",target:"_self"}]})}});