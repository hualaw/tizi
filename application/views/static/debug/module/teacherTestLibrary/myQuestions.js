define(function(require,exports){
    var _valid = require("module/common/basics/teacherTestLibrary/valid");//加载验证库
    // 我的题库答案显示效果
    exports.showAnswer = function(){  
         $(".ques-body").live("click",function(){
            $(this).find(".ansBox").stop(true,true).slideToggle(200);
         });
    };
    // 我的题库新建分组
    exports.addNewGroup = function(){
        var newGroup = $('.newGroup');
        var ucDoc_nav = $('#ucDoc_nav');//唯一id
        //绑定弹出框
        newGroup.live('click',function(){
            //创建弹出框
            var dialog = $.tiziDialog({
                id:"AddDocForm",
                content:ucDoc_nav.html().replace("addDocForm_beta","addDocForm"),
                icon:null,
                title:"新建试题分组",
                ok:function(){
                    //提交form
                    $('.addDocForm').submit();
                    return false;
                },
                cancel:true
            });
            _valid.addNewGroupValid();//新建分组验证
        });
    };
    // 我的题库编辑分组
    exports.editNewGroup = function(){
        var edit_doc = $('.edit_doc');
        var ucDoc_Editor = $('#ucDoc_Editor');//唯一id
        //绑定弹出框
        edit_doc.live('click',function(){
            var that = $(this);
            //初始化分组id
            $(".group_gid").val(that.data('gid'));
            //处理值得回填
            var element = that.parents("tr").find(".group-name").find("a");
            var title = element.text();
            ucDoc_Editor.find("input[name=new_name]").attr("value",title); //注入title
            //创建弹出框
            var dialog = $.tiziDialog({
                id:"EditDocForm",
                content:ucDoc_Editor.html().replace("editDocForm_beta","editDocForm"),
                icon:null,
                title:"编辑试题分组",
                ok:function(){
                    //提交form
                    $('.editDocForm').submit();
                    return false;
                },
                cancel:true
            });
            _valid.EditNewGroupValid(that);//编辑分组验证
        });
    };
    // 我的题库删除分组
    exports.delNewGroup = function(){
        $('.del_doc').live('click',function(){
            var groupLi = $('.doc_group li').eq(1);
            var that = $(this);
            if(that.attr('disabled') == 'disabled'){return false;}
            that.attr('disabled','disabled');
            var title = that.parents("tr").find(".group-name").find("a").text();
            var content = '<div class="teacherTestLibrary"><div class="myGroupInput"><h2>是否确定删除分组：<b style="color: #ff6600;">'+title+'</b>？</h2><h2>删除分组后，' +
                '该分组中的文档将标记为未分组。</h2></div></div>';
            //post数据
            var baseuri = baseUrlName + "user/user_question/update_group";
            var subject_id = $('.subNavBox').data('subject');
            var group_id = that.data('gid');
            var post_data = {'sid':subject_id,'gid':group_id,'op_type':'delete'};
            //创建弹出框
            var dialog = $.tiziDialog({
                content:content,
                icon:null,
                title:"删除试题分组",
                ok:function(){
                    $.tizi_ajax({
                        url: baseuri,
                        type: 'POST',
                        data: post_data,
                        dataType: 'json',
                        success: function(data){
                            if(data.error_code == false) {
                                $.tiziDialog({content:data.error});
                            }else{
                                //删除行
                                that.parents("tr").remove();
                                that.parents(".grop_list").find(".paperNum").eq(0).html(data.count);
                                //groupLi.html('<span class="group_txt"><a href="javascript:void(0);" class="filter-group" data-gid="0">未选分组</a><b>('+data.count+')</b></span>');
                            }
                        }
                    });
                },
                cancel:true
            });
        });
    };
	//进入分组
	exports.myquestionPage = function(ele){
		ele = ele || 1;
		var groupID = $(".questionAddress").data("gid");
		var subjectID = $(".subNavBox").data("subject");
		var getData = {'subject':subjectID,'page':ele,'gid':groupID,'ver':(new Date).valueOf()};
		$.tizi_ajax({url: baseUrlName + "user/user_question/ajax_get_teacher_question", 
				type: 'GET',
				data: getData,
				dataType: 'json',
				success: function(data){
					if(data.errorcode == true){
						$("#ajax_content").html(data.html);
						}else{
							$.tiziDialog({content:data.error});
						}
				}
		});
	};
	exports.delQues = function(){
		var content = '<div class="md_docText"><h2 style="font-size:16px;font-weight:normal">是否确定删除此题目?</h2></div>';
			$('.del_ques').live('click',function(){
				var that = $(this);
				if(that.attr('disabled') == 'disabled'){
					return false;
				}
				that.attr('disabled','disabled');
				var subject_id = $('.subNavBox').data('subject');
				var getData = {'qid':that.data("num"),'sid':subject_id,'ver':(new Date).valueOf()};
				//创建弹出框
				var dialog = $.tiziDialog({
					content:content,
					icon:null,
					title:"删除题目",
					ok:function(){
						$.tizi_ajax({url: baseUrlName + "user/user_question/del_question", 
							type: 'GET',
							data: getData,
							dataType: 'json',
							success: function(data){
								if(data.error_code == true){
									exports.myquestionPage(1);
								}else{
									$.tiziDialog({content:data.error});
								}
							}
						});
					},
					cancel:function(){
						that.removeAttr('disabled');
					}
				});
			});
	}

    // 我的题库题目分组v20140512
    exports.groupQues = function(){
        var group_ques_box = $('#group_ques_box');
        var group_ques = $(".group_ques");
		var size = $(".csub-list option").size();
		for (var i = 0; i < size; i++) {
			$('.dataStore').data($(".csub-list option").eq(i).text(),$(".csub-list option").eq(i).val());
		}
        //绑定弹出框
        group_ques.live('click',function(){
            //创建弹出框
			$(".c-qid").val($(this).data("num"));
			$(".old-sid").val($(".subNavBox").data("subject"));
			var subjectText = $(".subNavBox").data("subtext");
			var index = $(".dataStore").data(subjectText);
			$(".csub-list option").eq(index).attr("selected",true); //赋值
            var dialog = $.tiziDialog({
                id:"GroupQuesForm",
                content:group_ques_box.html().replace("GroupQuesForm_beta","GroupQuesForm"),
                icon:null,
                title:"题目分组",
                ok:function(){
                    //提交form
                    $('.GroupQuesForm').submit();
					return false;
                },
                cancel:true
            });
            _valid.GroupQuesValid();//新建分组验证
        });
    };
	
	exports.ajaxGetGroups = function(){
		$('.csub-list').live('change',function(){
			var obj_ele = $(this);
			var values = obj_ele.val();
			var base_uri = baseUrlName+'user/user_question/group_ajax_select';
			var getData = {'subject_id':values,'ver':(new Date).valueOf()};
			$.tizi_ajax({url: base_uri,
				type: 'GET',
				data: getData,
				dataType: 'json',
				success: function(data){
					if(data.error_code == true){
						$('.s_newGroup').html(data.groups);
					}else{
						$.tiziDialog({content:data.error});
					}
				}
			});
		});
	}
});