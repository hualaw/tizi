define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}});