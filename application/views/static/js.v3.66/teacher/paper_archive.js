Teacher.paper.archive={init:function(){Teacher.paper.archive.page()},page:function(page){var type=$("#filter_type").attr("data_type");this.randerPage(page,type)},randerPage:function(page,type){var sid=$(".subject-chose").data("subject");(""==page||null==page||void 0==page)&&(page=""),content="",$.tizi_ajax({type:"GET",dataType:"json",url:baseUrlName+"paper/"+baseNameSpace+"_archive",async:!1,data:{sid:sid,rf:!0,type:type,page:page,ver:(new Date).valueOf()},success:function(data){data.errorcode?""!=data&&$(".archive_list").html(data.html):$.tiziDialog({content:data.error})}})},delete_log:function(page,type,slid){var sid=$(".subject-chose").data("subject");(""==page||null==page||void 0==page)&&(page=""),content="",$.tizi_ajax({type:"POST",dataType:"json",url:baseUrlName+0/0+baseNameSpace+"_archive/delete_save_log",async:!1,data:{sid:sid,slid:slid},success:function(data){1==data.errorcode&&Teacher.paper.archive.randerPage(1,type),$.tiziDialog({content:data.error})}})}};