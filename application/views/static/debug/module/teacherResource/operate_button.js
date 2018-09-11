define(function(require, exports) {
    require('validForm');
    require('tizi_ajax');
    require('json2');//for IE7
    require('mustache');
    require('cookies');
    var cloudValid  = require("module/common/basics/teacherCloud/cloudValid");
    var Teadownload = require("tizi_download");

//网 盘 系 列 操作
    exports.dropBoxDialog = function(){
        seajs.use(['tiziDialog','tizi_ajax'],function(){
            // 新建文件夹
            $('#createNewFileBtn').live('click',function(){
                var cur_dir_id = $('.current_dir_id').val();
                $('.create_folder').val(cur_dir_id);
                $.tiziDialog({
                    id:'create_new_fileId',
                    title:'新建文件夹',
                    content:$('#creatNewFlie').html().replace('creatNewFileForm_beta','creatNewFileForm'),
                    icon:null,
                    width:600,
                    ok:function(){
                        $('.creatNewFileForm').submit();
                        return false;
                    },
                    cancel:true
                });
                // 前端验证
                cloudValid.creat();
            });
            //下载文件
            $('.downloadFile').live('click',function(){
                var file_name = $(this).attr('file_name');
                var file_id = $(this).attr('file_id');
                $.tizi_ajax({
                        url: baseUrlName + "teacher/cloud/download_verify", 
                        type: 'POST',
                        data: {'file_id' :file_id,'file_name':file_name},
                        dataType: 'json',
                        success: function(data){
                            if(data.errorcode == false) {                       
                                $.tiziDialog({content:data.error});
                            }else{
                                var fname = url = '';
                                if(data.file_type==1){
                                    var down_url=baseUrlName + "teacher/cloud/download/?url="+data.file_path
                                     +'&file_name='+data.file_encode_name+'&file_id='+data.file_id;
                                    url = down_url ;
                                    fname = data.fname;
                                    // Teadownload.down_confirm_box(url,fname,false);
                                    Teadownload.down_confirm_box(data.file_path,data.fname,false);//ghl的下载接口
                                }else{
                                    fname = data.fname;
                                    url = data.url;
                                    Teadownload.down_confirm_box(url,fname,true);
                                     // Teadownload.force_download(data.url,data.fname,true,true);
                                }
                            }
                        }
                });
            });
            // 分享文件 -- 无班级可分享
            $('.ClassShareFile').live('click',function(){
                var has_class = $('.has_class').val();
                if($('.shareFliesHasClassForm_beta').length>0){
                    $('.get_file_id').val($(this).attr('file_id'));
                    $.tiziDialog({
                        id:'shareFile_id',
                        title:'分享文件',
                        content:$('#shareFliesBox_hasClass').html().replace('shareFliesHasClassForm_beta','shareFliesHasClassForm'),
                        icon:null,
                        width:600,
                        ok:function(){
                            $('.shareFliesHasClassForm').submit();
                            return false;
                        },
                        cancel:true
                    });
                    // 前端验证
                    cloudValid.shareFile();
                }else{
                    $.tiziDialog({
                        id:'',
                        title:'分享文件',
                        content:$('#shareFliesBox_noClass').html(),
                        icon:null,
                        width:600,
                        ok:function(){
                        },
                        cancel:true
                    });
                }
            });
            /*班级分享, 转换中的文档给提示*/
                $('.check_pfop_file').live('click',function(){
                    var file_id = $(this).attr('file_id');
                    // var share_id = $(this).attr('share_id');
                    var url = baseUrlName + 'resource/res_file/check_pfop/' + file_id;
                    var that = $(this);
                    $.tizi_ajax({
                        'url' : url,
                        'type' : 'GET',
                        'dataType' : 'json',
                        'data' : {},
                        success : function(data){
                            if(data.errorcode!=true){
                                $.tiziDialog({content:data.error});
                            }else{
                                var to_url = baseUrlName + "cloud/cloud/file_detail/"+file_id;
                                window.open(to_url,"_blank");
                            }
                        }
                    });
                    
                });
            // 移动文件
            $('.moveFlie').live('click',function(){
                var that = $(this);
                var is_file =that.attr('is_file')||'';
                var flag = false;
                $.tizi_ajax({
                    'url' : baseUrlName + 'resource/res_tree/tree',
                    'type' : 'GET',
                    'dataType' : 'json',
                    'data' : {  },
                    'async':false,
                    success : function(json, status){
                        if (json.html){
                             $('.new_tree').html(json.html);
                             $('.new_dir_tree').html(json.dir_html);
                             flag = true;
                        }else {
                            $.tiziDialog({content:'系统忙，请稍后再试'});
                        }
                    },
                    error : function(){
                        $.tiziDialog({content:'系统忙，请稍后再试'});
                    }
                });
                checkFolderInfo(that);
                if(flag){
                    if(is_file){
                        var title = '移动文件到';
                        //文件弹出框
                        $.tiziDialog({
                            id:'moveFilePopDialog',
                            title:title,
                            content:$("#moveFilePop").html(),
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
                        // 前端验证
                        cloudValid.moveFileTreeValid();
                    }else{
                        var title = '移动文件夹到';
                        //文件夹弹出框
                        $.tiziDialog({
                            id:'moveDirPopDialog',
                            title:title,
                            content:$("#moveDirPop").html(),
                            icon:null,
                            width:760,
                            ok:function(){
                                $('.moveDirTreeForm').submit();
                                return false;
                            },
                            close : function(){
                            },
                            cancel:true
                        });
                        // 前端验证
                        cloudValid.moveDirTreeValid();
                    }
                }
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
            // 重命名文件
            $('.resetFlieName').live('click',function(){
                $('#resetFileNameTxt').attr('value',$(this).attr('file_name'));
                $('.is_file').val($(this).attr('is_file'));
                $('.rename_id').val($(this).attr('file_id'));
                $.tiziDialog({
                    id:'resetFile_id',
                    title:'重命名文件',
                    content:$('#resetFileNamePop').html().replace('resetFileNameForm_beta','resetFileNameForm'),
                    icon:null,
                    width:600,
                    ok:function(){
                        $('.resetFileNameForm').submit();
                        return false;
                    },
                    cancel:true
                });
                // 前端验证
                cloudValid.resetFile();
            });
            // 删除文件
            $('.deleteFile').live('click', function(){
                var file_id = $(this).attr('file_id');
                var is_file = $(this).attr('is_file');
                var node = $(this);
                var _width = 400;
                if(is_file==1){
                    var content = '是否删除该文件?';
                    var title = '删除文件';
                }else{
                    var alertContent = "<h2>是否确定删除此文件夹？</h2><p style='color:#ff0000'>请注意：文件夹下的所有文件也会被删除！</p>";
                    var content = alertContent;
                    var title = '删除文件夹';
                    _width = 500;
                }
                $.tiziDialog({
                    title:title,
                    content:content,
                    icon:'question',
                    width:_width,
                    ok:function(){
                        $.tizi_ajax({
                            'url' : baseUrlName + 'teacher/cloud/del',
                            'type' : 'POST',
                            'dataType' : 'json',
                            'data' : {
                                'is_file' : is_file,
                                'file_id' : file_id
                            },
                            success : function(json, status){
                                if (json.errorcode == 1){
                                    $.tiziDialog({
                                        content : json.error,
                                        icon : 'succeed',
                                        ok : function(){
                                            node.parent().parent().remove();
                                            var student_total = $('#student_total').html();
                                            $('#student_total').html(student_total-1);
                                            window.location.reload();
                                        }
                                    });
                                }else {
                                    $.tiziDialog({content:json.error});
                                }
                            },
                            error : function(){
                                $.tiziDialog({content:'系统忙，请稍后再试'});
                            }
                        });
                    },
                    cancel:true
                });
            });
            // 共享文件给tizi   share_to_tizi
            $(".shareAll").live('click',function(){
                var shareAll_fileId = $(".shareAll_fileId");
                shareAll_fileId.val($(this).attr("file_id"));
                $.tiziDialog({
                    id:"shareAll_id",
                    title:"共享文件",
                    content:$("#sharAllFilePop").html().replace('sharAllFileForm_beta','sharAllFileForm'),
                    icon:null,
                    width:400,
                    ok:function(){
                        $('.sharAllFileForm').submit();
                        return false;
                    },
                    cancel:true
                });
                // 前端验证
                cloudValid.shareAllValid();
            });
            // 上传文件的回调内容, 点击才弹出来，
            $('#upFileBeta').live('click',function(){
                $.tiziDialog({
                    id:'',
                    title:'上传文件（0/4）',
                    content:$('#upFilePop').html().replace('upFilePopForm_beta','upFilePopForm'),
                    icon:null,
                    width:600,
                    ok:function(){
                        $('.upFilePopForm').submit();
                        return false;
                    },
                    cancel:true
                });
            }); 
            //检测文档是否转换好
            // exports.inprocess = function(page){
                $('.file_trans_inprocess').live('click',function(){
                    var file_id = $(this).attr('file_id');
                    var url = baseUrlName + 'teacher/cloud/res/doc_process/' + file_id;
                    var to_url = baseUrlName + 'cloud/cloud/file_detail/' + file_id;
                    var that = $(this);
                    $.tizi_ajax({
                        'url' : url,
                        'type' : 'GET',
                        'dataType' : 'json',
                        'data' : {},
                        success : function(data){
                            if(data.errorcode!=1){
                                $.tiziDialog({content:'文档正在处理中，请稍候'});
                            }else{
                                window.open(to_url,"_blank");
                            }
                        }
                    });
                });
            // };
        });
    };


})