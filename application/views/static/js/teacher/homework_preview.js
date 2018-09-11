/*作业预览开始*/
Teacher.paper.homework_preview = {
	savePaper: $("#save-btn"),
	saveasPaper: $("#saveas-btn"),

	init:function(){
	    Teacher.paper.homework_preview.del_question();//删除
	    Teacher.paper.homework_preview.change_a_question();//换一题
	    Teacher.paper.homework_preview.click_q();//点击题目显示答案

		//设置试卷重置
		$('#reset-btn').click("click",function(){
			$.tiziDialog({
				content:"是否新建一份空白作业，并放弃所有未保存的修改？",
				cancel:true,
				ok:function(){Teacher.paper.homework_preview.reset_paper();}
			});
		});
		// 弹出框 下载标题 编辑 
		$('.edit_btn1').click(function(){
			$('#download_title').attr("disabled",false); 
			$(this).hide();
			$('.edit_btn2').show();
		});
		$('.edit_btn2').click(function(){
			$('#download_title').attr("disabled",true); 
			$(this).hide();
			$('.edit_btn1').show();
		});
		// 题目归档
		this.savePaper.click(function(){
			Teacher.paper.homework_preview.saveBtnClick(0);
	    });
	    // 题目归档
		this.saveasPaper.click(function(){
			Teacher.paper.homework_preview.saveBtnClick(1);
	    });
	},
	saveBtnClick:function(save_as){
		//判断作业题是否为空
		if($('.subjectBox').length <=0){
			$.tiziDialog({content:Teacher.paper.common.ErrorNoQuestion});
			return false;
		}
		callbackfn = function(){
			var preTitle = $('.preview-title h3').html();
			$.tiziDialog({
				id : 'commonSaveTitle',
				title:'保存作业',
				content:$('.saveHomeworkContent').html().replace('commonSaveTitleForm_beta','commonSaveTitleForm'),
				icon:null,
				width:600,
				ok:function(){
					$('.commonSaveTitleForm').submit();
					return false;
					//Teacher.paper.homework_preview.saveBtnClick(1);
				},
				cancel:true
			});
			$('.homework_title').val(preTitle);
			//前端验证
			Common.valid.homeworkSaveTitle(save_as);

			$('#tiziLogin').hide();
			$('#tiziRegister').hide();
		}
		seajs.use('tizi_login_form',function(ex){
			ex.loginCheck('function',callbackfn);
		});
	},
	//点击题目显示答案
	click_q:function(){
		$('.click_q').live('click',function(){
			//显示隐藏试卷答案
			if($((this)).find('.answer').hasClass('undis')) $(this).find('.answer').removeClass('undis');
			else $(this).find('.answer').addClass('undis');
		})
	},
    //换一题
    change_a_question:function(){
	    //换一题
	    $('.change_a_q').live('click',function(){
	    	this_a = this;
			if($(this_a).hasClass('disabled')){return;}
			$(this_a).addClass('disabled');

			var sid = $('.subject-chose').data('subject');
			var cid = $(this_a).attr('data-cid');
			var qtype = $(this_a).attr('data-qtype_id');
			var pqtype = qtype;
			var qid = $(this_a).attr('data-qid');
			var qlevel = $(this_a).attr('data-level');
			var qindex = $(this_a).parents('.question-item-box').find('.orders').text();
			var qids = new Array();
			$(this_a).parents('.preview-content').find('.change_a_q').each(function(i,e){
		        if($(e).attr('data-qorigin') === '0') qids.push($(e).attr('data-qid'));
		    });

			var param = {'sid':sid,'cid':cid,'qids':qids,'qindex':qindex,'qid':qid,'qtype':qtype,'pqtype':pqtype,'qlevel':qlevel};
			//console.log(data);

	    	Teacher.paper.homework_preview.change_inner_question(param,qid,function(){
				$('#q'+qid).find('.change_a_q').removeClass('disabled');
			},function(){
				$('#q'+qid).find('.change_a_q').removeClass('disabled');
			});
	    });
    },
    change_inner_question:function(param,qid,success,err){		
		$.tizi_ajax({
			type: "POST",
            dataType: "json",  
            url: baseUrlName+"paper/homework_question/change_question",
            data: param,
            success: function(data) {
				//判断是否有分页的显示loading图片
            	if(data.errorcode == true) {
            		$('#subjectBox'+qid).attr('id','subjectBox'+data.qid);       		
					$('#subjectBox'+data.qid).attr('data-pqid',data.pqid);
                	$('#subjectBox'+data.qid).html(data.html);
                	Teacher.paper.homework_preview.up_inner_question(function(){});
            		success();
            	}else{
            		$.tiziDialog({content:data.error});
            		err();
            	}
            }
		});
	},
    //预览时，删除某一道问题
    del_question:function(){
    	$('.del_q').live('click',function(){
            var qid = $(this).attr('data-qid');
            var qorigin = $(this).attr('data-qorigin');
            $.tiziDialog({
				content:"确定删除当前题目？",
				cancel:true,
				ok:function(){
					Teacher.paper.homework_preview.del_inner_question(qid,qorigin,function(this_a){
						$('#subjectBox'+qid).remove(); //右侧题目消失
       					Teacher.paper.homework_preview.subjectOrders();
       					var question_len = $('.subjectBox').length;
       					$('#homeworkSum').text(question_len);
					},function(){});

				}
			});

    	});
    	$('.toUp').live('click',function(){  //上升
    		if($(this).parent().parent().prev().hasClass('subjectBoxStart')){return};

			var onthis = $(this).parent().parent();
			var getup = $(this).parent().parent().prev();
			$(getup).before(onthis);
            
            //Teacher.paper.homework_preview.orderSort();
            $('.toUp').removeClass('toUp').addClass('toUp-2'); // 点击上升按钮去掉class
            Teacher.paper.homework_preview.up_inner_question(function(){
            	$('.toUp-2').addClass('toUp').removeClass('toUp-2');
            });//ajax
            Teacher.paper.homework_preview.subjectOrders();
        });
        $('.toDown').live('click',function(){  //下降
        	if($(this).parent().parent().next().hasClass('subjectBoxEnd')){return};

			var onthis = $(this).parent().parent();
			var getdown = $(this).parent().parent().next();
			$(getdown).after(onthis);

            //Teacher.paper.homework_preview.orderSort();
            $('.toDown').removeClass('toDown').addClass('toDown-2'); // 点击下降按钮去掉class
            Teacher.paper.homework_preview.up_inner_question(function(){
            	$('.toDown-2').addClass('toDown').removeClass('toDown-2');
            });//ajax
            Teacher.paper.homework_preview.subjectOrders();
        });
    },
    // 序号排序
    subjectOrders : function(){
		$('.subjectBox').find('.orders').each(function(index, element) {
			$(element).text(index+1);
		});
    },
    up_inner_question:function(unlock){
	    var qorder = new Array();
	    $('.subjectBox').each(function(i,e){
	        qorder.push($(e).attr('data-pqid'));
	    })
	    var url = baseUrlName + "paper/homework_preview/save_question_order";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'qtype':0,'sid':sid,"qorder":qorder.toString()}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode == false){
		            $.tiziDialog({content:data.error});
		        }
		        unlock();
		    }
		});
	},
	del_inner_question:function(qid,qorigin,success,err){
	    var url = baseUrlName + "paper/homework_question/remove_question_from_paper";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'qid':qid,'sid':sid,'qorigin':qorigin}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode==true){
		            success();
		            Teacher.paper.paper_common.randerCart(data.question_cart);
		        }else{
		            err();
		            $.tiziDialog({content:data.error});
		        }  
		    }
		});
	},
	reset_paper:function(){
	    var url = baseUrlName + "paper/homework_question/reset_paper";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'sid':sid}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode == true){
		            $.tiziDialog({ok:false,content:data.error});
		            location.href=baseUrlName + "teacher/homework/preview/" + sid;
		        }  
		        else{
		            $.tiziDialog({content:data.error});
		        }

		    }
		});
	}
}
/*作业预览结束*/