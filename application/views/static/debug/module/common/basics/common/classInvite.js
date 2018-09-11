/*
 * 备注：
 */
define(function(require, exports) {
    
    require('tizi_ajax');
    
    require('tiziDialog');
    
    // 加入和创建班级
    exports.accept = function(){
        $('#accept').live('click',function(){
			$.tizi_ajax({
				'url' : baseUrlName + 'class/invite/accept',
				'data' : {
					'class_id' : $('#class_id').val(),
					'redirect' : 'callback:classInvite'
				},
				'type' : 'POST',
				'dataType' : 'json',
				success : function(json, status){
					seajs.use('module/common/ajax/teacherClass/classAjax', function(ex){
						ex.acceptInvite(json);
					});
				}
			});
		});
    };
});