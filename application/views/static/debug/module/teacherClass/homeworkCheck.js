// 这是检查作业的的脚本文件
// author shanghongliang
// date 2014-07-17 11:36
define(function(require,exports){
	exports.homeworkCheck={
		init:function(){
			var _this=this;
			$(".operateLink").click(function(e){
				//写评语
				if($(this).hasClass("comment")){
					//将所有的学生取消选择
					$("#stuList [type=checkbox]").removeAttr("checked");
					//在checkbox中将当前学生选中
					$("#"+$(this).attr("student_id")).attr("checked","checked");
					_this.comment();
				//查看评语
				}else{
					//获取当前学生的评语
					$("#cDialogWrap .commentCont").html($(this).attr("cmt"));
					_this.lookComment();
				}
				e.preventDefault();
			});

		},
		//评论弹出框
		comment:function(){
			var _this=this;
			//弹框
			$.tiziDialog({
				title:'写评语',
				content:$("#wcDialogWrap").html().replace("commentForm_beta","commentForm"),
				icon:null,
				ok:function(){
					//提交评语
					_this.submitComment();
					return false;
				},
				cancel:true
			});
			//全选checkbox的clcik事件
			$("#selectAll").click(function(){
				var _val=$(this).attr("checked");
				if(_val){
					$("#stuList [type=checkbox]").attr("checked","checked");
				}else{
					$("#stuList [type=checkbox]").removeAttr("checked");
				}
			});
		},
		//查看评语弹出框
		lookComment:function(){
			//弹框
			$.tiziDialog({
				title:'查看评语',
				content:$("#cDialogWrap").html(),
				icon:null,
				okVal:"取消"
			});
		},
		//提交评语ajax
		submitComment:function(){
	        var _select=$(".commentForm #stuList input[type=checkbox]:checked"),stuIds=[],msg="";
        	_select.each(function(){
        		stuIds.push($(this).attr("student_id"));
        	});
        	var _comment=$("#commentInput").val().replace(/(^\s+)|(\s+$)/g,"")
        	if(!_comment){
        		msg="请编写评语内容";
        	}
        	if(_comment.length>100){
        		msg="评语字数请精简在100字以内";
        	}
        	if(stuIds.length===0){
        		msg="请选择一位学生";
        	}
        	if(msg){
        		//弹框
				$.tiziDialog({
					title:'提示',
					content:msg,
					icon:null
				});
				return ;
        	}
        	//发送平的ajax请求
            var ass_id = $('.ass_id').val();
	        $.tizi_ajax({
                'url' : baseUrlName + 'homework/teacher_check/give_cmt',
                'type' : 'POST',
                'dataType' : 'json',
                'data':{"stuIds":stuIds.join(","),"comment":_comment,'ass_id':ass_id},
                success : function(data, status){
					if(data.errorcode == true){
                        $.tiziDialog({
                            content:'添加成功',
                            close:function(){window.location.reload(); }
                        });
                    }else{
                        $.tiziDialog({content:'添加失败'});
                    }
                },
                error:function(data){
					$.tiziDialog({
						title:'提示',
						content:'系统繁忙，请稍候',
						icon:null
					});
                }
             });
		}
	};
});