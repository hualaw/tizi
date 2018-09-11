define(function(require,exports){
	exports.callback = function(){
		$(".creatNewClassForm").Validform().resetStatus();
		$('.creatNewClassForm').submit();
		return false;
	}

	exports.close = function(){
		$(".creatNewClassForm").Validform().resetStatus();
		return false;
	}

});