define("module/teacherUnLogin/paper",["module/common/basics/common/highlight"],function(require){var _pageName=$(".mainContainer").attr("pagename");switch(_pageName){case"examHall":seajs.use("module/teacherPaper/paperExam",function(ex){ex.paperExam.init()})}require("module/common/basics/common/highlight").highlightMenu()}),define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}});