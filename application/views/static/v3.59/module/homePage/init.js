define("module/homePage/init",["module/common/method/common/returnTop","module/common/basics/common/login","tizi_ajax","validForm","tizi_datatype","tizi_valid","module/common/method/homePage/popDiv","module/common/method/common/tab","module/homePage/scroll"],function(require){require("module/common/method/common/returnTop").cReturnTopfn(),require("module/common/basics/common/login").commonLogin(),require("module/common/method/homePage/popDiv").selectCity(),require("module/common/method/homePage/popDiv").weixinContent(),require("module/common/method/common/tab").homePageTab(),require("module/homePage/scroll"),require("module/common/method/homePage/popDiv").srcMarquee()}),define("module/common/method/common/returnTop",[],function(require,exports){exports.cReturnTopfn=function(){$(".cReturnTop").length<1&&$("body").append('<div class="cReturnTop">返回顶部</div>'),$(window).scroll(function(){$(window).scrollTop()>300?$(".cReturnTop").fadeIn(1500):$(".cReturnTop").fadeOut(1500)}),$(".cReturnTop").click(function(){return $("body,html").animate({scrollTop:0},1e3),!1})}}),define("module/common/basics/common/login",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/Validform/5.3.2/Validform","lib/tizi_common/0.0.1/tizi_datatype","lib/tizi_common/0.0.1/tizi_valid","tizi_ajax","tiziDialog","validForm","tizi_datatype","tizi_validform"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/Validform/5.3.2/Validform");require("lib/tizi_common/0.0.1/tizi_datatype").dataType();exports.commonLogin=function(){require("lib/tizi_common/0.0.1/tizi_valid").indexLogin()}}),define("module/common/method/homePage/popDiv",[],function(require,exports){exports.selectCity=function(){$("#changeCity").click(function(e){e.stopPropagation(),$(this).find(".narrowDown").show(),$("#cityContent").show()}),$("#cityContent a").each(function(){$(this).click(function(e){var obj=$(this),area_id=obj.attr("area"),subject_type=$("#subjectName").attr("subject_type");e.stopPropagation(),$("#areaName").html(obj.text()),$("#areaName").attr("area",area_id),$("#changeCity .narrowDown").hide(),$("#cityContent").hide(),$.tizi_ajax({url:baseUrlName+"paper/paper_exam/position?area_id="+area_id+"&stype_id="+subject_type,type:"GET",dataType:"json",success:function(data){$("#expos_list").html(data.html)}})})}),$("body").click(function(){$("#changeCity .narrowDown").hide(),$("#cityContent").hide()})},exports.weixinContent=function(){$("#weixinDiv").click(function(e){e.stopPropagation(),$(this).find(".narrowDown").show(),$("#weixinDivContent").show()}),$("#weixinDivContent a").each(function(){$(this).click(function(e){e.stopPropagation(),$("#areaName").html($(this).text()),$("#weixinDiv .narrowDown").hide(),$("#weixinDivContent").hide()})}),$("body").click(function(){$("#weixinDiv .narrowDown").hide(),$("#weixinDivContent").hide()})},exports.srcMarquee=function(){function start(){timer=setInterval(scrolling,speed),pause||(obj.scrollTop+=1)}function scrolling(){obj.scrollTop%lineH!=0?(obj.scrollTop+=1,obj.scrollTop>=obj.scrollHeight/2&&(obj.scrollTop=0)):(clearInterval(timer),setTimeout(start,delay))}var timer,lineH=35,speed=30,delay=1e3,pause=!1,obj=document.getElementById("marqueeBox");obj.innerHTML+=obj.innerHTML,obj.onmouseover=function(){pause=!0},obj.onmouseout=function(){pause=!1},obj.scrollTop=0,setTimeout(start,delay)}}),define("module/common/method/common/tab",[],function(require,exports){exports.homePageTab=function(){$("#changeSubject a").live("click",function(){var obj=$(this),subject_type=obj.attr("sn"),area_id=$("#areaName").attr("area");$(this).addClass("active").siblings().removeClass("active"),$("#subjectName").attr("subject_type",obj.attr("sn")),$.tizi_ajax({url:baseUrlName+"paper/paper_exam/position?area_id="+area_id+"&stype_id="+subject_type,type:"GET",dataType:"json",success:function(data){$("#expos_list").html(data.html)}})})},exports.stuPraticeTab=function(clickObj,className,showObj){clickObj.click(function(){var index=clickObj.index($(this));clickObj!=$(".stuNav li")&&($(".courseNav li").removeClass("stuOn"),$(".exercises .practiceBox").removeClass("dis"),$(".courseNav:eq("+index+") li").eq(0).addClass("stuOn"),$(".exercises:eq("+index+") .practiceBox").eq(0).addClass("dis")),clickObj.removeClass(className),$(this).addClass(className),showObj.removeClass("dis"),showObj.eq(index).addClass("dis")})},exports.gradeTab=function(){var reg=new RegExp("^.*/([1-6]{1})"),key=window.location.href.match(reg);null!=key&&(index=key[1]-1,$(".stuNav").find("a").removeClass("active"),$(".stuNav").find("a:eq("+index+")").addClass("active"))},exports.subjectTab=function(){var reg=new RegExp("^.*#([1-9]{1})"),key=window.location.href.match(reg);null!=key&&(index=key[1],$(".courseNav").find(".s_"+index).click())},exports.Tab=function(tabTit,on,tabCon){$(tabCon).each(function(){$(this).children().eq(0).show().siblings().hide()}),$(tabTit).each(function(){$(this).children().eq(0).addClass(on)}),$(tabTit).children().click(function(){$(this).addClass(on).siblings().removeClass(on);var index=$(tabTit).children().index(this);$(tabCon).children().eq(index).show().siblings().hide()})},exports.homeworkTab=function(tabTit,on,tabCon){$(tabCon).each(function(){$(this).children().eq(0).show().siblings().hide()}),$(tabTit).each(function(){$(this).children().eq(0).addClass(on)}),$(tabTit).children().click(function(){$(this).addClass(on).siblings().removeClass(on);var index=$(tabTit).children().index(this);$(tabCon).children().eq(index).show().siblings().hide()})}}),define("module/homePage/scroll",[],function(){var wind=function(id,w,ul,li,prev,next,ms){this.id=$(id),this.w=this.id.find(w),this.ul=$(ul),this.ulLi=this.ul.find(li),this.prev=$(prev),this.next=$(next),this.nextTarget=0,this.autoTimer=null,this.ms=ms};wind.prototype={start:function(){var _this=this;this.prev.click(function(){_this.prevFn()}),this.next.click(function(){_this.nextFn()}),this.id.hover(function(){clearInterval(_this.autoTimer),_this.prev.show(),_this.next.show()},function(){_this.autoTimer=setInterval(function(){_this.autoPlay()},_this.ms),_this.prev.hide(),_this.next.hide()}),clearInterval(this.autoTimer),this.autoTimer=setInterval(function(){_this.autoPlay()},_this.ms)},showSlides:function(index){this.ul.animate({left:-(this.ulLi.width()+14)*index+"px"})},prevFn:function(){this.nextTarget--,this.nextTarget<0&&(this.nextTarget=0),this.showSlides(this.nextTarget)},nextFn:function(){this.nextTarget++,this.lastItem(),this.showSlides(this.nextTarget)},lastItem:function(){this.nextTarget>4&&(this.nextTarget=4)},autoPlay:function(){this.nextTarget++,this.lastItem(),this.showSlides(this.nextTarget)}},new wind("#windS1",".w","#windS1Ul","li","#prev","#next",5e3).start()});