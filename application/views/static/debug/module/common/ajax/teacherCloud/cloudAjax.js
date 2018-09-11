// 网盘ajax
define(function(require, exports) {
	// 新建文件夹
	exports.creat = function(data){
		if (data.errorcode == 1){
			$.tiziDialog.list['create_new_fileId'].close();
			$.tiziDialog({
				content : data.error,
				icon : 'succeed',
				ok : function(){
				},
				close:function(){
					window.location.reload();
				}
			});
		}else {
			$.tiziDialog({content:data.error});
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
	// 重命名文件
	exports.resetFile = function(data){
		if (data.errorcode == 1){
			$.tiziDialog.list['resetFile_id'].close();
            var file_id = data.file_id;
            var file_li = $('li[df_id='+file_id+']');
            //提取后缀
            var ext = file_li.find('label.ftitle').attr('file-ext');
            if(ext){ext = '.'+ext;}else{ext = '';}
            //修改删除文件
            var is_file = file_li.find('.btnList a.resetFlieName').attr('is_file');
            var name = data.name;
            if(is_file){
                name = data.name+ext;
            }
            file_li.find('label.ftitle').text(name);
            file_li.find('label.ftitle').attr('title',name);
            file_li.find('a.downloadFile').attr('file_name',name);
            file_li.find('a.resetFlieName').attr('file_name',data.name);
			$.tiziDialog({
				content : data.error,
				icon : 'succeed',
				ok : function(){
					// window.location.href = data.redirect;
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
    //共享文件
    exports.shareAllAjax = function(data){
        if (data.errorcode == 1){
            $.tiziDialog.list['shareAll_id'].close();
            $.tiziDialog({
                content : data.error,
                icon : 'succeed',
                ok : function(){
                    if(data.errorcode==true){
                        // window.location.href = data.url;
                    }
                }
            });
        }else {
            $.tiziDialog({content:data.error});
        }
    }

});