Teacher.paper.search={submitBtn:$("#search_submit"),questionList:$(".question-list"),init:function(){this.submitBtn.click(function(){Teacher.paper.search.submit_select()}),$(".seachBoxInput").keypress(function(event){13==event.keyCode&&($(".seachBoxInput").blur(),Teacher.paper.search.submit_select())}),this.questionList.on("click",".question-content",function(){var this_a=this;$(this_a).find(".answer").toggle()}),this.questionList.on("click",".control-btn",function(){var this_a=this;Teacher.paper.paper_question.addQuestionClick(this_a)}),this.questionList.on("click",".all_in",function(event){return event.stopPropagation(),Teacher.paper.paper_question.addQuestionsClick(),!1}),Teacher.paper.paper_common.randerQuestion=Teacher.paper.search.randerQuestion,Teacher.paper.search.selects(".paperType .posr")},randerQuestion:function(){if($(".question-box").length>0){this_a=Teacher.paper.search;var page_num=$(".page").find(".active").html();void 0==page_num&&(page_num=1);var stype=$(".seachResult").attr("stype"),slevel=$(".seachResult").attr("slevel"),skeyword=$(".seachResult").find("em").text();this_a.page(page_num,stype,slevel,skeyword,!0)}},selects:function(_id){$(_id).each(function(){$(this).find("select").on("change",function(e){e.stopPropagation();var sel=$(this).find("option:selected"),data_id=sel.attr("data-id");data_id&&$(this).siblings("input").attr("data-id",data_id)})})},submit_select:function(){if(-1!=$("#search_submit").attr("class").indexOf("disabled"))return!1;var stype=($(".subject-chose").data("subject"),$("#qtype").find("input").attr("data-id")),slevel=$("#qlevel").find("input").attr("data-id"),skeyword=$("#qkeyword").find("input").val();0>stype?$.tiziDialog({content:"请选择题型"}):0>slevel?$.tiziDialog({content:"请选择难度"}):""==skeyword||"请输入关键字"==skeyword?$.tiziDialog({content:"请输入关键字"}):(ga("send","event","Search-Paper","Search",skeyword),this.page(1,stype,slevel,skeyword,!0))},page:function(page,stype,slevel,skeyword,noloading){1!=noloading&&($(".all_in").removeClass("all_in").addClass("all_in_2"),Common.floatLoading.showLoading()),$("#search_submit").addClass("disabled");var sid=$(".subject-chose").data("subject");$.tizi_ajax({type:"GET",dataType:"json",url:baseUrlName+"paper/"+baseNameSpace+"_search/get_question",data:{sid:sid,stype:stype,slevel:slevel,skeyword:skeyword,page:page,ver:(new Date).valueOf()},success:function(data){$(".all_in_2").removeClass("all_in_2").addClass("all_in"),Common.floatLoading.hideLoading(),$("#search_submit").removeClass("disabled"),1==data.errorcode?($(".question-list").html(data.html),Teacher.paper.search.auto_height()):$.tiziDialog({content:data.error})}})},auto_height:function(){$(".paperDetails").css("height",$(window).height()-$(".allPaper .path").height()-$(".page").height()-140).css("overflow","auto"),$(".mainContainer").css("height",$(window).height()-$(".allPaper .path").height()-$(".page").height()-33).css("overflow-y","hidden")},add_BtnPaper:function(){$(".question-list .question-box").first().find("a.control-btn").prepend('<div class="all_in cBtnNormal fl" style="margin-right:5px;"><a class="addAllPaper"><i>将本页题目全部加入试卷</i></a></div>')}};