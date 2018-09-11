/*搜索选题开始*/
Teacher.paper.search = {
	submitBtn : $('#search_submit'),
	questionList : $(".question-list"),
	//初始化
	init:function(){
		//智能选题提交按钮
		this.submitBtn.click(function(){
			Teacher.paper.search.submit_select();	
		});
		$('.seachBoxInput').keypress(function(event){	 
			if(event.keyCode==13){	 
				$('.seachBoxInput').blur();	 
				Teacher.paper.search.submit_select();	 
			}		 
		});
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
		Teacher.paper.paper_common.randerQuestion = Teacher.paper.search.randerQuestion;
		Teacher.paper.search.selects(".paperType .posr");	
	},
	randerQuestion:function(){
		if($('.question-box').length > 0){
			this_a = Teacher.paper.search;
			var page_num = $('.page').find('.active').html();
			if(page_num == undefined){
				page_num = 1;
			}
			var stype = $('.seachResult').attr('stype');
			var slevel = $('.seachResult').attr('slevel');
			var skeyword = $('.seachResult').find('em').text();

			this_a.page(page_num,stype,slevel,skeyword,true);
		}
	},
	selects:function(_id)
	{
		//Common.hoverSelects(_id);
		$(_id).each(function(){
			$(this).find("select").on("change",function(e){
				e.stopPropagation();
				var sel = $(this).find("option:selected");//获取选中值
				//获取data_id
				var data_id = sel.attr("data-id");
				if(data_id){
					$(this).siblings('input').attr('data-id',data_id);
				}
			});	
		});
	},
	//提交选项方法
	submit_select:function(){
		if($('#search_submit').attr('class').indexOf('disabled') != -1){
			return false;
		}
		var sid = $('.subject-chose').data('subject');
		var stype = $('#qtype').find('input').attr('data-id');
		var slevel = $('#qlevel').find('input').attr('data-id');
		var skeyword = $('#qkeyword').find('input').val();

		if(stype < 0){
			$.tiziDialog({content:"请选择题型"});		
		}else if(slevel < 0){
			$.tiziDialog({content:"请选择难度"});
		}else if(skeyword == '' || skeyword == '请输入关键字'){
			$.tiziDialog({content:"请输入关键字"});
		}else{
			ga('send', 'event', 'Search-Paper', 'Search', skeyword);
			this.page(1,stype,slevel,skeyword,true);
		}		
	},
	//翻页
	page:function(page,stype,slevel,skeyword,noloading){
		if(noloading != true){
			//判断是否有分页的显示loading图片
			$('.all_in').removeClass('all_in').addClass('all_in_2');
			Common.floatLoading.showLoading();
		}
		$('#search_submit').addClass('disabled');
		var sid = $('.subject-chose').data('subject');
		$.tizi_ajax({
			type: "GET",
            dataType: "json",
            url: baseUrlName + "paper/paper_search/get_question",
            data: {'sid':sid,'stype':stype,'slevel':slevel,'skeyword':skeyword,'page':page,ver:(new Date).valueOf()},
            success: function(data) {
				//判断是否有分页的显示loading图片
				$('.all_in_2').removeClass('all_in_2').addClass('all_in');
				Common.floatLoading.hideLoading();
                $('#search_submit').removeClass('disabled');
            	if(data.errorcode == true) {
                	$('.question-list').html(data.html);
            		Teacher.paper.search.auto_height();
            		 //搜索选题走模板注入全部加入功能v20140117
            		//Teacher.paper.search.add_BtnPaper();//vk02
            	}else{
            		$.tiziDialog({content:data.error});
            	}
            }
		});
	},
	//自动计算高度
	auto_height:function(){
		$('.paperDetails').css('height',$(window).height() - $('.allPaper .path').height() - ($('.page').height())-140).css('overflow','auto');
		$('.mainContainer').css('height',$(window).height() - $('.allPaper .path').height() - ($('.page').height())-33).css('overflow-y','hidden');
	},
	//追加第一个添加本页按钮
	add_BtnPaper:function(){
		$(".question-list .question-box").first().find('a.control-btn').prepend('<div class="all_in cBtnNormal fl" style="margin-right:5px;"><a class="addAllPaper"><i>将本页题目全部加入试卷</i></a></div>');
	}
}
/*搜索选题结束*/