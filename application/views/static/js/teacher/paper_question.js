/*组卷选题知识点选题开始*/
Teacher.paper.paper_question = {
	typeCur : $(".current-type"),
	treeList : $(".tree-list"),
	questionList : $(".question-list"),
	typeOption : $(".type-list ul"),
	typeItem : $(".type-list a"),
	typeList : $(".type-list"),
	mainMenu : $(".child-menu"),
	filterLink : $(".filter-box a"),
	mainContent : $(".child-content .content-wrap"),
	//初始化
	init:function(){
		Teacher.paper.paper_question.page(1);
		Teacher.paper.paper_question.get_category(this.typeCur.data("cselect"));
		Teacher.paper.paper_common.randerQuestion = Teacher.paper.paper_question.randerQuestion;
		this.treeList.on("click",".item",function(){
			var this_a = this;
			Teacher.paper.paper_question.treeItemClick(this_a);
		});
		// this.treeList.on("click",".icon",function(){
		// 	var this_a = this;
		// 	Teacher.paper.paper_question.treeListClick(this_a);
		// });
		this.questionList.on("click",".question-content",function(){
			var this_a = this;
			$(this_a).find(".answer").toggle();
		});
		this.questionList.on("click",".all_in",function(event){
			var this_a = this;
			event.stopPropagation();//阻止事件冒泡
			Teacher.paper.paper_question.addQuestionsClick(this_a);
			return false;
		});
		this.questionList.on("click",".control-btn",function(){
			var this_a = this;
			Teacher.paper.paper_question.addQuestionClick(this_a);
			// $('.mainContainer .content-wrap').css('width',$('.mainContainer .content-wrap').width()+"px");
		});
		this.typeItem.click(function(){
			
			var this_a = this;
			var thisText = $(this_a).text();
			//Teacher.paper.paper_question.typeOption.hide();
			$(this_a).parent().parent().siblings('a').children("span").text(thisText);	
		});	
		this.typeOption.click(function(){
			Teacher.paper.paper_question.typeOption.hide();
		});	
		this.typeList.hover(function(){
			var this_a = this;
			$(this_a).children('.current-type').addClass("hover");
			if($(this_a).children('ul').children().length > 0){
				$(this_a).children('ul').show();
			}else{
				$(this_a).children('ul').hide();
			}
		},function(){
			var this_a = this;
			$(this_a).children('.current-type').removeClass("hover");
			$(this_a).children('ul').hide();
		});
		// this.mainMenu.resizable({
		// 	handles: 'e',
		// 	maxWidth: 390,
		// 	minWidth: 190,
		// 	resize:function(){
		// 		var this_a = Teacher.paper.paper_question;
		// 		var resizeVal = this_a.mainMenu.width()+20
		// 		this_a.mainContent.css("margin-left",resizeVal+"px");
		// 	}
		// });		
		this.filterLink.click(function(){
			var this_a = this;
			Teacher.paper.paper_question.filterQuestionClick(this_a);
		});
	},
	getUrlData:function(ele){
		ele = ele || 1;
		var nselectVal = $(".tree-list .active").data() || {nselect:$(".current-type").data("cselect")}
		var finalData = $.extend({page:ele},nselectVal,$(".current-type").data(),$(".filter-box .active").eq(0).data(),$(".filter-box .active").eq(1).data());
		return finalData;
	},
	randerQuestion:function(){
		this_a = Teacher.paper.paper_question;
		var page_num = $('.page').find('.active').html();
		var urlData = this_a.getUrlData(page_num);
		this_a.get_question(urlData,true);
	},
	treeItemClick:function(this_a){
		//解决多次点击统一连接多次
		if($(this_a).hasClass('active')){
			return false;
		}
		this.treeList.find("a").removeClass("active");
		$(this_a).addClass("active");
		var urlData = this.getUrlData();
		this.get_question(urlData);
	},
	treeListClick:function(this_a){
		var thisId = $(this_a).data("url_id");
		var thisClass = $(this_a).attr("class");
		var thisStatus = thisClass.split(" ")[1];
		var thisChild = $(this_a).parent("div").siblings("ul");
		var statusText = thisStatus.split("-");
		var changeStatus = function(){
			if(statusText[1]=="plus"){
				return statusText[0]+"-subtract";
			}else if(statusText[1]=="subtract"){
				return statusText[0]+"-plus";
			}
		}
		if(statusText[1]=="plus" && thisChild.length==0){
			this.get_category(thisId,$(this_a).parent("div"));
		}
		
		if(statusText[1]!="item"){
			$(this_a).removeClass(thisStatus).addClass(changeStatus());
			thisChild.toggle();
		}
	},
	addQuestionClick:function(this_a){
		var qid = $(this_a).data("question_id");
		var sid = this.typeCur.data("sid");
		var qorigin = $(this_a).data("question_origin");
		if(qorigin == undefined) qorigin = paperQuestionOrigin;

		var category_id = $(this_a).data("category_id");
		if(category_id == undefined) category_id = 0;
		var course_id = $(this_a).data("course_id");
		if(course_id == undefined) course_id = 0;
		var qdata = {'qid':qid,'sid':sid,'qorigin':qorigin,'category_id':category_id,'course_id':course_id};

		var index = $('.control-btn').index(this_a);
		if($(this_a).children("span:visible").length>0){
			$('.control-btn').eq(index).removeClass('control-btn').addClass('control-btn-2');
			this.add_question(qdata,index,function(){
				$('.control-btn-2').removeClass('control-btn-2').addClass('control-btn');
			});
		}else{
			$('.control-btn').eq(index).removeClass('control-btn').addClass('control-btn-2');
			this.remove_question(qid,sid,qorigin,index,function(){
				$('.control-btn-2').removeClass('control-btn-2').addClass('control-btn');
			});
		};

	},
	/* 把所有题目加入备选作业 */
	addQuestionsClick:function(this_a){
		//alert('1')
		var sid = this.typeCur.data("sid");
		var index = $('.control-btn').index(this);

		var qids = new Array();
		$('.question-list .question-box').each(function(index){
		  	var id = $(this).attr('data-question_id');
		  	var category_id = $(this).attr('data-category_id');
			if(category_id == undefined) category_id = 0;
			var course_id = $(this).attr('data-course_id');
			if(course_id == undefined) course_id = 0;
		  	qids[index] = {'id':id,'category_id':category_id,'course_id':course_id};
		});

		var qorigin = $(this_a).data("question_origin");
		if(qorigin == undefined) qorigin = paperQuestionOrigin;
		
		var qdata = {'qids':qids,'sid':sid,'qorigin':qorigin};

		$('.all_in').removeClass('all_in').addClass('all_in_2');
		this.add_questions(qdata,index,function(){
			$('.all_in_2').removeClass('all_in_2').addClass('all_in');
		});
	},
	filterQuestionClick:function(this_a){	
		//解决多次点击统一连接多次
		if($(this_a).attr('class') == 'active'){
			return false;
		}
		$(this_a).parents("ul").find("a").removeClass("active");
		$(this_a).addClass("active");
		var urlData = this.getUrlData();
		this.get_question(urlData);
	},
	get_question:function(getData,noloading){
		if(noloading != true){
			//判断是否有分页的显示loading图片
			$('.all_in').removeClass('all_in').addClass('all_in_2');
			Common.floatLoading.showLoading();
		}
		getData['ver'] = (new Date).valueOf();
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/get_question",
			type:"GET",
			dataType:"json",
			data:getData,
			success:function(data){
				//判断是否有分页的显示loading图片
				$('.all_in_2').removeClass('all_in_2').addClass('all_in');
				Common.floatLoading.hideLoading();
				if(data.errorcode == true){
					$(".question-list").html(data.html);

					if($('.question-box').length < 1){
				        $('.all_in').hide();
				    }else{
				        $('.all_in').show();
				    }
				    //解决ie6下点击中间树栏目的时候右侧五滚动条的问题 vk1
				    $('.mainContainer .content').css('height',$('.mainContainer .content').height()).css('overflow-y','scroll');
				    //知识点选题走模板注入全部加入功能v20140117
				    //$(".question-list .question-box").first().find('a.control-btn').find(".cBtnNormal").addClass("fl");
				    //$(".question-list .question-box").first().find('a.control-btn').find("em").addClass("fl");
				    //$(".question-list .question-box").first().find('a.control-btn').prepend('<div class="all_in cBtnNormal fl" style="margin-right:5px;"><a class="addAllPaper"><i>将本页题目全部加入试卷</i></a></div>');
				}else{
					//$.tiziDialog({content:data.error});
					$('.child-content').html('<div class="no_con"></div>');
				}
			}
		});
		var _screenPaper = $('.screenPaper').outerHeight(true);
		if(_screenPaper!==null){
			// $('.mainContainer').height($(window).height() - 180);
			$(".tree-list").height($(window).height()-72-_screenPaper);
			$(".child-menu").height($(window).height()-72-_screenPaper);
			$(".child-content .child-menu").css({
				"padding-top":"0px"
			});
			$('.child-content .content').height($(window).height()-72-_screenPaper).css('overflow-y','scroll');
		};
	},
	get_category:function(cnselectVal,element){
		return false;
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/get_category",
			type:"GET",
			dataType:"json",
			data:{cnselect:cnselectVal,ver:(new Date).valueOf()},
			success:function(data){
				if(data.errorcode==true){
					var len = data.category.length;
					var category = data.category;
					for(var i=0; i<len; i++){
						if(category[i].is_leaf==0){
							switch(i){
								case len-1:
									category[i].is_leaf = "bottom-plus";
								break;
								default:
									category[i].is_leaf = "top-plus";
								break;
							}
						}else{
							switch(i){
								
								case len-1:
									category[i].is_leaf = "bottom-item";
								break;
								default:
									category[i].is_leaf = "normal-item";
								break;
							}
						}
					}
					var listTemp = $("#tree-list-content").html();
					var treeList = Mustache.to_html(listTemp,data);
					if(element){
						element.after(treeList);
					}else{
						$(".tree-list").html(treeList)
					}
				}else{
					$.tiziDialog({content:data.error});
				}
			}
		});
	},
	add_question:function(qdata,index,unlock){
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/add_question_to_paper",
			type:"POST",
			dataType:"json",
			data:qdata,
			success:function(data){
				unlock();
				if(data.errorcode==true){
					$('.control-btn').eq(index).addClass("add-true");
					$('.control-btn').eq(index).removeClass("add-false");
					Teacher.paper.paper_common.randerCart(data.question_cart);
				}else{
					$.tiziDialog({content:data.error});
				}

			}
		});
	},
	add_questions:function(qdata,index,unlock){
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/add_questions_to_paper",
			type:"POST",
			dataType:"json",
			data:qdata,
			success:function(data){
				unlock();
				if(data.errorcode==true){
					$('.add-false').addClass('add-true').removeClass('add-false');
					Teacher.paper.paper_common.randerCart(data.question_cart);
				}else{
					Teacher.paper.paper_common.randerCart(data.question_cart);
					Teacher.paper.paper_common.randerQuestion();
					$.tiziDialog({content:data.error});
				}
			}
		});
	},
	remove_question:function(qidVal,sidVal,qorigin,index,unlock){
		$.tizi_ajax({
			url:baseUrlName + "paper/paper_question/remove_question_from_paper",
			type:"POST",
			dataType:"json",
			data:{qid:qidVal,sid:sidVal,qorigin:qorigin},
			success:function(data){
				unlock();
				if(data.errorcode==true){
					$('.control-btn').eq(index).addClass("add-false");
					$('.control-btn').eq(index).removeClass("add-true");
					Teacher.paper.paper_common.randerCart(data.question_cart);
				}else{
					$.tiziDialog({content:data.error});
				}
			}
		});
	},
	//翻页
	page:function(page){
		this.get_question(this.getUrlData(page));
	},
	//纠错功能
	// haveError:function(){
	// 	var paperTip = $(".paperTip");
	// 	var question_id = '';
	// 	var category_id = '';
	// 	paperTip.live('click',function(){
	// 		question_id = $(this).attr("data-question_id"); //获取唯一题目id.
	// 		category_id = $(this).attr("data-category_id");
	// 		$(".haveErrorQId").val(question_id);//注入隐藏域id
	// 		$(".haveErrorQIdSpan").html(question_id);//显示id
	// 		$.tiziDialog({
	// 			id:'createHaveError',
	// 			title:'题目纠错',
	// 			content:$('#haveErrorPop').html().replace('haveErrorForm_beta','haveErrorForm'),
	// 			icon:null,
	// 			width:400,
	// 			init:function(){
	// 				//添加上传图片脚本
	// 				Teacher.paper.paper_question.haveErrorUploadImages();
	// 			},
	// 			ok:function(){
	// 				$('.haveErrorForm').submit(); //提交
	// 				return false;
	// 			},
	// 			cancel:true,
	// 			close:function(){
	// 				$(".haveErrorForm").find(".uploadify").each(function(){
	// 					var upload_id = $(this).attr('id');
	// 					$('#'+upload_id).uploadify('destroy');
	// 			    });
	// 				$(".choseFile .error").remove();
	// 			}
	// 		});
	// 		//添加验证
	// 		Common.valid.paperHaveError.addPaperHaveError();
	// 	});
	// },
	//题目收藏
	paperFavorit : function(){
		$('.paperFavorit').live('click',function(){
			$(this).toggleClass('paperFavoritClick');
			if($(this).hasClass('paperFavoritClick')){
				var cont = '<form class="paperFavoritForm" action="#" method="post" onsubmit="return false;"><p>请选择分组：</p><div>初中语文&nbsp;&nbsp;<select><option>所有未分组</option></select></div></form>';
				$.tiziDialog({
					id:'paperFavoritHaveError',
					title:'将题目收藏到我的题库',
					content:cont,
					icon:null,
					width:400,
					ok:function(){
						$('.paperFavoritForm').submit(); //提交
						return false;
					},
					cancel:true,
					close:function(){

					}
				});	
				//添加验证
				//Common.valid.paperHaveError.paperFavoritHaveError();
			}
		});
	},
	//教师端纠错上传图片
    haveErrorUploadImages:function(){
        var that = this;
        var fbupload = $(".haveErrorForm").find(".fbupload"); //替换防止反复提取
        fbupload.each(function(){
            var node = $(this);
            var loading = staticUrlName+'image/answerquestion/loading.gif';
            var upload_id = node.attr('id');
            $('#'+upload_id).uploadify({
                'formData' : $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post'),
                'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                'uploader' : baseUrlName+'upload/feedback?id='+upload_id, //需要上传的url地址
                'multi'    : false,
                'buttonClass': 'choseFileBtn',
                'buttonText' :"上传图片",
                'fileTypeExts' : '*.jpg; *.png;*.gif',
                'fileSizeLimit' : '2048KB',
                'fileObjName' : upload_id,
                'button_image_url':baseUrlName+'quit',
                'width'           :102,
                'height' :28,
                'overrideEvents': ['onSelectError','onDialogClose'],
                onUploadStart:function(file){
                    $('.haveErrorForm .'+upload_id).html('<img src="'+loading+'" class="imgloading"/>');
                },
                onSWFReady:function(){
                },
                onFallback : function() {
                    $('.imgTips').html(noflash);
                },
                onSelectError : function(file, errorCode, errorMsg) {
                    switch (errorCode) {
                        case -110:
                            $.tiziDialog({content:"文件 [" + file.name + "] 过大！每张图片不能超过2M"});                        
                            break;  
                        case -120:
                            $.tiziDialog({content:"文件 [" + file.name + "] 大小异常！不可以上传大小为0的文件"});
                            break;  
                        case -130:
                            $.tiziDialog({content:"文件 [" + file.name + "] 类型不正确！不可以上传错误的文件格式"});
                            break;  
                    }  
                    return false;
                },
                onUploadSuccess: function(file, data, response) {
                    var json = JSON.parse(data);
                    var upload_id_img = $('.haveErrorForm .'+upload_id);
                    if(json.code == 1){
                        var img_path = json.img_path;
                        that.drawImage(img_path,92,71,upload_id);
                        upload_id_img.removeClass('red');
                        upload_id_img.addClass('upladpicSpan').removeClass("upladpic");
                        // //往页面上的#picture_urls写
                        // var ps = $('#picture_urls').val();
                        // ps+=(img_path)+',';
                        // $('#picture_urls').val(ps);
                        // console.log($('#picture_urls').val());
                    }else{
                        upload_id_img.html('<b>'+json.msg+'</b>');
                        upload_id_img.addClass('red');
                    };
                    upload_id_img.siblings('.clearpic').show();//显示删除图标
                    upload_id_img.siblings('.clearpic').on('click',function(){
                        upload_id_img.find("img").remove();
                        upload_id_img.siblings('.clearpic').hide();//隐藏删除图标
                        upload_id_img.removeClass('red');
                        upload_id_img.removeClass('upladpicSpan').addClass("upladpic");;//移除背景图片
                    });
                },
                onUploadComplete: function(file) {
                }
            });
        });
    },
    drawImage:function(src,width,height,upload_id){
        var image=new Image();
        var Img = new Image();//返回值
        image.src=src;
        image.onload = function(){
           if(image.width>width||image.height>height){
                //现有图片只有宽或高超了预设值就进行js控制 
                w=image.width/width;
                h=image.height/height;
                if(w>h){
                    //宽比高大 
                    //定下宽度为width的宽度 
                    Img.width=width; 
                    //以下为计算高度 
                    Img.height=image.height/w;
                }else{
                    //高比宽大 
                    //定下宽度为height高度 
                    Img.height=height; 
                    //以下为计算高度 
                    Img.width=image.width/h; 
                }
            }else{
                h=image.height/height;
                Img.width=image.width/h;
                Img.height=height;
            }
            $('.haveErrorForm .'+upload_id).html('<img src="'+src+'" class="picture_urls" width="'+Img.width+'" height="'+Img.height+'"/>');
         }
   }
}
/*组卷选题结束*/