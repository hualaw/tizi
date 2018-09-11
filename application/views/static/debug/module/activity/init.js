// 这是班级空间脚本入口文件
define(function(require){
	var _curActive=$("#tiziModule").attr("curActive");
	switch(_curActive){
		// 模块是湖南株洲问卷调查活动专题页面
		case 'surveyZhuzhou':
			seajs.use('module/activity/surveyZhuzhou/init');
		break;
        // 模块是招聘教师活动专题页面
        case 'ActiveHire':
            seajs.use('module/activity/ActiveHire/init',function(main){
                main.init();
            });
        break;
        // 模块是家长状元开奖活动专题页面
        case 'parentChampions':
            seajs.use('module/activity/champions/init');
        break;
        // 模块是河南足球专题
        case 'activityFootball':
            seajs.use('module/activity/activityFootball/init');
        break;
        // 模块是刷题
        case 'activityGaokaoShuati':
            seajs.use('module/activity/gaokaoShuati/init');
        break;
	}
	// 加载返回顶部脚本
	require('module/common/method/common/returnTop').cReturnTopfn();
});
