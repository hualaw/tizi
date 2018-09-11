/*试卷预览开始*/
Teacher.paper.paper_preview = {
	mainMenu : $(".child-menu"),
	mainContent : $(".child-content .content-wrap"),
	menuContainer : $(".paper-list-container"),
	mainContainer : $(".preview-content"),
	typeList : $(".type-list"),
	typeCur : $(".current-type"),
	typeOption : $(".type-list ul"),
	typeItem :$(".type-list select"),
	// 定义radio模式的切换结构开始
	typeInputItem :$(".type-list p.types"),
	// 定义radio模式的切换结构开始
	typeHandle : ".type-handle-box",
	questionHandle : ".question-handle-box",
	questionItemBox : ".question-item-box",
	unitBox : ".unit-box",
	savePaper: $("#save-btn"),
	saveasPaper: $("#saveas-btn"),
	resetPaper: $("#reset-btn"),
	userUnit:$(".user-unit"),
	requestBtn:$("a.request-btn"),
	init:function(){
		Teacher.paper.paper_preview.boxInit();
   		Teacher.paper.paper_preview.orderSort(); //初始化时默认序号的排列（从头至尾排下去）
    	Teacher.paper.paper_preview.initStyle();//初始化试卷模式（标准、测验）
		Teacher.paper.paper_preview.number_change();
		Teacher.paper.paper_preview.sortable_zujuan();
		$(window).resize(function(){
			Teacher.paper.paper_preview.boxInit();
		});
		//鼠标悬浮在试卷内容的效果
		this.mainContainer.on({"mouseenter":function(){
			var $this = $(this);
			$(this).parent(".paper-type-box").addClass("paper-type-hover");
		},"mouseleave":function(){
			$(this).parent(".paper-type-box").removeClass("paper-type-hover");
		}},Teacher.paper.paper_preview.typeHandle);
		
		this.mainContainer.on({"mouseenter":function(){
			var $this = $(this);
			$(this).parent(".question-type-box").addClass("question-type-hover");
	        var len = $(this).parent(".question-type-box").find('.question-item-box').length;//设置题目type下没有题目时“清空”字样为灰色
	        if(len == 0){
	            $(this).parent('.question-type-box').find('.control-box').find('.text-icon').css('color','grey');
	        }
		},"mouseleave":function(){
			$(this).parent(".question-type-box").removeClass("question-type-hover");
		}},Teacher.paper.paper_preview.questionHandle);
		
		this.mainContainer.on({"mouseenter":function(){
			$(this).addClass("question-item-hover");
		},"mouseleave":function(){
			$(this).removeClass("question-item-hover");
		}},Teacher.paper.paper_preview.questionItemBox);
		
		this.mainContainer.on({"mouseenter":function(){
			$(this).addClass("hover");
		},"mouseleave":function(){
			$(this).removeClass("hover");
		}},Teacher.paper.paper_preview.unitBox);

		//设置结构效果
		this.typeList.hover(function(){
			var this_a = Teacher.paper.paper_preview;
			//this_a.typeCur.addClass("hover");
			this_a.typeOption.show();
		},function(){
			var this_a = Teacher.paper.paper_preview;
			//this_a.typeCur.removeClass("hover");
			this_a.typeOption.hide();
		});
		// 修改成radio模式的切换结构开始
		this.typeInputItem.find('input[type="radio"]').click(function(){
			var this_a = Teacher.paper.paper_preview;
			var thisType = $(this).attr('id');
			$('.current-type').attr('sty',$(this).attr('sty'));
			switch(thisType){
				case "default":
					$(".icon-view").removeClass("hide");
					$(".preview-content .fn-hide").removeClass("fn-hide");
	                $('.current-type').attr('sty','1');
				break;
				case "static":
					$(".icon-view").removeClass("hide");
					$(".preview-content .fn-hide").removeClass("fn-hide");
					$("#menu-secret-mark .icon-view,#menu-student-info .icon-view,#menu-cent-box .icon-view").addClass("hide");
					$("#secret-mark,#student-info,#cent-box").addClass("fn-hide");
	                $('.current-type').attr('sty','2');
				break;
				case "test":
					$(".icon-view").removeClass("hide");
					$(".preview-content .fn-hide").removeClass("fn-hide");
					$("#menu-paper-prititle .icon-view,#menu-separate-line .icon-view,#menu-secret-mark .icon-view,#menu-paper-info .icon-view,#menu-cent-box .icon-view,#menu-alert-info .icon-view,.list-type-title>.icon-view").addClass("hide");
					$("#paper-prititle,#separate-line,#secret-mark,#paper-info,#cent-box,#alert-info,.paper-type-box>.handle-box,.deco-box").addClass("fn-hide");
	                $('.current-type').attr('sty','3');
				break;
				case "homework":
					$("#menu-paper-prititle .icon-view,#menu-separate-line .icon-view,#menu-secret-mark .icon-view,#menu-paper-info .icon-view,#menu-cent-box .icon-view,#menu-alert-info .icon-view,#menu-student-info .icon-view,.list-title>.icon-view").addClass("hide");
					$("#paper-prititle,#student-info,#separate-line,#secret-mark,#paper-info,#cent-box,#alert-info,.handle-box").addClass("fn-hide");
	                $('.current-type').attr('sty','4');
				break;
			}
			this_a.typeOption.hide();
			//this_a.typeCur.children("span").text(thisText);
			//this_a.typeCur.removeClass("hover");
	        this_a.ajax_struction();//ajax
		});
		// 修改成radio模式的切换结构结束
		this.typeItem.change(function(){
			var this_a = Teacher.paper.paper_preview;
			//var thisText = $(this).text();
			var thisType = $(this).val();
	        $('.current-type').attr('sty',$(this).attr('sty'));
			switch(thisType){
				case "default":
					$(".icon-view").removeClass("hide");
					$(".preview-content .fn-hide").removeClass("fn-hide");
	                $('.current-type').attr('sty','1');
				break;
				case "static":
					$(".icon-view").removeClass("hide");
					$(".preview-content .fn-hide").removeClass("fn-hide");
					$("#menu-secret-mark .icon-view,#menu-student-info .icon-view,#menu-cent-box .icon-view").addClass("hide");
					$("#secret-mark,#student-info,#cent-box").addClass("fn-hide");
	                $('.current-type').attr('sty','2');
				break;
				case "test":
					$(".icon-view").removeClass("hide");
					$(".preview-content .fn-hide").removeClass("fn-hide");
					$("#menu-paper-prititle .icon-view,#menu-separate-line .icon-view,#menu-secret-mark .icon-view,#menu-paper-info .icon-view,#menu-cent-box .icon-view,#menu-alert-info .icon-view,.list-type-title>.icon-view").addClass("hide");
					$("#paper-prititle,#separate-line,#secret-mark,#paper-info,#cent-box,#alert-info,.paper-type-box>.handle-box,.deco-box").addClass("fn-hide");
	                $('.current-type').attr('sty','3');
				break;
				case "homework":
					$("#menu-paper-prititle .icon-view,#menu-separate-line .icon-view,#menu-secret-mark .icon-view,#menu-paper-info .icon-view,#menu-cent-box .icon-view,#menu-alert-info .icon-view,#menu-student-info .icon-view,.list-title>.icon-view").addClass("hide");
					$("#paper-prititle,#student-info,#separate-line,#secret-mark,#paper-info,#cent-box,#alert-info,.handle-box").addClass("fn-hide");
	                $('.current-type').attr('sty','4');
				break;
			}
			this_a.typeOption.hide();
			//this_a.typeCur.children("span").text(thisText);
			//this_a.typeCur.removeClass("hover");
	        this_a.ajax_struction();//ajax
		});
		//隐藏和显示可设置内容效果vk01
		this.mainMenu.on("click",".icon-view",function(){
			if($('.icon-view').attr('class').indexOf('disabled') > 0) return false;
			$('.icon-view').addClass('disabled');
			var this_a = Teacher.paper.paper_preview;
			var $this = $(this);
			var thisId = "#"+$this.parent().attr("data-id");
			var hideEle;
			$this.toggleClass("hide");
			if($(thisId).children(".handle-box").length>0){
				hideEle = $(thisId).children(".handle-box");
			}else{
				hideEle = $(thisId);
			}
			hideEle.toggleClass("fn-hide");
			if(hideEle.is(":visible")){
				this_a.flashMask(hideEle);
				this_a.goPosition(hideEle);
			}
	        this_a.ajax_left_select();
		});	
		//内容点击闪烁定位效果
		this.mainMenu.on("click",".menu-item-title",function(){
			var this_a = Teacher.paper.paper_preview;
			var $this = $(this);
			var thisId = "#"+$this.parent().attr("data-id");
			if($(thisId).children(".handle-box").length>0){
				this_a.flashMask($(thisId).children(".handle-box"));
			}else{
				this_a.flashMask($(thisId));
			}
			this_a.goPosition($(thisId));
		})
		this.mainMenu.on("click",".question-item",function(){
			var this_a = Teacher.paper.paper_preview;
			var $this = $(this);
			var thisId = "#"+$this.attr("data-id");
			this_a.flashMask($(thisId));
			this_a.goPosition($(thisId));
		});		
		this.mainContent.on("click",".question-item-box",function(){
			var this_a = Teacher.paper.paper_preview;
			var $this = $(this);
			var thisId = "#menu-"+$this.attr("id");
			this_a.flashMask($(thisId),171,16);

			//显示隐藏试卷答案
			if($this.find('.answer').hasClass('undis')) $this.find('.answer').removeClass('undis');
			else $this.find('.answer').addClass('undis');
		});
		this.mainContainer.on("click","a",function(e){
			var this_a1 = this;
			Teacher.paper.paper_preview.nBtnClick(e, this_a1);
		});
		//设置试卷弹窗  左上角的设置按钮
		this.mainMenu.on("click",".set-btn",function(){
			var this_a = this;
			Teacher.paper.paper_preview.setBtnClick(this_a);
		});
		this.mainMenu.on("click",".icon-set",function(){
			var this_a = this;
			Teacher.paper.paper_preview.setIcoBtnClick(this_a);
		});
		this.mainContainer.on("click",".unit-box",function(){
			var this_a = this;
			Teacher.paper.paper_preview.setUnitBtnClick(this_a);
		});
		this.mainContainer.on("click",".handle-box",function(){
			var this_a = this;
			Teacher.paper.paper_preview.setHandleBtnClick(this_a);
		});
		this.savePaper.click(function(){
			Teacher.paper.paper_preview.saveBtnClick(0);
	    });
	    this.saveasPaper.click(function(){
	    	Teacher.paper.paper_preview.saveBtnClick(1);
	    });
	    //设置试卷重置
		this.resetPaper.click(function(){
			$.tiziDialog({
				content:"是否新建一份空白试卷，并放弃所有未保存的修改？",
				cancel:true,
				ok:function(){Teacher.paper.paper_preview.reset_paper();}
			});
		});
	},
	saveBtnClick:function(save_as){
		//判断作业题是否为空
		if($('.question-item-box').length <=0){
			$.tiziDialog({content:Teacher.paper.common.ErrorNoQuestion});
			return false;
		}
		callbackfn = function(){
			var preTitle = $('.preview-title h3').html();
			$.tiziDialog({
				id : 'commonSaveTitle',
				title:'保存试卷',
				content:$('.savePaperContent').html().replace('commonSaveTitleForm_beta','commonSaveTitleForm'),
				icon:null,
				width:480,
				okVal:'保存试卷',
				ok:function(){
					$('.commonSaveTitleForm').submit();
					return false;
				},
				cancel:true,
				cancelVal:'继续编辑'
			});
			$('.paper_title').val(preTitle);
			//前端验证
			Common.valid.paperSaveTitle(save_as);

			$('#tiziLogin').hide();
			$('#tiziRegister').hide();
		}
		seajs.use('tizi_login_form',function(ex){
			ex.loginCheck('function',callbackfn);
		});
	},
	setHandleBtnClick:function(this_a){
		var thisId = "#win-"+$(this_a).parent().attr("id");
		this.randerConfigBox(this_a);
        this.renderAlertBox();
		this.goPosWin(thisId);
        this.radio_render('.openwin-box');
	},
	setUnitBtnClick:function(this_a){
		var thisId = "#win-"+$(this_a).attr("id");
		this.randerConfigBox(this_a);
        this.renderAlertBox();
		this.goPosWin(thisId);
        this.radio_render('.openwin-box');
	},
	setIcoBtnClick:function(this_a){
		var thisId = "#win-"+$(this_a).parent().data("id");
		this.randerConfigBox(this_a);		
        this.renderAlertBox();
        this.goPosWin(thisId);
        this.radio_render('.openwin-box');
	},
	setBtnClick:function(this_a){
		this.randerConfigBox(this_a);
    	this.renderAlertBox();
    	this.radio_render('.openwin-box');
	},
	//渲染config窗口
	randerConfigBox:function(this_a){
		$.tiziDialog({
			icon:null,
			width:700,
			title:"试卷设置",
			cancel:true,
			content:'<div class="openwin-box">'+$("#setting-content").html()+'</div>',
			ok:function(){
				var this_a = Teacher.paper.paper_preview;
	            var jsonstr = this_a.getOption($('.openwin-box')); //get post json str
	            var sid = $('.subject-chose').data('subject');
	            
	            var url = baseUrlName + 'paper/paper_preview/save_paper_config';
	            $.tizi_ajax({
	            	url:url,
	            	type:"POST",
					dataType:"json",
	            	data:{config:jsonstr,sid:sid},
	            	success:function(data){
		            	if(data.errorcode == false){
		            		$.tiziDialog({content:data.error});
		            	}
		            }
		        });

	            //确定后渲染页面
	            var optionJson = new Function("return "+this_a.getOption($('.openwin-box'),'id'))();
	            this_a.reRenderPage(optionJson);
			}
		});
	},
	//控制按钮点击
	nBtnClick:function(e, this_a1){
        this_a = $(this_a1).find('span');
        if(this_a.attr('class') == 'del-icon'){  //删除题目
            if(this_a.attr('opt') == 'inner'){
                var qid = '#q' + this_a.data("qid");
                var menuId = '#menu-q' + this_a.data('qid');

                $.tiziDialog({
					content:"确定删除当前题目？",
					cancel:true,
					ok:function(){
						var qorigin = this_a.data('qorigin');
						Teacher.paper.paper_preview.del_inner_question(this_a.data('qid'),qorigin,function(){
							$(qid).remove(); //右侧题目消失
	                		$(menuId).remove(); //左侧导航消失
	                		Teacher.paper.paper_preview.orderSort();
	                		Teacher.paper.paper_preview.orderType();
						},function(){});

					}
				});
            }
            else{
                var refer = this_a.attr('refer');

                $.tiziDialog({
					content:"确定清空并删除当前题型？",
					cancel:true,
					ok:function(){
						Teacher.paper.paper_preview.del_question_type(this_a.attr('refer'),function(){
							$('#question-type'+refer).remove();
                			$('#menu-type-li-questiontype'+refer).remove();
                			Teacher.paper.paper_preview.orderType();
            				Teacher.paper.paper_preview.orderSort();
						},function(){});
					}
				});
            }
            //this.orderType();
            //this.orderSort();
        }    
        if(this_a.attr('class') == 'text-icon'){
            if(this_a.html() == '清空'){//清空
                var refer = this_a.attr('refer');
                if(this_a.parents('.question-type-box').find('.question-item-box').length > 0){
	                $.tiziDialog({
						content:"确定清空当前题型？",
						cancel:true,
						ok:function(){
							Teacher.paper.paper_preview.clean_question_type(this_a.attr('refer'),function(){
								$('#question-type'+refer).find('.question-item-box').remove();
	                			$('#menu-outer-ul-question-type'+refer).find('.question-item').remove();
	                			Teacher.paper.paper_preview.orderType();
	                			Teacher.paper.paper_preview.orderSort();
							},function(){});
						}
					});
				}
            }
        }
        if(this_a.attr('class') == 'up-icon'){  //上升
            if(this_a.attr('opt') == 'inner'){
                var outerId = this_a.parent().parent().parent().parent().attr('id')
                var inde = this.isFirstEle('#q'+this_a.data('qid'),'#'+outerId,'.question-item-box');
                if(inde == 1 || inde == 0){
					//阻止事件冒泡
                	e.stopPropagation();
                	return;
                };
                this.moveQuestion('#' + outerId,'#'+this_a.parent().parent().parent().attr('id'),'up','.question-item-box')
                this.moveQuestion('#menu-outer-ul-' + outerId,'#menu-q'+this_a.data('qid'),'up','.question-item')
                this.orderSort();
                $('.up-icon').removeClass('up-icon').addClass('up-icon-2'); 
                this.up_inner_question(outerId,function(){
                	$('.up-icon-2').addClass('up-icon').removeClass('up-icon-2');
                });//ajax
            }
            else{
                var outerId = '#question-type' + this_a.attr('refer');
                if(this_a.attr('papertype') == '1'){
                    var inde = this.isFirstEle(outerId,'#paper-type1','.question-type-box');
                    if(inde == 1 || inde == 0) {
		                //阻止事件冒泡
                		e.stopPropagation();
                		return;
                    }
                    this.moveQuestion('#paper-type1',outerId,'up','.question-type-box');
                    this.moveQuestion('#menu-paper1-con','#menu-type-li-questiontype' + this_a.attr('refer'),'up','.menu-type-li');
                    $('.up-icon').removeClass('up-icon').addClass('up-icon-2'); 
                    this.up_question_type('paper-type1',function(){
                    	$('.up-icon-2').addClass('up-icon').removeClass('up-icon-2');
                    });//ajax
                }else{
                    var inde = this.isFirstEle(outerId,'#paper-type2','.question-type-box');
                    if(inde == 1 || inde == 0) {
		                //阻止事件冒泡
                		e.stopPropagation();
                		return;
                    }
                    this.moveQuestion('#paper-type2',outerId,'up','.question-type-box');
                    this.moveQuestion('#menu-paper2-con','#menu-type-li-questiontype' + this_a.attr('refer'),'up','.menu-type-li');
                    //调整题目顺序效果
					this.sortable_zujuan();
                    $('.up-icon').removeClass('up-icon').addClass('up-icon-2'); 
                    this.up_question_type('paper-type2',function(){
                    	$('.up-icon-2').addClass('up-icon').removeClass('up-icon-2');
                    });//ajax
                }
                this.orderType();
                this.orderSort();
            }
        }
        if(this_a.attr('class') == 'down-icon'){  //下降
            if(this_a.attr('opt') == 'inner'){
                var outerId = this_a.parent().parent().parent().parent().attr('id')
                var inde = this.isFirstEle('#q'+this_a.data('qid'),'#'+outerId,'.question-item-box');
                if(inde == 1 || inde == 2){
                	//阻止事件冒泡
                	e.stopPropagation();
                	return;
                };
                this.moveQuestion('#' + outerId,'#'+this_a.parent().parent().parent().attr('id'),'down','.question-item-box')
                this.moveQuestion('#menu-outer-ul-' + outerId,'#menu-q'+this_a.data('qid'),'down','.question-item')
                this.orderSort();
        		$('.down-icon').removeClass('down-icon').addClass('down-icon-2'); // 点击下降按钮去掉class
                this.up_inner_question(outerId,function(){
                	$('.down-icon-2').addClass('down-icon').removeClass('down-icon-2');
                });//ajax
            }
            else{
                var outerId = '#question-type' + this_a.attr('refer');
                if(this_a.attr('papertype') == '1'){
                    var inde = this.isFirstEle(outerId,'#paper-type1','.question-type-box');
                    if(inde == 1 || inde == 2) {
		                //阻止事件冒泡
                		e.stopPropagation();
                		return;
                    }
                    this.moveQuestion('#paper-type1',outerId,'down','.question-type-box');
                    this.moveQuestion('#menu-paper1-con','#menu-type-li-questiontype' + this_a.attr('refer'),'down','.menu-type-li');
        			$('.down-icon').removeClass('down-icon').addClass('down-icon-2'); // 点击下降按钮去掉class
                    this.up_question_type('paper-type1',function(){
                    	$('.down-icon-2').addClass('down-icon').removeClass('down-icon-2');
                    });//ajax
                }else{
                    var inde = this.isFirstEle(outerId,'#paper-type2','.question-type-box');
                    if(inde == 1 || inde == 2) {
		                //阻止事件冒泡
                		e.stopPropagation();
                		return;
                    }
                    this.moveQuestion('#paper-type2',outerId,'down','.question-type-box');
                    this.moveQuestion('#menu-paper2-con','#menu-type-li-questiontype' + this_a.attr('refer'),'down','.menu-type-li');
					//调整题目顺序效果
					this.sortable_zujuan();
                    $('.down-icon').removeClass('down-icon').addClass('down-icon-2'); // 点击下降按钮去掉class
                    this.up_question_type('paper-type2',function(){
                    	$('.down-icon-2').addClass('down-icon').removeClass('down-icon-2');
                    });//ajax
                }
                this.orderType();
                this.orderSort();
            }
        }
        if(this_a.attr('class') == 'set-icon'){  //设置
		    var thisId = "#win-"+$(this_a1).parent().parent().parent().attr("id");

		    $.tiziDialog({
				icon:null,
				width:700,
				title:"试卷设置",
				cancel:true,
				content:'<div class="openwin-box">'+$("#setting-content").html()+'</div>',
				ok:function(){
					var this_a = Teacher.paper.paper_preview;
                    var jsonstr = this_a.getOption($('.openwin-box')); //get post json str
                    var sid = $('.subject-chose').data('subject');

                    var url = baseUrlName + 'paper/paper_preview/save_paper_config';
                    $.tizi_ajax({
                    	url:url,
                    	type:"POST",
						dataType:"json",
                    	data:{config:jsonstr,sid:sid},
                    	success:function(data){
	                    	if(data.errorcode == false){
	                    		$.tiziDialog({content:data.error});
	                    	}
	                    }
	                });

                    //确定后渲染页面
                    var optionJson = new Function("return "+this_a.getOption($('.openwin-box'),'id'))();
                    this_a.reRenderPage(optionJson);
				}
			});	    
            this.renderAlertBox();
		    this.goPosWin(thisId);
            this.radio_render('.openwin-box')
        }
        if(this_a.attr('class') == 'change-icon'){//换一题
			if($(this_a).hasClass('disabled')){return;}
			$(this_a).addClass('disabled');

			var sid = $('.subject-chose').data('subject');
			var cid = $(this_a).attr('data-cid');
			var qtype = $(this_a).attr('data-qtype_id');
			var pqtype = $(this_a).parents('.question-type-box').attr('id').substr(13);
			var qid = $(this_a).attr('data-qid');
			var qlevel = $(this_a).attr('data-level');
			var qindex = $(this_a).parents('.question-item-box').find('.question-index').text();
			var qids = new Array();
			$(this_a).parents('.question-type-box').find('.change-icon').each(function(i,e){
		        if($(e).attr('data-qorigin') === '0') qids.push($(e).attr('data-qid'));
		    });

			var param = {'sid':sid,'cid':cid,'qids':qids,'qindex':qindex,'qid':qid,'qtype':qtype,'pqtype':pqtype,'qlevel':qlevel};
			//console.log(data);

			this.change_inner_question(param,qid,function(){
				$('#q'+qid).find('.change-icon').removeClass('disabled');
			},function(){
				$('#q'+qid).find('.change-icon').removeClass('disabled');
			});
        }
		e.stopPropagation();
	},
	change_inner_question:function(param,qid,success,err){		
		$.tizi_ajax({
			type: "POST",
            dataType: "json",  
            url: baseUrlName+"paper/paper_question/change_question",
            data: param,
            success: function(data) {
				//判断是否有分页的显示loading图片
            	if(data.errorcode == true) {
            		$('#q'+qid).attr('id','q'+data.qid);       		
					$('#q'+data.qid).attr('data-pqid',data.pqid);
                	$('#q'+data.qid).html(data.html);
                	$('#menu-q'+qid).attr('id','menu-q'+data.qid);
                	$('#menu-q'+data.qid).attr('data-pqid',data.pqid);
                	$('#menu-q'+data.qid).attr('data-id','q'+data.qid);
                	$('#menu-q'+data.qid).attr('title',data.qtitle);
                	$('#menu-q'+data.qid).find('.title-content').html(data.qtitle);
                	Teacher.paper.paper_preview.up_inner_question($('#q'+data.qid).parents('.question-type-box').attr('id'),function(){});
					//Teacher.paper.homework_preview.flashMask($('#q'+data.qid));
					//Teacher.paper.homework_preview.goPosition($('#q'+data.qid));
            		success();
            	}else{
            		$.tiziDialog({content:data.error});
            		err();
            	}
            }
		});
	},
	del_inner_question:function(qid,qorigin,success,err){
	    var url = baseUrlName + "paper/paper_question/remove_question_from_paper";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'qid':qid,'sid':sid,'qorigin':qorigin}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode==true){
		            success();
		            Teacher.paper.paper_common.randerCart(data.question_cart);
		        }else{
		            err();
		            $.tiziDialog({content:data.error});
		        }    
		    }
		});
	},
	del_question_type:function(type,success,err){
	    var url = baseUrlName + "paper/paper_preview/delete_question_type";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'qtype':type,'sid':sid}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode==true){
		            success();
		            Teacher.paper.paper_common.randerCart(data.question_cart);
		        }else{
		            err();
		            $.tiziDialog({content:data.error});
		        }
		    }
		});
	},
	up_inner_question:function(type,unlock){
	    var outer = $('#'+type);
	    var qorder = new Array();
	    type=type.substr(13);
	    outer.find('.question-item-box').each(function(i,e){
	        qorder.push($(e).attr('data-pqid'));
	    })
	    var url = baseUrlName + "paper/paper_preview/save_question_order";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'qtype':type,'sid':sid,"qorder":qorder.toString()}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode == false){
		            $.tiziDialog({content:data.error});
		        }
		        unlock();
		    }
		});
	},
	up_question_type:function(type,unlock){
	    var outer = $('#'+type);
	    var qorder = new Array();
	    outer.find('.question-type-box').each(function(i,e){
	        qorder.push($(e).attr('id').substr(13));
	    })
	    var url = baseUrlName + "paper/paper_preview/save_question_type_order";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'sectiontype':type.substr(10),'sid':sid,"qtorder":qorder.toString()}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode == false){
		            $.tiziDialog({content:data.error});
		        }
		        unlock();
		    }
		});
	},
	//拖动排序ajax
	ajax_move_question:function(qid,typeid){
	    var q_item = $('#'+typeid).find('.question-item');
	    var qorder = new Array();
	    q_item.each(function(i,e){
	        var rid = $(e).attr('data-pqid');
	        qorder.push(rid);
	    });
	    var sid = $('.subject-chose').data('subject');
	    var url = baseUrlName + 'paper/paper_preview/move_question';
	    var para = {'qorder':qorder.toString(),'pqid':qid,'qtype':typeid.substr(27),'sid':sid}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        Teacher.paper.paper_common.randerCart(data.question_cart);
		        if(data.errorcode == false) $.tiziDialog({content:data.error});
		    }
		});
	},
	clean_question_type:function(type,success,err){
	    var url = baseUrlName + "paper/paper_question/remove_question_from_cart";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'qtype':type,'sid':sid}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode==true){
		            success();
		            Teacher.paper.paper_common.randerCart(data.question_cart);
		        }else{
		            err();
		            $.tiziDialog({content:data.error});
		        }   
		    }
		});
	},
	//结构变更
	ajax_struction:function(){
	    $('body').find('#temp-con-div').remove();
	    $('body').append("<div id='temp-con-div' class='fn-hide'></div>");
	    var contentHtml = $('#setting-content').html();
	    $('#temp-con-div').html(contentHtml);
	    this.renderAlertBox('#temp-con-div');
	    //.openbox里的 radio-render-cent-verify（是否显示分数栏）改成1
	    $('#temp-con-div').find('.radio-render-cent-verify').html('1');
	    this.radio_render('#temp-con-div');
	    var jsonstr = this.getOption($('#temp-con-div'));
	    $('body').find('#temp-con-div').remove();
	    //测验、作业模式时将分数栏显示为0
	    switch(this.getStruc()){
	        case '3':jsonstr = this.changeIsCheckScore(jsonstr);break;
	        case '4':jsonstr = this.changeIsCheckScore(jsonstr);break;
	    }
	    //ajax
	    var url = baseUrlName + "paper/paper_preview/save_paper_config";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'config':jsonstr,'sid':sid,'type':this.getStruc()}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
	        	if(data.errorcode == false){
	        		$.tiziDialog({content:data.error});
	        	}
		    }
		});
	},
	//测验、作业模式时将分数栏显示为0 function
	changeIsCheckScore:function(str){
	    var json = new Function("return " + str)();
	    for(i in json){
	        if(json[i]['ischeckedscore']){
	            json[i]['ischeckedscore'] = 0;
	        }
	    }
	    return JSON.stringify(json);
	},
	//是否显示变更
	ajax_left_select:function(){
	    $('body').find('#temp-con-div').remove();
	    $('body').append("<div id='temp-con-div' class='fn-hide'></div>");
	    var contentHtml = $('#setting-content').html();
	    $('#temp-con-div').html(contentHtml);
	    this.renderAlertBox('#temp-con-div');
	    this.radio_render('#temp-con-div');
	    var jsonstr = this.getOption($('#temp-con-div'));
	    $('body').find('#temp-con-div').remove();
	    //ajax
	    var url = baseUrlName + "paper/paper_preview/save_paper_config";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'config':jsonstr,'sid':sid};
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
	    		$('.icon-view').removeClass('disabled');
	        	if(data.errorcode == false) $.tiziDialog({content:data.error});
		    }
		});
	},
	//得到当前试卷类型（默认、标准、检测、作业）
	getStruc:function(){
	    var strName = $('.current-type').attr('sty');
	    return strName;
	},
	reset_paper:function(){
	    var url = baseUrlName + "paper/paper_question/reset_paper";
	    var sid = $('.subject-chose').data('subject');
	    var para = {'sid':sid}
	    $.tizi_ajax({
	    	url:url,
	    	type:"POST",
			dataType:"json",
	    	data:para,
	    	success:function(data){
		        if(data.errorcode == true){
		            $.tiziDialog({ok:false,content:data.error});
		            location.href=baseUrlName + "teacher/paper/preview/" + sid;
		        }  
		        else{
		            $.tiziDialog({content:data.error});
		        }

		    }
		});
	},
	
	//点击弹出框“确定”按钮后重新渲染页面 vk3
	reRenderPage:function(json){
	    for(i in json){
	        var refer = i.substr(4);
	        var ischeck = json[i]['ischecked'];
	        var ischeckscore = json[i]['ischeckedscore'];
	        var headerEle = ['separate-line','secret-mark','paper-title','paper-prititle','paper-info','student-info','cent-box','alert-info'];
	        headerEle = headerEle.toString();
	        if(ischeck){
	            $('#menu-'+refer).find('.icon-view').removeClass('hide');
	            if( headerEle.indexOf(refer) != -1) $('#'+refer).removeClass('fn-hide');//头部的8个
	            else $('#'+refer).find('.question-handle-box').removeClass('fn-hide');//非头部
	        }
	        else{
	            $('#menu-'+refer).find('.icon-view').addClass('hide');
	            if( headerEle.indexOf(refer) != -1) $('#'+refer).addClass('fn-hide');//头部的8个
	            else $('#'+refer).find('.question-handle-box').addClass('fn-hide');//非头部
	        }
	        if(ischeckscore != undefined){
	            if(ischeckscore == 0){
	                $('#'+refer).find('.deco-box').addClass('fn-hide');
	            }
	            else{
	                $('#'+refer).find('.deco-box').removeClass('fn-hide');
	            }
	        }
	        //点击确定后渲染卷面文字（title\note）
	        if(i.substr(0,17) == 'win-question-type'){
	            var ch_title = json[i].title;
	            var ch_des = json[i].content;
	            $('#'+i.substr(4)).find('.change-inner-title').html(ch_title);
	            $('#'+i.substr(4)).find('.change-inner-des').html(ch_des);
	            //left cart
	            $('#left-question-cart-name'+i.substr(17)).html(json[i].title);
	            //menu-type-render
	            $('.menu-render-type-name'+i.substr(17)).html(json[i].title);
	        }
	        if("secret-mark,paper-title,paper-prititle,paper-info,student-info".indexOf(i.substr(4)) != -1){
	            $('#'+i.substr(4)).html(json[i].title);
	        }
	        if(i == 'win-alert-info'){
	            $('#alert-info').find('dd').html(json[i].content);
	        }
	        if(i == 'win-paper-type1' || i == 'win-paper-type2'){
	        	if(ischeck){
		           	$('#'+i.substr(4)).find('.type-handle-box').removeClass('fn-hide');//非头部
		        }
		        else{
		            $('#'+i.substr(4)).find('.type-handle-box').addClass('fn-hide');//非头部
		        }
	            var ch_title = json[i].title;
	            var ch_des = json[i].content;
	            $('#'+i.substr(4)).find('.type-handle-box').find('dt').html(ch_title);
	            $('#'+i.substr(4)).find('.type-handle-box').find('dd').html(ch_des);
	        }
	    }
	},
	//初始化渲染radio选中与否
	radio_render:function(openbox){
	    var tables = $(openbox).find('table');
	    for(var i=0;i<tables.length;i++){
	        var table = tables.eq(i);
	        var radiorender = tables.find('.radio-render')
	        if(radiorender.length != 0){
	            for(var j=0;j<radiorender.length;j++){
	                var rname = radiorender.eq(j).attr('name');
	                var val = parseInt(radiorender.eq(j).html());
	                val = val==0?1:0;
	                $('input[name="'+rname+'"]').eq(val).attr('checked',true);
	            }
	        }
	    }
	},
	//上移 下移 右侧题目
	moveQuestion:function(container,moveEle,toward,sameEle){
	    var now_no = $(container).find(sameEle).index($(moveEle));
	    var nu = (toward=='up')?(now_no-1):(now_no);
	    if(toward == 'up' && now_no == 0) return;
	    if(toward == 'down' && now_no == $(container).find(sameEle).length - 1) return;
	    var clo = $(moveEle).clone();
	    $(moveEle).remove();
	    if(toward == 'up') $(container).find(sameEle).eq(nu).before(clo);
	    if(toward == 'down') $(container).find(sameEle).eq(nu).after(clo);
	},
	//判断当前元素是否是在外部container第一位
	isFirstEle:function(ele,con,same){
	    var isfir = $(con).find(same).index($(ele));
	    var len = $(con).find(same).length;
	    if(len == 1){
	        return 1;
	    }
	    if(isfir == 0) {return 0};
	    if(isfir == $(con).find(same).length - 1) {return 2};
	},
	//json格式化试卷设置内容现实与否
	getOption:function(openbox,get_show){
	    var getshow = 'pos';
	    if(get_show != null) getshow = get_show;
	    var tables = openbox.find('table');
	    var sendStr = '{';
	    for(var i=0;i<tables.length;i++){
	        if(tables.eq(i).attr(getshow).substr(0,14) == "win-paper-type"){
	            sendStr += '"' + tables.eq(i).attr(getshow).substr(0,17) + '":';
	        }
	        else sendStr += '"' + tables.eq(i).attr(getshow) + '":';
	        sendStr +='{';
	        if(tables.eq(i).attr(getshow).replace(/[^0-9]/ig,"") != ''){
	            sendStr += '"id":' + tables.eq(i).attr(getshow).replace(/[^0-9]/ig,"") + ',';
	        }
	        if(tables.eq(i).attr(getshow).substr(0,17) == 'win-paper-typeone'){
	            sendStr += '"type":1,'
	        }
	        if(tables.eq(i).attr(getshow).substr(0,17) == 'win-paper-typetwo'){
	            sendStr += '"type":2,'
	        }
	        var inputs = tables.eq(i).find('input,textarea');
	        var ischeck = false;
	        for(var j=0;j<inputs.length;j++){
	            if(inputs.eq(j).attr('type') == 'radio'){
	                var checkval = $('input[name="'+inputs.eq(j).attr('name')+'"]:checked').val()
	                if(j==0){
	                    if(j == inputs.length-2) sendStr += '"ischecked":' + checkval;
	                    else sendStr += '"ischecked":' + checkval+',';
	                }
	                if(j==1){
	                }
	                if(j==2){
	                    if(j == inputs.length-2) sendStr += '"ischecked":' + checkval;
	                    else sendStr += '"ischeckedscore":' + checkval+',';
	                }
	                if(j==3){
	                }
	            }
	            else{
	                if(inputs.eq(j).attr('class') == 'text-input'){
	                    if(j == inputs.length-1) sendStr += '"title":"' +inputs.eq(j).val().replace(/"/g,"'")+'"';
	                    else sendStr += '"title":"' +inputs.eq(j).val()+'",';
	                }
	                else{
	                    if(j == inputs.length-1) sendStr += '"content":"' +inputs.eq(j).val().replace(/"/g,"'").replace(/\n/g,'<br />')+'"';
	                    else sendStr += '"content":"' +inputs.eq(j).val()+'",';
	                }
	            }
	        }
	        if(i == tables.length-1) sendStr += '}';
	        else sendStr += '},';
	    }
	    sendStr +='}';
	    return sendStr
	},
	//根据左侧动态渲染弹出框radio
    renderAlertBox:function(con){
    	var conBox = '.openwin-box';
        if(con != undefined) conBox = con;
        //左侧试卷头部渲染
        var left_header = $('.paper-header').find('li');
        for(var i=0;i<left_header.length;i++){
            var icon_radio = left_header.eq(i).find('.icon-view');
            if(icon_radio.length != 0){
                if(icon_radio.eq(0).attr('class').indexOf('hide') == -1){
                    var outer = left_header.eq(i).data('id');
                    $(conBox).find('#win-'+outer).find('span[name="'+outer+'"]').html(1);
                }
                else{
                    var outer = left_header.eq(i).data('id');
                    $(conBox).find('#win-'+outer).find('span[name="'+outer+'"]').html(0);
                }
            }
        }
        //左侧题型渲染
        var child_list = $('.paper-list-container').find('.child-list');
        $('.paper-set-win').find('table[id^="win-question-type"]').hide(); // 初始化 试卷设置弹出框的题型
        var win_length = $('.paper-set-win').find('table[id^="win-question-type"]').length;
        for(var n=0;n<child_list.length;n++){
            var type_li = child_list.eq(n).find('.menu-type-li');
            for(var i=0;i<type_li.length;i++){
                var icon_radio = type_li.eq(i).find('.icon-view');
                if(icon_radio.length != 0){
                    if(icon_radio.eq(0).attr('class').indexOf('hide') == -1){
                        var outer = 'question-type'+type_li.eq(i).attr('id').replace(/[^0-9]/ig,"");                        
                        $(conBox).find('#win-'+outer).find('span[name="'+outer+'"]').html(1);
                    }
                    else{
                        var outer = 'question-type'+type_li.eq(i).attr('id').replace(/[^0-9]/ig,"");
                        $(conBox).find('#win-'+outer).find('span[name="'+outer+'"]').html(0);                        
                    }
                    // 试卷设置弹出框 题型渲染
                    $('.paper-set-win table[id="win-'+ outer +'"]').show(); 
                    // 试卷设置弹出框 其他 题型渲染
                    $('.paper-set-win').find('table[id^="win-question-type"]').eq(win_length-1).show();
                    // 重新排序 和 大写输出
					var righttype = $('.paper-set-win').find('table[id^="win-question-type"]:visible').find('.title-td');
			        this.renderNum(righttype);
                }
            }
        }

        //分卷渲染
        var paperheader = $('.paper-list-container').find('.list-type-title');
        for(var i=0;i<paperheader.length;i++){
            var icon_v =  paperheader.eq(i).find('.icon-view');
            if(icon_v.length != 0){
                if(icon_v.eq(0).attr('class').indexOf('hide') == -1){
                    var outer = paperheader.eq(i).data('id'); 
                    $(conBox).find('#win-'+outer).find('span[name="'+outer+'"]').html(1);
                } 
                else{
                    var outer = paperheader.eq(i).data('id'); 
                    $(conBox).find('#win-'+outer).find('span[name="'+outer+'"]').html(0);
                }
            }
        }
        
        //渲染弹出框文字 title note等
        $('.preview-content').find('.unit-box').each(function(i,e){
            var this_id = $(e).attr('id');
            if("secret-mark,paper-title,paper-prititle,paper-info,student-info".indexOf(this_id) != -1){
                $('#win-'+this_id).find('input[type="text"]').val($(e).text());
            } 
            if(this_id == 'alert-info'){
                $('#win-alert-info').find('textarea').val($(e).find('dd').html().replace(/<br>/g,'\n'));
            }
        });
        //渲染卷头分卷1 分卷2
        $('.preview-content').find('.paper-type-box').find('.type-handle-box').each(function(i,e){
            var ch_title = $(e).find('dt').text();        
            var ch_des = $(e).find('dd').html();
            var this_id = $(e).parent().attr('id');
            $('#win-'+this_id).find('input[type="text"]').val(ch_title);
            $('#win-'+this_id).find('textarea').val(ch_des.replace(/<br>/g,'\n'));

        });
        //渲染题型的title note
        $('.preview-content').find('.question-type-box').find('.question-handle-box').each(function(i,e){
            var ch_title = $(e).find('.change-inner-title').text();        
            var ch_des = $(e).find('.change-inner-des').html();
            var this_id = $(e).parent().attr('id');
            $('#win-'+this_id).find('input[type="text"]').val(ch_title);
            $('#win-'+this_id).find('textarea').val(ch_des.replace(/<br>/g,'\n'));

            //渲染评分栏显示选项
            var ch_deco = $(e).find('.deco-box');
            if(ch_deco.attr('class').indexOf('hide') == -1){
            	var outer = $('#win-'+this_id).find('input[type="radio"]').eq(3).attr('name');
            	$(conBox).find('#win-'+this_id).find('span[name="'+outer+'"]').html(1);
            }
            else{
            	var outer = $('#win-'+this_id).find('input[type="radio"]').eq(3).attr('name');
            	$(conBox).find('#win-'+this_id).find('span[name="'+outer+'"]').html(0);
            }
        });
    },
    flashMask:function(effect,width,height){
		var w = width || effect.width();
		var h = height || effect.height();
		effect.prepend('<div class="mask"></div>');
		$(".mask").css({width:w,height:h}).fadeIn("fast",function(){
			$(".mask").fadeOut("fast",function(){
				$(".mask").remove();
			});
		});
	},
	renderNum:function(righttype){
		righttype.each(function(index,element){
	    	$(element).text('题型'+(index+1)+ '头部');
			switch($(element).text().substr(2,1)){
				case"1":$(element).text("题型一头部");break;
				case"2":$(element).text("题型二头部");break;
				case"3":$(element).text("题型三头部");break;
				case"4":$(element).text("题型四头部");break;
				case"5":$(element).text("题型五头部");break;
				case"6":$(element).text("题型六头部");break;
				case"7":$(element).text("题型七头部");break;
				case"8":$(element).text("题型八头部");break;
				case"9":$(element).text("题型九头部");break;
				case"10":$(element).text("题型十头部");break;
				case"11":$(element).text("题型十一头部");break;
				case"12":$(element).text("题型十二头部");break;
				case"13":$(element).text("题型十三头部");break;
				case"14":$(element).text("题型十四头部");break;
				case"15":$(element).text("题型十五头部");break;
				case"16":$(element).text("题型十六头部");break;
				case"17":$(element).text("题型十七头部");break;
				case"18":$(element).text("题型十八头部");break;
				case"19":$(element).text("题型十九头部");break;
				case"20":$(element).text("题型二十头部");break;
				default:return
			}
		});	
	},
	//点击或拖拽后页面滚动到对应位置
	goPosition:function(effect){
		this.mainContainer.scrollTop(0);
		this.mainContainer.scrollTop(effect.offset().top-90);
		return effect;
	},
	goPosWin:function(thisId){
		$(thisId).addClass("active");
		$(".paper-set-win").scrollTop(0);
		$(".paper-set-win").scrollTop($(thisId).position().top-60);
	},	
    //初始化试卷模式（标准、测试、作业..）
    initStyle:function(){
        var sty = $('.current-type').attr('sty');
        switch (sty){
            case '1':$('.current-type').find('span').html('默认结构');break;
            case '2':$('.current-type').find('span').html('标准结构');break;
            case '3':$('.current-type').find('span').html('测验结构');break;
            case '4':$('.current-type').find('span').html('作业结构');break;
        }
    },
	//具体题目序号变化
	orderSort:function(ele){
		Teacher.paper.paper_common.orderSort();
	},
    //题型序号排序
    orderType:function(){
        Teacher.paper.paper_common.orderType();
    },
    number_change:function(){
    	Teacher.paper.common.number_change(".type-title-nu");
    	Teacher.paper.common.number_change(".menu-questiontype-nu");
    },
	//拖拽后对应内容移动
	contentSort:function(itemContainer,itemContent,itemOrder){
		var itemvar = $(itemContent).clone();
		$(itemContent).remove();
		if(itemOrder=="append"){
			$(itemContainer).append(itemvar);
		}else{
			$(itemContainer).children().eq(itemOrder).after(itemvar);
		}
	},
	boxInit:function(){
		this.menuContainer.height(this.mainMenu.height()-30);
		this.mainContainer.height(this.mainContent.height()-60);
	},
	//调整题目顺序效果
	sortable_zujuan:function(){
		this.mainMenu.find(".question-box").sortable({
			axis:"y",
			connectWith: ".question-box",
			stop:function(event,ui){
				var this_a = Teacher.paper.paper_preview;
				var itemContent = "#"+ui.item.attr("data-id");
				var itemLen = ui.item.siblings().length;
				var itemParent = "#"+ui.item.parent().siblings(".list-title").attr("data-id");
				var itemIndex = ui.item.index();
				var itemOrder;
				if(itemLen==0){
					itemOrder = "append";
				}else{
					itemOrder = itemIndex;
				}
				this_a.contentSort(itemParent,itemContent,itemOrder);
				this_a.orderSort();
				this_a.flashMask($(itemContent));
				this_a.goPosition($(itemContent));
	            this_a.ajax_move_question(ui.item.attr('data-pqid'),ui.item.parent().attr('id'));//ajax
			}
		});
	}
}
/*试卷预览结束*/