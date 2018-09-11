define(function(require,exports){
    // 梯子网首页试卷切换
    exports.homePageTab = function(){
        $('#changeSubject a').live('click', function(){
			var obj = $(this);
            var subject_type = obj.attr('sn');
            var area_id = $('#areaName').attr('area');
            $(this).addClass('active').siblings().removeClass('active');
            $('#subjectName').attr('subject_type', obj.attr('sn'));
            $.tizi_ajax({
				'url' : baseUrlName + 'paper/paper_exam/position?area_id='+area_id+'&stype_id='+subject_type,
				'type' : 'GET',
				'dataType' : 'json',
				success : function(data){
					$('#expos_list').html(data.html);
				}
			});
        });
    }
	//自主练习首页tab
	exports.stuPraticeTab=function(clickObj,className,showObj){
	    clickObj.click(function (){
	        var index = clickObj.index($(this));

	        if(clickObj!=$(".stuNav li")){	        	
		        $(".courseNav li").removeClass("stuOn");
		        $(".exercises .practiceBox").removeClass("dis");
		        $(".courseNav:eq("+index+") li").eq(0).addClass("stuOn");
		        $(".exercises:eq("+index+") .practiceBox").eq(0).addClass("dis");
	        }
	        clickObj.removeClass(className);
	        $(this).addClass(className);
	        showObj.removeClass("dis");
	        showObj.eq(index).addClass("dis");

	    });
	};
	//年级和学科是旧的tab  通过控制详情页返回首页的url 来判断当前选项卡的  这个后面会删了 后台去控制这个
	//年级 tab
	exports.gradeTab=function(){
        var reg = new RegExp("^.*\/([1-6]{1})"); 
        var key = window.location.href.match(reg);
        if(key == null) return;
        index = key[1]-1;
        $(".stuNav").find("a").removeClass("active");
        $(".stuNav").find("a:eq("+index+")").addClass("active");

    }
    //学科tab
    exports.subjectTab=function(){
        var reg = new RegExp("^.*#([1-9]{1})");
        var key = window.location.href.match(reg);
        if(key == null) return;
        index = key[1];
        $(".courseNav").find(".s_"+index).click();
    }
    // tab 选项卡
    exports.Tab = function(tabTit, on, tabCon){
        $(tabCon).each(function() {
            $(this).children().eq(0).show().siblings().hide();
        });
        $(tabTit).each(function() {
            $(this).children().eq(0).addClass(on);
        });
        $(tabTit).children().click(function() {
            $(this).addClass(on).siblings().removeClass(on);
            var index = $(tabTit).children().index(this);
            $(tabCon).children().eq(index).show().siblings().hide();
        });
    }
    // tab 选项卡
    exports.homeworkTab = function(tabTit, on, tabCon){
        $(tabCon).each(function() {
            $(this).children().eq(0).show().siblings().hide();
        });
        $(tabTit).each(function() {
            $(this).children().eq(0).addClass(on);
        });
        $(tabTit).children().click(function() {
            $(this).addClass(on).siblings().removeClass(on);
            var index = $(tabTit).children().index(this);
            $(tabCon).children().eq(index).show().siblings().hide();
        });
    }
})