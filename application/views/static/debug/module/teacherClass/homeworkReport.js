// 这是检查作业的的脚本文件
// author shanghongliang
// date 2014-07-17 11:36
define(function(require,exports){
	exports.init=function(){
		//报告的tab切换绑定
		$(".subjectTab").click(function(){
			var _subId=$(this).attr("subject_id");
			$(this).parent().children(".subjectTab").removeClass("active");
			$(this).addClass("active");
			$(".reportTable").removeClass("active");
			$(".reportTable[subject_id="+_subId+"]").addClass("active");
		});
	}
});