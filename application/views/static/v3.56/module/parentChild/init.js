define("module/parentChild/init",["module/common/basics/common/highlight","module/parentChild/myChild"],function(require){require("module/common/basics/common/highlight").highlightMenu();var _tabname=$(".mainContainer").attr("tabname");switch(_tabname){case"bind":seajs.use(["module/common/method/common/tab","module/parentChild/vaild","tizi_validform"],function(ex1,ex2,ex3){ex1.stuPraticeTab($(".bindChildTitle"),"bindChildOn",$(".bindChild form")),ex2.bindChild(),ex2.bindCreatChild(),ex2.parLogin(),ex2.cancelBind(),ex3.changeCaptcha("creatChildBox"),ex3.bindChangeVerify("creatChildBox")});break;case"my":seajs.use(["module/common/method/common/tab","module/parentChild/vaild"],function(ex,ex2){ex2.cancelBind(),ex.stuPraticeTab($(".childList li"),"childGreenBg",$(".childDetails"))});break;case"study":seajs.use("",function(){})}var _mychild=require("module/parentChild/myChild");"myChild"==$(".mainContainer").attr("pagename")&&_mychild.init()}),define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}}),define("module/parentChild/myChild",[],function(require,exports){exports.init=function(){$(".option_index").val(0),tab(),getChildData(0),exports.getPagination(0,1),pageHandle(),get_child_homework(0,1),get_pagination=function(param){seajs.use("module/parentChild/myChild",function(ex){index=parseInt($(".option_index").val()),ex.getPagination(index,param)})}},tab=function(){$(".childDetails:eq(0)").addClass("dis"),$(".childList").find("li:eq(0)").addClass("childGreenBg"),$(".childList").find("li").click(function(){var index=$(".childList").find("li").index($(this));$(".option_index").val(index),getChildData(index),exports.getPagination(index,1),get_child_homework(index,1)})},pageHandle=function(){$(".pagination").find("a").live("click",function(){var page_num=$(this).text(),child_index=parseInt($(".childDetails").index($(this).parents(".childDetails")))+1;get_child_homework(child_index-1,page_num)})},exports.getPagination=function(index,page_num){var _child_tpl=$(".childDetails:eq("+index+")");_child_tpl.find(".childHomework").find(".nohasWork").length>0||(url=baseUrlName+"parent/parent_child/get_homework_pagination/"+(index+1),$.ajax({url:url,type:"GET",data:"page="+page_num,success:function(data){_child_tpl.find(".page").html(data)}}))},getChildData=function(index){if(index=parseInt(index),!(0>index)){var data_url=baseUrlName+"parent/parent_child/get_child_data/"+(index+1)+"?ver="+(new Date).valueOf(),_child_tpl=$(".childDetails:eq("+index+")");0==_child_tpl.find(".messList").length&&$.ajax({url:data_url,dataType:"json",type:"GET",success:function(data){if(99==data.status){var student_data=eval("("+data.msg+")"),_tpl_1='<img src= "'+student_data.avatar+'" alt="" class="fl"><div class="fl"><p class="messList"><strong class="bindChildName">'+student_data.name+'</strong><span class="childGrade"></span></p><p class="messList">学号：'+student_data.student_id+"</p><p>上次登录时间："+student_data.last_login_time+"</p></div>";_child_tpl.find(".childMessLeft").html(_tpl_1);var _tpl_2='<p class="messList"><span>总答题量：'+student_data.q_do_num+"道</span><span>班级创建人："+student_data.teacher_name+"</span><span>作业次数："+student_data.homework_count+"次</span><span>班级人数："+student_data.student_num+'</span></p><div class="cancelBind" kid="'+student_data.kid+'"kidname="'+student_data.name+'"><a href="javascript:;">取消绑定</a></div>';_child_tpl.find(".childMessRight").html(_tpl_2)}}})}},get_child_homework=function(index,page_num){if(index=parseInt(index),page_num=parseInt(page_num),!(0>index)){0>page_num&&(page_num=1);var data_url=baseUrlName+"parent/parent_child/get_child_homework/"+(index+1)+"/"+page_num,_child_tpl=$(".childDetails:eq("+index+")");_child_tpl.find(".childHomework").find(".nohasWork").length>0||$.ajax({url:data_url,dataType:"json",type:"GET",success:function(data){if(99==data.status){var _tpl="",homework={};for(student_homework=eval("("+data.msg+")"),0==student_homework.length?_child_tpl.find(".nohasWork").length<1&&_child_tpl.find(".childHomework").append('<div class="nohasWork">无作业</div>'):_child_tpl.find(".childHomework").find("table").show(),i=0;i<student_homework.length;i++)homework=student_homework[i],_tpl+="<tr><td>"+homework.starttime+"</td><td>"+homework.subject+"</td><td>"+homework.download+"</td><td>"+homework.question_stats+"</td><td>"+homework.correct_rate+'</td><td><a href="'+baseUrlName+"parent/parent_child/homework_preview/"+(index+1)+"/"+homework.id+'" target="_blank" class="workCheck">查看</a></td><td>'+homework.comment+"</td></tr>";_child_tpl.find(".homework_list").html(_tpl)}}})}}});