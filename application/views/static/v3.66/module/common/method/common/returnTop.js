define("module/common/method/common/returnTop",[],function(require,exports){exports.cReturnTopfn=function(){$(".cReturnTop").length<1&&$("body").append('<div class="cReturnTop">返回顶部</div>'),$(window).scroll(function(){$(window).scrollTop()>300?$(".cReturnTop").fadeIn(1500):$(".cReturnTop").fadeOut(1500)}),$(".cReturnTop").click(function(){return $("body,html").animate({scrollTop:0},1e3),!1})}});