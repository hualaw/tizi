//  我的题库
define(function(require, exports) {
    // 添加分组验证
    exports.addNewGroupValidAjax = function(data){
        if(data.error_code == false) {
            $.tiziDialog({content:data.error});
        }else{
			var group_name = data.new_name;
			var bindUrl = baseUrlName + 'teacher/user/myquestion/g/'+data.error;
            var doc_str = '<tr>'+
                '<td class="questionGrop group-name"><a href="'+bindUrl+'" data-gid="'+data.error+'">'+group_name+'</a></td>' +
                '<td class="paperNum">0 道</td>' +
                '<td class="operation"><a href="javascript:void(0)" data-gid="'+data.error+'" class="edit_doc">编辑</a>&nbsp;' +
                '<a href="javascript:void(0)" class="del_doc">删除</a></td></tr>';
            $(doc_str).insertAfter(".grop_list tbody tr:eq(1)");
            //关闭
            $.tiziDialog.list['AddDocForm'].close();
        }
    }
    //编辑分组验证
    exports.EditNewGroupValidAjax = function(data,that){
        var ele = that; //传入对象操作回传值
        if(data.error_code == false) {
            $.tiziDialog({content:data.error});
        }else{
            ele.parents("tr").find(".group-name").find("a").text(data.new_group_name);
            //关闭
            art.dialog.list['EditDocForm'].close();
        }
    }
    // 添加上传新题验证
    exports.uploadQuesAjax = {
        myQuestion:function(data){
            if(data.error_code){
                var type = data.type;
                var title = type=="insert"?"继续上传":"继续编辑";
                $.tiziDialog(
                    {
                        content:data.error,
                        okVal:title,
                        cancelVal:"查看我的试题",
                        ok:function(){
                            this.close();
                            if(type == "insert"){
                                window.location.href=baseUrlName + "teacher/user/myquestion/new";
                            }
                        },
                        cancel:function(){
                            var optionId = $('.s_subject option:selected').val();
                            window.location.href=baseUrlName + "teacher/user/myquestion/"+optionId;
                        }
                    }
                );
            }else{
                $.tiziDialog({content:data.error});
            }
        },
        //添加个人中心我的题库页面分组验证v20140114
        myGroupTipForm:function(data,obj){
            var oldGroup = obj.parent().find(".s_Group");
            //$.tiziDialog.list['tmpGroupTipId'].close();
            if(data.error_code){
                var newGroupName = data.new_name;
                var newGroupID = data.error;
                var newOption = '<option value ="'+newGroupID+'" selected="selected">'+newGroupName+'</option>';
                oldGroup.removeAttr("selected");
                oldGroup.append(newOption);

                //此处把新分组注入到分组select
                $.tiziDialog.list['tmpGroupTipId'].close();//关闭弹出form表单
            }else{
                $.tiziDialog({content:data.error});
            }
        }
    }
    // 我的题库题目分组v20140512
    exports.GroupQuesValidAjax = function(data){
        if (data.error_code == true){
            $.tiziDialog.list['GroupQuesForm'].close();
            $.tiziDialog({
                content : data.error,
                icon : 'succeed',
                ok : function(){
                    window.location.reload();
                }
            });
        }else {
            $.tiziDialog({content:data.error});
        }
    }
});