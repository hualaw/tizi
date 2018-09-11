define(function(require,exports){
	require('tizi_ajax');
	require('tiziDialog');

	exports.paperDownload = {
		downWord : $(".down-word-btn"),
		downCard : $(".down-card-btn"),
		init:function(){
			//下载试卷
			var _this = this;
			$(".down-word-btn").click(function(){
				_this.open_downword($(this).attr('slid'));
			});
			 //下载答题卡
			$(".down-card-btn").click(function(){
				_this.open_downcard($(this).attr('slid'));
			});

			$(".del-log-btn").click(function(){
				var slid = $(this).attr('slid');
                var page = $('.page').find('.active').html();
                var type = $('#filter_type').attr('data_type');
				$.tiziDialog({
					content:"确定删除当前文档？",
					cancel:true,
					ok:function(){
		                seajs.use('module/teacherPaper/paperArchive',function(ex){
				            ex.paperArchive.delete_log(page,type,slid);
				        });
					}
				});
            });
		},
		open_downword:function(slid){
			if(this.downWord.attr('disabled') == 'disabled'){
				return false;
			};
			$.tiziDialog({
				title:"下载试卷",
				content:$("#down-paper-content").html().replace('downWin_tpl','downWin')
					.replace('PaperDownBox_tpl','PaperDownBox').replace('PaperDownBoxWord_tpl','PaperDownBoxWord'),
				icon:null,
				width:909,
				init:function(){
					require('tizi_validform').changeCaptcha('PaperDownBox');
    				require('tizi_validform').bindChangeVerify('PaperDownBox');
				},
				okVal:'立即下载试卷',
				ok:function(){	
					var checkcode = require('tizi_validform').checkCaptcha('PaperDownBox',1,1);
					if(!checkcode) return false;
					this_a = exports.paperDownload;
					var downWord = $("a[slid='"+slid+"'][class='down-word-btn']");
		        	downWord.html('下载中');
		        	downWord.attr('disabled','disabled');
		            this_a.download(slid,'paper',function(){
		            	this_a = exports.paperDownload;
		            	downWord.html('下载');
		            	downWord.removeAttr('disabled');
		            });
				},
				cancel:true
			});
			$('.teacherPaper .downWin .wordType p.in').click(function(){
				$('.teacherPaper .downWin .wordType p.in').removeClass('active');
				$(this).find('input').addClass('windInput').attr('checked','checked');
				$(this).addClass('active');
			});

			$('.teacherPaper .downWin .paperSizes p.in').click(function(){
				$('.teacherPaper .downWin .paperSizes p.in').removeClass('active');
				$(this).find('input').addClass('windInput').attr('checked','checked');
				$(this).addClass('active');
			});
			
			$('.teacherPaper .downWin .roleType p.in').click(function(){
				$('.teacherPaper .downWin .roleType p.in').removeClass('active');
				$(this).find('input').addClass('windInput').attr('checked','checked');
				$(this).addClass('active');
			})
		},
		open_downcard:function(slid){
			if(this.downCard.attr('disabled') == 'disabled'){
				return false;
			};
			$.tiziDialog({
				title:"下载答题卡",
				content:$("#down-card-content").html().replace('downWin_tpl','downWin'),
				icon:null,
				width:787,
				okVal:"立即下载答题卡",
				ok:function(){
					this_a = exports.paperDownload;
					var downCard = $("a[slid='"+slid+"'][class='down-card-btn']");
		        	downCard.html('下载中');
		        	downCard.attr('disabled','disabled');
		            this_a.download(slid,'card',function(){
		            	this_a = exports.paperDownload;
		            	downCard.html('答题卡');
		            	downCard.removeAttr('disabled');
		            });
				},
				cancel:true
			});
		},
		setDownloadChecked:function(download_type,name,value){
			var len = $('.downWin_tpl').find("input[type=radio][name="+download_type+'_'+name+"]").length;
		    for(var i = 0; i < len; i++){
		    	var obj = $('.downWin_tpl').find("input[type=radio][name="+download_type+'_'+name+"]").eq(i);
		    	if(obj.val() == value){
		    		obj.attr('checked',true);
		    		obj.parent().parent().addClass('active');
		    	}
		    	else{
		    		obj.attr('checked',false);
		    		obj.parent().parent().removeClass('active');
		    	}
		    }
		},
		download:function(slid,download_type,unlock){
		    var paper_version = $('.downWin').find("input[type=radio][name="+download_type+"_version]:checked").val();
		    this.setDownloadChecked(download_type,'version',paper_version);
		    var paper_type = $('.downWin').find("input[type=radio][name="+download_type+"_type]:checked").val();
		    this.setDownloadChecked(download_type,'type',paper_type);
		    var post_data = {
	            'paper_version': paper_version, 
	            'paper_type': paper_type,
	            'save_log_id':slid,
	            'download_type':download_type
	        };
		    if(download_type == 'paper') {
		        var paper_size = $('.downWin').find("input[type=radio][name=paper_size]:checked").val();
		        this.setDownloadChecked(download_type,'size',paper_size);
		        post_data['captcha_name'] = 'PaperDownBox';
		        post_data['captcha_word'] = $('.PaperDownBoxWord').val();
		        post_data['paper_style'] = 'default';
		        post_data['paper_size'] = paper_size;
		        baseuri = baseUrlName + 'paper/download/paper';
		    }
		    if(download_type == 'card')
		    {
		        baseuri = baseUrlName + 'paper/download/card';
		    }

		    $.tizi_ajax({
		    	url: baseuri, 
	            type: 'POST',
	            data: post_data,
	            dataType: 'json',
	            success: function(data){
	                unlock();
	                if(data.errorcode == false) {                       
	                    $.tiziDialog({content:data.error});
	                }else{
	                	//var url=baseUrlName + 'download/paper?url='+data.url
	                    //	+'&file_name='+data.file_name+'&download_type='+download_type;
	                    var durl=data.durl + '?fileUrl=' + data.dlink + '&fileName=' 
	                    	+ data.file_name + '&token=' + data.dtoken;
	                    ga('send', 'event', 'Download-Paper-'+download_type, 'Download', data.fname);
	                	require('tizi_download').force_download(durl,data.fname);
	                }
	            }
		    })
		}
	}
});