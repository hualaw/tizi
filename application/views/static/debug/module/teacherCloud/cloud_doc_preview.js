// 这是网盘预览的脚本入口文件
define(function(require, exports){
	require('tiziDialog');
	require('tizi_ajax');
    //网盘  文档预览  flash右下角的下载   老师学生通用
	exports.shareDocDown = function(file_id, unlock){
		var source = $("#docnum").data('source');	
		var verify_url='';
		var download_url='';
		switch(source){
			case 'cloud':
				verify_url = baseUrlName + 'teacher/cloud/download_verify';
				download_url = baseUrlName + 'teacher/cloud/download/?url=';
				break;
			case 'student':
				verify_url = baseUrlName + 'student/cloud/downverify';
				download_url = baseUrlName + 'student/cloud/download?url=';
				break;
			default:
				return false;
				break;
		}
		
		$.tizi_ajax({
				url: verify_url, 
				type: 'POST',
				data: {'file_id' :file_id},
				dataType: 'json',
				success: function(data){
					if(data.errorcode == false) {                    	
						$.tiziDialog({content:data.error});
					}else{
						var Teadownload = require("tizi_download");
						var share_id = $('.download_share').attr('share_id');
						// var down_url= download_url+data.file_path
						// +'&file_name='+data.file_encode_name+'&file_id='+data.file_id+'&share_id='+share_id;
						// Teadownload.down_confirm_box(down_url,data.fname,false);

                        //2014-08-27   改为统一的ghl提供的下载接口
                        if(source=='student'){
                            Teadownload.down_confirm_box(data.file_path,data.fname,true,share_id);//有share_id就下载加 1
                        }else{
                            Teadownload.down_confirm_box(data.file_path,data.fname,true);
                        }
					}
				}
		});
		
	};
	
    exports.swfObjectInit = function(swfversion,jsversion){
        seajs.use(staticBaseUrlName + "flash/cloudFlash/swfobject",function(){
            //加载内容
            var cloudFlashUrl = staticBaseUrlName+'flash/cloudFlash/';
            // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection.
            var swfVersionStr = baseFlashVersion;
            // To use express install, set to playerProductInstall.swf, otherwise the empty string.
            var xiSwfUrlStr = cloudFlashUrl+"playerProductInstall.swf"+jsversion;
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
            Teacher.UserCenter.docLib.swfObjectHeight = flash_height;
            params.quality = "high";
            params.bgcolor = "#ffffff";
            params.wmode = "transparent";
            params.wmode = "Opaque";
            params.allowscriptaccess = "sameDomain";
            params.allowfullscreen = "true";
            var attributes = {};
            attributes.id = "TeacherPreview";
            attributes.name = "TeacherPreview";
            attributes.align = "middle";
            var swfUrl = cloudFlashUrl+"TeacherPreview.swf"+swfversion;
            swfobject.embedSWF(
                swfUrl, "flashContent",
                "1000", flash_height,
                swfVersionStr, xiSwfUrlStr,
                flashvars, params, attributes);
            swfobject.createCSS("#flashContent", "display:block;text-align:left;");
        });
    }
    exports.swfObjectHeight = function(){
        //var contentBoxH = $(".contentBox").position().top;
        //var flash_height = $(document).height()-contentBoxH;
		var flash_height = $(window).height()-164; 
		if(flash_height<465) flash_height = 465;
        $("#flashContent").height(flash_height);
        return flash_height;
    }

});