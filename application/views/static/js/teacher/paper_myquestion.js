/*我的题库选题开始*/
Teacher.paper.myquestion = {
	questionList : $(".question-list"),
	typeList:$(".typelist"),
	//初始化
	init:function(){
		this.questionList.on("click",".question-content",function(){
			var this_a = this;
			$(this_a).find(".answer").toggle();
		});		
		this.questionList.on("click",".control-btn",function(){
			var this_a = this;
			Teacher.paper.paper_question.addQuestionClick(this_a);
		});
		this.questionList.on("click",".all_in",function(event){
			var this_a = this;
			event.stopPropagation();//阻止事件冒泡
			Teacher.paper.paper_question.addQuestionsClick(this_a);
			return false;
		});
		$('.filter-group').live('click',function(){
			if($(this).parent().next().text() > 0 ){
				$('.current-type').attr('data-gid',$(this).attr('data-gid'));
				$('.current-type').attr('val',$(this).text());
				Teacher.paper.myquestion.page();
			}else{
				return false;
			}
		});
		Teacher.paper.paper_common.randerQuestion = Teacher.paper.myquestion.randerQuestion;
	},
	page:function(page_num){
		if(page_num == undefined){
			page_num = 1;
		}
		this.get_question(page_num);
	},
	randerQuestion:function(){
		if($('.question-box').length > 0){
			var page_num = $('.page').find('.active').html();
			if(page_num == undefined){
				page_num = 1;
			}
			Teacher.paper.myquestion.get_question(page_num,true);
		}
	},
	get_question:function(page,noloading){

		if(noloading != true){
			//判断是否有分页的显示loading图片
			$('.all_in').removeClass('all_in').addClass('all_in_2');
			Common.floatLoading.showLoading();
		}
		var sid = $('.subject-chose').data('subject');
		var gid = $('.current-type').attr('data-gid');
		var gname = $('.current-type').attr('val');
		$.tizi_ajax({
			url: baseUrlName + "paper/paper_myquestion/get_myquestion", 
			type: 'GET',
			data: {'page':page,'sid':sid,'gid':gid,'gname':gname,'ver':(new Date).valueOf()},
			dataType: 'json',
			success: function(data){
				$('.all_in_2').removeClass('all_in_2').addClass('all_in');
				Common.floatLoading.hideLoading();
				if(data.errorcode == true){
					$(".question-list").html(data.html);
					//解决ie6下点击中间树栏目的时候右侧五滚动条的问题 vk1
				    $('.mainContainer .content').css('height',$('.mainContainer .content').height()).css('overflow-y','scroll');				
				}else{
					$.tiziDialog({content:data.error});
				}
			}
		});
	}
}
/*我的题库选题结束*/