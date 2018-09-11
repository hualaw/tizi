define(function(require,exports){
    require("tizi_ajax");
    require("tiziDialog");
	require('cookies');
    var valid  = require("module/activity/champions/valid");
    exports.init=function(){
        exports.setupVideo();//初始化视频播放
        exports.makeAppoint();//绑定预约课程
        exports.bindVideoPlay();//绑定视频播放高亮
    };
    /*初始化视频播放开始*/
    exports.setupVideo=function(){
        //调入播放器
		//var auto_start = $.cookies.get('_zy_autostart') ? false : true;
        TiZiplayer("container").setup({
			playlist:playlist,
			primary:"flash",
			height:307,
			width:456,
			adCover:"http://tizi.oss.aliyuncs.com/static/zhuangyuan/video.jpg",
			adEnd:"http://tizi.oss.aliyuncs.com/static/zhuangyuan/video.jpg",
			autostart:false
		});
		//if (auto_start) $.cookies.set('_zy_autostart', '1', { hoursToLive : 24 * 365, domain: baseCookieDomain });
        //调用错误方法
        TiZiplayer().onSetupError(function(){
            seajs.use('tizi_validform',function(ex){
                ex.detectFlashSupport(function(){
                    $('#container').html(noflash);
                });
            });
        });
    };
    /*初始化视频播放结束*/
    /*预约课程开始*/
    exports.makeAppoint = function(){
        $(".seeActive").each(function(){
			var that = $(this);
			that.on("click",function(){
				var champion_user_id = that.attr('data');
				$('.championId').val(champion_user_id);
				seajs.use('tizi_login_form',function(ex){
					ex.loginCheck('function',function(){
						$.tiziDialog({
							id:"makeAppointDialog",
							title:"见面会预约",
							content:$("#makeAppoint").html().replace('makeAppointForm_beta','makeAppointForm'),
							icon:null,
							width:490,
							init:function(){},
							ok:function(){
								$('.makeAppointForm').submit();
								return false;
							},
							cancel:true
						});
						// 绑定验证
						valid.makeAppointValid();
					});
				});
			});
		});
    };
    /*预约课程结束*/
    /*高亮开始*/
    exports.bindVideoPlay = function(){
        $(".photoImage").each(function(){
            var that = $(this);
            that.hover(function(){
                that.addClass("hoverActive");
            },function(){
                that.removeClass("hoverActive");
            })
        })
    }
    /*高亮结束*/
});
