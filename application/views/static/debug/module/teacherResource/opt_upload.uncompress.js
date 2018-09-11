define(function(require,exports){
    /**
     * 同步/知识点目录  res_dir  文件上传
     * @constructor
     */
    exports.dropBoxFileList = function(){
        var isIE = !!window.ActiveXObject;
        var isIE6 = isIE && !window.XMLHttpRequest;
        $('.dropBoxup').live('click',function(){
            var res_type = $(this).data("res_type");//获取文件目录Id
            var json = {'res_type':res_type};
            $.tiziDialog({
                id:'fileListUploadPop',
                title:'上传文件',
                content:$('#fileListUploadPop').html(),
                icon:null,
                dblclick:false,
                esc:false,
                width:595,
                init:function(){
                    $(".teacherResource .fileListTip").show();
                    $(".teacherResource .fileListQueue").html("");
                    var btn_ok = $(".aui_footer .aui_state_highlight");
                    btn_ok.hide();
                    $('.share_to_tizi_ids').val("");
                    if(json.res_type!="" && isIE6){
                        exports.uploadifyFile(json);
                    }
                },
                ok:function(){
                    $("#shareFileUp").uploadify('destroy');
                    //if($.browser.mozilla && $.browser.version == '11.0'){$("#shareFileUp").uploadify('destroy');}
                    var is_check=$('input:radio[name="share"]:checked').val();
                    $(".fileListUploadForm .fileShare").val(is_check);//注入是否勾选
                    var dir_cat_id = $(".dir_cat_id").val();// var sub_cat_id = $(".sub_cat_id").val();// var dir_id = $(".dir_id").val();
                    var file_ids = $('.share_to_tizi_ids').val();
                    if(file_ids==""){
                        $.tiziDialog.list["fileListUploadPop"].close();
                        window.location.reload();
                    }
                    if(is_check&&file_ids!=""){
                        // var json_obj = {'file_ids':file_ids,"is_check":is_check,"dir_cat_id":dir_cat_id,"sub_cat_id":sub_cat_id,"dir_id":dir_id};
                        var json_obj = {'file_ids':file_ids};
                        $.tizi_ajax({
                            'url' : baseUrlName + 'resource/share_to_tizi/add',
                            'type' : 'POST',
                            'dataType' : 'json',
                            'data' : json_obj,
                            'async':false,
                            success : function(data){
                                if (data.error_code == true){
                                    window.top.location.reload();
                                }else {
                                    $.tiziDialog({
                                        content:"添加成功",
                                        icon:'succeed',
                                        ok:function(){
                                            window.location.reload();
                                        },
                                        close:function(){
                                            window.location.reload();
                                        }
                                    });
                                }
                            },
                            error : function(){
                                $.tiziDialog({content:'系统忙，请稍后再试'});
                            }
                        });
                    }
                },
                cancel:function(){
                    if(isIE6){window.location.reload();}
                    $("#shareFileUp").uploadify('destroy');
                }
            });
            if(json.res_type!="" && !isIE6){
                exports.uploadifyFile(json);
            }
        });
    };
    /**
     * 上传文件单体实例
     * @param json
     */
    exports.uploadifyFile = function(json){
        var flag = false;
        var checkFlag = false;
        var queueJsonStr = "";
        var queueNum = 0;
        var docExt = '*.doc;*.docx;*.ppt;*.pptx;*.xls;*.xlsx;*.wps;*.et;*.dps;*.pdf;*.txt;';
        var allowType = '*.*';
        var cur_dir_id = json.cur_dir_id;
        var shareFileUp = $("#shareFileUp");
        var cFileObj = $('.current_dir_id');
        var dir_cat_id = $(".dir_cat_id").val();
        var sub_cat_id = $(".sub_cat_id").val();
        var dir_id = $(".dir_id").val();
        var res_type = json.res_type;
        seajs.use(['flashUploader','cookies'],function(){
            shareFileUp.uploadify({
            'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf?var='+(new Date()).getTime(),
            'uploader' : 'http://up.qiniu.com',//需要上传的url地址
            'buttonClass'     : 'choseFileBtn',
            'button_image_url':staticBaseUrlName+staticVersion+'image/teacherResource/placeBtn.png',
            'buttonText' :"&nbsp;&nbsp;&nbsp;",
            'fileTypeExts' : allowType,
            'fileSizeLimit' : '200MB',
            'fileObjName' : 'file',
            'multi'	: true,
            'width' : 120,
            'height' :40,
            'successTimeout' : 7200,
            'uploadLimit' : 5,
            'overrideEvents': ['onSelectError','onDialogClose','onUploadProgress','onCancel','onUploadSuccess'],
            onSWFReady:function(){},
            onDialogClose:function(queueData){
                queueNum = queueData.queueLength;
            },
            onSelect:function(file){
                var cFileName = cFileObj.data('fname');
                if(!cFileName){
                    cFileName = '';
                }
                var praseName = file.name;
                if(praseName.length>30)praseName = praseName.substring(0,30) + "...";
                var praseSize = exports.praseFileSize(file.size);
                queueJsonStr +="{\"queue_id\":\""+file.index+"\",\"file_id\":\""+file.id+"\",\"file_name\":\""+praseName+"\",\"size\":\""
                    +praseSize+"\",\"cdir\":\""+cFileName+"\",\"status\":\""+file.filestatus+"\"},";
                //shareFileUp.startUpload();
            },
            onFallback : function() {
                $('.choseFile').html(noflash);
                $('#upDropFile').hide();
            },
            onSelectError : function(file, errorCode, errorMsg) {
                switch (errorCode) {
                    case -100:
                        $(".choseFile").find(".error").remove();
                        $.tiziDialog({content:"每次最多上传5个文件"});
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
                $(".aui_buttons [type='button']").eq(1).attr("disabled","true").text("上传中...");
                $(".aui_close").hide();
                if(file.type != "" && docExt.indexOf(file.type) != -1){
                    //oss上传
                    var formData = $.tizi_token({'res_type':res_type,"dir_cat_id":dir_cat_id,"sub_cat_id":sub_cat_id,"dir_id":dir_id,'cur_dir_id':cur_dir_id,'session_id':$.cookies.get(baseSessID)},'post');
                    shareFileUp.uploadify("settings", "formData", formData);
                    shareFileUp.uploadify("settings", "uploader", baseUrlName+'upload/cloud');
                    shareFileUp.uploadify("settings", "fileObjName", "shareFileUp");
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
                    shareFileUp.uploadify("settings", "fileObjName", 'file');
                    shareFileUp.uploadify("settings", "uploader", 'http://up.qiniu.com');
                    var formData = {'token':fileToken,'key':fileKey,'cur_dir_id':cur_dir_id,'old_name':oldFileName,'file_size':fileSize};
                    shareFileUp.uploadify("settings", "formData", formData);
                }
                if(flag === false && queueJsonStr != ""){
                    queueJsonStr=queueJsonStr.substring(0,queueJsonStr.length-1);
                    queueJsonStr = "{\"files\":["+queueJsonStr+"]}";
                    var myobj = JSON.parse(queueJsonStr);
                    var listTemp = $('#upFilePop').html();
                    var alertContent = Mustache.to_html(listTemp,myobj);
                    $(".fileListQueue").html(alertContent);
                    $("#upDropFile").hide();
                    $(".fileListTip").hide();
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
                        cFileObj.find(".complete").html(percentage+'%');
                        cFileObj.find(".UploadCancel").html("x");
                    }
                }
                if(bytesUploaded == bytesTotal){
                    cFileObj.find(".UploadCancel").html("");
                    cFileObj.find(".UploadCancel").attr("href","javascript:void(0);");

                }
            },
            onCancel: function(file) {
                var cFileIndex = file.index;
                var cFileObj = $("#file_num_"+cFileIndex);
                cFileObj.remove();
                if(queueNum == 1){
                    flag =false;
                    queueJsonStr = "";
                }
            },
            onUploadSuccess: function(file, data, response) {
                queueJsonStr = "";
                var cFileIndex = file.index;
                var cFileObj = $("#file_num_"+cFileIndex);
                var json = JSON.parse(data);
                if(json.key){
                    //qiniu上传
                    var persistentId = 0;
                    if(json.persistentId != undefined){
                        persistentId = json.persistentId;
                    }
                    $.tizi_ajax({
                        'url' : baseUrlName + 'cloud/cloud/qiniu_upload',
                        'type' : 'POST',
                        'dataType' : 'json',
                        'data' : {'res_type':res_type,"dir_cat_id":dir_cat_id,"sub_cat_id":sub_cat_id,"dir_id":dir_id,'cur_dir_id':cur_dir_id,'key':json.key,'file_name':file.name,'file_size':file.size,'persistent_id':persistentId},
                        success : function(data){
                            if (data.error_code == true){
                                cFileObj.find(".complete").html("100%");
                                cFileObj.find('strong').css({'width': '100%'});
                                if(queueNum == 1){
                                    flag =false;
                                }else if(queueNum > 1 && cFileIndex == queueNum-1){
                                    flag =false;
                                }
                                //上传成功后，写到$('.share_to_tizi_ids').val()，共享给tizi网
                                var ids = $('.share_to_tizi_ids').val();
                                // console.log(data.new_file_id);
                                checkFlag = true;
                                $('.share_to_tizi_ids').val(ids+','+data.new_file_id);
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
                        }else if(queueNum > 1 && cFileIndex == queueNum-1){
                            flag =false;
                        }
                        //上传成功后，写到$('.share_to_tizi_ids').val()，共享给tizi网
                        var ids = $('.share_to_tizi_ids').val();
                        checkFlag = true;
                        $('.share_to_tizi_ids').val(ids+','+json.new_file_id);
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
                    setTimeout(function(){window.top.location.reload();}, 1000);
                }
            },
            onUploadComplete:function(){
                var btn_ok = $(".aui_footer .aui_state_highlight");
                btn_ok.show();
                $(".aui_close").hide();
                $(".aui_buttons [type='button']").eq(1).hide();
//                $("#shareFileUp").uploadify('destroy');
            }
        });
        });
    };
    // 上传错误
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

});