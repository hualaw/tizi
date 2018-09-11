var TeacherCommon = {};
//判断中间列表高度
if($(".tree-list").length == 1){
	$(".tree-list").height($(window).height()-80-26).css('overflow-y','scroll');
};
if($('.countDay').length == 1){
	$(".tree-list").height($(window).height()-80-48).css('overflow-y','scroll');
}
if($('.countDay').next('.type-box').find('select').find('option').length >= 2){
	$(".tree-list").height($(window).height()-80-66).css('overflow-y','scroll');
}
TeacherCommon.leftSlidown = function(json){
	var _id = json.id,_con = json.con,_dis = json.dis,_active = json.active;
	$(_id).hover(function(){
		$(this).find("h2").attr("id",_active);
		$(_con).addClass(_dis);
	},function(){
		$(this).find("h2").attr("id",'');
		$(_con).removeClass(_dis)
	})	
}
//根据右侧内容id判断左侧菜单当前内容
if($('.mainContainer').attr('pagename') != undefined){
	$('.mainMenu li').each(function(i){
		if($('.mainContainer').attr('pagename') == $('.mainMenu li').eq(i).attr('name')){
			$('.mainMenu li').eq(i).find('a').attr('class','active');
			if($(this).attr('name') == 'paper_cequestion'){
				$(this).find('a').css({
					background:'#c00',
					color:'#fff'
				})
			}
		}	
	})
};
// 当前学科下拉脚本
TeacherCommon.subjectSlidown = function(){
	$('.currentSubject').hover(function(){
		$(this).find('.bd').show();
	},function(){
		$(this).find('.bd').hide();
	})
};
TeacherCommon.subjectSlidown();

//老师端演示动画脚本
TeacherCommon.demoAnimation = {
	init:function(){
		this.outPaper();
	},
	// 出卷子演示动画
	outPaper:function(){
		$('.demoAnimation .outPaper').click(function(){
			$.tiziDialog({
				title:$('.demoAnimation .outPaper').html(),
				content:'<object id="mutiupload" name="mutiupload" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="800" height="560"><param name="movie" value="' + staticUrlName + 'flash/outPaper.swf" /><param name="wmode" value="transparent" /><param name="allowScriptAccess" value="always" /><!--[if !IE]>--><object type="application/x-shockwave-flash" data="' + staticUrlName + 'flash/outPaper.swf" width="800" height="560" wmode="transparent" allowScriptAccess="always"><!--<![endif]--><span class="flashNotice"><a href="http://www.adobe.com/go/getflashplayer" target="_blank"><img src="' + staticUrlName + 'image/common/get_flash_player.gif" alt="下载Flash播放器" /></a><span>您需要安装11.4.0以上的版本的Flash播放器，才能正常访问此页面。</span><!--[if !IE]>--></object><!--<![endif]--></object>',
				icon:null,
				ok:null,
				width:800,
				height:560
			})
		})
	}
};
Common.comValidform.detectFlashSupport(
	function(){
		$('.demoAnimation').remove();
	},
	function(){    
		TeacherCommon.demoAnimation.init();
	}
);

Teacher = {}

Teacher.tableStyleFn = function(){
	$('.tableStyle tr').hover(function(){
		$(this).addClass('tdf1').siblings().removeClass('tdf1');
	},function(){
		$('.tableStyle tr').removeClass('tdf1');
	})
};
Teacher.tableStyleFn();

//判断1024分辨率下 win7下的ie6下的左侧布局错误的bug
if(screen.width == 1024 && $.browser.msie && ($.browser.version == "6.0") && !$.support.style){
	$('.question-list').css('width','97%').css('overflow','hidden');
}

//出卷子、留作业中部下拉最大高度判断
Teacher.typeListUlHeight = function(){
	var obj = $('.child-menu .type-box .type-list');
	if(obj.length > 0){
		obj.each(function(i){
			$(this).find('ul').css('width','178px');
			if(i == 1){
				$(this).find('ul').css('margin-left','-92px');
			}
			if($(this).find('ul').height() > 300){
				$(this).find('ul').css('height','300px');
			}else{
				$(this).find('ul').css('height','auto');
			};
		})
	};
	if(obj.length == 1){
		obj.find('ul').css('width','138px');
	}
}
Teacher.typeListUlHeight();

/*老是端选题开始*/
Teacher.paper = {}

Teacher.paper.common = {
	ErrorNoQuestion:"您还没有添加题目",

	/* 上下移动按钮的背景变换 */
	number_change:function(id){
	    $(id).each(function(){
	    	switch($(this).text()){
	    		case"1":$(this).text("一");break;
	    		case"2":$(this).text("二");break;
	    		case"3":$(this).text("三");break;
	    		case"4":$(this).text("四");break;
	    		case"5":$(this).text("五");break;
	    		case"6":$(this).text("六");break;
	    		case"7":$(this).text("七");break;
	    		case"8":$(this).text("八");break;
	    		case"9":$(this).text("九");break;
	    		case"10":$(this).text("十");break;
	    		case"11":$(this).text("十一");break;
	    		case"12":$(this).text("十二");break;
	    		case"13":$(this).text("十三");break;
	    		case"14":$(this).text("十四");break;
	    		case"15":$(this).text("十五");break;
	    		case"16":$(this).text("十六");break;
	    		case"17":$(this).text("十七");break;
	    		case"18":$(this).text("十八");break;
	    		case"19":$(this).text("十九");break;
	    		case"20":$(this).text("二十");break;
	    		default:return
	    	}
	    });
	}
}