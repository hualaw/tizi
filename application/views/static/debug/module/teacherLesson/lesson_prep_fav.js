// 备课收藏  2014-07-14
define(function(require, exports){
    var lessonValid  = require("module/common/basics/teacherLesson/lessonValid");
     /*初始化*/
    exports.init = function(){
        exports.init_ls_prep_my_page.initPage();
        exports.checkAllFile();
        exports.myFileMoveFn();
        exports.myFileChangeFn();
        exports.myFiledeleteFn();

    };

    //我的文件全选
    exports.checkAllFile = function(){
        $("#allFile").live('click',function(){
            if(this.checked) {
                $(".myFileTable input[name='everyFile']").each(function() {
                    this.checked = true;
                });
            }else{
                $(".myFileTable input[name='everyFile']").each(function() {
                    this.checked = false;
                });
            }
        });
    };
    exports.has_checked = function(){
        var id_str = '';
        $(".myFileTable input[name='everyFile']").each(function() {
            if($(this).attr('checked') == 'checked'){
                id_str += $(this).attr('id')+",";
            }
        });
        return id_str;
    };
    // 我的文件 - 移动文件
    exports.myFileMoveFn = function(){
        $('.myFileMove').live('click',function(){
            //检测有没有文件被选中，没有的话不让操作   
            var id_str = exports.has_checked();
            $('.move_ids_str').val(id_str);
            if(id_str.length<1){
                return false;
            }
            var that = $(this);
            // checkFolderInfo(that);
            var title = '移动文件到';
            //文件弹出框
            $.tiziDialog({
                id:'moveFilePopDialog',
                title:title,
                content:$("#moveFilePop").html().replace('moveFileTreeForm_beta','moveFileTreeForm'),
                icon:null,
                width:760,
                ok:function(){
                    $('.moveFileTreeForm').submit();
                    return false;
                },
                close : function(){
                },
                cancel:true
            });
            //调用相应的js方法：联动啊什么的
            seajs.use('module/teacherLesson/upload_file',function(ex){
                ex.getSubjecType();
                ex.getAjaxVersion();
                ex.getAjaxGrades();
                ex.getAjaxNodes();
                //设置隐藏域值
                ex.setHiddenValue();
            }); 
            // 前端验证
            lessonValid.moveFileTreeValid();
        });
        function checkFolderInfo(that){
            var textView = $(".moveFile .textView");
            var $ele = that;
            var resource_id = $ele.attr('moved-resource-id')||'';
            $('.resource_id').val(resource_id);
            // alert($('.resource_id').val());
            textView.find("a").live('click',function(){
                var TypeTree =$("#"+$(this).parents(".moveFile").attr("id"));
                $(this).parent().find("a").removeClass("active");
                $(this).addClass("active");
                //控制联动显隐
                var dir_id = $(this).data("dir_id")||"";
                var cat_id = $(this).data("cat_id")||"";
                var to_dir_id = $(this).data("to_dir_id")||"";
                var sub_cat_id = $(this).data("sub_cat_id")||"";
                var to_type = $(this).data("type")||"";
                if(cat_id!==""){
                    var cataLogue = TypeTree.find("#moveCatalogueList").find("div[data-catelog='"+cat_id+"']");
                    var classList = TypeTree.find("#moveClassList").find("div[data-type='"+cat_id+"']");
                    //控制目录
                    TypeTree.find("#moveCatalogueList").find("div").removeClass("dis").addClass("undis");
                    if(cataLogue){
                        cataLogue.removeClass("undis").addClass("dis");
                    }
                    //控制年级
                    TypeTree.find("#moveClassList").find("div").removeClass("dis").addClass("undis");
                    if(classList){
                        classList.removeClass("undis").addClass("dis");
                    }
                    //控制其他文件
                    if(cat_id=="cloud"){TypeTree.find(".to_type").val("xx");}
                }
                //控制填充数据value
                var textView = $(this).parents(".textView").attr("id");
                switch (textView){
                    case "moveFolderList":
                        TypeTree.find(".dir_id").val(dir_id);
                        TypeTree.find(".cat_id").val(cat_id);
                        break;
                    case "moveCatalogueList":
                        TypeTree.find(".to_dir_id").val(to_dir_id);
                        break;
                    default:
                        TypeTree.find(".to_type").val(to_type);
                }
            });
        }
    };
    // 我的文件 - 转存  共享到班级
    exports.myFileChangeFn = function(){
        $('.myFileChange').live('click',function(){
            //检测有没有文件被选中，没有的话不让操作   
            var id_str = exports.has_checked();
            $('.chosen_files').val(id_str);//赋值到form中
            if(id_str.length<1){
                // $.tiziDialog({content:'请至少选择一个文件'})
                return false;
            }
            $.tiziDialog({
                id:'changeFile_id',
                title:'转存文件',
                content:$('#changeFilePop').html().replace('changeFileForm_beta','changeFileForm'),
                icon:null,
                width:600,
                ok:function(){
                    $('.changeFileForm').submit();
                    return false;
                },
                cancel:true
            });
            // 前端验证
            lessonValid.changeFile();
        });
    };
    // 我的文件 - 删除
    exports.myFiledeleteFn = function(){
        $('.myFileDelete').live('click',function(){
             //检测有没有文件被选中，没有的话不让操作   
            var id_str = exports.has_checked();
            if(id_str.length<1){
                return false;
            }
            var cur_page = $('.current_page').val();
            $.tiziDialog({
                id:'deleteFile_id',
                title:'删除文件',
                content:'是否确定删除这些收藏文件？',
                icon:'question',
                width:400,
                ok:function(){
                    $.tizi_ajax({
                        'url' : baseUrlName+'lesson/less_my_fav/del_fav',
                        'type' : 'POST',
                        'dataType' : 'json',
                        'data' : {ids:id_str},
                        success : function(data){
                            if(data.errorcode!=true){
                                $.tiziDialog({content:data.error});
                            }else{
                                exports.page(cur_page);
                            }
                        }
                    });
                },
                cancel:true
            });
        });
    };

/*  分页 相关function  start*/
    exports.init_ls_prep_my_page = {
        initPage:function(){
            if(typeof prep_fav_page != 'function'){
                prep_fav_page = function(page){
                    exports.page(page);                 
                }
            }
        }
    };            
    exports.page = function(page){exports.get_files(exports.getUrlData(page)); }
    exports.getUrlData=function(ele){
        ele = ele || 1;
        var cat_id = $(".cat_id").val();
        var sub_cat_id = $(".sub_cat_id").val();
        var finalData = $.extend({page:ele},{cat_id:cat_id},{sub_cat_id:sub_cat_id});
        return finalData;
    } 
    exports.get_files = function(getData){
        getData['ver'] = (new Date).valueOf();
        seajs.use('tizi_ajax',function(){ //不知为何这里要use一下
        $.tizi_ajax({
            'url' : baseUrlName + 'lesson/less_my_fav/fav_list/'+getData['cat_id']+'/'+getData['sub_cat_id'],
            'type' : 'GET',
            'dataType' : 'json',
            'data' : {flip:true,page:getData['page'],subject_id:$('.subject_id').val()},
            success : function(data){
                if (data.errorcode == true){
                    $('.lessonTabDiv').html(data.html);
                }else{
                }
            },
            error : function(){
                $.tiziDialog({content:'系统忙，请稍后再试'});
            }
        });
        });
    } 
/* 分页 相关function  over */
     
    
});