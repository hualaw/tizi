define(function(require,exports){
	require('tizi_ajax');
	require('tiziDialog');
	require("wdatePicker");
	var homeworkValid = require("module/common/basics/teacherHomework/homeworkValid");
	// 下载下拉效果
	exports.downLoanMenu=function(){
		$(".downLoadBtn").click(function(e){
			e.stopPropagation();
			$('.paperListBox .downLoadBtn').css('z-index','10');
			$('.paperListBox .downLoadBtn').find("ul").hide();
			$(this).css('z-index','500');
			$(this).find("ul").show();
		});
		$('body').click(function(){
			$(".downLoadBtn ul").hide();
		})
	};
	// 点击布置试卷button
	exports.collocation=function(){
		$(".arrangement").click(function(){
			var that = $(this);
			//获取布置框
            var empty = false;
            var box_title = '';
             
            // if($('.setHomeworkForm_beta').length<1)
            // {
                $.tizi_ajax({
                    type: "GET",  
                    dataType: "json",  
                    url: baseUrlName + "homework/teacher_assign/assign_box",
                    async:false, 
                    data: {},
                    success: function(data) {
                        if(data.errorcode == true) { 
                            if(data.empty)empty=true;
                            box_title = data.box_title;
                            $('#setHomeworkPop').html(data.html);
                        }else{
                            return false;
                        }
                    }
                });
            // }
             
            $('.ex_name').val(that.attr('logname'));
            $('.paper_id').val(that.attr('paper_id'));
            $('.save_id').val(that.attr('save_id'));
            if(!empty){
                // if($('#ori_box').html().length>1){
                //     $('#setHomeworkPop').html($('#ori_box').html());
                // } 
                $.tiziDialog({
                    title:'留作业',
                    content:$('#setHomeworkPop').html().replace('setHomeworkForm_beta','setHomeworkForm'),
                    icon:null,
                    init:function(){
                         // 公共select模拟
                             seajs.use('tizi_select');
                            // 美化select
                              $('select').jqTransSelect();
                     },
                    ok:function(){
                        if($('.setHomeworkForm').length>0){
                            $('.setHomeworkForm').submit();
                            return false;
                        }else{//没有班级

                        }
                    },
                    cancel:true
                });
                //前端验证
                homeworkValid.setHomeworkVid();
            }else{
                $.tiziDialog({
                    title:box_title,
                    content:$('#setHomeworkPop').html().replace('setHomeworkForm_beta','setHomeworkForm'),
                    icon:null,
                    ok:function(){ 
                    	// window.location.href = baseUrlName + "teacher/class/my"; 
                    },
                    width:600,
                    cancel:true
                });
            }
		});
		$(".starDay").live("focus",function(){
			WdatePicker({maxDate:'#F{$dp.$D(\'endyDay\')||\'2020-10-01\'}'}) 

		})
		$(".endyDay").live("focus",function(){
			WdatePicker({minDate:'#F{$dp.$D(\'starDay\')}',maxDate:'2020-10-01'}) 

		})
	};


	
});