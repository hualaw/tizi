define(function(require, exports) {
	require('validForm');
	require('tizi_ajax');
	require('json2');//for IE7
	require('mustache');
	require('cookies');
	var cloudValid  = require("module/common/basics/teacherCloud/cloudValid");
	// var Teadownload = require("tizi_download");
	//上传文件,选好文件后触发
	exports.dropBoxFieldFn = function(){
		seajs.use(['flashUploader','cookies'],function(){
			var cFileObj = $('.current_dir_id');
			var cur_dir_id = cFileObj.val();
			var flag = false;
			var queueJsonStr = "";
			var queueNum = 0;
			var statusDialog;
			var docExt = '*.doc;*.docx;*.ppt;*.pptx;*.xls;*.xlsx;*.wps;*.et;*.dps;*.pdf;*.txt;';
			var allowType = '*.*';
			$('#shareFileUp').uploadify({
				'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
				'uploader' : 'http://up.qiniu.com', //需要上传的url地址
		        'buttonClass'     : 'choseFileBtn',
				'button_image_url':staticBaseUrlName+staticVersion+'image/teacherResource/placeBtn.png',
		        'buttonText' :"上传文件",
				'fileTypeExts' : allowType,
				'fileSizeLimit' : '200MB',
				'fileObjName' : 'file',
				'multi'	: true,
		        'width' : 110,
		        'height' :35,
				'preventCaching':true,
				'successTimeout' : 7200,
				'uploadLimit' : 10,
				'overrideEvents': ['onSelectError','onDialogClose','onUploadProgress','onCancel','onUploadSuccess'],
				onSWFReady:function(){
				},
				onDialogClose:function(queueData){
					queueNum = queueData.queueLength;
				},
				onSelect:function(file){
					$("#shareFileUp-queue").hide();
					var cFileName = cFileObj.data('fname');
					if(!cFileName){
						cFileName = '';
					}
					var praseName = file.name;
					if(praseName.length>30)praseName = praseName.substring(0,30) + "...";
					var praseSize = exports.praseFileSize(file.size);
					queueJsonStr +="{\"queue_id\":"+file.index+",\"file_id\":\""+file.id+"\",\"file_name\":\""+praseName+"\",\"size\":\""
								+praseSize+"\",\"cdir\":\""+cFileName+"\",\"status\":"+file.filestatus+"},";
				},
				onFallback : function() {
		            $('.choseFile').html(noflash);
		            $('#upDropFile').hide();
		        },
				onSelectError : function(file, errorCode, errorMsg) {
					switch (errorCode) {
						case -100: 
							$(".choseFile").find(".error").remove();
							$.tiziDialog({content:"每次最多上传10份文档"});
							break;  
						case -110:
							$(".choseFile").find(".error").remove();
							$.tiziDialog({content:"文件 [" + file.name + "] 过大！每份文档不能超过200M"});						
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
					if(file.type != "" && docExt.indexOf(file.type) != -1){
						//oss上传
						var formData = $.tizi_token({'cur_dir_id':cur_dir_id,'session_id':$.cookies.get(baseSessID)},'post');
						$("#shareFileUp").uploadify("settings", "formData", formData);
						$("#shareFileUp").uploadify("settings", "uploader", baseUrlName+'upload/cloud');
						$("#shareFileUp").uploadify("settings", "fileObjName", 'shareFileUp');
					}else{
						//qiniu上传
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
						$("#shareFileUp").uploadify("settings", "fileObjName", 'file');	
						$("#shareFileUp").uploadify("settings", "uploader", 'http://up.qiniu.com');	
						var formData = {'token':fileToken,'key':fileKey,'cur_dir_id':cur_dir_id,'old_name':oldFileName,'file_size':fileSize};
						$("#shareFileUp").uploadify("settings", "formData", formData);
					}
					if(flag === false && queueJsonStr != ""){
						queueJsonStr=queueJsonStr.substring(0,queueJsonStr.length-1);
						queueJsonStr = "{\"files\":["+queueJsonStr+"]}";
						var myobj = JSON.parse(queueJsonStr);
						var listTemp = $('#upFilePop').html();
						var alertContent = Mustache.to_html(listTemp,myobj);
						statusDialog = $.tiziDialog({
							id:'',
							title:'上传文件',
							content:alertContent,
							icon:null,
							width:600,
							dblclick:false,
							ok:false,
							cancel:false
						});
						flag =true;
					}
				},
				onUploadProgress: function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
					var cFileIndex = file.index;
					var cFileObj = $("#file_num_"+cFileIndex);
					if(bytesUploaded > 0 && bytesUploaded < bytesTotal){
						var percentage = bytesUploaded/bytesTotal * 100;
						percentage = percentage.toFixed(1)
						if(percentage<95){
							cFileObj.find('strong').css({'width': percentage + '%'});
							cFileObj.find(".complete").html(percentage+'%');
							cFileObj.find(".UploadCancel").html("x");
						}
					}
					if(bytesUploaded == bytesTotal){
						cFileObj.find(".UploadCancel").html("");
						cFileObj.find(".UploadCancel").attr("href","javascript:;");
						
					}
				},
				onCancel: function(file) {
					var cFileIndex = file.index;
					var cFileObj = $("#file_num_"+cFileIndex);
					cFileObj.remove();
					if(queueNum == 1){
						flag =false;
						queueJsonStr = "";
						statusDialog.close();
					}
				},
				onUploadSuccess: function(file, data, response) {
					queueJsonStr = "";
					var cFileIndex = file.index;
					var cFileObj = $("#file_num_"+cFileIndex);
					var json = JSON.parse(data);
					if(json.key){
						//console.log(json);
						//qiniu上传
						var persistentId = 0;
						if(json.persistentId != undefined){
							persistentId = json.persistentId;
						}
						// alert(persistentId);
						$.tizi_ajax({
							'url' : baseUrlName + 'cloud/cloud/qiniu_upload',
							'type' : 'POST',
							'dataType' : 'json',
							'data' : {'cur_dir_id':cur_dir_id,'key':json.key,'file_name':file.name,'file_size':file.size,'persistent_id':persistentId},
							success : function(data){
								if (data.error_code == true){
									cFileObj.find(".complete").html("100%");
									cFileObj.find('strong').css({'width': '100%'});
									if(queueNum == 1){
										flag =false;
										setTimeout(function(){window.top.location.reload();}, 2000);
									}else if(queueNum > 1 && cFileIndex == queueNum-1){
										flag =false;
										setTimeout(function(){window.top.location.reload();}, 2000);
									}
								}else {
									cFileObj.find(".complete").css("color","red");
									cFileObj.find(".complete").html(data.error);
								}
							},
							error : function(){
								$.tiziDialog({content:'系统忙，请稍后再试'});
							}
						});
					}else if(json.code){
						//oss上传
						if(json.code == 1){
							cFileObj.find(".complete").html("100%");
							cFileObj.find('strong').css({'width': '100%'});
							if(queueNum == 1){
								flag =false;
								setTimeout(function(){window.top.location.reload();}, 2000);
							}else if(queueNum > 1 && cFileIndex == queueNum-1){
								flag =false;
								setTimeout(function(){window.top.location.reload();}, 2000);
							}
						}else if(json.code == -6){
							$.tiziDialog({content:json.msg,time:3});
						}else{
							cFileObj.find(".complete").css("color","red");
							cFileObj.find(".complete").html('上传失败');
						}
					}else{
						cFileObj.find(".complete").css("color","red");
						cFileObj.find(".complete").html('上传失败');
					}
				},
				onUploadError:function(file, errorCode, errorMsg, errorString){
					
					exports.uploadError(file, errorCode, errorMsg, errorString,'cloud_upload');
				},
				onQueueComplete:function(queueData){
				
					if(queueData.uploadsSuccessful < queueData.filesSelected && queueData.filesSelected!=1){
						flag =false;
						setTimeout(function(){window.top.location.reload();}, 2000);
					}
				}
			});
			$('#shareFileUp object').css({'left':'0px'});

		});
	};
	//上传错误
	exports.uploadError = function(file, errorCode, errorMsg, errorString,source){
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
	exports.praseFileSize = function(size){
		var mod = 1024;
        var units = ['B','KB','MB'];
        for (var i = 0; size > mod; i++) 
        {
            size /= mod;
        }
        return size.toFixed(2)+' '+units[i];
	};

	//返回首页方法
	exports.reHome = function(ele){
			var toUrl = $(ele).data("tourl");
			if($.cookies.get('_mdir')){
				$.cookies.set('_mdir',0,{domain:'.tizi.com',path:'/'});
			}
			window.top.location.href=toUrl;
	};
	//输入框特殊字符限制
	exports.showKeyPress = function(evt){
		evt = (evt) ? evt : window.event
		return exports.checkSpecificKey(evt.keyCode);
	};
	exports.checkSpecificKey = function(keyCode){
		var specialKey = "<>\/:*?|\\\"\^\+";//Specific Key list
		var realkey = String.fromCharCode(keyCode);
		var flg = false;
		flg = (specialKey.indexOf(realkey) >= 0);
		if (flg) {
		    //alert('请勿输入特殊字符: ' + realkey);
		    return false;
		}
		return true;
	};
	//进入文件夹
	exports.into_dir = function(){
		$('.into_dir').live('click',function(){
			var current_dir_id = $(this).attr('data-dir-id');
			$.tizi_ajax({
				'url' : baseUrlName + 'teacher/cloud/dir/'+current_dir_id,
				'type' : 'GET',
				'dataType' : 'json',
				// 'data' : {'class_id' : class_id	},
				success : function(json){
					if (json.errorcode == 1){
						$('.cloud_file_list').html(json.html);
						exports.dropBoxFieldFn();//为了获取新的cur_dir_id
						exports.tableStyleFn();
						$('.old_cloud_breadcrumbs').html(json.old_cloud_breadcrumbs);
                        require("module/teacherCloud/cloudAdd").hoverOtherFileList();//调用高亮方法
					}else{
						if(typeof(json.href_to)!==undefined){
							window.top.location.href=baseUrlName+json.href_to;
						}else{
							$.tiziDialog({content:'系统忙，请稍后再试'});
						}
						
					}
				},
				error : function(){
					$.tiziDialog({content:'系统忙，请稍后再试'});
				}
			});
		});
	};
	//退回上一层目录
	exports.go_back_dir = function(){
		$('.go_back_dir').live('click',function(){
			var current_dir_id = $(this).attr('data-dir-id');
			$.tizi_ajax({
				'url' : baseUrlName + 'teacher/cloud/dir/'+current_dir_id,
				'type' : 'GET',
				'dataType' : 'json',
				'data' : {'back' : true	},
				success : function(json){
					if (json.errorcode == 1){
						$('.cloud_file_list').html(json.html);
						exports.dropBoxFieldFn();//为了获取新的cur_dir_id
						exports.tableStyleFn();
						$('.old_cloud_breadcrumbs').html(json.old_cloud_breadcrumbs);
                        require("module/teacherCloud/cloudAdd").hoverOtherFileList();//调用高亮方法
					}else{
						$.tiziDialog({content:'系统忙，请稍后再试'});
					}
				},
				error : function(){
					window.location.href = baseUrlName + "teacher/cloud";
				}
			});
		});
	};
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
				exports.get_file_in_a_dir(dirid);
			}
		});
	};
	//班级分享，从网盘选择文件上传，选择一个文件夹，展示出该文件夹下的所有该类型文件
	//@deprecated
	exports.get_file_in_a_dir = function(dirid){
		var filetype = $('#fromInterPop').attr('chosen-type');
		$.tizi_ajax({
			'url' : baseUrlName + 'teacher/cloud/get_files_render',
			'type' : 'GET',
			'dataType' : 'json',
			'data' : {'dir_id' :dirid,'filetype':filetype},
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
	//cloud index翻页相关
	exports.get_files=function(getData){
		getData['ver'] = (new Date).valueOf();
		$.tizi_ajax({
			'url' : baseUrlName + 'cloud/cloud/get_files',
			'type' : 'GET',
			'dataType' : 'json',
			'data' : getData,
			success : function(data){
				if (data.errorcode == true){
					$('.ajax_file_list').html(data.html);
                    require("module/teacherCloud/cloudAdd").hoverOtherFileList();//调用高亮方法
				}else{
					$.tiziDialog({content:'系统忙，请稍后再试'});
				}
			},
			error : function(){
				$.tiziDialog({content:'系统忙，请稍后再试'});
			}
		});
	};
	exports.typeActive = $("#file_type_list").find(".active");
	exports.getUrlData=function(ele){
		ele = ele || 1;
		var cTypeVal = exports.typeActive.data('ctype') || 0;
		var cDir = $(".current_dir_id");
		var finalData = $.extend({page:ele},{ctype:cTypeVal},{cdir:cDir.val()});
		return finalData;
	};
	exports.page = function(page){
		exports.get_files(exports.getUrlData(page));
	};
	//返回网盘首页
	exports.reCloudHome = function(){
		$('.ico_workCenter').live('click',function(){
			exports.reHome(this);
		});
	};
	//输入框特殊字符限制
	exports.specialWordLimt = function(){
		document.onkeypress = exports.showKeyPress;

	};
	// 新表格样式鼠标经过改变背景色
	exports.tableStyleFn =  function(){
		$('.tableStyle tr').hover(function(){
			$(this).addClass('tdf1').siblings().removeClass('tdf1');
		},function(){
			$('.tableStyle tr').removeClass('tdf1');
		})
	};
});