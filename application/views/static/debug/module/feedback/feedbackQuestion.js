/**
 * Created by 91waijiao on 14-5-4.
 */
define(function (require,exports) {
    require("flashUploader");
    require("cookies");
    require("json2");
    //添加纠错功能
    exports.haveError = function(){
        exports.haveErrorUploadImages();
        //添加验证
        require("module/common/basics/feedback/feedbackQuestion/valid").addPaperHaveErrorValid();
    }
    //纠错上传图片
    exports.haveErrorUploadImages = function(){
        var that = this;
        var fbupload = $(".haveErrorForm").find(".fbupload"); //替换防止反复提取
        fbupload.each(function(){
            var node = $(this);
            var loading = staticUrlName+'image/answerquestion/loading.gif';
            var upload_id = node.attr('id');
            $('#'+upload_id).uploadify({
                'formData' : $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post'),
                'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                'uploader' : baseUrlName+'upload/feedback?id='+upload_id, //需要上传的url地址
                'multi'    : false,
                'preventCaching':true,
                'buttonClass': 'choseFileBtn',
                'buttonText' :"上传图片",
                'fileTypeExts' : '*.jpg; *.png;*.gif',
                'fileSizeLimit' : '2048KB',
                'fileObjName' : upload_id,
                'button_image_url':baseUrlName+'quit',
                'width'           :102,
                'height' :28,
                'overrideEvents': ['onSelectError','onDialogClose'],
                onUploadStart:function(file){
                    $('.haveErrorForm .'+upload_id).html('<img src="'+loading+'" class="imgloading"/>');
                },
                onSWFReady:function(){
                },
                onFallback : function() {
                    $('.imgTips').html(noflash);
                },
                onSelectError : function(file, errorCode, errorMsg) {
                    switch (errorCode) {
                        case -110:
                            $.tiziDialog({content:"文件 [" + file.name + "] 过大！每张图片不能超过2M"});
                            break;
                        case -120:
                            $.tiziDialog({content:"文件 [" + file.name + "] 大小异常！不可以上传大小为0的文件"});
                            break;
                        case -130:
                            $.tiziDialog({content:"文件 [" + file.name + "] 类型不正确！不可以上传错误的文件格式"});
                            break;
                    }
                    return false;
                },
                onUploadSuccess: function(file, data, response) {
                    var json = JSON.parse(data);
                    var upload_id_img = $('.haveErrorForm .'+upload_id);
                    if(json.code == 1){
                        var img_path = json.img_path;
                        that.drawImage(img_path,92,71,upload_id);
                        upload_id_img.removeClass('red');
                        upload_id_img.addClass('upladpicSpan').removeClass("upladpic");
                    }else{
                        upload_id_img.html('<b>'+json.msg+'</b>');
                        upload_id_img.addClass('red');
                    };
                    upload_id_img.siblings('.clearpic').show();//显示删除图标
                    upload_id_img.siblings('.clearpic').on('click',function(){
                        upload_id_img.find("img").remove();
                        upload_id_img.siblings('.clearpic').hide();//隐藏删除图标
                        upload_id_img.removeClass('red');
                        upload_id_img.removeClass('upladpicSpan').addClass("upladpic");;//移除背景图片
                    });
                },
                onUploadComplete: function(file) {
                }
            });
        });
    }
    exports.drawImage = function(src,width,height,upload_id){
        var image=new Image();
        var Img = new Image();//返回值
        image.src=src;
        image.onload = function(){
            if(image.width>width||image.height>height){
                //现有图片只有宽或高超了预设值就进行js控制
                w=image.width/width;
                h=image.height/height;
                if(w>h){
                    //宽比高大
                    //定下宽度为width的宽度
                    Img.width=width;
                    //以下为计算高度
                    Img.height=image.height/w;
                }else{
                    //高比宽大
                    //定下宽度为height高度
                    Img.height=height;
                    //以下为计算高度
                    Img.width=image.width/h;
                }
            }else{
                h=image.height/height;
                Img.width=image.width/h;
                Img.height=height;
            }
            $('.haveErrorForm .'+upload_id).html('<img src="'+src+'" class="picture_urls" width="'+Img.width+'" height="'+Img.height+'"/>');
        }
    }
});