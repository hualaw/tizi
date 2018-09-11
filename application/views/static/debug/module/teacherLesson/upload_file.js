define(function(require, exports){
    /**
     * 备课上传文件
     * @param json
     */
    //上传文件,选好文件后触发

    // require('tiziDialog');
    // require('tiziDialog');
    require('json2');
    exports.dropBoxFieldFn = function(){
        seajs.use(['flashUploader','cookies'],function(){
            var cFileObj = $('.current_dir_id');
            var newFileIDS = "";
            var newFileObj = $(":input[name='new_file_id']");
            var countObj = $("#count");
            var cur_dir_id = cFileObj.val();
            var flag = false;
            var queueJsonStr = "";
            var queueNum = 0;
            var complete_num = 0;
            var statusDialog;
            var docExt = '*.doc;*.docx;*.ppt;*.pptx;*.xls;*.xlsx;*.wps;*.et;*.dps;*.pdf;*.txt;';
            var allowType = '*.*';
            $('#shareFileUp').uploadify({
                'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                'uploader' : 'http://up.qiniu.com', //需要上传的url地址
                'buttonClass'     : 'choseFileBtn',
                'button_image_url':staticBaseUrlName+staticVersion+'image/teacherResource/placeBtn.png',
                'buttonText' :"选择我的文件",
                'fileTypeExts' : allowType,
                'fileSizeLimit' : '200MB',
                'fileObjName' : 'file',
                'multi' : true,
                'width' : 160,
                'height' :39,
                'preventCaching':true,
                'successTimeout' : 7200,
                'uploadLimit' : 5,
                'overrideEvents': ['onSelectError','onUploadStart','onDialogClose','onUploadProgress','onCancel','onUploadSuccess'],
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
                    $('.chooseMyFile').html(noflash);
                    $('#upDropFile').hide();
                },
                onSelectError : function(file, errorCode, errorMsg) {
                    switch (errorCode) {
                        case -100: 
                            $(".choseFile").find(".error").remove();
                            $.tiziDialog({content:"每次最多上传5份文档"});
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
                        var formData = $.tizi_token({'show_place':1,'cur_dir_id':cur_dir_id,'session_id':$.cookies.get(baseSessID)},'post');
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
                                'url' : baseUrlName + 'resource/cloud/ajax_get_token_key',
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
                        var formData = {'show_place':1,'token':fileToken,'key':fileKey,'cur_dir_id':cur_dir_id,'old_name':oldFileName,'file_size':fileSize};
                        $("#shareFileUp").uploadify("settings", "formData", formData);
                    }
                    if(flag === false && queueJsonStr != ""){
                        queueJsonStr=queueJsonStr.substring(0,queueJsonStr.length-1);
                        queueJsonStr = "{\"files\":["+queueJsonStr+"]}";
                        var myobj = JSON.parse(queueJsonStr);
                        var listTemp = $('#upFilePop').html();
                        var innerContent = Mustache.to_html(listTemp,myobj);
                        $(".upFileTable").html(innerContent);
                        //$(".uploadBefore").css("position","absolute");
                        //$(".uploadBefore").css("visibility","hidden");
						$(".uploadBefore").css("height",0);
						$(".uploadBefore").css("overflow","hidden");
                        $(".uploadAfter").show();
                        // statusDialog = $.tiziDialog({
                        //     id:'',
                        //     title:'上传文件',
                        //     content:alertContent,
                        //     icon:null,
                        //     width:600,
                        //     dblclick:false,
                        //     ok:false,
                        //     cancel:false
                        // });
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
                           $('loadBar').css({
                                            'width': percentage + '%',
                                             'border-bottom-color':"yellow"
                                            });
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
                            'url' : baseUrlName + 'resource/cloud/qiniu_upload',
                            'type' : 'POST',
                            'dataType' : 'json',
                            'data' : {'show_place':1,'cur_dir_id':cur_dir_id,'key':json.key,'file_name':file.name,'file_size':file.size,'persistent_id':persistentId},
                            success : function(data){
                                if (data.error_code == true){
                                    complete_num++;
                                    countObj.text(complete_num);
                                    cFileObj.find(".complete").html("100%");
                                    cFileObj.find('strong').css({'width': '100%'});
                                    newFileIDS += data.new_file_id+',';
                                    newFileObj.val(newFileIDS);
                                    // if(queueNum == 1){
                                    //     flag =false;
                                    //     setTimeout(function(){window.top.location.reload();}, 2000);
                                    // }else if(queueNum > 1 && cFileIndex == queueNum-1){
                                    //     flag =false;
                                    //     setTimeout(function(){window.top.location.reload();}, 2000);
                                    // }
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
                            complete_num++;
                            countObj.text(complete_num);
                            cFileObj.find(".complete").html("100%");
                            cFileObj.find('strong').css({'width': '100%'});
                            newFileIDS += json.new_file_id+',';
                            newFileObj.val(newFileIDS);
                            if(queueNum == 1){
                                flag =false;
                                //setTimeout(function(){window.top.location.reload();}, 2000);
                            }else if(queueNum > 1 && cFileIndex == queueNum-1){
                                flag =false;
                                //setTimeout(function(){window.top.location.reload();}, 2000);
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
                   var obj=$("#moveCatalogueList").find('.active');
                   var _index=obj.index();
                   var _hright=obj.height();
                   var _scrollTop=_index*_hright;
                   $("#moveCatalogueList").scrollTop(_scrollTop);
                    // if(queueData.uploadsSuccessful < queueData.filesSelected && queueData.filesSelected!=1){
                    //     flag =false;
                    //     setTimeout(function(){window.top.location.reload();}, 2000);
                    // }
                }
            });
            $('#shareFileUp object').css({'left':'0px'});

        });
    };
    // 上传错误
    exports.uploadError = function(file, errorCode, errorMsg, errorString,source){
        $.tizi_ajax({
            'url' : baseUrlName + 'resource/cloud/upload_error',
            'type' : 'POST',
            'dataType' : 'json',
            'data' : {'file_index':file.index,'file_name':file.name,'errorCode':errorCode,'errorMsg':errorMsg,'errorString':errorString,'source':source},
            success : function(data){
                return;
            }
        });
    };
    // 转换数字类型
    exports.praseFileSize = function(size){
        var mod = 1024;
        var units = ['B','KB','MB'];
        for (var i = 0; size > mod; i++)
        {
            size /= mod;
        }
        return size.toFixed(2)+' '+units[i];
    };
    //确认上传
    exports.confirmUploadFn = function(){
        $('.confirmUpload').live('click',function(){
            exports.moveFileTreeValid();
        });
    };
    //移动文件验证
    exports.moveFileTreeValid = function(){
        var _Form=$(".moveFileTreeForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                //cloudAjax.moveFileTreeAjax(data);
                if(data.error_code==true){
					var btn = null;
					var alertContent = "";
					var devoteUrl=loginUrlName + "teacher/user/devote/check";
					if(data.is_share==true){
						alertContent = '上传成功！<br />您可以在个人中心<a href="'+devoteUrl+'" target="_blank">我的贡献</a>栏目中查看审核进度。';
						btn = [
                            {
                                name: '我的文件',
                                callback: function () {
                                    window.location.href=data.mine_file_url;
                                },
                                focus: true
                            },
                            {
                                name: '继续上传',
                                callback: function () {
                                    window.location.reload();
                                }
                            }
                        ];
					}else{
						alertContent = "上传成功！";
						btn = [
                            {
                                name: '我的文件',
                                callback: function () {
                                    window.location.href=data.mine_file_url;
                                },
                                focus: true
                            },
                            {
                                name: '继续上传',
                                callback: function () {
                                    window.location.reload();
                                }
                            }
                        ];
					}
                    $.tiziDialog({
                        id: '',
                        title:'提示',
                        content:alertContent,
                        icon:'',
                        width:600,
                        button: btn,
                        ok:false
                    });
                }else{
                    $.tiziDialog({'content':data.error});
                }
                
            }
        });
        _Form.addRule([
            {
                ele:"select:last",
                datatype:"*",
                nullmsg:"请选择文件夹",
                errormsg:"请选择文件夹"
            },
            {
                ele:".to_dir_id",
                datatype:"*",
                nullmsg:"请选择目录",
                errormsg:"请选择目录"
            },
            {
                ele:".to_type",
                datatype:"*",
                nullmsg:"请选择分类",
                errormsg:"请选择分类"
            }
        ]);
    }
    exports.setHiddenValue = function (){
        var textView = $(".moveFile .textView");

        textView.find("a").live('click',function(){
            var TypeTree =$("#"+$(this).parents(".moveFile").attr("id"));
            var source = $(this).data("source");
            $(this).parent().find("a").removeClass("active");
            $(this).addClass("active");
            switch (source){
                case "node":
                    $("input[name='to_dir_id']").val($(this).data("nselect"));
					if($(".file-type").is(":hidden")){
						$(".file-type").show();
					}
                    break;
                case "type":
                    $("input[name='type']").val($(this).data("type"));
                    break;
                default:
                     break;
            }
        });
    };
	
	exports.cancleUpload = function (){
		$(".cancleUpload").live('click',function(){
			window.location.reload();
		});
	}

    var baseAjaxUrl = baseUrlName + 'lesson/lesson_cloud/';
    //ajax 上传目录级联加载
    exports.getSubjecType = function(){
            $('.s_grade').on('change',function(){
                var obj_ele = $(this);
                var values = obj_ele.val();
                var base_uri = baseAjaxUrl+'ajax_subject';
                var getData = {'grade_id':values,'ver':(new Date).valueOf()};
                $.tizi_ajax({url: base_uri,
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        if(data.error_code == true){
                            $('.s_subject').html(data.error);
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        };

    exports.getAjaxVersion = function(){
            $('.s_subject').on('change',function(){
                var obj_ele = $(this);
                var values = obj_ele.val();
                var base_uri =  baseAjaxUrl+'ajax_version_msg';
                var getData = {'subject_id':values,'doc_upload':1,'ver':(new Date).valueOf()};
                $.tizi_ajax({url: base_uri,
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        if(data.error_code == true){
                            $('.s_version').html(data.course);
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        }; 

    exports.getAjaxGrades = function(){
            $('.s_version').on('change',function(){
                var obj_ele = $(this);
                var values = obj_ele.val();
                var base_uri =  baseAjaxUrl+'ajax_get_grades';
                var getData = {'v':values,'ver':(new Date).valueOf()};
                $.tizi_ajax({url: base_uri,
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        if(data.error_code == true){
                            $('.s_stage').html(data.html);
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        };       
    exports.getAjaxNodes = function(){
            $('.s_stage').on('change',function(){
                var obj_ele = $(this);
                var values = obj_ele.val();
                var base_uri =  baseAjaxUrl+'ajax_get_nodes';
                var getData = {'grade':values,'ver':(new Date).valueOf()};
                $.tizi_ajax({url: base_uri,
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        if(data.error_code == true){
                            $("#moveCatalogueList").html(data.html);
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        };     
});