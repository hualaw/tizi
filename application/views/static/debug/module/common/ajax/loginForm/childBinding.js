define(function(require,exports){
	exports.callback = function(){
		$(".bindChildForm").Validform().resetStatus();
		$('.bindChildForm').submit();
		return false;
	}

	exports.close = function(){
		$(".bindChildForm").Validform().resetStatus();
		return false;
	}

});