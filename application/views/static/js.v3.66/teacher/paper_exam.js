Teacher.paper.paperExam={questionList:$(".question-list"),init:function(exam_id){this.setOptionVal(),exam_id?($(".filterBox").addClass("undis"),this.getExamQuestion(exam_id)):this.page(),this.questionList.on("click",".question-content",function(){var this_a=this;$(this_a).find(".answer").toggle()}),this.questionList.on("click",".control-btn",function(){var this_a=this;Teacher.paper.paper_question.addQuestionClick(this_a)}),this.questionList.on("click",".all_in",function(event){return event.stopPropagation(),Teacher.paper.paper_question.addQuestionsClick(),!1}),Teacher.paper.paper_common.randerQuestion=Teacher.paper.paperExam.randerPage},page:function(page){this.randerPage(page)},randerPage:function(page){$(".filterBox").removeClass("undis"),getData={sid:$(".subject-chose").data("subject"),etype:$("#optionVal").attr("data-type"),atype:$("#optionVal").attr("data-province"),page:page,ver:(new Date).valueOf()},$.tizi_ajax({type:"GET",dataType:"json",url:baseUrlName+"paper/"+baseNameSpace+"_exam/get_exam/question",async:!1,data:getData,success:function(data){data.errorcode?""!=data&&($(".exam_list").html(data.html),$(".headerQuestion .exam_list").css({height:$(window).height()-$(".filterBox").height()-$(".header").height()-$(".footer").height()-15,overflow:"auto"}),$(".headerQuestion .paperHall").css("padding-right","0px")):$.tiziDialog({content:data.error})}})},setOptionVal:function(){$(".optionList a").each(function(){$(this).live("click",function(){$(this).addClass("active").parent().siblings().find("a").removeClass("active");var title=$(this).parents(".optionList").data("title"),type=$(this).parents(".optionList").data("count"),val=$(this).attr(type);$("#optionVal").attr("data-"+title,val);var page=$(".pagination .active").text();Teacher.paper.paperExam.randerPage(page)})})},getExamQuestion:function(exam_id){var sid=$(".subject-chose").data("subject");$.tizi_ajax({type:"GET",dataType:"json",url:baseUrlName+"paper/"+baseNameSpace+"_exam/get_question",async:!1,data:{exam_id:exam_id,sid:sid},success:function(data){data.errorcode?""!=data&&($(".question-list").html(data.html),$(".headerQuestion .question-list").css({height:$(window).height()-$(".filterBox").height()-$(".header").height()-$(".footer").height()-15,overflow:"auto"}),$(".headerQuestion .question-list").css("padding-right","0px")):(Teacher.paper.paperExam.page(),$.tiziDialog({content:data.error}))}})}};