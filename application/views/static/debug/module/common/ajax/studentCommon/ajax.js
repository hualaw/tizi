// 学生首页
define(function(require, exports) {
    // 加入新班级
    exports.studentAddClass= function(data){
        if (data.code == 1){
            //console.log(data);
            var content = $('#addNewClassuccWin').html();
            content = content.replace('%classname%', data.classname);
            content = content.replace('%student_id%', data.student_id);
            $.tiziDialog({
                content:content,
                icon:'succeed',
                ok:function(){
                    window.location.reload();
                },
                close:function(){
                    window.location.reload();
                }
            });
        } else {
            $.tiziDialog({content:data.msg});
        }
    };
});