var paperQuestionOrigin = 0;
var homeworkQuestionOrigin = 0;

Teacher.paper.paper_common = {
	questionCart : $(".question-cart"),
	hiddenArrow : $(".hidden-arrow a"),
	menuLink : $(".menu a"),
	subjectBox : $(".subject-box"),
	subjectLink : $(".subject-chose"),
	subjectList : $(".subject-list"),
	mainMenu : $(".main-menu"),
	mainWrap : $(".main-wrap"),
	mainContainer : $(".preview-content"),
	childMenu :$('.child-menu'),

	init:function(){
		Teacher.paper.paper_common.initBase();
		Teacher.paper.paper_common.get_question_cart(this.subjectLink.data("subject"));
		$(window).resize(function(){
			Teacher.paper.paper_common.initBase();//据说要干掉
		});
		//左侧选题menu点击效果，失效
		this.menuLink.click(function(){
			var this_a = this;
			this.menuLink.removeClass("active");
			$(this_a).addClass("active");
		});
		//左侧科目选择效果
		this.subjectBox.hover(function(){
			var this_a = Teacher.paper.paper_common;
			this_a.subjectLink.addClass("subject-active");
			this_a.subjectList.show();
		},function(){
			var this_a = Teacher.paper.paper_common;
			this_a.subjectLink.removeClass("subject-active");
			this_a.subjectList.hide();
		});
		this.hiddenArrow.click(function(){
			var this_a = this;
			Teacher.paper.paper_common.hiddenArrowClick(this_a);
		});
		this.questionCart.on("click","a.del-btn",function(){
			var this_a = this;
			Teacher.paper.paper_common.delQuestionCartClick(this_a);
		});
		this.questionCart.on("click","a.empty-btn",function(){
			Teacher.paper.paper_common.emptyQuestionCartClick();
		});
	},
	randerQuestion:function(){
		return;
	},
	//隐藏左侧内容按钮效果
	hiddenArrowClick:function(this_a){		
		var thisStatus = $(this_a).attr("class");
		switch(thisStatus){
			case "hide-menu":
				this.mainMenu.hide();
				this.mainWrap.addClass("full-width");
				$(this_a).attr("class","show-menu");
			break;
			case "show-menu":
				this.mainMenu.show();
				this.mainWrap.removeClass("full-width");
				$(this_a).attr("class","hide-menu");
			break;	
		}	
	},
	//左侧删除试题效果
	delQuestionCartClick:function(this_a){
		var id = $(this_a).data("id");
		var subject_id = this.subjectLink.data("subject");		
		if($(this_a).parent().parent().children('.second-col').text() == '0'){
			return false;
		}else{
			$.tiziDialog({
				content:'确定清空当前题型？',
				ok:function(){
					var this_a = Teacher.paper.paper_common;
					this_a.remove_question_from_cart(id,subject_id,function(){
				        $('#question-type'+id).find('.question-item-box').remove();
				        $('#menu-outer-ul-question-type'+id).find('.question-item').remove();
				        this_a.orderSort();
				        this_a.orderType();
					},function(){});
				},
				cancel:true
			})
		}
	},
	emptyQuestionCartClick:function(){
		var id = 0;
		var subject_id = $(".subject-chose").data("subject");
		var sum = 0;  
	    $(".question-table").find('.second-col').each(function(i){  
	      sum = sum + parseInt($(this).text());  
	    }); 
		if(sum == '0'){
			return false;
		}else{
			$.tiziDialog({
				content:'确定清空全部题型？',
				ok:function(){
					var this_a = Teacher.paper.paper_common;
					this_a.remove_question_from_cart(id,subject_id,function(){
						$('.paper-body').find('.ui-sortable').empty();
				        $('.preview-content').find('.question-type-box').find('.question-item-box').remove();
				        this_a.orderSort();
				        this_a.orderType();
					},function(){});
				},
				cancel:true
			})
		}
	},
	get_question_cart:function(sidVal){
		$(".preview-btn").hide();
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/get_question_cart",
			type:"GET",
			dataType:"json",
			data:{sid:sidVal,ver:(new Date).valueOf()},
			success:function(data){
				if(data.errorcode==true){
					Teacher.paper.paper_common.randerCart(data.question_cart);
					$(".preview-btn").show();
				}else{
					$.tiziDialog({content:data.error});
				}
			}
		});
	},
	remove_question_from_cart:function(qtypeVal,sidVal,success,err){
		var this_a = Teacher.paper.paper_common;
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/remove_question_from_cart",
			type:"POST",
			dataType:"json",
			data:{qtype:qtypeVal,sid:sidVal},
			success:function(data){
				if(data.errorcode==true){
					success();
					this_a.randerCart(data.question_cart);
					this_a.randerQuestion();
				}else{
					err();
					$.tiziDialog({content:data.error});
				}
			}
		});
	},
	//初始化框架
	initBase:function(){
		//调用左侧高度
		Common.getLeftBar({
			id:"#leftBar"	
		});
		//调用头部右侧下拉菜单
		Common.headerNav({
			id:"#navSlidown",
			ul:"ul",
			dis:"dis"	
		});
		var initHeight= $(window).height() - $(".header").height() - $(".footer").height();
		$('.mainContainer').css('height',initHeight);
		$('.mainContainer .content').css('height',initHeight).css('overflow-y','scroll');
		$('.preview-content').css('height',initHeight-60);
		$(".drag-line").css("height", initHeight);
		$(".drag-line a").css("margin-top",initHeight/2+"px");
		$(".ui-resizable-eu").css("height", initHeight+10);
		$(".child-menu").css("height", initHeight-10);
		// windguo2014年3月8日修改
		// var winHeight = $(window).height();
		// var winWidth = $(window).width();
		// var initHeight = winHeight-80;
		
		$(".main-menu,.hidden-arrow,iframe").height(initHeight);
		this.hiddenArrow.css("margin-top",initHeight/2-8+"px");
	},
	//选题表格百分比
	percentShow:function(){
		var totleNum = parseInt(this.mainMenu.find("h4").children("span").text());
		var questionArr = [];
		$(".second-col").each(function(index, element) {
            questionArr.push(parseInt($(this).text()));
        });
		var percentArr = $.map(questionArr,function(n){
			if(totleNum==0){
				return 0;
			}else{
				return Math.round(n*100/totleNum);
			}
		})
		
		$(".percent-text span").each(function(index, element) {
			$(this).text(percentArr[index]);
		});
		
		$(".percent-line").each(function(index, element) {
            $(this).width(Math.round(percentArr[index]/2));
        });
			
	},
	//渲染已选试题表格
	randerCart:function(data){
		if(data != undefined) {
			//var questionCart = $("#question-cart-content").html();
			//var cart = Mustache.to_html(questionCart,data);
			this.questionCart.html(data.html);
			this.percentShow();
		}
	},
	orderSort:function(ele){
		var con = this.mainContainer.find(".question-index");
		var ele = this.childMenu.find(".list-order");
		ele.each(function(index, element) {
			$(element).text(index+1);
		});
		con.each(function(index, element) {
			$(element).text(index+1+'、');
		});
	},
	orderType:function(){
		var righttype = $('.preview-content').find('.type-title-nu');
        var lefttype = $('.child-menu').find('.menu-questiontype-nu');
        righttype.each(function(index,element){
            $(element).text(index+1);
        });
        lefttype.each(function(index,element){
            $(element).text(index+1);
        });
        Teacher.paper.common.number_change(".type-title-nu");
    	Teacher.paper.common.number_change(".menu-questiontype-nu");
	}
}