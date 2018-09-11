
define(function(require, exports){ 
    require("tiziDialog")
    exports.lookReviews=function(){
        $(".comment a").click(function(){
            var comment_content = $(this).parents(".comment").find(".comment_content").text();
            $.tiziDialog({
                title:"查看评语",
                content: comment_content,
                icon:false
            })
        })
    };
    
    student_paper_pagination = function(page){
        
        window.location.href = baseUrlName+'student/class/paper?page='+page;
        
    }

});
