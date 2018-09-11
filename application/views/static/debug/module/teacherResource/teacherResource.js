define(function(require, exports) {
    require('validForm');
    require('tizi_ajax');
    require('tiziDialog');
    require('json2');//for IE7
    require('mustache');
    var resourceValid  = require("module/common/basics/teacherResource/resourceValid");
    /****************************首页脚本开始*********************************/
     /**     * 新建同步章节
     * @constructor
     */
    exports.chapter = function(){
        $(".chapter").on("click",function(){
            $.tiziDialog({
                id:"chapterDialog",
                title:"新建同步章节目录文件夹",
                content:$("#chapterBox").html().replace('chapterIndexBoxForm_beta','chapterIndexBoxForm'),
                icon:null,
                width:780,
                init:function(){

                },
                ok:function(){
                    $('.chapterIndexBoxForm').submit();
                    return false;
                },
                cancel:true
            });
            // 绑定验证
            resourceValid.chapterIndexValid();
        })
    }
    /**
     * 请选择同步章节
     * @constructor
     */
    exports.chooseVersion = function(){
       var TagA = $(".Synchapter .vlist a");//获取TagA集合
       var json = {};
       var sId ="",gradeId="",versionId="";
       exports.versionTitle();
       TagA.live("click",function(){
            if($(this).parents("li").find(".pool").hasClass("semester")){
                /*学段开始*/
                sId = $(this).data("sid");
                $("#semester").val(sId);
                $("#semester").attr("data-title",$(this).text());
                $("#semester").siblings(".Validform_checktip").remove();
                /*学段结束*/
                json = {"grade":$("#semester").val(),"subject_id":'',"s_type":$("#s_type").val()};
                exports.getSemester(json);
            }
            if($(this).parents("li").find(".pool").hasClass("subject")){
                /*学科开始*/
                gradeId = $(this).data("gradeid");
                stypeId = $(this).data("s_type");
                $("#subject").val(gradeId);//学科
                $("#s_type").val(stypeId);//学科type
                $("#subject").attr("data-title",$(this).text());
                $("#subject").siblings(".Validform_checktip").remove();
                /*学科开始*/
                json = {"grade":$("#semester").val(),"subject_id":$("#subject").val(),"s_type":$("#s_type").val()};
                exports.getSemester(json);
            }
            if($(this).parents("li").find(".pool").hasClass("subversion")){
               /*版本开始*/
               versionId = $(this).data("versionid");
               $("#subversion").val(versionId);
               $("#subversion").attr("data-title",$(this).text());
               //点了第三级，第四级的最前面的cat_id没有替换上$('#grade').val()
               var _crazy_already = $("#grade_cat_id .gradeList[data-cat_id='"+versionId+"']");
               var _sub_cat_id = _crazy_already.find(".active").attr("data-sub_cat_id");
               $("#grade").val(_sub_cat_id);
               $("#subversion").siblings(".Validform_checktip").remove();
               $("#grade_cat_id .gradeList").each(function(){
                    if($(this).data("cat_id")==versionId){
                        $(this).siblings().removeClass("dis").addClass("undis").end().removeClass("undis").addClass("dis");
                        $("#grade").attr("data-title",_crazy_already.find(".active").eq(0).text());
                        return false;
                    }
               })
               /*版本结束*/
               /*控制title开始*/
               exports.versionTitle();
               /*控制title结束*/
            }
            if($(this).parents("li").find(".pool").hasClass("grade")){
               /*年级开始*/
               $("#grade").val($(this).data("sub_cat_id"));
               $("#grade").attr("data-title",$(this).text());
               $("#grade").siblings(".Validform_checktip").remove();
               /*年级结束*/
               /*控制title开始*/
               exports.versionTitle();
               /*控制title结束*/
            }
            $(this).siblings("a").removeClass("active").end().addClass("active");//高亮
       });
    }
    exports.versionTitle = function(){
        var chapterName = $("#semester").attr("data-title")+$("#subject").attr("data-title")+""+$("#subversion").attr("data-title")+$("#grade").attr("data-title")+"";
        $("#chapterName").html(chapterName);
        $(".Synchapter .folderName").val(chapterName);
    }
    /**
     * 请求同步章节
     * @constructor
     */
    exports.getSemester = function(json){
        var data = json;
        var banben_id = 0;//点第二级的时候，确定第一个第三级的id
        var chapterName="";
        var count_in_cri = 0;
        $.tizi_ajax({
            'url' : baseUrlName + 'teacher/cloud/res/course_box',
            'type' : 'POST',
            'dataType' : 'json',
            'data' :data,
            success : function(json, status){
                var data_grad='',data_root='',data_last='';
                var sub_from_grade = json.sub_from_grade;//学科
                var category_root_id = json.data.category_root_id;//版本
                var gradeId = json.subject_id;
                /*控制学科开始*/
                $.each(sub_from_grade,function(i,n){//第二级，科目
                   if(gradeId== n.id){
                       data_grad+='<a href="javascript:;" data-s_type="'+n.type+'" data-gradeid="'+n.id+'" class="active">'+ n.name+'</a>';
                       $('#subject').val(n.id);
                   }else{
                       data_grad+='<a href="javascript:;" data-s_type="'+n.type+'" data-gradeid="'+n.id+'">'+ n.name+'</a>';
                   }
                });
                $("#sub_from_grade").html(data_grad);
                /*控制学科结束*/
                /*控制版本/年级开始*/
                $.each(category_root_id,function(i,n){
                    /*控制版本开始*/
                    count_in_cri++;
                    if(count_in_cri==1){
                      //这里的n不是第一个的意思！！！是category_root_id中的key
                      data_root += '<input type="hidden" name="subversion" value="'+n.id+'" id="subversion" class="pool subversion" data-title="'+n.name+'"/>'
                      data_root+='<a href="javascript:;" data-versionid="'+n.id+'" class="active">'+ n.name+'</a>';
                      banben_id = n.id;
                    }else{
                      data_root+='<a href="javascript:;" data-versionid="'+n.id+'">'+ n.name+'</a>';
                    }
                    var sub_cat = n.sub_cat;
                    data_last='<div class="gradeList undis" data-cat_id="'+ n.id+'">';
                    var count_in_sc = 0;
                    $.each(sub_cat,function(j,m){//最后一级,必修1 等等
                        count_in_sc++;
                        if(count_in_sc==1){
                            data_last+='<a href="javascript:;" data-sub_cat_id="'+m.id+'" class="active">'+ m.name+'</a>';
                            if(count_in_cri==1){//当 是 第一组版本时
                                $("#grade").val(m.id);//取第一个id为最后一级的id
                                $("#grade").attr("data-title", m.name);//取第一个id为最后一级的name
                            }
                        }else{
                            data_last+='<a href="javascript:;" data-sub_cat_id="'+m.id+'">'+ m.name+'</a>';
                        }
                    });
                    data_last+="</div>";
                    $("#subversion").val(banben_id);//版本
                    $("#grade_cat_id").append(data_last);
                    $("#grade_cat_id .gradeList").each(function(){
                        if($(this).data("cat_id")==banben_id){
                            $("#grade_cat_id .gradeList").removeClass("dis").addClass("undis");
                            $(this).removeClass("undis").addClass("dis");
                        }
                    })
                    $("#subversion").siblings(".Validform_checktip").remove();
                    /*控制版本结束*/
                });
                $("#category_root_id").html(data_root);
                /*控制版本/年级结束*/
                exports.versionTitle();
            },
            error : function(){
                $.tiziDialog({content:'系统忙，请稍后再试'});
            }
        });
    }
    /**
     * 新建知识点
     * @constructor
     */
    exports.lore = function(){
        $(".lore").on("click",function(){
            $.tiziDialog({
                id:"loreDialog",
                title:"新建知识点目录文件夹",
                content:$("#loreBox").html().replace('loreIndexBoxForm_beta','loreIndexBoxForm'),
                icon:null,
                width:780,
                init:function(){

                },
                ok:function(){
                    $('.loreIndexBoxForm').submit();
                    return false;
                },
                cancel:true
            });
            // 绑定验证
            resourceValid.loreIndexValid();
        })
    }
    /**
     * 请选择知识点目录
     * @constructor
     */
    exports.chooseCatalog = function(){
        var TagA = $(".Synlore .vlist a");//获取TagA集合
        var json = {};
        var sId ="",category_id="";
        var loreName="";
        TagA.live("click",function(){
            if($(this).parents("li").find(".pool").hasClass("semester")){
                sId = $(this).data("sid");
                $("#Catasemester").val(sId); //学段
                $("#Catasemester").attr("data-title",$(this).text());
                $("#Catasemester").siblings(".Validform_checktip").remove();
                json = {"grade":$("#Catasemester").val(),"subject_id":$("#Catasubject").val()};
                exports.getCatalog(json);
            }
            if($(this).parents("li").find(".pool").hasClass("subject")){
                category_id = $(this).data("category_id");//category_id
                $('#category_id').val($(this).data('category_id'));
                $("#Catasubject").val(category_id);//学科
                $("#category_id").val(category_id);//提交字段
                $("#Catasubject").attr("data-title",$(this).text());
                $("#Catasubject").siblings(".Validform_checktip").remove();
                loreName = $("#Catasemester").attr("data-title")+$("#Catasubject").attr("data-title")+'知识点库';
                $("#loreName").html(loreName);
                $(".Synlore .folderName").val(loreName);
            }
            $(this).siblings("a").removeClass("active");
            $(this).addClass("active");
        });
    }
    /**
     * 请选择获取知识点目录交互
     * @constructor
     */
    exports.getCatalog = function(json){
        var data = json;
        var loreName="";
        $.tizi_ajax({
            'url' : baseUrlName + 'teacher/cloud/res/cat_box',
            'type' : 'POST',
            'dataType' : 'json',
            'data' :data,
            success : function(json, status){
                var data_grad='';
                var sub_from_grade = json.sub_from_grade;//学科信息列表
                $.each(sub_from_grade,function(i,n){
                    if(i==0){
                        data_grad+='<a href="javascript:;" data-category_id="'+n.category_id+'" data-s_type="'+n.type+'" data-gradeid="'+n.id+'" class="active">'+ n.name+'</a>';
                        $("#Catasubject").val(n.category_id);
                        $("#category_id").val(n.category_id);//提交字段
                        $("#Catasubject").attr("data-title",n.name);
                    }else{
                        data_grad+='<a href="javascript:;" data-category_id="'+n.category_id+'" data-s_type="'+n.type+'" data-gradeid="'+n.id+'">'+ n.name+'</a>';
                    }
                });
                $("#cata_from_grade").html(data_grad);
                /*放入title*/
                loreName = $("#Catasemester").attr("data-title")+$("#Catasubject").attr("data-title")+'知识点库';
                $("#loreName").html(loreName);
                $(".Synlore .folderName").val(loreName);
            },
            error : function(){
                $.tiziDialog({content:'系统忙，请稍后再试'});
            }
        });
    }
    /**
     * 绑定没有教材版本
     * @constructor
     */
    exports.bindHavenov = function(){
        $(".havenov").live("click",function(){
            var grade = $('#semester').val();
            var subject_id = $('#subject').val();
            $('#add_subject_id').val(subject_id);
            $('#add_grade').val(grade);

            $.tiziDialog({
                id:"havenovDialog",
                title:"用户反馈",
                content:$("#havenovBox").html().replace('havenovIndexBoxForm_beta','havenovIndexBoxForm'),
                icon:null,
                width:430,
                init:function(){
                    $.tiziDialog.list["chapterDialog"].close();
                },
                ok:function(){
                    $('.havenovIndexBoxForm').submit();
                    return false;
                },
                cancel:true
            });
            // 绑定验证
            resourceValid.havenovIndexValid();
        })
    }
    /**
     * 删除常用文件方法
     * @constructor
     */
    exports.delCommonFolder = function(){
        $("#FolderList li").hover(function(){
            if($(this).hasClass("th_hd")){return;}
            $(this).addClass("active");
        },function(){
            if($(this).hasClass("th_hd")){return;}
            $(this).removeClass("active");
        });
    }
    /****************************首页脚本结束*********************************/
    /****************************同步目录,知识点目录开始*********************************/
    /**
     *  
     * @constructor
     */
    exports.hoverFileText = function(){
        $(".fileDirectText .md_bd li").hover(function(){
            $(".fileDirectText .md_bd li").removeClass("active");
            $(this).addClass("active");
        },function(){
            $(this).removeClass("active");
        });
    }
     
    /**
     *  
     * @constructor
     */
    exports.hoverOtherFileList = function(){
        $(".otherFileList .md_bd li").hover(function(){
            if($(this).hasClass("nav_tab")){return;}
            $(".otherFileList .md_bd li").removeClass("active");
            $(this).addClass("active");
        },function(){
            $(this).removeClass("active");
        });
    }
    /****************************同步目录,知识点目录结束***********************/
    /*res_list  分页 相关function  start*/
    exports.init_reslist_page = {
        initPage:function(){
            if(typeof reslist_page != 'function'){
                reslist_page = function(page){
                    exports.page(page);                 
                }
            }
        }
    };
    exports.page = function(page){
        exports.get_files(exports.getUrlData(page));
    };
    exports.getUrlData=function(ele){
        ele = ele || 1;
        var res_type = $(".res_type").val();
        var sub_cat_id = $(".sub_cat_id").val();
        var finalData = $.extend({page:ele},{res_type:res_type},{sub_cat_id:sub_cat_id});
        return finalData;
    };
    exports.get_files=function(getData){
        getData['ver'] = (new Date).valueOf();
        $.tizi_ajax({
            'url' : baseUrlName + 'teacher/cloud/res/res_list/'+getData['sub_cat_id']+'/'+getData['res_type']+'/'+getData['page']+'/'+1,
            'type' : 'GET',
            'dataType' : 'json',
            'data' : {},
            success : function(data){
                if (data.errorcode == true){
                    $('.reslist_tpl').html(data.html);
                    exports.hoverOtherFileList();//调用高亮方法
                }else{
                    $.tiziDialog({content:'系统忙，请稍后再试'});
                }
            },
            error : function(){
                $.tiziDialog({content:'系统忙，请稍后再试'});
            }
        });
    };
      /*res_list  分页 相关function  over */
    /****************************其他文件页脚本开始*******************************/

    /****************************其他文件页脚本结束*******************************/
    /****************************预览页脚本开始*******************************/
    require('module/teacherLesson/lesson_prepare').lessonInit();
    //备课预览flash初始化
    exports.swfObjectInit = function(swfversion,jsversion){
        seajs.use(staticBaseUrlName + "flash/lessonFlash/swfobject",function(){
            //加载内容
            var lessonUrl = staticBaseUrlName+'flash/lessonFlash/';
            // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection.
            var swfVersionStr = baseFlashVersion;
            // To use express install, set to playerProductInstall.swf, otherwise the empty string.
            var xiSwfUrlStr = lessonUrl+"playerProductInstall.swf"+jsversion;
            //alert(contentBoxH);
            var flashvars = {};
            var params = {};
            var flash_height = exports.swfObjectHeight();
            var docExt = $("#docnum").data("ext");
            switch(docExt){
                case 'doc':
                    flash_height = 1096;
                    break;
                case 'docx':
                    flash_height = 1096;
                    break;
                case 'ppt':
                    flash_height = 590;
                    break;
                case 'pptx':
                    flash_height = 590;
                    break;
                default :
                    flash_height = 1096;
                    break;
            }
            Teacher.lesson.prepare.swfObjectHeight = flash_height;
            params.quality = "high";
            params.bgcolor = "#ffffff";
            params.wmode = "transparent";
            params.wmode = "Opaque";
            params.allowscriptaccess = "sameDomain";
            params.allowfullscreen = "true";
            var attributes = {};
            attributes.id = "TiZiPaper";
            attributes.name = "TiZiPaper";
            attributes.align = "middle";
            var swfUrl = lessonUrl+"TiZiPaper.swf"+swfversion;
            swfobject.embedSWF(
                swfUrl, "flashContent",
                "750", flash_height+5,
                swfVersionStr, xiSwfUrlStr,
                flashvars, params, attributes);
            swfobject.createCSS("#flashContent", "display:block;text-align:left;");
        });
    }
    exports.swfObjectHeight = function(){
        var contentBoxH = $(".flashView").position().top;
        var flash_height = $(document).height()-contentBoxH;
        $("#flashContent").height(flash_height);
        return flash_height;
    }
    /****************************预览页脚本结束*******************************/
});