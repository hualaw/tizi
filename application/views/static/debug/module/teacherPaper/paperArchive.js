define(function(require,exports){
	require('tizi_ajax');
	require('tiziDialog');

	exports.paperArchive = {
		init:function(){
			this.page();
			this.initPage();
		},
        initPage:function(){
            if(typeof archive_page != 'function'){
                archive_page = function(page){
                    seajs.use('module/teacherPaper/paperArchive',function(ex){
                        ex.paperArchive.page(page);
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
	            url: baseUrlName + "paper/paper_archive/", //提交到一般处理程序请求数据 
	            async:false, 
	            data: {'rf':true,'type':type,'page':page,ver:(new Date).valueOf()},                 
	            success: function(data) {
	            	if(data.errorcode){
	                	if(data!='') {
	                		$(".archive_list").html(data.html);//将返回的数据载入页面
		    				require('module/common/method/common/height').leftMenuBg();
		    				
		    				seajs.use('module/teacherPaper/paperDownload',function(ex){
					            ex.paperDownload.init();
					        });

				            //archive按钮
				            seajs.use('module/teacherPaper/menu',function(menu){
				                menu.downLoanMenu();
				                menu.collocation();
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
	            url: baseUrlName + "paper/paper_archive/delete_save_log", //提交到一般处理程序请求数据 
	            async:false, 
	            data: {'slid':slid},                 
	            success: function(data) {
	                if(data.errorcode == true) {
	                    seajs.use('module/teacherPaper/paperArchive',function(ex){
				            ex.paperArchive.randerPage(page,type);
				        });
	                }else{
	                	$.tiziDialog({content:data.error});
	                }
	            }
		    });
		}
	}
});