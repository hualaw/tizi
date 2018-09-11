// 班级空间脚本入口文件
/*
 *	1、用var声明的方法只在本页面调用，如var sharefieldFn = function(ele,alType){};
 *  2、用exports声明的方法在init.js调用，如exports.classManageDialog = function(){};
 */
define(function(require, exports){
	require('tiziDialog');
	require('validForm');
	require('tizi_ajax');
	require('json2');//for IE7
	require('mustache');
    require('tizi_select');// 公共select模拟
	var classValid = require('module/common/basics/teacherClass/classValid');
	var classAjax  = require('module/common/ajax/teacherClass/classAjax');
	var tabContral = require('module/common/method/common/tab');
	var Teadownload = require("tizi_download");
    //点击题目收缩与展开
	exports.click_q = function(){
		$('.click_q').live('click',function(){
			//显示隐藏试卷答案
			$(this).find('.answer').toggle();
		})
	};
	
//	//班级跳转
//	exports.goclass = function(){
//		$('#goclass').live('change', function(){
//			var class_id = $(this).val();
//			var base_url = baseUrlName + 'teacher/class/';
//			window.location.href = base_url + class_id;
//		});
//	}
    //模拟下拉框
    exports.simulatDrop = function(){
        // 美化select
        $('#goclass').jqTransSelect();
    }
    //班级跳转
    exports.goclass = function(val){
        var class_id = val;
        var base_url = baseUrlName + 'teacher/class/';
        window.location.href = base_url + class_id;
    }

    //添加方法 收缩与展开
    exports.addWaySlide = function (){    
    	//我已了解操作 slideup	
		$(".slideUpWay").click(function (){
			var _this = $(this);
			if(_this.parent().parent().hasClass("slideDownClass")){
				_this.parent().parent().find('.slideBox').slideUp(function (){
        			_this.parent().parent().removeClass("slideDownClass");
        			_this.parent().parent().find(".wayP .slideUndis").removeClass("undis");
        		});
			}
        	_this.parent().parent().find('.slideBox').slideUp().find(".addText").removeClass("slideDown").addClass("slideUp");
    		$(".addWay .wayP .slideUndis").removeClass("undis");
        });
		//slide 
        $(".addWay .addText").click(function (){
        	var _this = $(this);
        	//slideDown title change
        	if($(".addWay .wayP .slideUndis").hasClass("undis")){
        		$(".addWay .wayP .slideUndis").removeClass("undis");
        	}else{
        		$(".addWay .wayP .slideUndis").addClass("undis");
        	}
        	//默认展开的时候 需要清除 展开的calss
        // 	if($(this).parent().hasClass("slideDownClass")){
        // 		$(this).parent().find('.slideBox').slideUp(function (){
        // 			_this.parent().removeClass("slideDownClass");
        // 			$(this).parent().find(".wayP .slideUndis").removeClass("undis");
    				// _this.removeClass("slideUp").addClass("slideDown"); 
    				// if($('.class_manage_btn li').eq(1).hasClass("active")){
	       //  			exports.ZeroClipboard();
	       //  			exports.ZeroClipboardSlide();
    				// }
        // 		});
        // 	}else{
        // 		_this.parent().find('.slideBox').slideToggle(function (){
    				// if($('.class_manage_btn li').eq(1).hasClass("active")){
	       //  			exports.ZeroClipboard();
	       //  			exports.ZeroClipboardSlide();
    				// }
        // 		});
        // 	}
        	//清除 了解此操作之后 再次点击slide的时候 首次slidebug
    		// if(_this.hasClass("slideUp")){
    		// 	_this.removeClass("slideUp").addClass("slideDown"); 
    		// }else{
    		// 	_this.removeClass("slideDown").addClass("slideUp");
    		// };
    		//copy flash
    		// if($('.class_manage_btn li').eq(1).hasClass("active")){
    		// 	require.async('tizi_validform',function (ex){
		    //         ex.detectFlashSupport(
		    //             function(){
		    //                 $('#inviteId').html('');
		    //             },function(){
		    //                 exports.ZeroClipboardSlide();
		    //             }
		    //         );
		    //     });
		    // }
        });
    }
    exports.classManageDialog = function(){
		$('.subject_me').live('change', function(){
			var subject_id = $(this).val();
			var class_id = $(this).attr('class_id');
			$.tizi_ajax({
				'url' : baseUrlName + 'class/update_info/class_subject',
				'type' : 'POST',
				'dataType' : 'json',
				'data' : {
					'subject_id' : subject_id,
					'class_id' : class_id
				},
				success : function(json, status){
					if (json.code == 1){
						$.tiziDialog({
							content:json.msg,
							icon:'succeed'
						});
					} else if (json.code == -999){
						showLogin.show_login();
					} else {
						$.tiziDialog({content:json.msg});
					}
				},
				error : function(){
					$.tiziDialog({content:'系统忙，请稍后再试'});
				}
			});
		});
		//退出班级
		$('.quit_btn').live('click',function(){
			var node = $(this);
			var classname = $(this).attr('name');
			var class_id = $(this).attr('data-id');
			$.tiziDialog({
				title:'退出班级',
				content:$('#quitClassPop').html().replace('%classname%', classname),
				icon:null,
				width:400,
				ok:function(){
					$.tizi_ajax({
						'url' : baseUrlName + 'class/leave/teacher',
						'type' : 'POST',
						'dataType' : 'json',
						'data' : {
							'class_id' : class_id
						},
						success : function(json){
							if (json.code == 1){
								$.tiziDialog({
									content : json.msg,
									icon : 'succeed',
									ok : function(){
										window.location.reload();
									}
								});
							} else if (json.code == -999){
								showLogin.show_login();
							} else {
								$.tiziDialog({content:json.msg});
							}
						},
						error : function(){
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
					});
				},
				cancel:true
			})
		});
		//解散班级
		$('.disband_btn').live('click',function(){
			var node = $(this);
			var class_id = $('#class_id').val();
			var classname = $(this).attr('name');
			$.tiziDialog({
				id:'addClassGradeId',
				title:'解散班级',
				content:$('#deleteClassPop').html().replace('%classname%', classname).replace('deleteClassPopForm_beta','deleteClassPopForm'),
				icon:null,
				width:400,
				ok:function(){
					$('.deleteClassPopForm').submit();
					return false;
				},
				cancel:true
			});
			classValid.deleteClass(class_id);
		});
		// 申请加入班级
		$('#applyIn').live('click',function(){
			var teacher_subject = $('#teacher_subject').val();
			var realname = $('#realname').val();
			var class_id = $('#class_id').val();
			if(teacher_subject == 0){
				$.tiziDialog({content:"请选择学科."});
			}
			if($.trim(realname) == ''){
				$.tiziDialog({content:"请输入您的真实姓名."});
			}
			if(teacher_subject > 0 && $.trim(realname) !== ''){
				$.tizi_ajax({
					'url' : baseUrlName + 'class/apply/dot',
					'type' : 'POST',
					'dataType' : 'json',
					'data' : {
						'subject_id' : teacher_subject,
						'realname' : realname,
						'class_id' : class_id
					},
					success : function(json){
						if (json.code == 1){
							$.tiziDialog({
								content : json.msg,
								icon : 'succeed',
								ok : function(){
									window.location.href = json.redirect;
								}
							});
						} else if (json.code == -999){
							showLogin.show_login();
						} else {
							$.tiziDialog({
								content : json.msg
							});
						}
					},
					error : function(){
						$.tiziDialog({content:'系统忙，请稍后再试'});
					}
				});			
			}
		});
		//申请加入班级，老师批准
		$('.iapp .ratify').live('click', function(){
			var node = $(this);
			var apply_id = $(this).parent().parent().attr('data-id');
			$.tizi_ajax({
				'url' : baseUrlName + 'class/apply/accept',
				'type' : 'POST',
				'dataType' : 'json',
				'data' : {
					'apply_id' : apply_id
				},
				success : function(json){
					if (json.code == 1){
						$.tiziDialog({
							content : json.msg,
							icon : 'succeed',
							ok : function(){
								node.parent().parent().remove();
							}
						});
					} else if (json.code == -999){
						showLogin.show_login();
					} else {
						$.tiziDialog({
							content : json.msg
						});
					}
				},
				error : function(){
					$.tiziDialog({content:'系统忙，请稍后再试'});
				}
			});
		});
		//申请加入班级，老师拒绝
		$('.iapp .refuse').live('click', function(){
			var node = $(this);
			var apply_id = node.parent().parent().attr('data-id');
			$.tizi_ajax({
				'url' : baseUrlName + 'class/apply/refuse',
				'type' : 'POST',
				'dataType' : 'json',
				'data' : {
					'apply_id' : apply_id
				},
				success : function(json){
					if (json.code == 1){
						$.tiziDialog({
							content : json.msg,
							icon : 'succeed',
							ok : function(){
								node.parent().parent().remove();
							}
						});
					} else if (json.code == -999){
						showLogin.show_login();
					} else {
						$.tiziDialog({
							content : json.msg
						});
					}
				},
				error : function(){
					$.tiziDialog({content:'系统忙，请稍后再试'});
				}
			});
		});
    	//修改班级名称
		$('#alterClassGrade').live('click',function(){
			var c = $('#ClassGrade').text();
			$.tiziDialog({
				id:'alterClassGradeId',
				title:'修改班级名称',
				content:$('#alterClassGradePop').html().replace('alterClassGradePopForm_beta','alterClassGradePopForm'),
				icon:null,
				width:300,
				ok:function(){
					$('.alterClassGradePopForm').submit();
					return false;
				},
				cancel:true
			});
			if (c.slice(-1) == '班'){
				c = c.substring(0, c.length - 1);
			}
			$('.newClassGrade').val(c);
			$('select.class_grade').val($('#cur_class_grade').val())
			classValid.alterClassGrade();
		});
		// 请离学生
		$('.rms').live('click', function(){
			var node = $(this);
			var name = $(this).attr("name");
			var csid = $(this).attr("csid");
			$.tiziDialog({
				title:'请离本班',
				content:$('#kickonePop').html().replace('%role%', '学生').replace('%name%', name),
				icon:null,
				width:400,
				ok:function(){
					$.tizi_ajax({
						'url' : baseUrlName + 'class/remove/student',
						'type' : 'POST',
						'dataType' : 'json',
						'data' : {
							'csid' : csid
						},
						success : function(json, status){
							if (json.code == 1){
								$.tiziDialog({
									content : json.msg,
									icon : 'succeed',
									ok : function(){
										node.parent().parent().css('display', 'none');
										var student_total = $('#student_total').html();
										$('#student_total').html(student_total-1);
									}
								});
							} else if (json.code == -999){
								showLogin.show_login();
							} else {
								$.tiziDialog({content:json.msg});
							}
						},
						error : function(){
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
					});
				},
				cancel : true
			});
		});
		// 请离老师
		$('.kickout').live('click',function(){
			var node = $(this);
			var name = $(this).attr("name");
			var ctid = $(this).attr("ctid");
			$.tiziDialog({
				title:'请离本班',
				content:$('#kickonePop').html().replace('%role%', '老师').replace('%name%', name),
				icon:null,
				width:400,
				ok:function(){
					$.tizi_ajax({
						'url' : baseUrlName + 'class/remove/teacher',
						'type' : 'POST',
						'dataType' : 'json',
						'data' : {
							'ctid' : ctid
						},
						success : function(json, status){
							if (json.code == 1){
								$.tiziDialog({
									content : json.msg,
									icon : 'succeed',
									ok : function(){
										node.parent().parent().css('display', 'none');
										var teacher_total = $('#teacher_total').html();
										$('#teacher_total').html(teacher_total-1);
									}
								});
							} else if (json.code == -999){
								showLogin.show_login();
							} else {
								$.tiziDialog({content:json.msg});
							}
						},
						error : function(){
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
					});
				},
				cancel:true
			});
		});
		// 重置密码
		$('.reset_password').live('click',function(){
			var student_name = $(this).attr('name');
			var uesr_id = $(this).attr('uid');
			var class_id = $("#class_id").val();
			var node = $(this);
			$.tiziDialog({
				title:'重置密码',
				content:$('#resetPasswordPop').html().replace('%name%', student_name),
				icon:null,
				width:400,
				ok:function(){
					$.tizi_ajax({
						'url' : baseUrlName + 'class/update_info/reset_password',
						'type' : 'POST',
						'dataType' : 'json',
						'data' : {
							'user_id' : uesr_id,
							'class_id' : class_id
						},
						success : function(json, status){
							if (json.code == 1){
								$.tiziDialog({
									content : json.msg,
									icon : 'succeed',
									ok : function(){
										$("#pwd_"+uesr_id).html(json.password);
									}
								});
							} else if (json.code == -999){
								showLogin.show_login();
							} else {
								$.tiziDialog({content:json.msg});
							}
						},
						error : function(){
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
					});
				},
				cancel:true
			});
		});
		//删除未登录的帐号
		$('.rmcreate').live('click', function(){
			var sid = $(this).attr('sid');
			var node = $(this);
			$.tiziDialog({
				title:'删除帐号',
				content:'是否删除该帐号?',
				icon:'question',
				width:400,
				ok:function(){
					$.tizi_ajax({
						'url' : baseUrlName + 'class/create_students/remove',
						'type' : 'POST',
						'dataType' : 'json',
						'data' : {
							'sid' : sid
						},
						success : function(json, status){
							if (json.code == 1){
								$.tiziDialog({
									content : json.msg,
									icon : 'succeed',
									ok : function(){
										node.parent().parent().remove();
										var student_total = $('#student_total').html();
										$('#student_total').html(student_total-1);
									}
								});
							} else if (json.code == -999){
								showLogin.show_login();
							} else {
								$.tiziDialog({content:json.msg});
							}
						},
						error : function(){
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
					});
				},
				cancel:true
			});
		});
    };
    // 学生管理和班级老师tab切换
    exports.classManageTab = function(){
		$('.class_manage_btn li').click(function(){
			$(this).addClass('active').siblings().removeClass('active');
			// 计算下班级详情的学生管理和班级老师的高度
			//$('.classTableHeight').height($(window).height()-80).css('overflow-y','scroll');
			var _index = $('.class_manage_btn li').index(this);
			$('.class_manage_con .cont').eq(_index).show().siblings().hide();
		});
	};
	//分享弹出框 , 点击确定发送分享请求
	exports.claSharePop = function(){
		$('.shareFile').live('click',function(){
			var tit = $(this).text();
			var _this = $(this);
			var filetype = $(this).attr('filetype');
			$('#fromInterPop').attr('chosen-type',filetype);
			$('.upBtn').show();
			$('.upList').hide();
			$.tiziDialog({
				title:tit,
				content:$('#sharePop').html().replace("share_field_beta","sharefield"),
				icon:null,
				width:600,
				id:'from_net_or_local',
				init:function(){
					/*初始化本地上传逻辑*/
					var allowType = '';
					switch (filetype) {
						case '1': 
							allowType = '*.doc;*.docx;*.ppt;*.pptx;*.xls;*.xlsx;*.wps;*.et;*.dps;*.pdf;*.txt;';
							break;  
						case '2':
							allowType = '*.jpg;*.png;*.gif;*.bmp;';
							break;  
						case '3':
							allowType = '*.rmvb;*.rm;*.flv;*.f4v;*.mp4;*.wmv;*.avi;*.mpeg;';
							break;  
						case '4':
							allowType = '*.mp3;*.wav;*.ape;';
							break; 
						case '5':
							allowType = '*.*';
							break;
					}
					sharefieldFn(this,allowType);
				},
				ok:function(){
					var class_id = $('.del_share_class_id').val();
					var t = $(".upList");
					var file_id = '';
					t.each(function(){
						file_id += $(this).attr('file_id')+',';
					});
					var desc = $('textarea').val();
					if($('.upList').length>1){ //有文件了,点确定做分享的请求
						$.tizi_ajax({
							'url' : baseUrlName + 'teacher/cloud/share',
							'type' : 'POST',
							'dataType' : 'json',
							'data' : {'class' :class_id,'file_id':file_id,'desc':desc},
							success : function(json){
								if(json.errorcode==1){
									$.tiziDialog({content:json.error});
									window.location.reload();
								}
							},
							error : function(){
								$.tiziDialog({content:'服务器繁忙，请稍候再试'});
							}
						});
					}else{
						$.tiziDialog({content:'请选择一个文件'});
						return false;
					}								
				},
				cancel:true,
				close:function(){
					$('#sharefield').uploadify("destroy");
				}
			});
		});
	};
	//取消分享
	exports.shareCancleFn = function(){
		$('.shareCancle').live('click',function(){
			var that = $(this);
			// var class_id = $('.del_share_class_id').val();
			var file_id = $(this).attr('file_id');
			var share_id = $(this).attr('share_id');
			var page = $('#current_page').val();
			$.tiziDialog({
				title:'取消分享',
				content:'确定取消分享吗？',
				icon:'question',
				width:360,
				ok:function(){
					$.tizi_ajax({
						'url' : baseUrlName + 'teacher/cloud/del_share',
						'type' : 'POST',
						'dataType' : 'json',
						'data' : {
							'share_id' : share_id,
							'file_id':file_id,
							'class_id':$('.del_share_class_id').val()
						},
						success : function(json){
							if (json.errorcode == 1){
								that.parents('.shareBox').remove();
								// $.tiziDialog({
								// 	content : json.error,
								// 	icon : 'succeed',
								// 	ok : function(){
								// 	}
								// });
								var num=$('.class_manage_btn').find('li.active a').html();
								num = Number(num.substring(3,num.length-1))-1;
								if(num>=0)
								$('.class_manage_btn').find('li.active a').html("文件("+num+")");
								class_share_pagination(page);
							} else {
								$.tiziDialog({content:json.error});
							}
						},
						error : function(){
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
					});
				},
				cancel:true,
				close:function(){
				}
			});		
		});
	};
	//班级分享翻页 定义
	var class_share_pagination = function(page){
		var ver = (new Date).valueOf();
		$.tizi_ajax({
			'url' : baseUrlName + 'class/details/page',
			'type' : 'GET',
			'dataType' : 'json',
			'data' : {'page':page,'class_id':$('.del_share_class_id').val(),'flip':true},
			success : function(data){
				if (data.errorcode == true){
					$('.classTableHeight2').html(data.html);
				}else{
					$.tiziDialog({content:'系统忙，请稍后再试'});
				}
			},
			error : function(){
				$.tiziDialog({content:'系统忙，请稍后再试'});
			}
		});
	};
	//班级分享翻页
	exports.class_share_pagination = function(page){
		class_share_pagination(page);
	};
	//班级分享, 转换中的文档给提示
	exports.inprocess = function(page){
		$('.inprocess').live('click',function(){
			var share_id = $(this).attr('share_id');
			var url = baseUrlName + 'cloud/cloud/doc_process/' + share_id;
			var to_url = baseUrlName + 'teacher/cloud/share_detail/' + share_id;
			var that = $(this);
			$.tizi_ajax({
				'url' : url,
				'type' : 'GET',
				'dataType' : 'json',
				'data' : {},
				success : function(data){
					if(data.errorcode!=1){
						$.tiziDialog({content:'文档正在处理中，请稍候'});
					}else{
						window.open(to_url,"_blank");
						// that.attr('target','_blank');
						// that.attr('href',to_url);
						// that.bind("click",function(){
							 
						// });
					}
				}
			});
			
		});
	};
	/*班级分享, 转换中的文档给提示*/
	exports.check_pfop = function(page){
		$('.check_pfop').live('click',function(){
			var file_id = $(this).attr('file_id');
			var share_id = $(this).attr('share_id');
			var url = baseUrlName + 'resource/res_file/check_pfop/' + file_id;
			var that = $(this);
			$.tizi_ajax({
				'url' : url,
				'type' : 'GET',
				'dataType' : 'json',
				'data' : {},
				success : function(data){
					if(data.errorcode!=true){
						$.tiziDialog({content:data.error});
					}else{
						var to_url = baseUrlName + "teacher/cloud/share_detail/"+share_id;
						window.open(to_url,"_blank");
					}
				}
			});
			
		});
	};
	//班级试卷翻页 定义
	var class_homework_pagination = function(page){
		var ver = (new Date).valueOf();
		$.tizi_ajax({
			'url' : baseUrlName + 'class/details/paper_page',
			'type' : 'GET',
			'dataType' : 'json',
			'data' : {'page':page,'class_id':$('#class_id').val()},
			success : function(data){
				if (data.errorcode == true){
					$('.homework_list_body').html(data.html);
				}else{
					$.tiziDialog({content:'系统忙，请稍后再试'});
				};
				// 下载作业init
				 seajs.use('module/teacherClass/downloadAssignment',function(ex){
		            ex.homeworkDownload.init();
		         });
			},
			error : function(){
				$.tiziDialog({content:'系统忙，请稍后再试'});
			}
		});
	};
	//班级试卷 翻页
	exports.class_homework_pagination = function(page){
		class_homework_pagination(page);
	}

	//班级zuoye翻页 定义 tizi4.0
	var class_zy_pagination = function(page){
		var ver = (new Date).valueOf();
		$.tizi_ajax({
			'url' : baseUrlName + 'class/details/homework_page',
			'type' : 'GET',
			'dataType' : 'json',
			'data' : {'page':page,'class_id':$('#class_id').val()},
			success : function(data){
				if (data.errorcode == true){
					$('.zuoye_list').html(data.html);
				}else{
					$.tiziDialog({content:'系统忙，请稍后再试'});
				};
			},
			error : function(){
				$.tiziDialog({content:'系统忙，请稍后再试'});
			}
		});
	};
	//班级作业 翻页  tizi4.0
	exports.class_zy_pagination = function(page){
		class_zy_pagination(page);
	}

	//从本地上传开始
	var sharefieldFn = function(ele,alType){
		seajs.use(['flashUploader','cookies'],function(){
			var flag = false;
			var docExt = '*.doc;*.docx;*.ppt;*.pptx;*.xls;*.xlsx;*.wps;*.et;*.dps;*.pdf;*.txt;';
			var queueJsonStr = ""; 
			var uploadShareDialog;
			$('#sharefield').uploadify({
				'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
				'uploader' : 'http://up.qiniu.com', //需要上传的url地址
				'buttonClass'     : 'choseFileBtn',
				'button_image_url':staticBaseUrlName+staticVersion+'image/teacherResource/placeBtn.png',
				'buttonText' :"从本地上传",
				'fileTypeExts' : alType,
				'fileSizeLimit' : '200MB',
				'fileObjName' : 'file',
				'multi'	: true,
				'width' : 106,
				'height' :35,
				'preventCaching':true,
				'successTimeout' : 7200,
				'uploadLimit' : 10,
				'overrideEvents': ['onSelectError','onUploadProgress','onCancel','onUploadSuccess','onDialogClose'],
				onSWFReady:function(){
				},
				onDialogClose:function(queueData){
					queueNum = queueData.queueLength;
				},
				onSelect:function(file){
					$("#sharefield-queue").hide();
					var praseName = file.name;
					if(praseName.length>24)praseName = praseName.substring(0,24) + "...";
					var praseSize = praseFileSize(file.size);
					queueJsonStr +="{\"queue_id\":"+file.index+",\"file_id\":\""+file.id+"\",\"file_name\":\""+praseName+"\",\"size\":\""
								+praseSize+"\",\"status\":"+file.filestatus+"},";
				},
				onFallback : function() {
					$('.choseFile').html(noflash);
					$('#fromLocalUp').hide();
				},
				onSelectError : function(file, errorCode, errorMsg) {
					switch (errorCode) {
						case -100: 
							$(".choseFile").find(".error").remove();
							$.tiziDialog({content:"每次只能上传并分享10个文件"});
							break;  
						case -110:
							$(".choseFile").find(".error").remove();
							$.tiziDialog({content:"文件 [" + file.name + "] 过大！每份文件不能超过200M"});						
							break;  
						case -120:
							$(".choseFile").find(".error").remove();
							$.tiziDialog({content:"文件 [" + file.name + "] 大小异常！不可以上传大小为0的文件"});
							break;  
						case -130:
							$(".choseFile").find(".error").remove();
							$.tiziDialog({content:"文件 [" + file.name + "] 类型不正确！不可以上传错误的文件格式"});
							break;  
					}  
					return false;
				},
				onUploadStart:function(file){
					okDal=false;
					if(file.type != "" && docExt.indexOf(file.type) != -1){//oss上传
						var formData = $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post');
						$("#sharefield").uploadify("settings", "formData", formData);
						$("#sharefield").uploadify("settings", "uploader", baseUrlName+'upload/cloud');
						$("#sharefield").uploadify("settings", "fileObjName", 'shareFileUp');
					}else{//qiniu上传						
						var oldFileName = file.name;
						var fileSize = file.size;
						var fileKey = '';
						var fileToken = '';
						$.tizi_ajax({
								'url' : baseUrlName + 'cloud/cloud/ajax_get_token_key',
								'type' : 'POST',
								'dataType' : 'json',
								'data' : {'file_ext' : file.type,'file_size':file.size,'file_name':file.name},
								'async':false,
								success : function(data){
									if (data.error_code == true){
										fileKey = data.file_key;
										fileToken = data.file_token;
									}else {
										$.tiziDialog({content:data.error});
										return false;
									}
								},
								error : function(){
									$.tiziDialog({content:'系统忙，请稍后再试'});
								}
							});
						$("#sharefield").uploadify("settings", "fileObjName", 'file');	
						$("#sharefield").uploadify("settings", "uploader", 'http://up.qiniu.com');	
						var formData = {'token':fileToken,'key':fileKey,'old_name':oldFileName,'file_size':fileSize};
						$("#sharefield").uploadify("settings", "formData", formData);
					}
					if(flag == false){
						queueJsonStr=queueJsonStr.substring(0,queueJsonStr.length-1);
						queueJsonStr = "{\"files\":["+queueJsonStr+"]}";
						var myobj = JSON.parse(queueJsonStr);
						var listTemp = $('#fromLocalPop').html();
						var alertContent = Mustache.to_html(listTemp,myobj);
						uploadShareDialog = $.tiziDialog({
							id:'alertContent',
							title:'分享文档',
							content:alertContent,
							icon:null,
							width:600,
							ok:function(){
								if (!okDal) {
									return false;
								};
								var class_id = $('.del_share_class_id').val();
								var file_id = $(".upList").attr('file_id');
								var desc = $('textarea').val();
								if(file_id !== ""){ //有文件了,点确定做分享的请求
									$.tizi_ajax({
										'url' : baseUrlName + 'teacher/cloud/share',
										'type' : 'POST',
										'dataType' : 'json',
										'data' : {'class' :class_id,'file_id':file_id,'desc':desc},
										success : function(json){
											if(json.errorcode==1){
												$.tiziDialog({content:json.error});
												window.location.reload();
											}
										},
										error : function(){
											$.tiziDialog({content:'服务器繁忙，请稍候再试'});
										}
									});
								}else{
									$.tiziDialog({content:'您没有要分享的文件'});
								}								
							},
							cancel:function(){
								$.tiziDialog.list["alertContent"].close();
                                $('#sharefield').uploadify('cancel',myobj.files[0].file_id);
                                $.tiziDialog.get('from_net_or_local').close();
							}
						});
						flag =true;
					}
				},
				onUploadProgress: function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
					var cFileIndex = file.index;
					var cFileObj = $("#file_num_"+cFileIndex);
					if(bytesUploaded > 0 && bytesUploaded < bytesTotal){
						var percentage = bytesUploaded/bytesTotal * 100;
						percentage = percentage.toFixed(1);
						if(percentage<95){
							cFileObj.find('strong').css({'width': percentage + '%'});
							cFileObj.find(".status").html(percentage+'%');
							cFileObj.find(".UploadCancel").html("删除");
						}
						$('.upList').parents('table').find('.aui_state_highlight').attr('disabled','disabled');
					}
					if(bytesUploaded == bytesTotal){
						cFileObj.find(".UploadCancel").remove();
					}
				},
				onCancel: function(file) {
					var cFileIndex = file.index;
					var cFileObj = $("#file_num_"+cFileIndex);
					cFileObj.remove();
					if(queueNum == 1){
						flag = false;
						queueJsonStr = "";
						uploadShareDialog.close();
					}
				},
				onUploadSuccess: function(file, data, response) {
					$('.upList').parents('table').find('.aui_state_highlight').removeAttr('disabled');
					queueJsonStr = "";
					var cFileIndex = file.index;
					var cFileObj = $("#file_num_"+cFileIndex);
					var json = JSON.parse(data);
					if(json.key){//qiniu上传
						var persistentId = 0;
	                    if(json.persistentId != undefined){
	                        persistentId = json.persistentId;
	                    }
						$.tizi_ajax({
							'url' : baseUrlName + 'cloud/cloud/qiniu_upload',
							'type' : 'POST',
							'dataType' : 'json',
							'data' : {'key':json.key,'file_name':file.name,'file_size':file.size,'persistent_id':persistentId},
							success : function(data){
								if (data.error_code == true){
									cFileObj.find(".status").remove();
									cFileObj.find('strong').css({'width': '100%'});
									if(data.new_file_id){
										ff = $(".upList").attr('file_id');
										ff += ','+data.new_file_id;
										$(".upList").attr('file_id',ff);
									}
								}else {
									cFileObj.find(".status").css("color","red");
									cFileObj.find(".status").html(data.error);
								}
							},
							error : function(){
								$.tiziDialog({content:'系统忙，请稍后再试'});
							}
						});
					}else if(json.code){
						if(json.code == 1){
							cFileObj.find(".status").remove();
							cFileObj.find('strong').css({'width': '100%'});
							if(json.new_file_id){
								ff = $(".upList").attr('file_id');
								ff += ','+json.new_file_id;
								$(".upList").attr('file_id',ff);
							}
						}else if(json.code == -6){
							$.tiziDialog({content:json.msg,time:3});
						}else{
							cFileObj.find(".status").css("color","red");
							cFileObj.find(".status").html(json.msg);
						}
					}else{
						cFileObj.find(".status").css("color","red");
						cFileObj.find(".status").html('上传失败');
					}
					okDal=true;
				},
				onUploadError:function(file, errorCode, errorMsg, errorString){
					uploadError(file, errorCode, errorMsg, errorString,'share_upload');
				},
				onQueueComplete:function(queueData){
					// alert(queueData.uploadsSuccessful)
					// alert(queueData.filesSelected)
					if(queueData.uploadsSuccessful < queueData.filesSelected && queueData.filesSelected!=1){
						queueJsonStr = "";
						flag=false;
						if(queueData.uploadsSuccessful<1){
							setTimeout(function(){window.top.location.reload();}, 1000);
						}
					}
				}
			});
			$('#sharefield').children().css({'left':'0px'});
		});
	};
	var uploadError = function(file, errorCode, errorMsg, errorString,source){
		$.tizi_ajax({
			'url' : baseUrlName + 'cloud/cloud/upload_error',
			'type' : 'POST',
			'dataType' : 'json',
			'data' : {'file_index':file.index,'file_name':file.name,'errorCode':errorCode,'errorMsg':errorMsg,'errorString':errorString,'source':source},
			success : function(data){
				return;
			}
		});
	};
	var praseFileSize = function(size){
		var mod = 1024;
        var units = ['B','KB','MB'];
        for (var i = 0; size > mod; i++) 
        {
            size /= mod;
        }
        return size.toFixed(2)+' '+units[i];
	};
	//从本地上传结束
	//从网盘上传开始
	exports.fromInterUpFn = function(){
		$('#fromInterUp').live('click',function(){
			var table = "<table><tr><th class='b_r'>文件名</th><th>大小</th></tr></table>";
			$('.DisRight').html(table);
			$('.tree-title').each(function(){
                if($(this).attr('dir-id')==0){
                  $(".folderList").eq(0).show();
                  // $(this).addClass("tree-title-add");
                }
            });
            var first_dir_id =0;// $('#first_dir_id').val();
			get_file_in_a_dir(first_dir_id);//直接显示‘全部文件’
			$.tiziDialog({
				title:'从网盘上传',
				content:$('#fromInterPop').html(),
				icon:null,
				width:600,
				ok:function(){	//点击确定后，
					var t = $('input:checkbox:checked');
					var arr = new Array();
					t.each(function(){
						var kid = new Array();
						kid['fid'] = $(this).attr('id');
						kid['fn'] = $(this).attr('file_name');
						kid['fs'] = $(this).attr('file_size');
						arr.push(kid);
					});
					if(arr.length>10){
						$.tiziDialog({content:'一次最多只能选择10个文件'});
						return false;
					}
					if(arr.length>0){
						var _html = '';
						for(var data in arr){
							_html += "<ul class='upList' file_id='"+ arr[data].fid 
							_html += "'><li class='cf'><span class='fl'>"+arr[data].fn
							_html += "</span><span class='size fr'>"+arr[data].fs+"</span></li></ul>";
						}
						$('.upStyle').append(_html);
						$('.upBtn').hide();
					}else{
						$.tiziDialog({content:'请至少选择一个文件'});
						return false;
					}
				},
				cancel:true
			});
		});
	};
	//教师 班级空间  下载文件
	exports.downloadFile = function(){
		seajs.use(['cookies'],function(){
			$('.downloadFile').live('click',function(){
				var file_name = $(this).attr('file_name');
				var file_id = $(this).attr('file_id');
				var share_id = $(this).attr('share_id');
				var alpha_class_id = $('.alpha_class_id').val();
				var source = $(this).attr('source');
				$.tizi_ajax({
						url: baseUrlName + "teacher/cloud/download_verify", 
						type: 'POST',
						data: {'file_id' :file_id,'file_name':file_name,'class_id':alpha_class_id,
							   'source':source},
						dataType: 'json',
						success: function(data){
							if(data.errorcode == false) {                    	
								$.tiziDialog({content:data.error});
							}else{
								var fname = url = '';
								if(data.source == 1){
									if(data.file_type==1){
										var down_url=baseUrlName + "teacher/cloud/download/?url="+data.file_path
										+'&file_name='+data.file_encode_name+'&file_id='+data.file_id;
										// Teadownload.force_download(down_url,data.fname,true);
										url = down_url ;
										fname = data.fname;
										// Teadownload.down_confirm_box(url,fname,false,share_id);
										Teadownload.down_confirm_box(data.file_path,data.fname,false,share_id);//ghl提供的下载接口
									}else{
										url = data.url;
										fname = data.fname;
										Teadownload.down_confirm_box(url,fname,true,share_id);
										// Teadownload.force_download(data.url,data.fname,true,true);
									}
								}else if(data.source==2){//下载收藏来的课件
									var down_url=baseUrlName + "download/doc?url="+data.file_path+'&file_name='+data.file_name;
									ga('send', 'event', 'Download-Lesson-doc', 'Download', data.fname);
									url = down_url ;
									fname = data.fname;
									Teadownload.down_confirm_box(url,fname,false,share_id);
								}
							}
						}
				});
			});
		});	
	};
	//班级分享，从网盘选择文件上传，选择一个文件夹，展示出该文件夹下的所有该类型文件
	var get_file_in_a_dir = function(dirid,sub){
		var filetype = $('#fromInterPop').attr('chosen-type');
		$.tizi_ajax({
			'url' : baseUrlName + 'teacher/cloud/get_files_render',
			'type' : 'GET',
			'dataType' : 'json',
			'data' : {'dir_id' :dirid,'filetype':filetype,'sub_cat_id':sub},
			success : function(json){
				if(json.errorcode==1){
					// console.log(json.html);
					$('.DisRight').html(json.html);
				}
			},
			error : function(){
				$.tiziDialog({content:'服务器繁忙，请稍候再试'});
			}
		});
	};
	//从网盘上传结束
	//移动文件 - 点击收缩
	exports.moveFileFn = function(){
		$('.tree-title').live('click',function(){
			var _nxt = $(this).next('ul');
			var _chd1 = $(this).children('a').eq(0);
			var _chd2 = $(this).children('a').eq(1);
			var that = $(this);
			$('.tree-title').removeClass('tree-title-add');
			$(this).addClass('tree-title-add');
			$('.shareItem').removeClass('unfold');
			if(_nxt.length > 0 && _nxt.css('display') == 'block'){
				_nxt.hide();
				_chd1.removeClass('icon-plus').addClass('icon-add');
				_chd2.removeClass('unfold').addClass('fold');
			}else{
				_nxt.show();
				_chd2.addClass('unfold');
				if($(this).find('.icon').hasClass('icon-add')){
					_chd1.removeClass('icon-add').addClass('icon-plus');
				}else{
					_chd1.removeClass('icon-add').removeClass('icon-plus');
				}
			}
			//如果是从网盘中选择某个文件上传，那么点击文件夹的时候要动态的取出该文件夹下的文件
			if($('.class_share_choose_file').val()){
				var dirid = $(this).attr('dir-id');
				var sub_cat_id = $(this).attr('sub_cat_id');
				get_file_in_a_dir(dirid,sub_cat_id);
			}
		});
	};
	exports.ZeroClipboard = function(){
		seajs.use('zeroClipboard',function(){
			ZeroClipboard.setMoviePath(staticUrlName+staticVersion+'lib/ZeroClipboard/1.0.7/ZeroClipboard.1.0.7.swf');
			var clip = new ZeroClipboard.Client();
			clip.setHandCursor(true);
			
			clip.setText($('#inviteCode').text());
			//clip.glue('inviteId1');
			clip.glue('iniInviteId');
			clip.addEventListener( "complete", function(){
				$.tiziDialog({
					id:"copyClipID",
					content:'复制成功啦！赶快发送给你身边的老师或学生吧：）',
					icon:'succeed'
				});
			});			
		});
	}
	exports.ZeroClipboardSlide = function(){
		seajs.use('zeroClipboard',function(){
			ZeroClipboard.setMoviePath(staticUrlName+staticVersion+'lib/ZeroClipboard/1.0.7/ZeroClipboard.1.0.7.swf');
			var clip = new ZeroClipboard.Client();
			clip.setHandCursor(true);
			
			clip.setText($('#iniInviteCode2').text());
			//clip.glue('inviteId1');
			clip.glue('iniInviteId2');
			clip.addEventListener( "complete", function(){
				$.tiziDialog({
					id:"copyClipID2",
					content:'复制成功啦！赶快发送给你身边的老师或学生吧：）',
					icon:'succeed'
				});
			});			
		});
	}
	// 点击复制插件结束
	// 添加学生账号开始
	exports.addStudent = function(){
		//新创建学生账号 导入学生名单生成账号
		$('#create_student_menu').live('click',function(){
			var imaxc = $('#icmaxc').text() - $('#student_total').text();
			$('#ichas').html($('#student_total').text());
			var dialog = $.tiziDialog({
				id:'uploadStudentList',
				title:'添加学生账号',
				content:$('#createStudentBillPop').html().replace('fileField_beta','fileField'),
				icon:null,
				width:600,
				cancel:true,
				close:function(){
					$('#fileField').uploadify('destroy');
					$(".choseFile .error").remove();
					if(jQuery.browser.version=="6.0"){
						window.location = window.location;
					}
				},
				ok:function(){
					//有学生姓名
					if($('#createStudent_name').is(':visible')){
						var text = $('#student_names_new');
						if($.trim(text.val()) == ''){
							text.next('.ValidformInfo').show();
							return false;
						}else{
							$.tizi_ajax({
								'url' : baseUrlName + 'class/create_students/b_name',
								'type' : 'POST',
								'dataType' : 'json',
								'data' : {
									'class_id' : $('#class_id').val(),
									'student_names' : text.val()
								},
								success : function(data, status){
									classAjax.createStudent(data, 'byname');
								}
							});
						}
						$.tiziDialog.list['uploadStudentList'].close();
						classValid.createStudent_new(class_id);
						
						
					}
					//无学生姓名
					if($('#createStudent_noName').is(':visible')){
						var create_number = $("#J_Amount").val();
						if (parseInt(create_number) > 0){
							$.tizi_ajax({
								'url' : baseUrlName + 'class/create_students/b_number',
								'type' : 'POST',
								'dataType' : 'json',
								'data' : {
									'class_id' : $('#class_id').val(),
									'create_number' : create_number
								},
								success : function(data, status){
									classAjax.createStudent(data, 'bynumber');
								}
							});
						}
					}
					//上传学生名单
					if($('#createStudent_bills').is(':visible')){
						
					}
					//学生已有账号
					if($('#createStudent_account').is(':visible')){
						
						var s = $('.stuAccName');
						if(s.val() == ''){
							s.next('.ValidformInfo').show();
							return false;
						}
						var student_versign = $('.stuAccName').val();
						$.tizi_ajax({
							'url' : baseUrlName + 'class/invite/t_invite_s',
							'type' : 'POST',
							'dataType' : 'json',
							'data' : {
								'class_id' : $('#class_id').val(),
								'versign' : student_versign
							},
							success : function(data, status){
								classAjax.teacherInputStudent(data);
							}
						});
					}
				}
			});
			// 默认输入的学生数量
			// var imaxc = $('#icmaxc').text() - $('#student_total').text();
			// if (imaxc > 60){
			// 	imaxc = 60;
			// }
			// $('#J_Amount').val(imaxc);
			//classValid.createStudentHasCount(class_id);
			xls_student();
			//tab切换
    		tabContral.Tab(".creatStuNav", "active", ".tabBox");
    		//无学生姓名输入框获取焦点
    		$('.creatStuNav li').eq(1).click(function(){
    			$('#J_Amount').focus();
    		});
		});
	};
	var xls_student = function(){
		seajs.use(['flashUploader','cookies'],function(){
			var class_id = $('#class_id').val();
			var that = this;
			var temp_arr = [];
			var flag = false;
			var msg = "";
			$('#fileField').uploadify({
				'formData' : $.tizi_token({'class_id':class_id,'session_id':$.cookies.get(baseSessID)},'post'),
				'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
				'uploader' : baseUrlName+'upload/csxls?class_id='+class_id, //需要上传的url地址
		        'buttonClass'     : 'choseFileBtn',
		        'button_image_url':false,
		        'buttonText' :"上传学生名单",
				'fileTypeExts' : '*.xls; *.xlsx;',
				'fileSizeLimit' : '20MB',
				'fileObjName' : 'fileField',
				'multi'	: false,
		        'width' : 192,
		        'height': 28,
				'uploadLimit' : 2,
				'overrideEvents': ['onSelectError','onDialogClose'],
				onSWFReady:function(){
				},
				onFallback : function() {
		            $('.choseFile').html(noflash);
		        },
				onSelectError : function(file, errorCode, errorMsg) {
						switch (errorCode) {
							case -100: 
								$(".choseFile").find(".error").remove();
								$.tiziDialog({content:"每次最多上传5份文档"});
								break;  
							case -110:
								$(".choseFile").find(".error").remove();
								$.tiziDialog({content:"文件 [" + file.name + "] 过大！每份文档不能超过20M"});						
								break;  
							case -120:
								$(".choseFile").find(".error").remove();
								$.tiziDialog({content:"文件 [" + file.name + "] 大小异常！不可以上传大小为0的文件"});
								break;  
							case -130:
								$(".choseFile").find(".error").remove();
								$.tiziDialog({content:"文件 [" + file.name + "] 类型不正确！不可以上传错误的文件格式"});
								break;  
						}  
						return false;
					},
				onUploadSuccess: function(file, data, response) {
				 	var json = JSON.parse(data);
					$("#fileField").attr("disabled",true);
					$(".aui_footer button").attr("disabled",true);
					$(".choseFile").append('<img src="'+staticUrlName+'image/tizi_dialog/icon/loading.gif"/>');
					if (json.code == 1){
						ga('send', 'event', 'CreateStudent-byexcel', 'CreateStudent', 'byexcel', json.data.length);
						//返回正确值
						student_prepare = json.data;
						//恢复默认值
						$("#fileField").removeAttr("disabled");
						$(".aui_footer button").removeAttr("disabled");
						$(".choseFile").find("img").remove();
						flag = true;
						msg = json.msg;
						//var str = '<div class="error">'+file.name+'导入成功</div>';
						//$(".choseFile").append(str);
					} else {
						$(".aui_footer button").removeAttr("disabled");
						$("#fileField").removeAttr("disabled");
						$(".choseFile").find("img").remove();
						$(".choseFile").find(".error").remove();
						//返回错误值
						var str1 = '<div style="margin-top:90px;" class="error colorRed font12">'+file.name+json.msg+'</div>';
						$(".choseFile").append(str1);
					}
				},
				onUploadComplete: function(file) {
					if(flag)
					{
						$.tiziDialog.list['uploadStudentList'].close();
						$.tiziDialog({
							content:msg,
							icon:"succeed",
							ok : function(){
								window.location.href = baseUrlName + 'teacher/class/' + $('#class_id').val() + '/student';
							},
							close : function(){
								window.location.href = baseUrlName + 'teacher/class/' + $('#class_id').val() + '/student';
							}
						});
					}
				}
			});
			$('#SWFUpload_0').css({'left':'-13px'});
		});
	};
	// 班级数量的加减 extend
	exports.addPlus = function(){
		jQuery.extend({
			min : 1,  
			reg : function(x) {  
			    jQuery('#J_Tip').html("");  
			    jQuery('#J_Tip').hide();  
			    return new RegExp("^[1-9]\\d*$").test(x);  
			},  
			amount : function(obj, mode) {  
			    var x = jQuery(obj).val();  
			    if (this.reg(parseInt(x))) {  
			        if (mode) {  
			            x++;  
			        } else {  
			            x--;  
			        }  
			    } else {  
			        //jQuery('#J_Tip').html("<i class=\"ico\"></i>请输入正确的数量！");  
			        jQuery('#J_Tip').show();  
			        jQuery(obj).val(1);  
			        jQuery(obj).focus();  
			    }  
			    return x;  
			},  
			reduce : function(obj) {  
			    var x = this.amount(obj, false);  
			    if (parseInt(x) >= this.min) {  
			        jQuery(obj).val(x);  
			    } else {
			    	var max = parseInt(jQuery('#icmaxc').text()) - parseInt(jQuery('#ichas').text());
			    	if(max == 0){
			    		jQuery('#J_Tip').html("<i class=\"ico\"></i>最多还可添加"+max+"人!");
			    		jQuery(obj).val(0);  
			    	}else{
			    		//jQuery('#J_Tip').html("<i class=\"ico\"></i>添加最少为" + this.min + "！");  
			    		//jQuery(obj).val(1);  
			    	}
			        jQuery('#J_Tip').show();  
			        jQuery(obj).focus();  
			    }  
			},  
			add : function(obj) {  
			    var x = this.amount(obj, true);  
			    //var max = jQuery('#nAmount').val();  
			    var max = parseInt(jQuery('#icmaxc').text()) - parseInt(jQuery('#ichas').text());
			    if (parseInt(x) <= parseInt(max)) {  
			        jQuery(obj).val(x);  
					if(max == 0){
				        jQuery('#J_Tip').html("<i class=\"ico\"></i>最多还可添加"+max+"人!");  
				        jQuery('#J_Tip').show();  
					}
			    } else {  
			        jQuery('#J_Tip').html("<i class=\"ico\"></i>最多还可添加"+max+"人!");  
			        jQuery('#J_Tip').show();  
			        jQuery(obj).val(max == 0 ? 0 : max);  
			        jQuery(obj).focus();  
			    }  
			},  
			modify : function(obj) {  
			    var x = jQuery(obj).val();  
			    //var max = jQuery('#nAmount').val();  
			    var max = parseInt(jQuery('#icmaxc').text()) - parseInt(jQuery('#ichas').text());
			    if (!this.reg(parseInt(x))) {  
			    	if(max == 0){
			    		jQuery('#J_Tip').html("<i class=\"ico\"></i>最多还可添加"+max+"人!"); 
			    		jQuery(obj).val(0);
			    		return false;  
			    	}
			    	if(x == 0){
			    		//jQuery('#J_Tip').html("<i class=\"ico\"></i>添加最少为" + this.min  + "！");
			    		//jQuery(obj).val(1); 
			    	}
			    	jQuery('#J_Tip').show();  
			         
			        jQuery(obj).focus();  
			        return;  
			    }  
			    var intx = parseInt(x);  
			    var intmax = parseInt(max);  
			    if (intx < this.min) {  
			        //jQuery('#J_Tip').html("<i class=\"ico\"></i>添加最少为" + this.min  + "！");  
			        //jQuery('#J_Tip').show();  
			        //jQuery(obj).val(this.min);  
			        jQuery(obj).focus();  
			    } else if (intx > intmax) {  
			        jQuery('#J_Tip').html("<i class=\"ico\"></i>最多还可添加"+intmax+"人!");  
			        jQuery('#J_Tip').show();  
			        jQuery(obj).val(max == 0 ? 0 : max);  
			        jQuery(obj).focus();  
			    }  
			}  
		});
		// 减
		$('.num_min').live('click',function(){
			jQuery.reduce('#J_Amount');
		});
		// 加
		$('.num_plus').live('click',function(){
			jQuery.add('#J_Amount');
		});
	};
	//添加学生账号结束
	//试卷
	exports.studentHomework = function(){
		// 删除布置的作业
		$('.deleteDownHomework').live('click',function(){
			var that = $(this);
			var assignment_id = $(this).attr('ass-id');
    		$.tiziDialog({ // 确定要删除么
    			content:'确定要删除此试卷?',
    			ok:function(){
    				$.tizi_ajax({url:baseUrlName+"class/teacher_class_paper/del/"+assignment_id,
		                type: 'POST',
		                data: {},
		                dataType: 'json',
		                success: function(data){
		                $.tiziDialog({
		                    content:data.error,
		                    ok:function(){
		                    	if(data.errorcode==true){
									that.parents('tr').remove();
									var num=$('.class_manage_btn').find('li.active a').html();
									num = Number(num.substring(3,num.length-1))-1;
									if(num>=0)
									$('.class_manage_btn').find('li.active a').html("试卷("+num+")");
		                    	}
		                    }
		                });  // tiziDialog
		              } // success end
		            });
    			}, // ok end
    			cancel:true
    		});
		});
		//查看作业下载名单
		$('.downloadNum').live('click',function(){
			var assignment_id = $(this).attr('ass-id');
			$.tizi_ajax({url:baseUrlName + "ep/ep/download_names/"+assignment_id+"/"+$('#class_id').val(),
                type: 'GET',
                data: {},
                dataType: 'json',
                success: function(data){
                	var _html = "<div class='teacherClass'><div class='pop_main'>";
                	_html+= "<h3>已下载学生("+data.yes_count+")</h3><ul class='studentList cf'>";
                	for(var i=0;i<data.yes.length;i++){
		             	_html+='<li>'+data.yes[i]+'</li>';
					}
					_html += "</ul><h3 class='m_t_20'>未下载学生("+data.no_count+")</h3><ul class='studentList cf'>";
					for(var i=0;i<data.no.length;i++){
		             	_html+='<li>'+data.no[i]+'</li>';
					}
                	_html += "</ul></div></div>";
                	$('#downloadNumPop').html(_html)
	                $.tiziDialog({
	                	title:'查看作业下载名单',
	                    content:$('#downloadNumPop').html(),
	                    icon:null,
						width:600,
	                    ok:function(){
	                    }
	                });  // tiziDialog
              } // success end
		    });    			
		});
	};
	//作业预览tab切换
	exports.homeworkPreTab = function(){
		//$('.classTableHeight').height($(window).height()-80).css('overflow-y','scroll');
		$('.homeworkPreTab li').click(function(){
			$(this).addClass('active').siblings().removeClass('active');
			//$('.classTableHeight').height($(window).height()-80).css('overflow-y','scroll');
			var _index = $('.homeworkPreTab li').index(this);
			$('.homeworkPreCon .homeworkCont').eq(_index).show().siblings().hide();
		});
	};
	//题目查看 [错题排行]
	exports.subjectDetial = function(){
		$('.subjectDetial').live('click',function(){
			var tit = $(this).text();
			var qid = $(this).attr('qid');
			$.tiziDialog({
				title:tit,
				content:$('#subjectDetialPop'+qid).html(),
				icon:null,
				width:600,
				ok:function(){					
				}
			});		
		});
	};
	//题目查看 [错题排行]
	exports.personWrongQ = function(){
		$('.personWrongQ').live('click',function(){
			var tit = "第"+$(this).text()+'题';
			var qid = $(this).attr('qid');
			// var paperid = $(this).attr('paperid');
			$.tiziDialog({
				title:tit,
				content:$('#subjectDetialPop'+qid).html(),
				icon:null,
				width:600,
				ok:function(){					
				}
			});		
		});
	};
    //写评语
    exports.comment = function(){
    	$('.writeComment').live('click',function(){
    		var t = $(this).text();
    		var assignment_id = $('.assignment_id').val();
    		var stu = $(this).attr('stu');
    		seajs.use('tizi_ajax',function(){
	    		$.tizi_ajax({
	    			url:baseUrlName+"class/teacher_class_paper/give_cmt_preview",
		            type: 'GET',
		            data: {'ass_id':assignment_id,ver:(new Date).valueOf()},
		            dataType: 'json',
		            success: function(data){
		             	if(data.errorcode == true){
		             		var _content = '<div class="teacherClass"><div class="pop_main"><div class="commentBpx"><div class="commentName fl"><div class="m_b_5">选择学生</div><ul>';
		             		for(var i=0;i<data.stu.length;i++){
		             			var is_checked = '';
		             			if(data.stu[i].user_id == stu){
		             				is_checked = 'checked';
		             			}
		             			_content+='<li><label for="'+data.stu[i].user_id+'">';
		             			_content+='<input type="checkbox" name="checkbox" id="'+data.stu[i].user_id+'" value="'+data.stu[i].user_id+'"' + is_checked +' />';
		             			_content+= data.stu[i].name+'</label></li>';
							 }
		             		_content += '</ul><div class="checkAll"><label for="checkAll"><input id="checkAll" type="checkbox" name="name" />全选</label></div></div><div class="commentDetial fl"><div class="m_b_5">评论内容</div><textarea class="cmt_content"></textarea></div></div></div></div>';
							$.tiziDialog({
								title:t,
								content:_content,
								icon:null,
								width:800,
								ok:function(){
									var chosen_student=""; 
		                            $("input[name='checkbox']:checked").each(function(){ 
		                                chosen_student+=$(this).val()+","; 
		                            });
		                            if(chosen_student.length<1){
		                            	$.tiziDialog({content:'请选择至少一个学生'});return false;
		                            }
		                            var _give_cmt = $('.cmt_content').val();
		                            if(_give_cmt.replace(/(^\s*)|(\s*$)/g,"").length<1){
		                            	$.tiziDialog({content:'评语内容不能为空'});return false;	
		                            }
		                            $.tizi_ajax({url:baseUrlName+"class/teacher_class_paper/insert_cmt",
						                type: 'POST',
						                data: {'stu_ids':chosen_student,'ass_id':assignment_id,cmt:_give_cmt,ver:(new Date).valueOf()},
						                dataType: 'json',
						                success: function(data){
						                	if(data==1){
						             			$.tiziDialog({content:'添加成功',close:function(){
                                        			window.location.reload();
                                    			}});   		
						                	}else{
						                		$.tiziDialog({content:data.error});
						                	}
						                }
						            });// end of insert cmt
								}
							});
							// $('#commentWin').remove();
							//写评论 全选
							checkAll();
		            	}else{
		            		$.tiziDialog({content:data.error});
		            	}
		            }
				}); //tizi_ajax controller/assign_homework
			});
    	});
    };
    //全选
    var checkAll = function(){
    	$("#checkAll").live('click',function(){
			if(this.checked) {
				$(".commentName input[name='checkbox']").each(function() {
					this.checked = true;
				});
			}else{
				$(".commentName input[name='checkbox']").each(function() {
					this.checked = false;
				});
			}
    	});
    };
	//保存设置
	exports.preserveFn = function(){
		$('.preserveBtn').live('click',function(){
	        var _Form=$(".preserveForm").Validform({
	            // 3说明是在输入框右侧显示
	            tiptype:3,
	            showAllError:false,
	            ajaxPost:true,

	            callback:function(data){
	            	if (data.code == 1){
	            		$.tiziDialog({
	            			icon:'succeed',
	            			content:data.msg,
	            			ok:function(){
	            				window.location.href = baseUrlName + 'teacher/class/' + $('#class_id').val();
	            			}
	            		});
	            	} else {
	            		$.tiziDialog({content:data.msg});
	            	}
	            }
	        });
	        _Form.addRule([
	                {
	                    ele:".className",
	                    datatype:"*",
	                    nullmsg:"请输入班级",
	                    errormsg:"班级输入错误！"
	                }
	            ]
	        );
		});
	};
});
