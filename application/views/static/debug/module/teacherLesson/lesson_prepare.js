// 这是备课资源的脚本入口文件
define(function(require, exports){
	var lessonValid  = require("module/common/basics/teacherLesson/lessonValid");
	require('tiziDialog');
	//备课首页初始化
	var lessonPrepare = {
		typeCur : $(".current-type"),
		treeList : $(".tree-list"),
		docList : $(".doc-list"),
		treeItem : $(".tree-list a"),
		filterLink : $(".head-type a"),
		filterOrderDate : $("#odate"),
		filterOrderPage : $("#opage"),
		filterOrderSize : $("#osize"),
		downDoc : $(".doc-down"),
		subjectLink : $(".subject-chose"),
		mainContent : $(".child-content .content-wrap"),
		submitBtn : $('#search_submit'),
		//初始化
		init:function(){
			lessonPrepare.page(1);
			this.treeItem.on("click",function(){
				var this_a = this;
				lessonPrepare.treeItemClick(this_a);
			});
			this.filterLink.click(function(){
				var this_a = this;
				lessonPrepare.filterQuestionClick(this_a);
			});
			this.filterOrderDate.live("click",function(){
				var this_a = this;
				lessonPrepare.filterQuestionClick(this_a);
			});
			this.filterOrderPage.live("click",function(){
				var this_a = this;
				lessonPrepare.filterQuestionPage(this_a);
			});
			this.filterOrderSize.live("click",function(){
				var this_a = this;
				lessonPrepare.filterQuestionSize(this_a);
			});
			/*this.submitBtn.live("click",function(){
				$(".head-type").find("a").removeClass("current");
				$(".all-type").addClass("current");
				lessonPrepare.submitSearch(1);
			});
			
			$('.searchText').live("keypress",function(event){
				if(event.keyCode==13){	 
					$('.searchText').blur();
					$(".head-type").find("a").removeClass("current");
					$(".all-type").addClass("current");	
					lessonPrepare.submitSearch(1);
				}
			});*/
		},
		fileDown:function(){
			// 显示备课顶部下载按钮
			//$('.doc-down').removeClass('undisIm');
			// 显示备课顶部下载按钮
			// console.log(this)

			this.downDoc.live('click',function(){
				var this_a = this;
				setTimeout(function(){
					lessonPrepare.doc_down_auth(this_a,null,null);
				},0)
			});
		},
		getBaseUrl:function()
		{		
			return "lesson/lesson_prepare/";
		},
		getUrlData:function(ele){
			ele = ele || 1;
			var nselectVal = $(".tree-list .active").data() || {nselect:$(".current-type").data("cselect")};
			var selectOrderType=1;/*默认降序*/
			var selectOrder =$(".head-order .active").eq(0).data("order")||0; //$(".head-order .active").eq(0).data()||{order:0};
			if(selectOrder=='1'){
				if($("#opage").hasClass("up")){
					selectOrderType=2;
				}
			}
			if(selectOrder=='2'){
				if($("#osize").hasClass("up")){
					selectOrderType=2;
				}
			}
			var finalData = $.extend({page:ele},nselectVal,$(".current-type").data(),$(".head-type  .active").eq(0).data(),{order:selectOrder},{otype:selectOrderType});
			return finalData;
		},
		treeItemClick:function(this_a){
			//解决多次点击统一连接多次
			if($(this_a).hasClass('active')){
				return false;
			}
			this.treeList.find("a").removeClass("active");
			$(this_a).addClass("active");
			var urlData = this.getUrlData();
			this.get_document(urlData);
		},
		filterQuestionClick:function(this_a){	
			//解决多次点击统一连接多次
			if($(this_a).attr('class') == 'active'){
				return false;
			}
			$(this_a).parent().find("a").removeClass("active");
			$(this_a).addClass("active");
			var ckeyword = $('.seachResult').find('em').text();
			if(ckeyword){
				this.submitSearch(1);
			}else{
				
				var urlData = this.getUrlData();
				this.get_document(urlData);
			}
		},
		filterQuestionPage:function(this_a){	
			//解决多次点击统一连接多次
			/*if($(this_a).attr('class') == 'active'){
				return false;
			}*/
			$(this_a).parent().find("a").removeClass("active");
			$(this_a).addClass("active");
			if($(this_a).hasClass("up")){
				$(this_a).removeClass("up");
				$(this_a).addClass("orther");
			}else if($(this_a).hasClass("orther")){
				$(this_a).removeClass("orther");
				$(this_a).addClass("up");
			}else{
				$(this_a).addClass("orther");
			}
			var ckeyword = $('.seachResult').find('em').text();
			if(ckeyword){
				this.submitSearch(1);
			}else{
				
				var urlData = this.getUrlData();
				this.get_document(urlData);
			}
		},
		filterQuestionSize:function(this_a){	
			//解决多次点击统一连接多次
			/*if($(this_a).attr('class') == 'active'){
				return false;
			}*/
			$(this_a).parent().find("a").removeClass("active");
			$(this_a).addClass("active");
			if($(this_a).hasClass("up")){
				$(this_a).removeClass("up");
				$(this_a).addClass("orther");
			}else if($(this_a).hasClass("orther")){
				$(this_a).removeClass("orther");
				$(this_a).addClass("up");
			}else{
				$(this_a).addClass("orther");
			}
			var ckeyword = $('.seachResult').find('em').text();
			if(ckeyword){
				this.submitSearch(1);
			}else{
				
				var urlData = this.getUrlData();
				this.get_document(urlData);
			}
		},
		get_document:function(getData){
			var base_url = this.getBaseUrl();
			getData['ver'] = (new Date).valueOf();
			var focusID = $(".tab_name").attr("data-focus");
			if(focusID.length>0){
				getData['focus_id'] = focusID;
				$(".tab_name").attr("data-focus","");
			}
			seajs.use('tizi_ajax',function(){
				$.tizi_ajax({url: baseUrlName + base_url + "get_document", 
						type: 'GET',
						data: getData,
						dataType: 'json',
						success: function(data){
							if(data.errorcode == true)
							{
								if(data.tab_url){
									$("#navMy").attr("href",baseUrlName+"teacher/lesson/prepare/mine/"+data.tab_url);
									$("#navFav").attr("href",baseUrlName+"teacher/lesson/prepare/fav/"+data.tab_url);
								}
								$(".all-type").html("全部（"+data.total_num+"份）");
								$(".doc-list").html(data.html);
                                $("html,body").animate({scrollTop:0},200);
								// 加载老师端左侧背景高度判断
								require('module/common/method/common/height').leftMenuBg();
								// 备课左侧再次点击时重新设置高度
								var _height=$('#wrapContent .lessonTabBox').height()+$('#wrapContent .lessonTxtTab').outerHeight(true);
								$('#wrapContent').height(_height);
								//Teacher.tableStyleFn();
							}else{
								$.tiziDialog({content:data.error});
							}
						}
					});
				});

		},
        initScrollBar:function(){
            // 添加备课树形结构滚动条
            var scrollPanel = $(".scrollPanel");
            if(scrollPanel.length>0){
                var scrollTreePanel1 = $("#scrollTreePanel1");
                $("#scrollTreeContent1").height(600);
                $("#scrollTreeContent1 .Scroller-Container").css({position:"absolute",left: "0",top:"0",width:"220px"});
                scrollTreePanel1.removeClass("undis");
                seajs.use('module/teacherLesson/scrollPanel',function(exports){
                    var scroller  = new exports.jsScroller(document.getElementById("scrollTreeContent1"), 210, 600);
                    var scrollbar = new exports.jsScrollbar(document.getElementById("scrollTreePanel1"), scroller, false);
                })
            }
        },
		//翻页
		page:function(page){
			var docCheck = $('#docnum').html();
			if(docCheck!=''&&docCheck!==undefined )return false;
			this.get_document(this.getUrlData(page));
		},
		doc_down_auth:function(this_a, file_id, unlock){
			var this_a1 = this_a;
			var file_id1 = file_id;
			var unlock1 = unlock;
			seajs.use('tizi_login_form',function(ex){
				ex.loginCheck('function',function(){
					if(unlock ==null && file_id==null){
						//调用下载提示弹出框效果
							lessonPrepare.docDownloadBox(this_a1,function(){
							lessonPrepare.doc_down_status(this_a1);
						});
					}else{
							lessonPrepare.docDownloadBox(this_a1,function(){					
							lessonPrepare.document_download(file_id1,unlock1);
						});
					}
					
				});
			});
		},
		doc_down_status:function(this_a){
			if($(this_a).attr('disabled') == 'disabled'){
				return false;
			}
			//$(this_a).html('下载中...');
			$(this_a).attr('disabled','disabled');
			var doc_num = $(this_a).data('num');
			lessonPrepare.document_download(doc_num,function(){
				//$(this_a).html('下载');
				$(this_a).removeAttr('disabled');
			});
		},
		/*v20140125下载提示弹出框*/
		docDownloadBox:function(obj,fn){
			var downdoc_count = Number($('.docCaptchaWord').attr('download-doc-count'));
			var downdoc_limit = Number($('.docCaptchaWord').attr('download-doc-limit'));
			
			$.tiziDialog({
				title:"下载文档",
				content:$("#confirmDownLoad").html().replace('DocDownBox_tpl','DocDownBox').replace('DocDownBoxWord_tpl','DocDownBoxWord'),
				icon:null,
				width:500,
				init:function(){
					require('tizi_validform').changeCaptcha('DocDownBox');
					require('tizi_validform').bindChangeVerify('DocDownBox');
					
				},
				ok:function(){
						var checkcode = require('tizi_validform').checkCaptcha('DocDownBox',1,1);
						if(!checkcode) return false;
					fn();
				},
				cancel:true
			});
		},
		document_download:function(file_id,unlock){
			var baseuri = baseUrlName + 'lesson/lesson_document/download_verify';
			var subject_id = $('.subject-chose').data('subject');
			var is_mine = $('.is_mine').val();
			var post_data = {
					'subject_id':subject_id,
					'file_id':file_id,
					'is_mine':is_mine,
					'captcha_word':$('.DocDownBoxWord').val(),
					'captcha_name':'DocDownBox'
					};
			seajs.use('tizi_ajax',function(){		
				$.tizi_ajax({
					url: baseuri, 
					type: 'POST',
					data: post_data,
					dataType: 'json',
					success: function(data){
						if(data.errorcode == false) {                    	
							$.tiziDialog({content:data.error});
						}else{
							// console.log(data);
							//return;
							ga('send', 'event', 'Download-Lesson-doc', 'Download', data.fname);
							if(data.source=="oss"){
								/*var down_url=baseUrlName + 'download/doc?url='+data.file_path
								+'&file_name='+data.file_name+'&type='+data.type;
								require('tizi_download').force_download(down_url,data.fname);*/
								require('tizi_download').force_download(data.file_path,data.fname,false,true);
							}else{
								require('tizi_download').force_download(data.file_path,data.fname,false,true);
							}
							
						}
						if(unlock!=null)unlock();
					}
				});
			});
		},
		getSearchData:function(ele){
			ele = ele || 1;
			var ckeyword = $('.seachResult').find('em').text().replace(/(^\s*)|(\s*$)/g,'');
			var keywordInput = $('.searchText').val().replace(/(^\s*)|(\s*$)/g,'');
			if(ckeyword && ckeyword == keywordInput ){
				var keyword = ckeyword;
			}else{
				var keyword = keywordInput;
				if(keyword=="请输入关键字"){keyword="";}
				if(keyword==""){
					return false;
				}
				ga('send', 'event', 'Search-Lesson', 'Search', keyword);
			}
			var subjectId = $('.subject-chose').data('subject');
			var nselectVal = $(".tree-list .active").data() || {nselect:$(".current-type").data("cselect")};
			var finalData = $.extend({page:ele},{skeyword:keyword},{sid:subjectId},nselectVal,$(".current-type").data(),$(".head-type  .active").eq(0).data(),{order:0});
			return finalData;
		},
		submitSearch:function(page){
			var getData = this.getSearchData(page);
			if(getData === false){
				$.tiziDialog({content:"请输入关键字"});
				return false;
			}
			getData['ver'] = (new Date).valueOf();
			$(this.submitBtn).addClass('disabled');
			seajs.use('tizi_ajax',function(){
				$.tizi_ajax({url: baseUrlName+"lesson/lesson_prepare/lesson_search", 
					type: 'GET',
					data: getData,
					dataType: 'json',
					success: function(data){
						$(this.submitBtn).removeClass('disabled');
						if(data.errorcode == true) {
							$(".doc-list").html(data.html);
							// 加载老师端左侧背景高度判断
							require('module/common/method/common/height').leftMenuBg();
						}else{
							$.tiziDialog({content:data.error});
						}
					}
				});
			});
		},
		sPage:function(page){
			this.submitSearch(page);
		}
	};
	exports.lessonInit = function(){
		lessonPrepare.init();//备课首页初始化外露
	};
	exports.lessonPage = function(page){
		lessonPrepare.page(page);//备课分页外露
	};
	exports.searchPage = function(page){
		lessonPrepare.sPage(page);//备课分页外露
	};
	exports.lessonDown = function(this_a, file_id, unlock){
		lessonPrepare.doc_down_auth(this_a, file_id, unlock);//备课下载外露
	};
	exports.docDown = function(){
		lessonPrepare.fileDown();//备课下载外露
	}
	//备课预览flash初始化
	/*exports.flash_init = function(flash_height){
        var docnum = document.getElementById("docnum").innerHTML;
        var arrayInit = new Array();
        arrayInit[0] = baseUrlName + 'lesson/lesson_document/flash_get_json';
        arrayInit[1] = staticPath+libPath+'lessonFlash/0.0.1/';
        arrayInit[2] = docnum;
        arrayInit[3] = basePageToken;
        arrayInit[4] = basePageName;
        arrayInit[5] = flash_height;//-134
        arrayInit[6] = 650;
        return arrayInit;
	};*/
    exports.swfObjectInit = function(swfversion,jsversion){
        seajs.use(staticBaseUrlName + "flash/lessonFlash/swfobject",function(){
            //加载内容
            var lessonUrl = staticBaseUrlName+'flash/lessonFlash/';
            // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection.
            var swfVersionStr = baseFlashVersion;
            // To use express install, set to playerProductInstall.swf, otherwise the empty string.
            var xiSwfUrlStr = lessonUrl+"playerProductInstall.swf"+jsversion;
            //alert(contentBoxH);
            var flashvars = {};
            var params = {};
            var flash_height = exports.swfObjectHeight();
			var docExt = $("#docnum").data("ext");
			switch(docExt){
				case 'doc':
					flash_height = 693;
					break;
				case 'docx':
					flash_height = 693;
					break;
				case 'ppt':
					flash_height = 576;
					break;
				case 'pptx':
					flash_height = 576;
					break;
				default :
					flash_height = 693;
					break;				
			}
            Teacher.lesson.prepare.swfObjectHeight = flash_height;
            params.quality = "high";
            params.bgcolor = "#ffffff";
            params.wmode = "transparent";
            params.wmode = "Opaque";
            params.allowscriptaccess = "sameDomain";
            params.allowfullscreen = "true";
            var attributes = {};
            attributes.id = "TiZiPaper";
            attributes.name = "TiZiPaper";
            attributes.align = "middle";
            var swfUrl = lessonUrl+"TiZiPaper.swf"+swfversion;
            swfobject.embedSWF(
                swfUrl, "flashContent",
                "750", flash_height+5,
                swfVersionStr, xiSwfUrlStr,
                flashvars, params, attributes);
            swfobject.createCSS("#flashContent", "display:block;text-align:left;");
        });
    }
    exports.swfObjectHeight = function(){
        var contentBoxH = $(".contentBox").position().top;
        var flash_height = $(document).height()-contentBoxH;
        $("#flashContent").height(flash_height);
        return flash_height;
    }
	//动态增加高亮同步章节
	exports.activeHightLight = function(){
        var forceNode = $(".subject-chose").attr("data-active_node");
		var nodeList = $(".slideContent li");
		if(!forceNode)return;
		$("#node_list a[data-nselect="+forceNode+"]").addClass("active");
    }
    
    // 收藏/取消收藏
    exports.store = function(){
        $('.storeUp').live('click',function(){
        	var _this = $(this);
        	if($('.storeUpOver').length>0){
        		return false;
        	}
        	setTimeout(function(){
				seajs.use('tizi_login_form',function(ex){
					ex.loginCheck('function',function(){
						$.tiziDialog({
							id:'',
							title:'收藏文件',
							content:'将此文件收藏到我的文件相应的同步单元目录下？',
							icon:'question',
							ok:function(){
								seajs.use('tizi_ajax',function(){
								$.tizi_ajax({url: baseUrlName+"lesson/lesson_document/favorite", 
										type: 'GET',
										data: {'id':_this.data("num"),'category':_this.data("category"),'type':_this.data("type")},
										dataType: 'json',
										success: function(data){
											if(data.errorcode == true) {
												_this.addClass('storeUpOver');
												_this.html('已收藏');
											}else{
												$.tiziDialog({content:data.error});
											}
										}
									});
								});
							},
							cancel:true
						});
					});
				});
			},0)
            
            
        });
    };
});