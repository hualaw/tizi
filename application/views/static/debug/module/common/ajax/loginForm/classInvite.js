define(function(require,exports){
	exports.callback = function(){
		$("#accept").click();
		return false;
	}

	exports.close = function(){
		return false;
	}

});