Teacher.paper.paperExam = {
    questionList : $(".question-list"),
	init:function(exam_id){
        this.setOptionVal();

		if(exam_id){
            $('.filterBox').addClass('undis');
            this.getExamQuestion(exam_id);
        }else{
        	this.page();
        }

        this.questionList.on("click",".question-content",function(){
            var this_a = this;
            $(this_a).find(".answer").toggle();
        });     
        this.questionList.on("click",".control-btn",function(){
            var this_a = this;
            Teacher.paper.paper_question.addQuestionClick(this_a);
        });
        this.questionList.on("click",".all_in",function(event){
            event.stopPropagation();//阻止事件冒泡
            Teacher.paper.paper_question.addQuestionsClick();
            return false;
        });
        $('.getExamQuestion').live('click',function(){
            var eid = $(this).attr('eid');
            Teacher.paper.paperExam.getExamQuestion(eid);
            return false;
        });
        //Teacher.paper.paper_common.randerQuestion = Teacher.paper.paperExam.randerPage;
        Teacher.paper.paper_common.randerQuestion = Teacher.paper.paperExam.renderExamQueston;
	},
	page:function(page){
	    this.randerPage(page);
	},
	randerPage:function(page){
		$('.filterBox').removeClass('undis');
        getData = {
            //"stype":$("#optionVal").attr("data-subject"),
            "sid":$('.subject-chose').data('subject'),
            "gtype":$("#optionVal").attr("data-grade"),
            "etype":$("#optionVal").attr("data-type"),
            "atype":$("#optionVal").attr("data-province"),
            "page":page,
            "ver":(new Date).valueOf()
        }
	    $.tizi_ajax({
            type: "GET",  
            dataType: "json",  
            url: baseUrlName + "paper/paper_exam/get_exam/question", //提交到一般处理程序请求数据 
            async:false, 
            data: getData,                 
            success: function(data) {
            	if(data.errorcode){
                	if(data!='') {
                		$(".exam_list").html(data.html);//将返回的数据载入页面
                        // windguo计算试卷选题高度开始
                        $('.headerQuestion .exam_list').css({
                            height:$(window).height() - $('.filterBox').height() - $(".header").height() - $(".footer").height()-15,
                            overflow:'auto'
                        });
                        $('.headerQuestion .paperHall').css('padding-right','0px');
                        // windguo计算试卷选题高度结束
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
                Teacher.paper.paperExam.randerPage(page);
            });
        })
    },
    renderExamQueston:function(){
        var eid = $('.exam-chose').attr('eid');
        if(eid){
            Teacher.paper.paperExam.getExamQuestion(eid);
        }
        return false;
    },
    getExamQuestion:function(exam_id){
        var sid = $('.subject-chose').data('subject');
        $.tizi_ajax({
            type: "GET",  
            dataType: "json",  
            url: baseUrlName + "paper/paper_exam/get_question", //提交到一般处理程序请求数据 
            async:false, 
            data: {'exam_id':exam_id,'sid':sid},                 
            success: function(data) {
                if(data.errorcode){
                    if(data!='') {
                        $(".question-list").html(data.html);//将返回的数据载入页面
                        // windguo计算试卷选题高度开始
                        $('.headerQuestion .question-list').css({
                            height:$(window).height() - $('.filterBox').height() - $(".header").height() - $(".footer").height()-15,
                            overflow:'auto'
                        });
                        $('.headerQuestion .question-list').css('padding-right','0px');
                        // windguo计算试卷选题高度结束
                    }
                }else{
                	Teacher.paper.paperExam.page();
                    $.tiziDialog({content:data.error});
                }
            }  
        });
    }
}