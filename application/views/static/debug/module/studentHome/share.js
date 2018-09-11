define(function(require,exports){
    require("tizi_ajax");
    require("tiziDialog");
    var Teadownload = require("tizi_download");

    //点赞分享文件
    exports.love_share = function(){
        $('.zan').live('click',function(){
            var share_id = $(this).attr('share_id');
            var that = $(this);
            $.tizi_ajax({
                'url' : baseUrlName + 'student/cloud/love',
                'type' : 'POST',
                'dataType' : 'json',
                'data' : {'share_id' :share_id},
                success : function(json){
                    if(json.errorcode==1){
                        var love = Number(that.parents().find('.get_love').attr('value'))+1;
                        that.parent().find('.get_love').text('获赞('+love+')');
                        that.remove();
                    }else{
                        $.tiziDialog({content:json.error});
                    }
                },
                error : function(){
                    $.tiziDialog({content:'系统繁忙，请稍后再试'});
                }
            });
        });
    };

    /*班级分享, 转换中的文档给提示*/
    exports.check_pfop = function(page){
        $('.check_pfop').live('click',function(){
            var file_id = $(this).attr('file_id');
            var share_id = $(this).attr('share_id');
            var url = baseUrlName + 'student/student_resource/check_pfop/' + file_id;
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
                        var to_url = baseUrlName + "student/cloud/file/"+share_id;
                        window.open(to_url,"_blank");
                    }
                }
            });
            
        });
    };

    
});
