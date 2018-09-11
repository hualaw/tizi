/*
 * 备注：此文件包括班级空间登录前和登录后的添加班级
 */
define(function(require, exports) {
    var classAddValid = require('module/common/basics/common/classAddValid');
    // 加入和创建班级
    exports.classSetting = function(){
        //点击省份
        $('.province li').live('click', function(){
            var _cityName = 
                // 北京
                $(this).attr('data-id') == 2 || 
                // 上海
                $(this).attr('data-id') == 25 || 
                // 天津
                $(this).attr('data-id') == 27 || 
                // 重庆
                $(this).attr('data-id') == 32 || 
                // 香港
                $(this).attr('data-id') == 33 || 
                // 澳门
                $(this).attr('data-id') == 34 || 
                // 台湾
                $(this).attr('data-id') == 35;
            if(_cityName){
                $('.city,.sctype,.school').hide();
            }else{
                $('.county,.school').hide();
            };
            if($(this).attr('class') !== 'active'){
                var id = $(this).attr('data-id');
                var ismunicipality = $(this).attr('ismunicipality');
                $.ajax({
                    'url' : baseUrlName + 'class/area?id='+id,
                    'type' : 'GET',
                    'dataType' : 'json',
                    success : function(json, status){
                        var listr = '';
                        for (var i = 0; i < json.length; ++i){
                            listr += '<li data-id="'+json[i].id+'">'+json[i].name+'</li>';
                        }
                        if (ismunicipality == 1){
                            $('.county').html(listr);
                            $('.county').fadeIn();
                        } else {
                            $('.city').html(listr);
                            $('.city').fadeIn();
                        }
                    }
                });
            }
        });
        //点击城市
        $('.city li').live('click', function(){
            if($(this).attr('class') !== 'active'){
                var id = $(this).attr('data-id');
                $.ajax({
                    'url' : baseUrlName + 'class/area?id='+id,
                    'type' : 'GET',
                    'dataType' : 'json',
                    success : function(json, status){
                        var listr = '';
                        for (var i = 0; i < json.length; ++i){
                            listr += '<li data-id="'+json[i].id+'">'+json[i].name+'</li>';
                        }
                        $('.county').html(listr);
                        $('.county').css('display', 'block');
                    }
                });
            }
        });
        //点击城镇
        $('.county li').live('click', function(){
			$.ajax({
				'url' : baseUrlName + 'class/area/sctype',
				'type' : 'GET',
				'dataType' : 'json',
				success : function(json, status){
					var listr = '';
					for (var i = 0; i < json.length; ++i){
						listr += '<li data-id="'+json[i].id+'">'+json[i].name+'</li>';
					}
					$('.sctype').html(listr);
					$('.sctype').fadeIn();
				}
			});
        });
        //点击学校
        $('.sctype li').live('click', function(){
            if($(this).attr('class') !== 'active'){
                var sctype = $(this).attr('data-id');
                var county_id = $('.aui_content .county li.active').attr('data-id');
                $.ajax({
                    'url' : baseUrlName + 'class/schools?id='+county_id+'&sctype='+sctype,
                    'type' : 'GET',
                    'dataType' : 'json',
                    success : function(json, status){
                        var listr = '';
                        for (var i = 0; i < json.length; ++i){
                            listr += '<li data-id="'+json[i].id+'">'+json[i].schoolname+'</li>';
                        }
                        $('.school').html(listr);
                        $('.school').css('display', 'block');
                    }
                });
            }
            if($(".noMySchollBtn").length>0){
                $(".noMySchollBtn").show();
            }
        });

        //设置学校点击地区效果
        $('.schooLocation li').live('click', function(){
            $(this).addClass('active').siblings().removeClass('active');
        });
        // 点击没有我的学校
        $(".noMySchollBtn").live("click",function(){
			var county = $(".schoolCounty li.active").attr("data-id");
			var sctype = $(".schoolGrade li.active").attr("data-id");          
			$.tiziDialog({id:"setSchollID"}).close();
			$(".theGenusScholl_n").removeClass("undis");
			$(".theGenusScholl_y").addClass("undis");
			$("#school_id").val("");
			$("#area_county_id").val(county);
			$("#sctype").val(sctype);
        });
        //设置学校
        $('#resetSchool').live('click',function(){
            var _this = $(this);
            $.ajax({
                'url' : baseUrlName + 'class/area?id=1',
                'type' : 'GET',
                'dataType' : 'json',
                success : function(json, status){
                    var listr = '';
                    for (var i = 0; i < json.length; ++i){
                        listr += '<li data-id="'+json[i].id+'" ismunicipality="'+json[i].ismunicipality+'">'+json[i].name+'</li>';
                    };
                    $('.province').html(listr);
                    $('.province').fadeIn();
                }
            });
            $(".noMySchollBtn").hide();
            $.tiziDialog({
                id:"setSchollID",
                title:'选择学校',
                top:100,
                content:$('#resetSchoolPop').html().replace('resetSchoolPopCon_beta', 'resetSchoolPopCon'),
                icon:null,
                width:800,
                ok:function(){
                    if($('.school li.active').length < 1){
                        return;
                    };
                     $(".theGenusScholl_n").addClass("undis");
                     $(".theGenusScholl_y").removeClass("undis");
                    var class_id = $('#class_id').val();
                    var school_id = $('.aui_content .school li.active').attr('data-id');
                    var fullname = '';
                    var province = $(".schoolProvice li.active").html();
                    var city = $(".schoolCity li.active").html();
                    var county = $(".schoolCounty li.active").html();
                    var schoolname = $(".schoolName li.active").html();
                    if (typeof city == 'undefined'){city = '';}
                    fullname = province + city + county + schoolname;
                    // 判断是否是重设学校
                    if(_this.hasClass('resetSchool')){
                         $(".theGenusScholl_n").add("undis");                        
                         $("#school").html(fullname);
                          $('#resetSchool').text('重设学校');
                        $("#schoolVal").val(school_id);
                    }else{
                        $("#schoolVal").val(school_id);
                        $("#school").html(fullname);
                        $('#resetSchool').text('重设学校');
                        if($("#schoolVal").val() > 0){
                            $('.schoolBox').find('.ValidformInfo,.Validform_checktip').hide();
                        }
                    }

                },
                cancel:true,
                close:function(){
                    $('.city,.county,.sctype,.school').hide();
                }
            });
        });
        
        //设置入学年份
        $('#resetSchoolYear').live('click',function(){
            $.tiziDialog({
                title:'选择年份',
                content:$('#resetSchoolYearPop').html(),
                icon:null,
                width:400,
                ok:function(){
                    var year = $('#set_year').val();
                    var class_id = $('#set_year').attr('classid');
                    if ($('#class_year').html() == year){
                        $.tiziDialog({
                            content : '班级入学年份修改成功.',
                            icon : 'succeed'
                        });
                    } else {
                        $.tizi_ajax({
                            'url' : baseUrlName + 'class/update_info/year',
                            'type' : 'POST',
                            'dataType' : 'json',
                            'data' : {
                                'year' : year,
                                'class_id' : class_id
                            },
                            success : function(json, status){
                                if (json.code == 1){
                                    $.tiziDialog({
                                        content : json.msg,
                                        icon : 'succeed',
                                        ok : function(){
                                            $('#class_year').html(year);
                                        }
                                    });
                                } else if (json.code == -999){
                                    showLogin.show_login();
                                } else {
                                    $.tiziDialog({content:json.msg});
                                }
                            },
                            error : function(){
                                $.tiziDialog({content:'系统忙，请稍后再试'});
                            }
                        });
                    }
                },
                cancel:true
            });
        });       
    };
});