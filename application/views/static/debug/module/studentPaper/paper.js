define(function(require,exports){

    require("tizi_ajax");
    require("tiziDialog");
	
    //调用做作业
    exports.do_homework = function(){
        var back_pause_tpl = 
            '<p>暂停啦.</p>';
        var pause_tpl = '<p>暂停啦.</p>';
        var next_continue_tpl = 
            '确认要离开此页面?';
        var homework_id = $("input[name='homework_id']").val();
        var paper_id = $("input[name='paper_id']").val();
        $.ajax({
            url:baseUrlName+"student/class/paper/get_answer?"+ new Date().getTime(),
            data:"homework_id="+homework_id+'&paper_id='+paper_id,
            type:"GET",
            dataType:"json",
            success:function(data){
                if(data.status != 99) return;
                data = eval("("+data.msg+")");
                var online = data.online;
                var offline = data.offline;
                if(online !=''){
                    $.each(online,function(key,val){
                        $(".question_"+val.question_id).find(".answerOption").find("a:eq("+(Number(val.index)-1)+")").addClass("active");
                    })
                }
            }
        })

        $(".nextDo").click(function(){

            $.tiziDialog({
                content:next_continue_tpl,
				ok:function(){
					var homework_id = $("input[name='homework_id']").val();
					var paper_id = $("input[name='paper_id']").val();
					$.tizi_ajax({
						url:baseUrlName+"student/class/paper/pause",
						data:{
							"homework_id":homework_id,
							"paper_id":paper_id
						},
						type:"POST",
						success:function(data){
							window.location.href= baseUrlName+'student/class/homework';       
						}
					})
				},
				cancel:true
            })
        })

        $(".question").find(".answer_option").click(function(){
            $(this).parents(".question").find(".answer_option").attr("checked",false);
            $(this).attr("checked","checked");
        })

        $(".over").click(function(){
            var question_done_num = $(".question_option .active").length;
            var online_question_num = $(".question_option").length;
            if(question_done_num < online_question_num){
                $.tiziDialog({
                    content:'请完成所有选择题后再提交'
                })             
                return;
            }
            var submit_title = '';
            var submit_tpl = '确认要提交作业吗';
            $.tiziDialog({
                content:submit_tpl,
                ok:function(){
                    submit_work();
                },
                cancel:true
            })
        })

        $(".answerOption").find("a").click(function(){

            if($(this).hasClass("active")) return false;
			$(this).addClass("active").siblings().removeClass("active");
            var homework_id = $("input[name='homework_id']").val();
            var paper_id = $("input[name='paper_id']").val();
            var ac_len = $(this).parents(".answerOption").find("a.active").length;
            if(!ac_len){
                var c_num = parseInt($(".c_num").text())+1;
                $(".c_num").text(c_num);
            }
            var question_id = $(this).parents(".questionList").find(".q_id").val();
            var input = $(this).text();
            $.tizi_ajax({
                url:baseUrlName+"student/class/paper/online",
                data:{
					'homework_id':homework_id,
					'paper_id':paper_id,
					'q_id':question_id,
					'input':input
				},
                type:"POST",
                dataType : 'json',
                success:function(data){
					
                }
            })
        })  


        function submit_work(){

            var homework_id = $("input[name='homework_id']").val();
            var paper_id = $("input[name='paper_id']").val();
            $.tizi_ajax({
                url: baseUrlName+"student/class/paper/submit",
                data:{
                    "homework_id":homework_id,
                    "paper_id":paper_id
                },
                type:"POST",
                dataType:"json",
                success:function(data){
                    if(data.status == 99){
                        window.location.href = baseUrlName+"student/homework/paper/report/"+homework_id+'/'+paper_id;L
                    }else if(data.status == 2){
                        $.tiziDialog({
                            content:'请完成所有选择题后再提交'
                        })
                    }else{
                        //window.location.reload();
                    }
                }
            })
        }


    }

	exports.report = function(){

		$(".q_status").click(function(){

            var homework_id = $("input[name='homework_id']").val();
            var paper_id = $("input[name='paper_id']").val();
			var question_id = $(this).parents(".questionList").find(".question_id").val();
			var q_status = $(this).attr("val");
			var that = $(this);
			$.tizi_ajax({
				url:baseUrlName+"student/class/paper/offline",
				data:{
					'homework_id':homework_id,
					'paper_id':paper_id,
					'q_id':question_id,
					'q_status':q_status
				},
				type:"POST",
				dataType : 'json',
				success:function(data){
					if(data.status == 99){
						that.siblings().remove();
						if(q_status == 1){
							that.parents(".questionList").addClass("error");
							that.addClass("errorBtn");
						}else{
							that.parents(".questionList").addClass("right");
							that.addClass("active");
						}
					}
				}
			})
		})
	}

    
});
