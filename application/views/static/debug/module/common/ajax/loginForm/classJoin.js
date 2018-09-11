define(function(require,exports){
	exports.callback = function(){
		$(".inVislbleClassForm").Validform().resetStatus();
		$('.inVislbleClassForm').submit();
		return false;
	}

	exports.close = function(){
		$(".inVislbleClassForm").Validform().resetStatus();
		return false;
	}
	
});