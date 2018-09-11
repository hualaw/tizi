define(function(require,exports){
	// 加载返回顶部脚本
	exports.cReturnTopfn = function(){
		if($('.cReturnTop').length < 1){
			$('body').append('<div class="cReturnTop">返回顶部</div>');
		};
		/*回到顶部*/
		$(window).scroll(function(){
			if ($(window).scrollTop()>300){
				$(".cReturnTop").fadeIn(1500);
			}else{
				$(".cReturnTop").fadeOut(1500);		
			}
		});
		/*回到顶部点击*/
		$('.cReturnTop').click(function(){
			$('body,html').animate({scrollTop:0},1000);
			return false;
		});
	}
});