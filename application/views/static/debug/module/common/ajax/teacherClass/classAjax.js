// 班级管理
define(function(require, exports) {
    var showLogin = require("module/common/basics/common/showLogin");
    exports.common = function(data){
		if (data.code == 1){
			$.tiziDialog({
                content : data.msg,
                icon : 'succeed',
                ok : function(){
                    window.location.href = data.redirect;
                }
            });
		} else if (data.code == -999){
            showLogin.show_login();
        } else {
            $.tiziDialog({content:data.msg});
        }
	};
    
    // 邀请加入
    exports.invite = function(data){
        if (data.code == 1){
            window.location.href = data.redirect;
        } else if (data.code == -999){
            showLogin.show_login();
        } else {
            $.tiziDialog({content:data.msg});
        }
    };
    // 解散班级
    exports.deleteClass = function(data){
        if (data.code == 1){
            $.tiziDialog.list['deleteClassId'].close();
                $.tiziDialog({
                    content : data.msg,
                    icon : 'succeed',
                    ok : function(){
                        window.location.reload(); 
                    }
                });
            } else if (data.code == -999){
                showLogin.show_login();
            } else {
                $.tiziDialog({content:data.msg});
            }
    };
    // 给我的学生创建帐号
    exports.createStudent = function(data, type){
        if (data.code == 1){
            //$.tiziDialog.list['uploadStudentList'].close();
            var st = data.students;
            ga('send', 'event', 'CreateStudent-'+type, 'CreateStudent', type, data.students.length);
            $.tiziDialog({
                content : data.msg + '<br />请将学号和密码告知您的学生，尽快登录。',
                icon : 'succeed',
                ok : function(){
                    var tr = '';
                    for (var i = 0; i < data.students.length; ++i){
                        var atr = $("#crtemplate").html();
                        atr = atr.replace("%student_name%", st[i].student_name);
                        atr = atr.replace("%student_id%", st[i].student_id);
                        atr = atr.replace("%student_id%", st[i].student_id);
                        atr = atr.replace("%password%", st[i].password);
                        tr += atr;
                    }
                    // 判断ie6 7，解决append tr的bug
                    if($.browser.msie&&($.browser.version == "7.0") || $.browser.msie&&($.browser.version == "6.0")){
                        $(".myallClass_student").append('<tr>' + tr + '</tr>');
                        $(".myallClass_student tr").each(function(){
                            if($(this).html() ==''){
                                $(this).remove();
                            }
                        })
                    }else{
                        $(".myallClass_student").append(tr);
                    }
                    var student_total = parseInt($("#student_total").text());
                    $("#student_total").text(student_total+data.students.length);
                    $("#down_student").css("display", "inline-block");
                    $('#havestudentdiv').css('display', 'block');
                    $("#clsaddbar").css("display", "inline-block");
                    $(".emptyNotice").css("display", "none");
                    $('.dlbtn').removeClass('iniBtnStyle');
                    $('.dlbtn').addClass('cBtnSilver');
                    var link = $('.dlbtn').attr('data');
                    $('.dlbtn').attr('href', link);
                }
            });
        } else if (data.code == -999){
            showLogin.show_login();
        } else {
            $.tiziDialog({content:data.msg});
        }
    };
    //老师填用户名学号等方式把学生拉入班级
    exports.teacherInputStudent = function(data){
        if (data.code == 1){
            //$.tiziDialog.list['uploadStudentList'].close();
            var st = data.students;
            $.tiziDialog({
                content : data.msg,
                icon : 'succeed',
                ok : function(){
                    var tr = '';
                    for (var i = 0; i < data.students.length; ++i){
                     var atr = $("#crtemplate").html();
                     atr = atr.replace("%student_name%", st[i].student_name);
                     atr = atr.replace("%student_id%", st[i].student_id);
                     atr = atr.replace("%student_id%", st[i].student_id);
                     atr = atr.replace("%password%", st[i].password);
                     atr = atr.replace("学生还未激活帐号", st[i].lastactive);
                     tr += atr;
                    }
                    // 判断ie6 7，解决append tr的bug
                    if($.browser.msie&&($.browser.version == "7.0") || $.browser.msie&&($.browser.version == "6.0")){
                     $(".myallClass_student").append('<tr>' + tr + '</tr>');
                     $(".myallClass_student tr").each(function(){
                         if($(this).html() ==''){
                             $(this).remove();
                         }
                     })
                    }else{
                     $(".myallClass_student").append(tr);
                    }
                    var student_total = parseInt($("#student_total").text());
                    $("#student_total").text(student_total+data.students.length);
                    if(data.code == 1){
                        window.location.href = window.location.href;
                    }
                }
            });
        } else if (data.code == -999){
            showLogin.show_login();
        } else {
            $.tiziDialog({content:data.msg});
        }
    };
    // 修改班级名称
    exports.alterClassGrade = function(data){
        if (data.code == 1){
            $.tiziDialog.list['alterClassGradeId'].close();
            $.tiziDialog({
                content : data.msg,
                icon : 'succeed',
                ok : function(){
                    var _name = data.name;
                    var _class_grade = $("#class_grade").find("option[value='"+data.class_grade+"']").text();
                    $('#ClassGrade').text(_name);
                    $('#class_grade_name').text(_class_grade);
                    $('#lft_'+$('#class_id').val()).text(_class_grade+_name);
                    $('#cur_class_grade').val(data.class_grade);
                }
            });
        } else if (data.code == -999){
            showLogin.show_login();
        } else {
            $.tiziDialog({content:data.msg});
        }
    };
    // 解散班级
    exports.deleteClass = function(data){
        if (data.code == 1){
            $.tiziDialog.list['deleteClassId'].close();
			$.tiziDialog({
				content : data.msg,
				icon : 'succeed',
				ok : function(){
					window.location.reload(); 
				}
			});
		} else if (data.code == -999){
			showLogin.show_login();
		} else {
			$.tiziDialog({content:data.msg});
		}
    };
    
    // 接受邀请
    exports.acceptInvite = function(data){
		if (data.code == 1){
			$.tiziDialog({
				content : data.msg,
				icon : 'succeed',
				close : function(){
					window.location.href = data.redirect;
				}
			});
		} else {
			$.tiziDialog({
				content:data.msg,
				close : function(){
					if (data.redirect){
						window.location.href = data.redirect;
					}
				}
			});
		}
	};
});
