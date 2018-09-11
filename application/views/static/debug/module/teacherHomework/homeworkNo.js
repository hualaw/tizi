// 这是老师无班级下的页面脚本文件
// author shanghongliang
// date 2014-07-30 9:14
define(function(require,exports){
	//引用dialog
	require('tiziDialog');
	exports.init=function(){
		//游戏图片click事件绑定
		$(".gameListBox .gameList").click(function(){
			//弹框
            var unit_id = $(this).attr('unit_id');
            var game_id = $(this).attr('game_id');
            var data = '<iframe src="'+baseUrlName+'class/game/preview/'+unit_id+'/'+game_id+'" width="700" height="500" marginwidth="0" marginheight="0" scrolling="no" frameborder="0"></iframe>';
            $.tiziDialog({
                icon:null,
                title:'体验作业',
                content: data,
                width:700,
                height:500,
                ok:false
            });
		});
	}
});
