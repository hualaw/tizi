define("module/parentCup/init",["module/parentCup/parentCup"],function(require){var _parentCup=require("module/parentCup/parentCup");_parentCup.changeBackColor(".searchList","li"),_parentCup.cupOn(),_parentCup.cupSearch()}),define("module/parentCup/parentCup",[],function(require,exports){exports.changeBackColor=function(par,child){$(par).find(child+":even").addClass("gray")},exports.cupOn=function(){for(var i=0;i<$(".cupNav li").length;i++){var catid=$(".cupNav").attr("data");22==catid?($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(1).addClass("curcupNav")):23==catid?($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(2).addClass("curcupNav")):24==catid?($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(3).addClass("curcupNav")):25==catid?($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(4).addClass("curcupNav")):26==catid?($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(5).addClass("curcupNav")):27==catid?($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(6).addClass("curcupNav")):28==catid&&($(".cupNav li").removeClass("curcupNav"),$(".cupNav li").eq(7).addClass("curcupNav"))}},exports.cupSearch=function(){$(".searchBtn").click(function(){$(".cupSearchForm").get(0).onsubmit=""==$(".cupSearchForm input[type=text]").val()?function(){return!1}:function(){}})}});