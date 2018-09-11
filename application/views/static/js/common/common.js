//seajs
var myDate = new Date();
var timestamp = myDate.getFullYear() +'' + myDate.getMonth()+1 + '' + myDate.getDate() + '' + myDate.getHours() + '' + myDate.getMinutes();
// seajs配置开始
seajs.config({
    // 基础目录
    base: staticPath,
    // 别名
    alias: aliasContent,
    // 映射
    'map': [
        [ /^(.*\.(?:css|js))(.*)$/i, '$1?version=' + timestamp ]
      ]
});
//公共调用，错误信息提示
seajs.use('tizi_msg', function(ex){
    ex.errormsg();
});
//公共调用，我的消息
seajs.use('tizi_notice', function(ex){
	ex.getNotice();
});
//公共调用，登录检测
seajs.use('tizi_login_form', function(ex){
    ex.loginCheckClick();
    ex.logoutCheckClick();
});

var Common = {
	getLeftBar:function(obj){
		$(obj.id).css("height",$(document).height() - 80)
	},
	//头部右侧下拉菜单
	headerNav:function(json){
		var _id = json.id,_ul = json.ul,_dis = json.dis;
		$(_id).hover(function(){
			$(this).find(_ul).addClass(_dis);	
		},function(){
			//$(this).find(_ul).removeClass(_dis);
		})
	},
	//鼠标经过input隐藏input的文字
	inputTips:function(id){
		// if($(id).val() !=='请输入关键字'){
		// 	$(id).val('请输入关键字');
		// }
		var txt = $(id).val();
		$(id).focus(function(){
			if($(id).val() == "请输入关键字"){
				$(id).val("");	
			}
		})
		$(id).blur(function(){
			if($(id).val() == ""){
				$(id).val(txt);
			}
		})
	}
}

Common.comValidform = {
	detectFlashSupport:function(fn_noflash,fn_flash) {
		if(fn_noflash == undefined) fn_noflash = function(){}
		if(fn_flash == undefined) fn_flash = function(){}
	    var hasFlash = false;
	    if (typeof ActiveXObject === "function") {
	      try {
	        if (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) {
	          hasFlash = true;
	        }
	      } catch (error) {}
	    }
	    if (!hasFlash && navigator.mimeTypes["application/x-shockwave-flash"]) {
	      hasFlash = true;
	    }
	    if(!hasFlash){
	    	fn_noflash();
	    }else{
	    	fn_flash();
	    }
	}
}

Common.floatLoading = {
	//页面logo加载的显示效果
	showLoading:function(){
		return;
		if($('.paperDetails .page').siblings('.question-box')){
			$('.paperDetails .page').siblings('.question-box').remove();
		}
		if($('.paperDetails .page').length == 0){
			if($('.cTextLoading').length < 1){
				$(".question-list").html('<div class="cTextLoading"></div>');
			}
		}
		if($('.paperDetails .page').length == 2){
			if($('.cTextLoading').length < 1){
				$('.paperDetails .page').first().after('<div class="cTextLoading"></div>')
			}
		}
	},
	//页面logo加载的隐藏效果
	hideLoading:function(){
		return;
		$('.floatLoadingCover').hide();
 		$('.cTextLoading').hide();
	},
	//append写入页面的弹出层的显示效果
	showFloatLoading:function(){
		if($('.floatLoadingCover').length <1){
			$('body').append('<div class="floatLoadingCover"></div><div class="cFloatLoadingGif"><span></span><p class="return undis"><a href="javascript:void(0)">返回上页</a></p></div>');
		}
		$('.return').hide();
		this.loadingDivShow();
		this.returnPre();
	},
	//append写入页面的弹出层的隐藏效果
	hideFloatLoading:function(){
		$('.floatLoadingCover').hide();
		$('.cFloatLoadingGif').hide();
		clearInterval(this.timers);
		clearInterval(this.timerID);
		$('.return').hide();
	},
 	//boxshow
	loadingDivShow:function(){
		$('.floatLoadingCover').css('height',$(window).height()).show();
		$('.cFloatLoadingGif').show();
	},
	//点击弹出层的返回上页
	returnPre:function(){
		$('.return').show();
		$('.return').click(function(){
			Common.floatLoading.hideFloatLoading();
		})
	}

}

/*错误信息弹出框*/
Common.Msg = {
	ErrorMsg : $('#errormsg'),
	errormsg : function(){
		if(this.ErrorMsg.html() != undefined && this.ErrorMsg.html() != ''){
			$.tiziDialog({
				content:this.ErrorMsg.html()
			})
		}	
	}
}
Common.Msg.errormsg();

Common.valid = {
	//添加教师端-纠错表单验证 v20140117
	paperHaveError:{
		addPaperHaveError:function(){
			var that = this;
			//添加表单验证
			var _Form=$(".haveErrorForm").Validform({
				tiptype:3,
				showAllError:false,
				ajaxPost:true,
				beforeSubmit:function(){
					var picture_urls = '';
					//处理图片链接
					$(".picture_urls").each(function(){
						picture_urls += picture_urls === '' ? '' : ',';
						picture_urls += $(this).attr('src');
					});
					$("#picture_urls").val(picture_urls);
				},
				callback:function(data){
					//调用ajax处理
					CommonAjax.paperHaveError.addPaperHaveError(data);
				}
		  	});
		  	_Form.addRule([
				{
					ele:".lite_textarea",
					datatype:"*",
					nullmsg:"错误描述不能为空",
	    			errormsg:"错误描述不能为空"
				},
				{
					ele:":checkbox:first",
					datatype:"*",
					nullmsg:"错误类型不能为空",
	    			errormsg:"错误类型不能为空"
				}
		  	]);
		}
	},
	//添加教师端-收藏题目 v20140320
	paperFavoritError : {
		paperFavoritHaveError:function(){
			var that = this;
			//添加表单验证
			var _Form=$(".paperFavoritForm").Validform({
				tiptype:3,
				showAllError:false,
				ajaxPost:true,
				beforeSubmit:function(){
					
				},
				callback:function(data){
					//调用ajax处理
					CommonAjax.paperFavoritHaveError.addSaveHaveError(data);
				}
		  	});
		  	_Form.addRule([
				{
					ele:":select",
					datatype:"*",
					nullmsg:"请选择分组",
	    			errormsg:"选择错误"
				}
		  	]);
		}
	},
	//学生端-答疑页面验证
    bindAqStart:function(){
        /*"data" : {"subject_id" : subject_id,"grade" : grade,"content" : content,"picture_urls" : picture_urls}*/
        var _Form=$(".aq_startForm").Validform({
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){
                var id = $("#aq_question_id").val();
                var subject_id = $("#aq_subject").val();
                var grade = $("#aq_grades").val();
                var content = $("#content").html();
                var picture_urls = '';
                //处理图片链接
                $(".picture_urls").each(function(){
                    picture_urls += picture_urls === '' ? '' : ',';
                    picture_urls += $(this).attr('src');
                });
                //console.log(picture_urls);
                $("#picture_urls").val(picture_urls);
                //处理内容文本
                $("#textareaContent").val(content);
                //判断学科
                if (subject_id == 0 || grade == 0){
                    $.tiziDialog({
                        content : "请选择科目和年级" 
                    });
                    return false;
                }
                //判断内容
                if ($.trim(content) === ''){
                    $.tiziDialog({
                        content : "提问内容不能为空"
                    });
                    return false;
                }
            },
            callback:function(data){
                //调用ajax处理
                CommonAjax.Aq.AqStart(data);
            }
        });
    },
    // 我要投诉验证
    complaint:function(){
        var _Form=$(".complaintConentForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype:3,
            showAllError:false,
            beforeSubmit:function(){
                /*调用验证码验证服务端信息*/
                var checkcode = $('.textCaptcha').val();
                return Common.comValidform.checkCaptcha(checkcode,1);
            },
            ajaxPost:true,
            callback:function(data){
                CommonAjax.Aq.complaint(data);
            }
        });
        _Form.addRule(
            [
                {
                    // 检测反馈内容
                    ele:".contentTextarea",
                    datatype:"*5-1000",
                    nullmsg:"请填写投诉内容！",
                    errormsg:"投诉内容5-1000个字符之间！"
                },
                {
                    ele:".tel",
                    datatype:"m | /^\\w{0}$/"
                    //errormsg:Common.vaildDataType.Phone.errormsg
                },
                {
                    // 检测验证码
                    ele:".textCaptcha"
                    //datatype:Common.vaildDataType.CaptchaCode.datatype,
                    //nullmsg:Common.vaildDataType.CaptchaCode.nullmsg,
                    //errormsg:Common.vaildDataType.CaptchaCode.errormsg
                }
            ]
        );
    },
    // 保存作业
    homeworkSaveTitle : function(save_as){
        var _Form=$(".commonSaveTitleForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(curform){
		        if($("#save-btn, #saveas-btn").attr('class').indexOf('disabled') > 0) return false;
		        $('#save-btn,#saveas-btn').addClass('disabled');
		        $('#save_as').val(save_as);
            },
            callback:function(data){
                $('#save-btn,#saveas-btn').removeClass('disabled');
                if(data.errorcode){
                	var save_title = $('#save_title').val();
                	$.tiziDialog.list['commonSaveTitle'].close();
	                $.tiziDialog({
	                	content:data.error,
	                	okVal:"去布置",
	                	icon : 'succeed',
	            		ok:function(){
	            			window.location.href = baseUrlName + 'teacher/homework/center';
	            		},
	            		cancelVal:"继续编辑",
	            		cancel:true
	                });
	            	$('.preview-title').find('h3').html(save_title);
	            }else{
	            	$.tiziDialog({content:data.error});
	            }
            }
        });
        _Form.addRule([
                {
                    ele:"#save_title",
                    datatype:"*1-20",
                    nullmsg:"请输入标题名称",
                    errormsg:"长度20个字符以内"
                }
            ]
        );
    },
    // 保存作业
    paperSaveTitle : function(save_as){
        var _Form=$(".commonSaveTitleForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(curform){
		        if($("#save-btn, #saveas-btn").attr('class').indexOf('disabled') > 0) return false;
		        $('#save-btn,#saveas-btn').addClass('disabled');
		        $('#save_as').val(save_as);
            },
            callback:function(data){
	            $('#save-btn,#saveas-btn').removeClass('disabled');
                if(data.errorcode){
                	var save_title = $('#save_title').val();
                	$.tiziDialog.list['commonSaveTitle'].close();
	                $.tiziDialog({
	            		content:data.error,
	            		icon : 'succeed',
	            		okVal:"去下载",
	            		ok:function(){
	            			window.location.href = baseUrlName + 'teacher/paper/center';
	            		},
	            		cancelVal:"继续编辑",
	            		cancel:true
	            	});
	            	$('.preview-title').find('h3').html(save_title);
	            }else{
	            	$.tiziDialog({content:data.error});
	            }
            }
        });
        _Form.addRule([
                {
                    ele:"#save_title",
                    datatype:"*1-20",
                    nullmsg:"请输入标题名称",
                    errormsg:"长度20个字符以内"
                }
            ]
        );
    }
};

CommonAjax = {}

// 添加教师端分组验证v20140120
CommonAjax.paperHaveError = {
	addPaperHaveError:function(data){
		if(data.errorcode == false) {                    	
		   $.tiziDialog({content:data.error});
		}else{
		   var content = "<p>纠错信息提交成功！</p><p>非常感谢您的反馈，我们会尽快处理。</p>";
		   $.tiziDialog({content:content});
		   //关闭
		   art.dialog.list['createHaveError'].close();
		}

	}
}

// 收藏题目
CommonAjax.paperFavoritHaveError = {
	addSaveHaveError:function(data){
		if(data.errorcode == false) {                    	
		   $.tiziDialog({content:data.error});
		}else{
		   var content = "<p>收藏成功！</p>";
		   $.tiziDialog({content:content});
		   //关闭
		   art.dialog.list['paperFavoritHaveError'].close();
		}

	}

}

//答疑投诉
CommonAjax.Aq = {
    complaint:function(res, status){
        if (res.errorcode){
            art.dialog.list['complaintId'].close();
             $.tiziDialog({
                content:'感谢您使用我们的产品！我们将会在3个工作日内处理您的投诉。',
                icon:'succeed',
                time:2
             })
        } else {
            $.tiziDialog({
                content:res.error,
                icon:null
            })
        }
    },
    AqStart:function(data){
        var json = data;
        if (json.error > 0){
            $.tiziDialog({
                icon : "succeed",
                content : "提问成功",
                ok : function(){
                    window.location.href = baseUrlName + "student/question/detail/" + json.error;
                }
            });
        } else {
            $.tiziDialog({
                content : json.error
            });
        }
    }
};
