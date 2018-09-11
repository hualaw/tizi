define(function(require,exports){
	require('tizi_ajax');
	require('tiziDialog');

	exports.homeworkArchive = {
		init:function(){
			this.page();
            this.initPage();
		},
        initPage:function(){
            if(typeof archive_page != 'function'){
                archive_page = function(page){
                    seajs.use('module/teacherHomework/homeworkArchive',function(ex){
                        ex.homeworkArchive.page(page);
                    });
                }
            }
        },
		page:function(page){
			var type = $('#filter_type').attr('data_type');
		    this.randerPage(page,type);
		},
		randerPage:function(page,type){
		    if(page=='' || page==null || page==undefined){  
		        page = '';  
		    }
		    content = '';  
		    $.tizi_ajax({
	            type: "GET",  
	            dataType: "json",  
	            url: baseUrlName + 'paper/homework_archive/', //提交到一般处理程序请求数据 
	            async:false, 
	            data: {'rf':true,'type':type,'page':page,ver:(new Date).valueOf()},                 
	            success: function(data) {
	            	if(data.errorcode){
	                	if(data!='') {
	                		$(".archive_list").html(data.html);//将返回的数据载入页面
		    				require('module/common/method/common/height').leftMenuBg();

		    				seajs.use('module/teacherHomework/homeworkDownload',function(ex){
					            ex.homeworkDownload.init();
					        });
		    			}
	                }else{
	                	$.tiziDialog({content:data.error});
	                }
	            }  
		    });
		},
		delete_log:function(page,type,slid){
		    if(page=='' || page==null || page==undefined){  
		        page = '';  
		    }  
		    content = '';  
		    $.tizi_ajax({
	            type: "POST",
	            dataType: "json",  
	            url: baseUrlName + "paper/homework_archive/delete_save_log", //提交到一般处理程序请求数据 
	            async:false, 
	            data: {'slid':slid},                 
	            success: function(data) {
	                if(data.errorcode == true) {
	                    seajs.use('module/teacherHomework/homeworkArchive',function(ex){
				            ex.homeworkArchive.randerPage(page,type);
				        });
	                }else{
	                   $.tiziDialog({content:data.error});
                    }
	            }
		    });
		}
	};
	var homeworkValid = require("module/common/basics/teacherHomework/homeworkValid");
	// 获取当前时间年月日
	exports.getCanlender = function(){
        var w = $('.Wdate');
        var myDate = new Date();
        var y = myDate.getFullYear();    
        var m = myDate.getMonth()+1;       
        var d = myDate.getDate();  
        if(m<10){
        	m = '0' + m;
        }else{
        	m = m;
        }
        if(d<10){
        	d = '0'+d;
        }else{
        	d = d;
        }
        var h = y+'-'+m+'-'+d; 
        w.attr('value',h); 
        $('#start_hour').val(myDate.getHours());
        $('.down_start_hour').val(myDate.getHours());
	};
	exports.setHomework = function(){
		exports.getCanlender();
        //点击布置作业
		seajs.use(['wdatePicker','tizi_ajax'],function(){
			$('.setHomework').live('click',function(){
                //获取布置框
                var empty = false;
                if($('.setHomeworkForm_beta').length<1)
                {
                    $.tizi_ajax({
                        type: "GET",  
                        dataType: "json",  
                        url: baseUrlName + "exercise_plan/exercise_plan_controller/assign_box",
                        async:false, 
                        data: {},
                        success: function(data) {
                            if(data.errorcode == true) { 
                                if(data.empty)empty=true;
                                $('#setHomeworkPop').html(data.html);
                            }else{
                                return false;
                            }
                        }
                    });
                }

                $('.ex_name').val($(this).attr('logname'));
                $('.paper_id').val($(this).attr('paper_id'));
                if(!empty){
                    $.tiziDialog({
                        title:'布置试卷',
                        content:$('#setHomeworkPop').html().replace('setHomeworkForm_beta','setHomeworkForm'),
                        icon:null,
                        width:600,
                        ok:function(){
                            // 在下作答起止时间判断
                            if($('.answerStyle').find('input').eq(0).attr('checked') == 'checked') {
                                var startTime = $('.start_day').val()+$('#start_hour').val()+$('#start_min').val();
                                var endTime = $('.end_day').val()+$('#end_hour').val()+$('#end_min').val();
                                if(startTime >= endTime){
                                    $.tiziDialog({
                                        title:'提示',
                                        content:'开始时间不能晚于结束时间！',
                                        width:600,
                                        ok:function(){
                                        },
                                        cancel:true
                                    });
                                    return false;
                                }
                            }
                            $('.setHomeworkForm').submit();
                            return false;
                            
                        },
                        cancel:true
                    });
                    //前端验证
                    homeworkValid.setHomeworkVid();
                }else{
                    $.tiziDialog({
                        title:'请先创建班级',
                        content:$('#setHomeworkPop').html().replace('setHomeworkForm_beta','setHomeworkForm'),
                        icon:null,
                        ok:function(){ window.location.href = baseUrlName + "teacher/class/my"; },
                        width:600,
                        cancel:true
                    });
                }
				
			});
		});
		// 删除作业存档
		$('.deleteHomework').live('click',function(){
            var save_id = $(this).attr('save_id');
            var num = $(this).attr('num');
            var that = $(this);
            $.tiziDialog({ // 确定要删除么
                content:'确定要删除么?',
                ok:function(){
                    $.tizi_ajax({url:"/ep/ep/del_homework_save",
                        type: 'POST',
                        data: {"save_id":save_id},
                        dataType: 'json',
                        success: function(data){
                        $.tiziDialog({
                            content:data.error,
                            ok:function(){
                                if(data.errorcode==true){
                                }
                            }
                        });  // tiziDialog
                        that.parents('tr').remove();
                        $('.num').each(function(){ //序号都自动减一
                            var _num = $(this).text();
                            if(_num>num){
                                _num -= 1;
                                $(this).text(_num);
                            }
                        });
                        $('a.deleteHomework').each(function(){ //序号都自动减一
                            var _num = $(this).attr('num');
                            if(_num>num){
                                _num -= 1;
                                $(this).attr('num',_num);
                            }
                        });
                      } // success end
                    });
                }, // ok end
                cancel:true
            });
		});
		// 删除下载过的作业
		$('.deleteDownHomework').live('click',function(){
			$(this).parents('tr').remove();
		});
	};
    //作答方式选择
    exports.answerStyle = function(){
		$(".answerStyle").live('click',function(){
			$('.homeworkTime').hide();
            $('.answerStyle').css('background','');
            
            $(this).find('input').attr('checked','checked');
            $(this).find('.homeworkTime').show();
            $(this).css('background','#daeeec');
            exports.getCanlender();
            exports.noticeShow();
           
		});
    };
    //选择学生答题顺序
    exports.answerOrder = function(){
        $(".answerOrder a").live('click',function(){
            $(this).find('input').attr('checked','checked');
            $('.answerOrder a').css('background','');
            $(this).css('background','#daeeec');
        });
    };
    //显示提示
    exports.noticeShow = function(){
        $('.answerStyle h4').find('span').hover(function(){
            $(this).siblings('p').show();
        },function(){
            $(this).siblings('p').hide();
        });
    };
});