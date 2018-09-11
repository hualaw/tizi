define(function(require,exports){
	//重新登录
	exports.show_login = function(){
		$.tiziDialog({
			content : json.msg,
			icon : 'question',
			ok : function(){
				window.location.href = baseUrlName + 'login';
			},
			cancel : true
		});
	}

})