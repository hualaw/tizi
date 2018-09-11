define(function(require,exports){
	require('tizi_ajax');
	require('tiziDialog');

	exports.paperExam = {
		init:function(){
            this.setOptionVal();
			this.page();
			this.initPage();
		},
		initPage:function(){
			if(typeof exam_page != 'function'){
				exam_page = function(page){
					seajs.use('module/teacherPaper/paperExam',function(ex){
	            		ex.paperExam.page(page);
	        		});
	        	}
	        }
		},
		page:function(page){
		    this.randerPage(page);
		},
		randerPage:function(page){
            getData = {
                "stype":$("#optionVal").attr("data-subject"),
                "gtype":$("#optionVal").attr("data-grade"),
                "etype":$("#optionVal").attr("data-type"),
                "atype":$("#optionVal").attr("data-province"),
                "page":page,
                "ver":(new Date).valueOf()
            }
		    $.tizi_ajax({
	            type: "GET",  
	            dataType: "json",  
	            url: baseUrlName + 'paper/paper_exam/get_exam', //提交到一般处理程序请求数据 
	            async:false, 
	            data: getData,                 
	            success: function(data) {
	            	if(data.errorcode){
	                	if(data!='') {
	                		$(".exam_list").html(data.html);//将返回的数据载入页面
		    				require('module/common/method/common/height').leftMenuBg();
		    			}
	                }else{
	                	$.tiziDialog({content:data.error});
	                }
	            }  
		    });
		},
        setOptionVal:function(){
            var paperExam = this;
            var getData ={};
            $('.optionList a').each(function(){
                $(this).live('click',function(){
                    $(this).addClass('active').parent().siblings().find('a').removeClass('active');
                    var title = $(this).parents(".optionList").data("title");
                    var type = $(this).parents(".optionList").data("count");
                    var val = $(this).attr(type);
                    //填充
                    $("#optionVal").attr("data-"+title,val);
                    var page = $('.pagination .active').text();
                    paperExam.randerPage(page);
                });
            })
        }
	}

});