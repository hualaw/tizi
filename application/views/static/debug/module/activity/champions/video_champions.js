define(function(require,exports){
    require("tizi_ajax");
    require("tiziDialog");
	require('cookies');
	var hits = 0;
    var valid  = require("module/activity/champions/valid");
    exports.init=function(){
        exports.setupVideo();//初始化视频播放
        exports.makeAppoint();//绑定预约课程
        exports.share();//绑定分享功能
        exports.videoPraise();//视频点赞
    };
    /*初始化视频播放开始*/
    exports.setupVideo=function(){
        //调入播放器
        TiZiplayer("container").setup({
            playlist:playlist,
            primary:"flash",
            height:432,
            width:750,
			autostart : true,
			adCover:"http://tizi.oss.aliyuncs.com/static/zhuangyuan/video.jpg",
			adEnd:"http://tizi.oss.aliyuncs.com/static/zhuangyuan/video.jpg"
        });
        //调用错误方法
        TiZiplayer().onSetupError(function(){
            seajs.use('tizi_validform',function(ex){
                ex.detectFlashSupport(function(){
                    $('#container').html(noflash);
                });
            });
        });

		TiZiplayer().onPlay(function(){
			hits++;
			var item = $('#hidden_video_id').val();
			if(hits == 1 && Number(item) > 0) {
				$('.video_h1').html('<img class="undis" src="'+baseUrlName+'image/1.gif?src=champion&func=video&sv_id='+item+'&ver='+(new Date).valueOf()+'"/>');
			}
		});

		// TiZiplayer().onReady(function(){
		// 	$('body').append('<script type="text/javascript" src="http://v2.uyan.cc/code/uyan.js?uid=1940820"></script>');
		// });
    };
    /*初始化视频播放结束*/
    /*预约课程开始*/
    exports.makeAppoint = function(){
        $(".seeActive").on("click",function(){
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
    };
    /*预约课程结束*/
	/*绑定分享开始*/
    exports.share=function(){
		$(".iconShare").toggle(function(){
			$(".shareBox").show();
		},function(){
			$(".shareBox").hide();
		});
        window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdPic":"","bdStyle":"0","bdSize":"32"},"share":{}};
        with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
    };
    /*绑定分享结束*/
	/* 视频点赞开始 */
	exports.videoPraise = function () {
		$('.iconTop').click(function () {
			var video_id = $('#hidden_video_id').val();
			if ($.cookies.get('champion_video_' + video_id)) {
				$.tiziDialog({
					title:'点赞失败',
					content: '你已经赞过该视频！',
					icon:true,
					width:500,
					ok:true,
					locked : true,
					icon:'warning'
				});
				return false;
			}

			$.tizi_ajax({
				type : 'post',
				url : baseUrlName  + 'zhuangyuan/praise',
				datatype : 'json',
				data : {video_id : video_id},
				success: function (data) {
					if (data != 1) {
						$.tiziDialog({
							title:'点赞失败',
							content: '操作失败，请稍候！',
							icon:true,
							width:500,
							ok:true,
							locked : true,
							icon:'warning'
						});
					} else if (data == 1) {
						$('.iconTop').html(function(index, html){
							return parseInt(html) + 1;
						});
						$.cookies.set('champion_video_' + video_id, '1');
					}
				},
				error : function () {

				}
			});

		});
	};
	/* 视频点赞结束 */
});
