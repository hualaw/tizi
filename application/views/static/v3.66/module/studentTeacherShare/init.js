define("module/studentTeacherShare/init",["module/common/basics/common/highlight","module/common/method/common/height"],function(require){require("module/common/basics/common/highlight").highlightMenu(),require("module/common/method/common/height").leftMenuBg()}),define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}}),define("module/common/method/common/height",[],function(require,exports){exports.leftMenuBg=function(){var _wrapContentHeight=$("#wrapContent").height(),_slideHeight=$("#slide").height();_wrapContentHeight>_slideHeight?$("#slide").height(_wrapContentHeight):$("#wrapContent").height(_slideHeight)}});