define(function(require, exports) {
    require('tiziDialog');
    exports.bindSubmit = function(data){
		if(data.errorcode){
            if(data.redirect) window.location.href=data.redirect;
        }else{
            $.tiziDialog({content:data.error});
        }
	};
});