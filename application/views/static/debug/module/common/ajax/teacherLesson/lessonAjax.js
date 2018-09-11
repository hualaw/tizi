// 网盘ajax
define(function(require, exports) {

	//分享文件
	exports.changeFile = function(data){
        $.tiziDialog.list['changeFile_id'].close();
		if (data.errorcode == true){
            $.tiziDialog({
                content : data.error,
                icon : 'succeed',
                ok : function(){
                }
            });
        }else {
            $.tiziDialog({content:data.error});
        }
	};
    // 移动文件验证
    exports.moveFileTreeAjax = function(data){
        if (data.errorcode == 1){
            $.tiziDialog.list['moveFilePopDialog'].close();
            $.tiziDialog({
                content : data.error,
                icon : 'succeed',
                ok : function(){
                    if(data.errorcode==true){
                        // window.location.href = data.url;
                        window.location.reload();
                    }
                }
            });
        }else {
            $.tiziDialog({content:data.error});
        }
    }
    // 移动文件夹验证
    exports.moveDirTreeAjax = function(data){
        if (data.errorcode == 1){
            $.tiziDialog.list['moveDirPopDialog'].close();
            $.tiziDialog({
                content : data.error,
                icon : 'succeed',
                ok : function(){
                    if(data.errorcode==true){
                        // window.location.href = data.url;
                        window.location.reload();
                    }
                }
            });
        }else {
            $.tiziDialog({content:data.error});
        }
    }

});