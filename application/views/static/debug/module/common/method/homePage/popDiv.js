define(function(require,exports){
	// 选择城市脚本
	exports.selectCity = function(){
		$('#changeCity').click(function(e){
			e.stopPropagation();
			$(this).find('.narrowDown').show();
			$('#cityContent').show();
		});
		$('#cityContent a').each(function(){
			$(this).click(function(e){
				var obj = $(this);
				var area_id = obj.attr('area');
				var subject_type = $('#subjectName').attr('subject_type');;
				e.stopPropagation();
				$('#areaName').html(obj.text());
				$('#areaName').attr('area', area_id);
				$('#changeCity .narrowDown').hide();
				$('#cityContent').hide();
				$.tizi_ajax({
					'url' : baseUrlName + 'paper/paper_exam/position?area_id='+area_id+'&stype_id='+subject_type,
					'type' : 'GET',
					'dataType' : 'json',
					success : function(data){
						$('#expos_list').html(data.html);
					}
				});
			})
		});
		$('body').click(function(){
			$('#changeCity .narrowDown').hide();
			$('#cityContent').hide();
		});
	};
	exports.weixinContent = function(){
		$('#weixinDiv').click(function(e){
			e.stopPropagation();
			$(this).find('.narrowDown').show();
			$('#weixinDivContent').show();
		});
		$('#weixinDivContent a').each(function(){
			$(this).click(function(e){
				e.stopPropagation();
				$('#areaName').html($(this).text());
				$('#weixinDiv .narrowDown').hide();
				$('#weixinDivContent').hide();
			})
		});
		$('body').click(function(){
			$('#weixinDiv .narrowDown').hide();
			$('#weixinDivContent').hide();
		});
	}
    // 添加致谢用户滚动效果
    exports.srcMarquee = function(){
        var lineH = 35,
            speed = 30,
            delay = 1000;
        var timer;
        var pause=false;
        var obj=document.getElementById("marqueeBox");
        obj.innerHTML+=obj.innerHTML;
        obj.onmouseover=function(){pause=true;}
        obj.onmouseout=function(){pause=false;}
        obj.scrollTop = 0;
        function start(){
            timer=setInterval(scrolling,speed);
            if(!pause){obj.scrollTop+=1;}
        }
        function scrolling(){
            if(obj.scrollTop%lineH!=0){
                obj.scrollTop += 1;
                if(obj.scrollTop>=obj.scrollHeight/2) obj.scrollTop = 0;
            }else{
                clearInterval(timer);
                setTimeout(start,delay);
            }
        }
        setTimeout(start,delay);
    }
})