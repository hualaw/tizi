//智能选题点击dt的chechBox选择dd的checkBox
Teacher.checkBoxDL = function(id){
	var _dtInput = $(id).find("dt input");
	_dtInput.click(function(){
		var _this = $(this).parent().parent().next().find("input");
		if($(this).attr("checked") == "checked"){
			_this.attr("checked", true);
		}
		else{
			_this.attr("checked", false);
		}
	})
}

/*智能选题开始 vk6*/
Teacher.paper.intelligent = {
	submitBtn : $('#intelligent_submit'),
	questionList : $(".question-list"),
	typeList:$(".typelist"),
	//初始化
	init:function(){
		//智能选题提交按钮
		this.submitBtn.click(function(){
			Teacher.paper.intelligent.submit_select();	
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
		this.questionList.on("click",".change-btn",function(){
			var this_a = this;
			Teacher.paper.intelligent.change_question(this_a);
		});
		Teacher.paper.paper_common.randerQuestion = Teacher.paper.intelligent.randerQuestion;
		//调用下拉菜单方法运算数值
		Teacher.paper.intelligent.selects(".typelist");
		Teacher.paper.intelligent.scrollFn();
	},
	randerQuestion:function(){
		if($('.question-box').length > 0){
			this_a = Teacher.paper.intelligent;
			var page_num = $('.page').find('.active').html();
			if(page_num == undefined){
				page_num = 1;
			}
			this_a.page(page_num,true);
		}
	},
	selects:function(_id){
		//绑定方法
		$(_id).on("change","select",function(event){ //绑定change事件
			event.stopPropagation(); //阻止冒泡
			var _text = $(this).text();
			if($(this).find('option:selected').attr('value')){
				var _val = $(this).find('option:selected').attr('value');
				$(this).prev("input").attr('value',_val);
			}
			/*题目数多于99 或者为0时候*/
			var papersum = 0;	
			$('.paperValSum').each(function(){
			     papersum += parseInt($(this).val());	
			});
			if(papersum > 99){
				$.tiziDialog({content:"一张试卷只能加入99道题目，请控制题目数量。"});
				$('#intelligent_submit').addClass('BtnDisabled');
			}else{
				$('#intelligent_submit').removeClass('BtnDisabled');
			}
		});	
	},
	//提交选项方法
	submit_select:function(){
		if($('#intelligent_submit').attr('class').indexOf('BtnDisabled') != -1){
			return false;
		}
		var sid = $('.subject-chose').data('subject'); //获取页面关键帧
		var typelist = this.get_typeList(); //获取选择题型数量select列表对象
		var categorylist = this.get_categoryList(); //获取选择知识点checkbox列表对象
		var cid = $('.category_root').find('input').attr('data-id'); //获取选择教材版本
		var difficult = $('#selectDifNum').html(); //获取选择难度
		var paperValSum = $(".paperValSum").val();
		//console.log(typelist)
		//console.log(categorylist)
		//console.log(cid)
		if(paperValSum > 99){
			$.tiziDialog({content:"一张试卷只能加入99道题目，请控制题目数量。"});
		}else if(typelist == ''){
			$.tiziDialog({content:"请选择题型数量,题型数量不能为0"});		
		}else if(categorylist == ''){
			$.tiziDialog({content:"请选择知识点，知识点不能为空"});
		}else{	
			//调用公共loading显示功能
			Common.floatLoading.showFloatLoading();
			$.tizi_ajax({
	            type: "GET",
	            dataType: "json",  
	            url: baseUrlName + "paper/paper_intelligent/select",
	            data: {'sid':sid,'typelist':typelist,'cids':categorylist,'cid':cid,'diff':difficult,ver:(new Date).valueOf()},
	            success: function(data) {
		        	//调用公共loading的关闭功能
		        	Common.floatLoading.hideFloatLoading();
	            	if(data.errorcode == true) {
						$('.question-list').html(data.html);
	            		Teacher.paper.intelligent.auto_height();
	            	}else{
	            		$.tiziDialog({content:data.error});
	            	}
	            }
	    	});
		}		
	},
	get_typeList:function()
	{
		var typelist = '';
		$(".typelist").each(function(){ 
	        if($(this).find('input').val() != 0){
	        	typelist += ','+$(this).find('select').attr('id') + '-' + $(this).find('input').val();
	    	}
	    });
	    typelist = typelist.substr(1);
		return typelist;
	},
	get_categoryList:function()
	{
		var cids='';
	    $("input[name='categorylist']:checkbox:checked").each(function(){ 
	        cids+=','+$(this).val();
	    }) 
	    cids=cids.substr(1);
	    return cids;
	},
	//翻页
	page:function(page,noloading){
		if(noloading != true){
			//判断是否有分页的显示loading图片
			$('.all_in').removeClass('all_in').addClass('all_in_2');
			Common.floatLoading.showLoading();
		}	
		var sid = $('.subject-chose').data('subject');
		var diff = $('.diff').attr('difficult');
		$.tizi_ajax({
			type: "GET",
            dataType: "json",  
            url: baseUrlName + "paper/paper_intelligent/get_question",
            data: {'sid':sid,'diff':diff,'page':page,ver:(new Date).valueOf()},
            success: function(data) {
				//判断是否有分页的显示loading图片
				$('.all_in_2').removeClass('all_in_2').addClass('all_in');
				Common.floatLoading.hideLoading();
            	if(data.errorcode == true) {
                	$('.question-list').html(data.html);
	            	Teacher.paper.intelligent.auto_height();
            	}else{
            		$.tiziDialog({content:data.error});
            	}
            }
		});
	},
	change_question:function(this_a){
		if($(this_a).hasClass('disabled')){return;}
		$('.all_in').removeClass('all_in').addClass('all_in_2');
		$(this_a).addClass('disabled');
		var sid = $('.subject-chose').data('subject');
		var cid = $(this_a).attr('data-category_id');
		var qtype = $(this_a).attr('data-qtype_id');
		var diff = $('.diff').attr('difficult');
		var qid = $(this_a).attr('data-question_id');
		var qids = new Array();
		$('.question-list').find('.question-box').each(function(i,e){
	        qids.push($(e).attr('data-question_id'));
	    });

		$(this_a).parents('.question-intelligent-item').attr('id','question-intelligent-item-'+qid);

		$.tizi_ajax({
			type: "GET",
            dataType: "json",  
            url: baseUrlName + "paper/paper_intelligent/change_question",
            data: {'sid':sid,'cid':cid,'qids':qids,'qid':qid,'qtype':qtype,'diff':diff,ver:(new Date).valueOf()},
            success: function(data) {
				//判断是否有分页的显示loading图片
				$('.all_in_2').removeClass('all_in_2').addClass('all_in');
            	$('#question-intelligent-item-'+qid).find('.change-btn').removeClass('disabled');
            	if(data.errorcode == true) {
                	$('#question-intelligent-item-'+qid).html(data.html);
                	$('#question-intelligent-item-'+qid).removeAttr('id');
	            	Teacher.paper.intelligent.auto_height();
            	}else{
            		$.tiziDialog({content:data.error});
            	}
            }
		});
	},
	//自动计算高度 vk7
	auto_height:function(){
		//智能选题走模板注入全部加入功能v20140117
		// $(".question-list .question-box").first().find('a.control-btn').find(".cBtnNormal").addClass("fl");
		// $(".question-list .question-box").first().find('a.control-btn').find("em").addClass("fl");
		// $(".question-list .question-box").first().find('a.control-btn').prepend('<div class="all_in cBtnNormal fl" style="margin-right:5px;"><a class="addAllPaper"><i>将本页题目全部加入试卷</i></a></div>');
		$('.paperDetails').css('height',$(window).height() - $('.allPaper .path').height() - ($('.page').height())-92).css('overflow','auto');
		$('.mainContainer').css('height',$(window).height() - $('.allPaper .path').height() - ($('.page').height())-42).css('overflow-y','hidden');
	},
	scrollFn : function(){
		var isScrolled = false;
		var startX = 0,startLevel = 0;
		$("#diy_dot").mousedown(function(e) {
			if (!isScrolled) {
				isScrolled = true;
				startX = e.clientX;
				startLevel = e.target.offsetLeft + 5
			}
		});
		$("#diy_dot").mouseup(function(e) {
			isScrolled = false;
			startLevel = e.target.offsetLeft + 5
		});
		$(document).mousemove(function(e) {
			if (isScrolled) {
				var t = e.clientX - startX;
				var n = startLevel + t;
				n = n > 100 ? 100 : n;
				n = n < 0 ? 0 : n;
				if (n <= 100 && n >= 0) {
					$("#diy_scroll_left").width(n + "px");
					$("#diy_scroll_right").width(100 - n + "px");
					$("#diy_dot").attr("style", "left:" + (n - 5) + "px");
				}
				$("#selectDifNum").html((n/100).toFixed(2))
			}
		});

		$(document).mouseup(function(e) {
			isScrolled = false
		});
		$(document).mouseup(function(e) {
			isScrolled = false
		});
	}
}
/*智能选题结束*/