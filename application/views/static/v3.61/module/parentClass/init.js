define("module/parentClass/init",["module/common/method/common/tab","module/parentCommon/parentPraise","tiziDialog"],function(require){var _tab=require("module/common/method/common/tab");_tab.stuPraticeTab($(".cupNav li"),"curcupNav",$(".cupList"));var _parentClass=require("module/parentCommon/parentPraise");_parentClass.Praise(),_parentClass.shareInsertBg()}),define("module/common/method/common/tab",[],function(require,exports){exports.homePageTab=function(){$("#changeSubject a").live("click",function(){var obj=$(this),subject_type=obj.attr("sn"),area_id=$("#areaName").attr("area");$(this).addClass("active").siblings().removeClass("active"),$("#subjectName").attr("subject_type",obj.attr("sn")),$.tizi_ajax({url:baseUrlName+"paper/paper_exam/position?area_id="+area_id+"&stype_id="+subject_type,type:"GET",dataType:"json",success:function(data){$("#expos_list").html(data.html)}})})},exports.stuPraticeTab=function(clickObj,className,showObj){clickObj.click(function(){var index=clickObj.index($(this));clickObj!=$(".stuNav li")&&($(".courseNav li").removeClass("stuOn"),$(".exercises .practiceBox").removeClass("dis"),$(".courseNav:eq("+index+") li").eq(0).addClass("stuOn"),$(".exercises:eq("+index+") .practiceBox").eq(0).addClass("dis")),clickObj.removeClass(className),$(this).addClass(className),showObj.removeClass("dis"),showObj.eq(index).addClass("dis")})},exports.gradeTab=function(){var reg=new RegExp("^.*/([1-6]{1})"),key=window.location.href.match(reg);null!=key&&(index=key[1]-1,$(".stuNav").find("a").removeClass("active"),$(".stuNav").find("a:eq("+index+")").addClass("active"))},exports.subjectTab=function(){var reg=new RegExp("^.*#([1-9]{1})"),key=window.location.href.match(reg);null!=key&&(index=key[1],$(".courseNav").find(".s_"+index).click())},exports.Tab=function(tabTit,on,tabCon){$(tabCon).each(function(){$(this).children().eq(0).show().siblings().hide()}),$(tabTit).each(function(){$(this).children().eq(0).addClass(on)}),$(tabTit).children().click(function(){$(this).addClass(on).siblings().removeClass(on);var index=$(tabTit).children().index(this);$(tabCon).children().eq(index).show().siblings().hide()})},exports.homeworkTab=function(tabTit,on,tabCon){$(tabCon).each(function(){$(this).children().eq(0).show().siblings().hide()}),$(tabTit).each(function(){$(this).children().eq(0).addClass(on)}),$(tabTit).children().click(function(){$(this).addClass(on).siblings().removeClass(on);var index=$(tabTit).children().index(this);$(tabCon).children().eq(index).show().siblings().hide()})}}),define("module/parentCommon/parentPraise",["lib/artDialog/4.1.7/artDialog"],function(require,exports){require("lib/artDialog/4.1.7/artDialog"),exports.shareInsertBg=function(){with(window._bd_share_config={common:{bdSnsKey:{},bdText:"",bdMini:"2",bdPic:"",bdStyle:"0",bdSize:"16"},share:{},selectShare:{bdContainerClass:"articleContBox",bdSelectMiniList:["qzone","tsina","tqq","renren","weixin"]}},document)0[(getElementsByTagName("head")[0]||body).appendChild(createElement("script")).src="http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion="+~(-new Date/36e5)]},exports.Praise=function(){$("span.praise").bind("click",function(){function praiseDialog(msg){$.tiziDialog({content:msg,icon:null,init:function(){$(".aui_buttons button").eq(1).remove()},cancel:!0})}var id=$(this).attr("data");$.tizi_ajax({url:"/parent/parent_login/praise",data:{id:id,redirect:"callback:parentPraise"},type:"GET",success:function(data){if("1"==data.status)$("em.praise").html(parseInt($("em.praise").html())+1),$("span.praise").html("已赞").addClass("praised").removeClass("praise");else if("-2"==data.status){if("已赞"==$("span.praised").html())return!1;praiseDialog(data.msg)}else{if("已赞"==$("span.praised").html())return!1;praiseDialog(data.msg)}}})})}});