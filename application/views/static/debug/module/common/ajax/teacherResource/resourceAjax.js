// 资源库验证脚本
define(function(require, exports) {
    var showLogin = require("module/common/basics/common/showLogin");
    // 新建同步章节验证
    exports.chapterIndexAjax = function(data){
        $.tiziDialog.list['chapterDialog'].close();
        var text ="";
        if(data.errorcode==true){
            var icon = 'succeed';
            text = "操作成功";
        }else{
            var icon = 'error';
            text = "操作失败";
        }
        $.tiziDialog({content:text,icon:icon,ok:function(){
            // alert(data.cat_id);
            if(data.cat_id != undefined){
                // alert(1);
                window.location.href = data.cat_id;
            }else{
                // alert()
                window.location.reload();
            }
        },close:function(){
            // window.location.reload();
        }});
    }
    // 新建同步知识点验证
    exports.loreIndexAjax = function(data){
        $.tiziDialog.list['loreDialog'].close();
        var text ="";
        if(data.errorcode==true){
            var icon = 'succeed';
            text = "操作成功";
        }else{
            var icon = 'error';
            text = "操作失败";
        }
        $.tiziDialog({content:text,icon:icon,ok:function(){
            // alert(data.cat_id);
            if(data.cat_id != undefined){
                // alert(1);
                window.location.href = data.cat_id;
            }else{
                window.location.reload();
            }
        },close:function(){
            // window.location.reload();
        }});
    }
    exports.havenovIndexAjax = function(data){
        $.tiziDialog.list['havenovDialog'].close();
        if(data.errorcode==true){
            var icon = 'succeed';
        }else{
            var icon = null;
        }
        $.tiziDialog({content :"您的反馈已成功提交，我们将尽快处理。",icon : icon,time:1});
    };
    // 新建文件夹
    exports.create = function(data){
        if (data.errorcode == true){
            $.tiziDialog({content : data.error,icon : 'succeed',time :1});
            window.location.reload();
        }else {
            $.tiziDialog({content:data.error,time :1});
        }
    };
    // 分享文件
    exports.shareFile = function(data){
        if (data.errorcode == 1){
            $.tiziDialog.list['shareFile_id'].close();
            $.tiziDialog({
                content : data.error,
                icon : 'succeed',
                ok : function(){
                    if(data.errorcode==true){
                        window.location.href = data.url;
                    }
                }
            });
        }else {
            $.tiziDialog({content:data.error});
        }
    };

});