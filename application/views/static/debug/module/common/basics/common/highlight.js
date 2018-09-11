define(function(require,exports){
	//个人中心判断左侧栏目高亮---根据右侧页面的pagename来判断左侧栏目高亮
	exports.highlightMenu = function(){
		if($('#wrapContent .mainContainer').attr('pagename') != undefined){
			$('#slide .menu li').each(function(i){
				if($('#wrapContent .mainContainer').attr('pagename') == $('#slide .menu li').eq(i).attr('name')){
					$('#slide .menu li').eq(i).find('a').addClass('active');	
				}	
			})
		};
	}
})