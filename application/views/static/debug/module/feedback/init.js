// 这是反馈页面调用脚本
define(function(require){
	var _pagename = $('.mainContainer').attr('pagename');
	switch(_pagename){
        // 页面是纠错
        case 'feedbackQuestion':
        seajs.use('module/feedback/feedbackQuestion',function(ex){
            ex.haveError();
        });
        break;
	}
});