define(function(require,exports){
	// 首页登录结果
	exports.indexSubmit=function(data){
		if(data.errorcode){
			if(data.redirect == 'reload'){
                window.location.reload();
            }else if(data.redirect){
				window.location.href=data.redirect;
			}
		}else{
			// 请求dialog插件
			require.async("tiziDialog",function(){
				$.tiziDialog({content:data.error});
			});
		}
	};
});