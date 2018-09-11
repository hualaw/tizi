define(function(require,exports){
	$(".haveErrorForm").Validform().resetStatus();
	$('.haveErrorForm').submit();
	return false;
});