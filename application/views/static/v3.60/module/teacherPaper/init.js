define("module/teacherPaper/init",["module/common/method/common/height","module/common/basics/common/highlight"],function(require){{var _pageName=$(".mainContainer").attr("pagename");$(".mainContainer").attr("tabname")}switch(_pageName){case"myQuestionLibrary":require("module/common/method/common/height").leftMenuBg();break;case"paperArchive":seajs.use("module/teacherPaper/paperArchive",function(ex){ex.paperArchive.init()});break;case"examHall":seajs.use("module/teacherPaper/paperExam",function(ex){ex.paperExam.init()})}require("module/common/basics/common/highlight").highlightMenu()}),define("module/common/method/common/height",[],function(require,exports){exports.leftMenuBg=function(){var _wrapContentHeight=$("#wrapContent").height(),_slideHeight=$("#slide").height();_wrapContentHeight>_slideHeight?$("#slide").height(_wrapContentHeight):$("#wrapContent").height(_slideHeight)}}),define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}});