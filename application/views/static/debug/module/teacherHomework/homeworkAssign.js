// 布置作业页面的脚本
// author shanghongliang
define(function(require,exports){
	//引用dialog
	require('tiziDialog');
	//引用时间控件
	require('wdatePicker');
	//引用select控件
	require('tizi_select');
	// 请求验证库
    require("validForm");
	exports.homeworkAssign={
		//初始化函数
		init:function(){
			//render页面
			this.initPage();
			//事件绑定
			this.initBind();
			//表单绑定
			this.assignSubmit();
			//游戏选择绑定
			this.selectGame();
			// 点击文档
			this.clickFile();
		},
		//初始化页面
		initPage:function(){
			//select转化
			$('select.selectWidth').jqTransSelect();
			//为时间输入框设置当天的时间
			var _myDate = new Date(),_y = _myDate.getFullYear(),
			_m = _myDate.getMonth()+1,_d = _myDate.getDate(),_h = _y+'-'+_m+'-'+_d,_time=parseInt(+new Date())+24*60*60*1000,
			_nextDate=new Date(_time),_ny=_nextDate.getFullYear(),_nm=_nextDate.getMonth()+1,_nd=_nextDate.getDate(),_h2=_ny+'-'+_nm+'-'+_nd;
			//设置开始时间日期
			$(".startDay").val(_h);
			//设置结束时间日期
			$(".endDay").val(_h2);
		},
		//初始时间绑定
		initBind:function(){
			// 可选择项的hover事件绑定
			$(".choiceItem").hover(function(){
				$(this).parent().children().removeClass("hover");
            	$(this).addClass("hover");
			},function(){
				$(this).parent().children().removeClass("hover");
			});

			//可以选择项的click事件绑定
			$(".assignWrap").on("click",".choiceItem",function(e){
				//判断是否为练习那种多选情况
				if($(this).hasClass("multi")){
					//获取里层的checkbox控件对象
					var _checkbox=$(this).find("input[type=checkbox]");
					//当前对象为选中的情况
					if($(this).hasClass("selected")){
						$(this).removeClass("selected");
						_checkbox.removeAttr("checked");
					}else{
						$(this).addClass("selected");
						_checkbox.attr("checked","checked");
					}
					var _table=$(this).parents(".exerciseTable"),_unit=_table.attr("unit"),_len=_table.find("input[type=checkbox]:checked").length,_item=$(".untilVal[unit="+_unit+"]");
					if(_len!==0){
						_item.children(".selectNum").text(_len);
						_item.addClass("checked");
					}else{
						_item.removeClass("checked");
					}
					
				//单选的情况
				}else{
					if($(this).hasClass("selected")){
						return false;
					}
					$(this).parent().children().removeClass("selected");
					$(this).addClass("selected");
					
					//如果是单元部分的选择
					if($(this).hasClass("untilVal")){
						//获取当前dom的unit值
						var _unit=$(this).attr("unit"),_target=null;
						$(".exerciseTable").each(function(){
							if($(this).attr("unit")===_unit){
								$(this).addClass("active");
							}else{
								$(this).removeClass("active");
							}
						});
					}
					
					//不是选择年级
					if($(this).hasClass("specGrade")){
						//阻止a标签默认行为
						e.preventDefault();
					}
					//切换教材版本
					if($(this).hasClass("specGrade")){
						//加载ajax
						var spec_g = $(this).attr('spec_g');
						// var zy_stage_id = $(this).attr('zy_stage_id');
						$.tizi_ajax({
				            type: "GET",  
				            dataType: "json",  
				            url: baseUrlName + 'homework/teacher_assign/get_units_data/'+spec_g, //提交到一般处理程序请求数据 
				            async:false, 
				            data: {'ajax':true,ver:(new Date).valueOf()},                 
				            success: function(data) {
				            	if(data.error_code){
			                	 	$(".untilName").html(data.unit_html);
			                	 	$(".exerciseWrap").html(data.prac_html);
			                	 	//重新绑定选择游戏
			                	 	exports.homeworkAssign.selectGame();
			                	 	//重新绑定重点词汇，重点句子的hover
			                	 	exports.homeworkAssign.hoverBox();
				                }else{
				                	$.tiziDialog({content:'系统繁忙，请稍候再试'});
				                }
				            }  
					    });
					}
					
				}
				e.stopPropagation();
			});
			
			
			
			//练习预览弹出框
			$(".assignWrap").on("click",".innerTable .gamePic",function(e){
				//弹框
                var unit_id = $(this).attr("chapid");
                if (!unit_id) {
                    var unit_id = $(this).parents(".exerciseTable").attr("unit");
                }
                var game_id = $(this).attr("game_id");
                var game_type_id = $(this).attr("game_type");
                var data = '<iframe src="'+baseUrlName+'class/game/preview/'+unit_id+'/'+game_id+'/'+game_type_id+'" width="700" height="500" marginwidth="0" marginheight="0" scrolling="no" frameborder="0"></iframe>';
                $.tiziDialog({
                    icon:null,
                    title:'体验作业',
                    content: data,
                    width:700,
                    height:500,
                    ok:false
                });

                e.preventDefault();
				e.stopPropagation();
			});
			//日期控件的绑定
			$(".datenInput").click(function(){
				var _myDate = new Date(),_y = _myDate.getFullYear(),
				_m = _myDate.getMonth()+1,_d = _myDate.getDate(),_h = _y+'-'+_m+'-'+_d;
				
				WdatePicker({errDealMode:1,minDate:'_h'});
			});

			//重点词汇，重点句子的hover绑定
			this.hoverBox();
		},
		//发布作业按钮提交绑定
		assignSubmit:function(){
			var _form = $(".assginHomeWorkForm").Validform({
	            beforeSubmit: function(curform) {
	            	//设置科目
	            	$("#inpSubjectName").val($(".subjectList").children("a.selected").text());
	            	//设置年级
	            	$("#inpGradeName").val($(".gradeList").children("a.selected").text());
	            	//设置教材版本
	            	$("#banbenId").val($().children(".materialval.selected").attr("banben_id"));
	            	//设置外教视频
	            	var _videos=$(".videoChoice").children("input[type=checkbox]:checked"),_videosVal=[];
	            	_videos.each(function(){
	            		_videosVal.push($(this).attr("id"));
	            	});
	            	$("#inpVideos").val(_videosVal.join(","));
	            	//设置游戏
	            	var _games=$(".exerciseChoice").not(".paperChoice").find("input[type=checkbox]:checked"),_gamesVal=[];
	            	_games.each(function(){
	            		var sub_id = $("input[name=subject_id]").attr("value");
	            		if(sub_id==21){//小学英语的游戏category_id就取到unit_id
	            			var _unit=$(this).parents(".exerciseTable").attr("unit");
	            		}else{//非小学英语要去到小节的category_id
	            			var _unit = $(this).parents(".tr_chap").attr("chapid");
	            		}
	            		var _gameType=$(this).parents(".choiceItem").find(".exerciseSelect").attr("game_type_id");
	            		_gamesVal.push(_unit+"-"+$(this).parents(".choiceItem").find(".exerciseSelect").val()+"-"+_gameType);
	            	});
	            	//选择试卷包
	            	var _papers=$(".paperChoice").find("input[type=checkbox]:checked"),_papersVal=[];
	            	_papers.each(function(){
	            		var _unit=$(this).parents(".exerciseTable").attr("unit");
	            		_papersVal.push(_unit+"-"+$(this).parents(".choiceItem").find(".paperSelect").attr('value'));
	            	});
	            	$("#inpGames").val(_gamesVal.join(","));
	            	$("#inpPapers").val(_papersVal.join(","));//试卷包
	            	if(_videosVal.length==0&&_gamesVal.length==0&&_papersVal.length==0){
	            		$.tiziDialog({
							title:'提示',
							content:"请选择一项练习",
							icon:null
						});
	            		return false;
	            	}
	            },
	            ajaxPost: true,
	            callback: function(data) {
	            	require.async("module/common/ajax/teacherHomework/homeworkAjax",function(ex){
	                    ex.assign(data);
	                });
	            }
	        });
		},
		//选择游戏
		selectGame:function(){
			//游戏下拉切换图片
			$(".exerciseSelect").change(function(){
				var _img=$(this).parents(".innerTable").find(".gamePic"),
				_imgUrl=_img.attr("src"),_val=$(this).val();
				//将显示的图片替换成新选择游戏的图片
				_img.attr("src",_imgUrl.replace(/\d+\.png/,_val+".png"));
				//将img上的game_id修改为新的id
				_img.attr("game_id",$(this).val());
			});
		},
		hoverBox:function(){
			// 重点词汇和重点句子hover事件绑定
			// $(".assignWrap").on("mouseenter mouseout",".hoverBox",function(e){
			// 	if(e.type==="mouseenter"){
			// 		var _item=$(this).children(".hoverWrap");
			// 		_item.show();
			// 		var _height=_item.children(".hoverInner")[0].clientHeight;
			// 		_item.children(".coverBg").css("height",_height);
			// 	}else if(e.type==="mouseout"){
			// 		$(this).children(".hoverWrap").hide();
			// 	}
				
			// });
			$(".hoverBox").hover(function(){
				var _item=$(this).children(".hoverWrap");
				_item.show();
				var _height=_item.children(".hoverInner")[0].clientHeight;
				_item.children(".coverBg").css("height",_height);
			},function(){
				$(this).children(".hoverWrap").hide();
			});
		},
		// 点击文档
		clickFile:function(){
			$('.exerciseChoice a.file').each(function(){
				$(this).click(function(e){
					e.stopPropagation();
				})
			})
		}
	};
});
