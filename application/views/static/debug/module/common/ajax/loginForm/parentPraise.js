define(function(require,exports){
	exports.callback = function(){
		$("span.praise").click();
		return false;
	}

	exports.close = function(){
		return false;
	}
	
});