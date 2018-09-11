// 配置sea的路径，别名等参数
// 获取当前时间
// var myDate = new Date();
// var timestamp = myDate.getFullYear() +'' + myDate.getMonth()+1 + '' + myDate.getDate() + '' + myDate.getHours() + '' + myDate.getMinutes();
// seajs配置开始
seajs.config({
	// 基础目录
    base: staticPath,
    // 别名
    alias: aliasContent,
	// 映射
	//'map': [
	//    [ /^(.*\.(?:css|js))(.*)$/i, '$1' + staticTimestamp ]
	//  ],
	preload:["jquery","swfObject"]
})


// 调用方法
seajs.use('jquery',function(){
	// 加载梯子网公共方法入口
	seajs.use('module/tiziCommon/init');
	// 得到当前页面是哪个
	var _page = $('#tiziModule').attr('module');
	// 如果page不存在则返回
	if(!_page){return;}

	seajs.use("module/" + _page + "/init");
	return;
})
