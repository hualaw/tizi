define(function(require,exports){
    //学生 专项练习开始
    require("tizi_ajax");
    require("tiziDialog");
    exports.init = function(){
		exports.select_option();
	}
	exports.select_option = function(){
		$(".action_ft a").click(function(){
			var option = $(this).text();
			var training_id = $("input[name='training_id']").val();
			$.tizi_ajax({
				url: baseUrlName+'practice/practice_training/next_question',
				data: {'option':option, 'id':training_id},
				type:'POST',
				dataType: 'json',
				success:function(data){
					if(data.status == 99){
						$(".action_aq").html(data.msg);
						$(".correct_num").text(parseInt($(".correct_num").text())+1);
					}else if(data.status == 1){
						window.location.href = baseUrlName;
					}else{
						window.location.href = baseUrlName+'practice/training/complete/'+training_id;
					}
				}
			})
		})
	}
});
