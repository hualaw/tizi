define("module/studentOral/init",["module/common/basics/common/highlight"],function(require){var _pageName=$(".mainContainer").attr("pagename"),_tabName=$(".mainContainer").attr("tabname");switch(_pageName){case"studentOral":switch(_tabName){case"studentOralList":seajs.use("module/studentOral/studentOralList",function(_videolist){_videolist.init()});break;case"studentOral":seajs.use("module/studentOral/studentOral",function(_video){_video.init(grade,video)}),seajs.use("module/studentOral/AD",function(_ad){_ad.indexVideo()})}}require("module/common/basics/common/highlight").highlightMenu()}),define("module/common/basics/common/highlight",[],function(require,exports){exports.highlightMenu=function(){void 0!=$("#wrapContent .mainContainer").attr("pagename")&&$("#slide .menu li").each(function(i){$("#wrapContent .mainContainer").attr("pagename")==$("#slide .menu li").eq(i).attr("name")&&$("#slide .menu li").eq(i).find("a").addClass("active")})}});