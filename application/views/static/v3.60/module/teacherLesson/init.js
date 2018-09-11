define("module/teacherLesson/init",["lib/JPlaceholder/0.0.2/JPlaceholder","module/teacherLesson/lesson_prepare","tiziDialog","module/common/method/common/height","tizi_validform","tizi_download","module/teacherLesson/lessonStar","tizi_ajax"],function(require){require("lib/JPlaceholder/0.0.2/JPlaceholder").JPlaceHolder.init(),require("module/teacherLesson/lesson_prepare").lessonInit(),require("module/teacherLesson/lessonStar").starHover(),require("module/teacherLesson/lessonStar").starClick()}),define("lib/JPlaceholder/0.0.2/JPlaceholder",[],function(require,exports){var JPlaceHolder={_check:function(){return"placeholder"in document.createElement("input")},init:function(){this._check()||this.fix()},fix:function(){jQuery(":input[placeholder]").each(function(){var self=$(this),txt=self.attr("placeholder"),pos=self.position(),w=self.outerWidth(!0),h=self.outerHeight(!0),paddingleft=self.css("padding-left"),holder=$('<em class="fixIe6"></em>').text(txt).css({position:"absolute",left:pos.left,top:pos.top,width:w,height:h,lienHeight:h,paddingLeft:paddingleft,color:"#aaa"}).appendTo(self.parent());self.focusin(function(){holder.hide()}).focusout(function(){self.val()||holder.show()}),holder.click(function(){holder.hide(),self.focus()})})}};exports.JPlaceHolder=JPlaceHolder}),define("module/teacherLesson/lesson_prepare",["lib/artDialog/4.1.7/artDialog","module/common/method/common/height","lib/tizi_common/0.0.1/tizi_validform","tizi_ajax","md5","tiziDialog","cookies","lib/tizi_common/0.0.1/tizi_download"],function(require,exports){require("lib/artDialog/4.1.7/artDialog");var lessonPrepare={typeCur:$(".current-type"),treeList:$(".tree-list"),docList:$(".doc-list"),treeItem:$(".tree-list a"),filterLink:$(".head-type a"),filterOrderDate:$("#odate"),filterOrderPage:$("#opage"),filterOrderSize:$("#osize"),downDoc:$(".doc-down"),subjectLink:$(".subject-chose"),mainContent:$(".child-content .content-wrap"),submitBtn:$("#search_submit"),init:function(){lessonPrepare.page(1),this.treeItem.on("click",function(){var this_a=this;lessonPrepare.treeItemClick(this_a)}),this.filterLink.click(function(){var this_a=this;lessonPrepare.filterQuestionClick(this_a)}),this.filterOrderDate.live("click",function(){var this_a=this;lessonPrepare.filterQuestionClick(this_a)}),this.filterOrderPage.live("click",function(){var this_a=this;lessonPrepare.filterQuestionPage(this_a)}),this.filterOrderSize.live("click",function(){var this_a=this;lessonPrepare.filterQuestionSize(this_a)}),this.submitBtn.live("click",function(){$(".head-type").find("a").removeClass("current"),$(".all-type").addClass("current"),lessonPrepare.submitSearch(1)}),$(".doc-down").removeClass("undisIm"),this.downDoc.live("click",function(){var this_a=this;lessonPrepare.doc_down_auth(this_a,null,null)}),$(".searchText").live("keypress",function(event){13==event.keyCode&&($(".searchText").blur(),$(".head-type").find("a").removeClass("current"),$(".all-type").addClass("current"),lessonPrepare.submitSearch(1))})},getBaseUrl:function(){return"lesson/lesson_prepare/"},getUrlData:function(ele){ele=ele||1;var nselectVal=$(".tree-list .active").data()||{nselect:$(".current-type").data("cselect")},selectOrderType=1,selectOrder=$(".head-order .active").eq(0).data("order")||0;"1"==selectOrder&&$("#opage").hasClass("up")&&(selectOrderType=2),"2"==selectOrder&&$("#osize").hasClass("up")&&(selectOrderType=2);var finalData=$.extend({page:ele},nselectVal,$(".current-type").data(),$(".head-type  .active").eq(0).data(),{order:selectOrder},{otype:selectOrderType});return finalData},treeItemClick:function(this_a){if($(this_a).hasClass("active"))return!1;this.treeList.find("a").removeClass("active"),$(this_a).addClass("active");var urlData=this.getUrlData();this.get_document(urlData)},filterQuestionClick:function(this_a){if("active"==$(this_a).attr("class"))return!1;$(this_a).parent().find("a").removeClass("active"),$(this_a).addClass("active");var ckeyword=$(".seachResult").find("em").text();if(ckeyword)this.submitSearch(1);else{var urlData=this.getUrlData();this.get_document(urlData)}},filterQuestionPage:function(this_a){$(this_a).parent().find("a").removeClass("active"),$(this_a).addClass("active"),$(this_a).hasClass("up")?($(this_a).removeClass("up"),$(this_a).addClass("orther")):$(this_a).hasClass("orther")?($(this_a).removeClass("orther"),$(this_a).addClass("up")):$(this_a).addClass("orther");var ckeyword=$(".seachResult").find("em").text();if(ckeyword)this.submitSearch(1);else{var urlData=this.getUrlData();this.get_document(urlData)}},filterQuestionSize:function(this_a){$(this_a).parent().find("a").removeClass("active"),$(this_a).addClass("active"),$(this_a).hasClass("up")?($(this_a).removeClass("up"),$(this_a).addClass("orther")):$(this_a).hasClass("orther")?($(this_a).removeClass("orther"),$(this_a).addClass("up")):$(this_a).addClass("orther");var ckeyword=$(".seachResult").find("em").text();if(ckeyword)this.submitSearch(1);else{var urlData=this.getUrlData();this.get_document(urlData)}},get_document:function(getData){var base_url=this.getBaseUrl();getData.ver=(new Date).valueOf(),seajs.use("tizi_ajax",function(){$.tizi_ajax({url:baseUrlName+base_url+"get_document",type:"GET",data:getData,dataType:"json",success:function(data){1==data.errorcode?($(".doc-list").html(data.html),require("module/common/method/common/height").leftMenuBg()):$.tiziDialog({content:data.error})}})})},initScrollBar:function(){var scrollPanel=$(".scrollPanel");if(scrollPanel.length>0){var scrollTreePanel1=$("#scrollTreePanel1");$("#scrollTreeContent1").height(600),$("#scrollTreeContent1 .Scroller-Container").css({position:"absolute",left:"0",top:"0",width:"220px"}),scrollTreePanel1.removeClass("undis"),seajs.use("module/teacherLesson/scrollPanel",function(exports){{var scroller=new exports.jsScroller(document.getElementById("scrollTreeContent1"),210,600);new exports.jsScrollbar(document.getElementById("scrollTreePanel1"),scroller,!1)}})}},page:function(page){var docCheck=$("#docnum").html();return""!=docCheck&&void 0!==docCheck?!1:(this.get_document(this.getUrlData(page)),void 0)},doc_down_auth:function(this_a,file_id,unlock){var this_a1=this_a,file_id1=file_id,unlock1=unlock;seajs.use("tizi_login_form",function(ex){ex.loginCheck("function",function(){null==unlock&&null==file_id?lessonPrepare.docDownloadBox(this_a1,function(){lessonPrepare.doc_down_status(this_a1)}):lessonPrepare.docDownloadBox(this_a1,function(){lessonPrepare.document_download(file_id1,unlock1)})})})},doc_down_status:function(this_a){if("disabled"==$(this_a).attr("disabled"))return!1;$(this_a).attr("disabled","disabled");var doc_num=$(this_a).data("num");lessonPrepare.document_download(doc_num,function(){$(this_a).removeAttr("disabled")})},docDownloadBox:function(obj,fn){Number($(".docCaptchaWord").attr("download-doc-count")),Number($(".docCaptchaWord").attr("download-doc-limit"));$.tiziDialog({title:"下载文档",content:$("#confirmDownLoad").html().replace("DocDownBox_tpl","DocDownBox").replace("DocDownBoxWord_tpl","DocDownBoxWord"),icon:null,width:500,init:function(){require("lib/tizi_common/0.0.1/tizi_validform").changeCaptcha("DocDownBox"),require("lib/tizi_common/0.0.1/tizi_validform").bindChangeVerify("DocDownBox")},ok:function(){var checkcode=require("lib/tizi_common/0.0.1/tizi_validform").checkCaptcha("DocDownBox",1,1);return checkcode?(fn(),void 0):!1},cancel:!0})},document_download:function(file_id,unlock){var baseuri=baseUrlName+"lesson/lesson_document/download_verify",subject_id=$(".subject-chose").data("subject"),post_data={subject_id:subject_id,file_id:file_id,captcha_word:$(".DocDownBoxWord").val(),captcha_name:"DocDownBox"};seajs.use("tizi_ajax",function(){$.tizi_ajax({url:baseuri,type:"POST",data:post_data,dataType:"json",success:function(data){if(0==data.errorcode)$.tiziDialog({content:data.error});else{var down_url=baseUrlName+"download/doc?url="+data.file_path+"&file_name="+data.file_name;ga("send","event","Download-Lesson-doc","Download",data.fname),require("lib/tizi_common/0.0.1/tizi_download").force_download(down_url,data.fname)}null!=unlock&&unlock()}})})},getSearchData:function(ele){ele=ele||1;var ckeyword=$(".seachResult").find("em").text();if(ckeyword)var keyword=ckeyword;else{var keyword=$(".searchText").val();if("请输入关键字"==keyword&&(keyword=""),""==keyword)return!1;ga("send","event","Search-Lesson","Search",keyword)}var subjectId=$(".subject-chose").data("subject"),nselectVal=$(".tree-list .active").data()||{nselect:$(".current-type").data("cselect")},finalData=$.extend({page:ele},{skeyword:keyword},{sid:subjectId},nselectVal,$(".current-type").data(),$(".head-type  .active").eq(0).data(),{order:0});return finalData},submitSearch:function(page){var getData=this.getSearchData(page);return getData===!1?($.tiziDialog({content:"请输入关键字"}),!1):(getData.ver=(new Date).valueOf(),$(this.submitBtn).addClass("disabled"),seajs.use("tizi_ajax",function(){$.tizi_ajax({url:baseUrlName+"lesson/lesson_prepare/lesson_search",type:"GET",data:getData,dataType:"json",success:function(data){$(this.submitBtn).removeClass("disabled"),1==data.errorcode?($(".doc-list").html(data.html),require("module/common/method/common/height").leftMenuBg()):$.tiziDialog({content:data.error})}})}),void 0)},sPage:function(page){this.submitSearch(page)}};exports.lessonInit=function(){lessonPrepare.init()},exports.lessonPage=function(page){lessonPrepare.page(page)},exports.searchPage=function(page){lessonPrepare.sPage(page)},exports.lessonDown=function(this_a,file_id,unlock){lessonPrepare.doc_down_auth(this_a,file_id,unlock)},exports.swfObjectInit=function(swfversion,jsversion){seajs.use(staticBaseUrlName+"flash/lessonFlash/swfobject",function(){var lessonUrl=staticBaseUrlName+"flash/lessonFlash/",swfVersionStr="11.4.0",xiSwfUrlStr=lessonUrl+"playerProductInstall.swf"+jsversion,flashvars={},params={},flash_height=exports.swfObjectHeight(),docExt=$("#docnum").data("ext");switch(docExt){case"doc":flash_height=700;break;case"docx":flash_height=700;break;case"ppt":flash_height=590;break;case"pptx":flash_height=590;break;default:flash_height=700}Teacher.lesson.prepare.swfObjectHeight=flash_height,params.quality="high",params.bgcolor="#ffffff",params.wmode="transparent",params.wmode="Opaque",params.allowscriptaccess="sameDomain",params.allowfullscreen="true";var attributes={};attributes.id="TiZiPaper",attributes.name="TiZiPaper",attributes.align="middle";var swfUrl=lessonUrl+"TiZiPaper.swf"+swfversion;swfobject.embedSWF(swfUrl,"flashContent","750",flash_height+5,swfVersionStr,xiSwfUrlStr,flashvars,params,attributes),swfobject.createCSS("#flashContent","display:block;text-align:left;")})},exports.swfObjectHeight=function(){var contentBoxH=$(".contentBox").position().top,flash_height=$(document).height()-contentBoxH;return $("#flashContent").height(flash_height),flash_height}}),define("module/common/method/common/height",[],function(require,exports){exports.leftMenuBg=function(){var _wrapContentHeight=$("#wrapContent").height(),_slideHeight=$("#slide").height();_wrapContentHeight>_slideHeight?$("#slide").height(_wrapContentHeight):$("#wrapContent").height(_slideHeight)}}),define("module/teacherLesson/lessonStar",["lib/tizi_ajax/0.0.1/tizi_ajax","lib/artDialog/4.1.7/artDialog"],function(require,exports){require("lib/tizi_ajax/0.0.1/tizi_ajax"),require("lib/artDialog/4.1.7/artDialog"),exports.starHover=function(){var levelContent=["很差","较差","还行","推荐","力荐"],_length=$("#satrRating a").length;$("#satrRating a").hover(function(){var _index=$(this).index();for($(".level-text").html(levelContent[_index-1]),i=0;_index>=i;i++)$($("#satrRating a")[i-1]).addClass("all")},function(){for($(".level-text").html("评星"),i=0;_length>i;i++)$($("#satrRating a")[i]).removeClass("all")})},exports.starClick=function(){$("#satrRating a").length;$("#satrRating a").click(function(){var _index=$(this).index(),levelVal=$(this).data("level"),docID=$("#docnum").text();$.tizi_ajax({type:"GET",dataType:"json",url:baseUrlName+"lesson/lesson_prepare/assess_star",data:{score:levelVal,doc_id:docID,ver:(new Date).valueOf()},success:function(data){if(data.error_code){for(i=0;_index>=i;i++)$($("#satrRating a")[i-1]).addClass("all");$("#satrRating a").unbind("hover"),$("#satrRating a").unbind("click"),$.tiziDialog({content:data.error,icon:"succeed"})}else $.tiziDialog({content:data.error})}})})}});