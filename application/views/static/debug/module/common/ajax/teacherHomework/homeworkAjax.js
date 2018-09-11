// 班级管理
define(function(require, exports) {
    //var showLogin = require("module/common/basics/common/showLogin");
    exports.assign = function(data){
        // console.log("assgin");
        if(data.errorcode==true){
            var icon = 'succeed';
        }else{
            var icon = null;
        }
        $.tiziDialog({
            content : data.error,
            icon : icon,
            ok : function(){
                if(data.errorcode){
                    window.location.href = data.url;
                }else{
                    // window.location.reload();
                }
            },
            close:function(){}
        });
    };

    exports.assign_paper = function(data){
        if(data.errorcode==true){
            var icon = 'succeed';
        }else{
            var icon = null;
        }
        $.tiziDialog({
            content : data.error,
            icon : icon,
            ok : function(){
                if(data.errorcode){
                    // console.log(data);
                    window.location.href = data.res.url;
                }
            },
            close:function(){
                
            }
        });
    };
});