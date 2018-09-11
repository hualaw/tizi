define(function(require, exports){
	require('tizi_ajax');
	require('tiziDialog');
	exports.starHover=function(){
		var levelContent = ['很差','较差','还行','推荐','力荐'];
		var _length=$("#satrRating a").length;
		$("#satrRating a").hover(
			function(){
				var _index=$(this).index();
				$(".level-text").html(levelContent[_index-1]);
				for(i=0;i<=_index;i++){
				$($("#satrRating a")[i-1]).addClass("all");

				};
				
			},
			function(){
				$(".level-text").html("评星");
				for(i=0;i<_length;i++){
				$($("#satrRating a")[i]).removeClass("all");

				};
			})
	};
	exports.starClick=function(){
			var _length=$("#satrRating a").length;
			$("#satrRating a").click(function(){				
					var _index=$(this).index();
					var levelVal = $(this).data("level");
					var docID = $("#docnum").text();
					$.tizi_ajax({
						type: "GET",  
						dataType: "json",  
						url: baseUrlName + 'lesson/lesson_prepare/assess_star',
						data: {'score':levelVal,'doc_id':docID,ver:(new Date).valueOf()},
						success: function(data) {
							if(data.error_code){	            			
								for(i=0;i<=_index;i++){
									$($("#satrRating a")[i-1]).addClass("all");
								};
								$("#satrRating a").unbind("hover");				
								$("#satrRating a").unbind("click");
								$.tiziDialog({
									content:data.error,
									icon:'succeed'
								});	
							}else{
								$.tiziDialog({content:data.error});
							}
						}  
					});
			});
	};
});