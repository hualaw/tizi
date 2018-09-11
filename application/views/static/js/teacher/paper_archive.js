/*试卷存档开始*/
Teacher.paper.archive = {
	init:function(){
		Teacher.paper.archive.page();		
	},
	page:function(page){
		var type = $('#filter_type').attr('data_type');
	    this.randerPage(page,type);
	},
	randerPage:function(page,type){
	    var sid = $('.subject-chose').data('subject');
	    if(page=='' || page==null || page==undefined){  
	        page = '';  
	    }
	    content = '';  
	    $.tizi_ajax({   
            type: "GET",  
            dataType: "json",  
            url: baseUrlName + "paper/paper_archive", //提交到一般处理程序请求数据 
            async:false, 
            data: {'sid':sid,'rf':true,'type':type,'page':page,ver:(new Date).valueOf()},                 
            success: function(data) {
            	if(data.errorcode){
                	if(data!='') $(".archive_list").html(data.html);//将返回的数据载入页面
                }else{
                	$.tiziDialog({content:data.error});
                }
            }  
	    });
	},
	delete_log:function(page,type,slid){
	    var sid = $('.subject-chose').data('subject');
	    if(page=='' || page==null || page==undefined){  
	        page = '';  
	    }  
	    content = '';  
	    $.tizi_ajax({
            type: "POST",  
            dataType: "json",  
            url: baseUrlName +  + "paper/paper_archive/delete_save_log", //提交到一般处理程序请求数据 
            async:false, 
            data: {'sid':sid,'slid':slid},                 
            success: function(data) {
                if(data.errorcode == true) {
                    Teacher.paper.archive.randerPage(1,type);
                }
                $.tiziDialog({content:data.error});
            }  
	    });
	}
}
/*试卷存档结束*/