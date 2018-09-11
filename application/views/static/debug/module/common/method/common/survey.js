define(function(require,exports){

    // 教师班级空间首页
    exports.teacherSurvey = function(){
        require('tiziDialog');
        require('cookies');
        if($.cookies.get("__joinTeacherSurvey") == null){
            $.tiziDialog({
                title:"在线调查",
                content:'尊敬的老师您好，为了向您提供更好的产品，我们特邀您参与梯子网在线问卷调查，只需一分钟即可完成，感谢您的参与！',
                icon:"survey2",
                ok:false,
                button:[
                    {
                        name:'参与调查',
                        className:'aui_state_highlight',
                        href:'http://www.diaochapai.com/survey898529',
                        target:'_blank',
                        callback:function(){
                            $.cookies.set("__joinTeacherSurvey", '1', { hoursToLive: 720 });
                        }
                    },
                    {
                        name:'残忍拒绝'
                    }
                ],
                close:function(){
                    if($.cookies.get("__joinTeacherSurvey") == null){
                        $.cookies.set("__joinTeacherSurvey", '0');
                    }
                }
            })
        }    
    }
    //  学生端首页
    exports.studentSurvey = function(){
        require('tiziDialog');
        require('cookies');
        if($.cookies.get("__joinStudentSurvey") == null){
            $.tiziDialog({
                title:"在线调查",
                content:'尊敬的同学你好，我们特邀你参与梯子网在线问卷调查，只需一分钟即可完成，感谢你的参与！',
                icon:"survey2",
                ok:false,
                button:[
                    {
                        name:'参与调查',
                        className:'aui_state_highlight',
                        href:'http://www.diaochapai.com/survey898553',
                        target:'_blank',
                        callback:function(){
                            $.cookies.set("__joinStudentSurvey", '1', { hoursToLive: 720 });
                        }
                    },
                    {
                        name:'残忍拒绝'
                    }
                ],
                close:function(){
                    if($.cookies.get("__joinStudentSurvey") == null){
                        $.cookies.set("__joinStudentSurvey", '0');
                    }
                }
            })
        }    
    }
})