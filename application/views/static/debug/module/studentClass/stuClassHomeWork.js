define(function(require, exports){ 
    require("tiziDialog")
    student_homework_pagination = function(page){

        window.location.href = baseUrlName+'student/class/homework?page='+page;

    }

   exports.stuClassHomeWork=function(){
        $(".operation a.popBtn").click(function(e){
           e.stopPropagation();
        	$(this).siblings(".stuHomeWork").toggle();
          $(this).parents('tr').siblings('tr').find('.stuHomeWork').hide();
        });
        $('body,html').click(function(){
          $(".stuHomeWork").hide();
        });
        $(".stuHomeWork").click(function(e){
            e.stopPropagation();
        })

        $(".watch_video").click(function(e){
            
            e.preventDefault();
            var uri = $(this).attr("href");
            var homework_id = $(this).parents("tr").find("input[name='homework_id']").val();
            var video_id = $(this).attr("videoId");
            var video = $(this).parents("li");
            $.tizi_ajax({
                url: baseUrlName+'homework/video_submit',
                data:{
                    'id':homework_id,
                    'video_id':video_id
                },
                type:'POST',
                success:function(data){
                    video.find(".watch_video").remove();
                    video.append('<em class="wcIcon">完成</em>');
                    window.open(uri,"_blank");                   
                }
            })
        })
        //test
        /*
        $(".stuHomeWork .starBtn").click(function(){
            var data = '<iframe src="http://192.168.14.132:8090/class/game/preview/42/1" width="700" height="600" marginwidth="0" marginheight="0" scrolling="no" frameborder="0"></iframe>';
            $.tiziDialog({
                icon:null,
                title:'体验作业',
                content: data,
                width:600,
                height:600,
                ok:false,
                close: function(){
                    $('iframe').each(function(){
                        //var win = this.contentWindow || this;
                        //console.log(this.contentWindow);
                    });
                }
            });
        })
    */

   };
});
