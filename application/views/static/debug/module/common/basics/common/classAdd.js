/*
 * 备注：此文件包括班级空间登录前和登录后的添加班级
 */
define(function(require, exports) {
    var classAddValid = require('module/common/basics/common/classAddValid');
    // 加入和创建班级
    exports.addNewClassDialog = function(){
        if($('.addNewClass').length > 0){
            $('.addNewClass').live('click',function(){
                $.ajax({
                    'url' : baseUrlName + 'class/create/last_info',
                    'type' : 'GET',
                    'dataType' : 'json',                    
                    success : function(json, status){
                        if (json.class_grade > 0){
							$('#class_grade').val(json.class_grade);
						}
						if (json.subject_type_id > 0){
							$('.teacher_subject').val(json.subject_type_id);
						}
						if (json.school_id > 0){
							$('#schoolVal').val(json.school_id);
							var sfn = json.school_info.province;
							if (json.school_info.city){
								sfn += json.school_info.city;
							}
							if (json.school_info.county){
								sfn += json.school_info.county;
							}
							if (json.school_info.school){
								sfn += json.school_info.school;
							}
							$('.schoolFullName').html(sfn);
						}
						if (json.school_define_id > 0){
							$("#schoolVal").val("");
							$("#area_county_id").val(json.school_info.county_id);
							$("#school_type").val(json.school_info.sctype);
							$("#schoolname").val(json.school_info.school);
							var sfn = json.school_info.province;
							if (json.school_info.city){
								sfn += json.school_info.city;
							}
							if (json.school_info.county){
								sfn += json.school_info.county;
							}
							if (json.school_info.school){
								sfn += json.school_info.school;
							}
							$('.schoolFullName').html(sfn);
						}
                    }
                });
                $.tiziDialog({
                    id:"addClassGradeId",
                    title:'添加新班级',
                    content:$('#inVislbleClass').html().replace('inVislbleClassForm_beta','inVislbleClassForm').replace('creatNewClassForm_beta','creatNewClassForm'),
                    icon:null,
                    width:600,
                    ok:false
                });
                // 前端验证
                classAddValid.invite();
                classAddValid.creat();
            }); 
        }else{
            // 前端验证
            classAddValid.invite();
            classAddValid.creat();
        }
    };
});