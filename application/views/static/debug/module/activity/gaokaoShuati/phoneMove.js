define(function (require,exports){
	//首页 动画
	exports.pnoneMove = function (){
		var c=0;
		function move(n){
			var _width=$(".moveBox").width();
			$(".pointerBox li").eq(n).addClass("active").siblings().removeClass("active");			
			$("#move").animate({
				left:-_width*n
			},100);
			$(".bannerBox li").eq(n).fadeIn(1000).siblings().hide();
		}
		$(".pointerBox li").click(function(){
			var n=$(this).index();
			c=n;
			move(n);
		})
		var timer=setInterval(function(){
			move(c);
			c++;
			if (c>=$(".pointerBox li").length) {
				c=0;
			};
		},6000)
	};
		


})