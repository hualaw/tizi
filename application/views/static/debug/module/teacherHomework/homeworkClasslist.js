// 这是班级列表的脚本文件
// author shanghongliang
// date 2014-07-17 11：27 
define(function(require,exports){
	exports.init=function(){
		//绑定作业falsh动画弹出框
		$(".homeworkList>li.gameItem").click(function(e){
			//先发送ajax

			//弹框
            var unit_id = $(this).attr('unit_id');
            var game_id = $(this).attr('game_id');
            var game_type_id = $(this).attr('game_type_id');
            var data = '<iframe src="'+baseUrlName+'class/game/preview/'+unit_id+'/'+game_id+'/'+game_type_id+'" width="700" height="500" marginwidth="0" marginheight="0" scrolling="no" frameborder="0"></iframe>';
            $.tiziDialog({
                icon:null,
                title:'体验作业',
                content: data,
                width:700,
                height:500,
                ok:false
            });

			e.preventDefault();
		});
		//删除作业绑定
		$(".delHomework").click(function(e){
			var zyid= $(this).attr('zuoye_id');
			$.tiziDialog({
				title:'提示',
				content:'确定删除该作业吗？',
				icon:'question',
				ok:function(){
					$.tizi_ajax({
			            type: "POST",  
			            dataType: "json",  
			            url: baseUrlName + 'homework/teacher_homework/del/'+zyid,
			            data: {},
			            success: function(data) {
			            	if(data.errorcode){
			            		$.tiziDialog({
			            			content:data.error,
			            			icon:'succeed',
			            			ok:function(){window.location.reload();}
		    					});
			                }else{
			                	$.tiziDialog({content:'系统繁忙，请稍候再试'});
			                }
			            }  
				    });
				},
				cancel:true
			})
		});
	}
});
