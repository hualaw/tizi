define(function(require,exports){
	exports.callback = function(){
		$(".creatChildForm").Validform().resetStatus();
		$('.creatChildForm').submit();
		return false;
	}

	exports.close = function(){
		$(".creatChildForm").Validform().resetStatus();
		return false;
	}

});