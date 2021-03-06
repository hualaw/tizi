/**
 * User: Angus
 * Date: 14-3-27
 * Time: 上午10:20
 */
define(function (require,exports) {
    //初始化引入信息
    var ckeditor = require("ckEditor");
    var _valid = require("module/common/basics/teacherTestLibrary/valid");//加载验证库
    //var wordImageUploader = require("wordImageUploader");
    /*上传新题*/
    exports.UploadAq = {
        appletUrl:'',
        init:function(){
            //判断是否是ie7
            if ($.browser.msie&&$.browser.version<8){
                $('.ckEd').prepend('<p class="ieTip">温馨提示：您的浏览器版本较低，将无法上传图片或者公式，建议您安装并使用谷歌、火狐或ie8以上浏览器，若使用360浏览器请切换为“极速模式”</p>');
                $("#question_content").addClass('ieTxtArea');
                $("#analysis").addClass('ieTxtArea');
                $("#option_answer").addClass('ieTxtArea');
            }else{
                seajs.use('ckSource',function(){
                    $("#question_content").ckeditor();
                    $("#analysis").ckeditor();
                    $("#option_answer").ckeditor();
                });
                var isWindows = this.checkOS();
                if(isWindows){
                    $(".Tip").show();
                    this.addPlugin();
                }
            }
            exports.my_question_ajax.init();
            this.openGroupInfo();//弹出学科框v20140114
        },
        //添加编辑器
        addPlugin:function(){
            //添加ckEditor
            seajs.use('ckSource',function(){
                var that = exports.UploadAq;
                var serverUrl = baseUrlName + 'upload/uques';
                var wordImageUploader = require("module/teacherTestLibrary/wordimage_uploader");//加载java_applet
                var uploader = new wordImageUploader.WordImageUploader(serverUrl,this.appletUrl);
                CKEDITOR.instances["question_content"].on("change", function() {
                    uploader.uploadWordImagesFromCKEditor(CKEDITOR.instances["question_content"]);
                });
                CKEDITOR.instances["analysis"].on("change", function() {
                    uploader.uploadWordImagesFromCKEditor(CKEDITOR.instances["analysis"]);
                });
                CKEDITOR.instances["option_answer"].on("change", function() {
                    uploader.uploadWordImagesFromCKEditor(CKEDITOR.instances["option_answer"]);
                });
            });
            require('module/common/method/common/height').leftMenuBg();
        },
        checkOS:function(){
            var os_type = '';
            windows = (navigator.userAgent.indexOf("Windows",0) != -1)?1:0;
            if (windows)return 1;
            else return 0;
        },
        //添加学科弹出框
        openGroupInfo:function(){
            var openGroupInfo = $('.openGroupInfo');
            var tmpGroupTip = "";
            var size = $(".s_newSubject option").size();
            for (var i = 0; i < size; i++) {
                $('.dataStore').data($(".s_newSubject option").eq(i).text(),$(".s_newSubject option").eq(i).val());
            }
            openGroupInfo.on('click',function(){
                var _that = $(this);
                $(".s_newSubject option").removeAttr("selected");//移除selected
                var subject_text = _that.parents("ul").find(".s_subject option:selected").text();  //获取旧的text
                var index = $('.dataStore').data(subject_text); //hash查找
                $(".s_newSubject option").eq(index).attr("selected",true); //赋值
                tmpGroupTip = $('.tmpGroupTip').html();//取form html
                //console.log($('.tmpGroupTip').html());
                $.tiziDialog({
                    id:"tmpGroupTipId",
                    content:tmpGroupTip,
                    icon:false,
                    ok:function(){
                        $(".myGroupTipForm").submit();
                        return false;
                    },
                    cancel:true,
                    init:function(){
                        _valid.uploadQuesValid.myGroupTipForm(_that); //调用验证弹出框脚本
                    }
                });
            });
        }
    };
    /*上传中*/
    exports.UploadING = {
        init:function(){
            $('#file_upload').uploadify({
                'formData' : $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post'),
                'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                'uploader' : baseUrlName+'upload/udoc',
                'buttonImage': staticBaseUrlName+staticVersion+'lib/uploadify/2.2/icon_org.png',
                'buttonClass'     : 'uploadify_oragne',
                'fileTypeExts' : '*.doc; *.docx; *.ppt; *.pptx; *.xls; *.xlsx; *.wps; *.et; *.dps; *.pdf; *.txt',
                'fileSizeLimit' : '20MB',
                'fileObjName' : 'documents',
                'width'           : 94,
                'text-indent' :10,
                'uploadLimit' : 5,
                'overrideEvents': ['onSelectError','onDialogClose','onUploadError'],
                'onSelectError' : function(file, errorCode, errorMsg) {
                    switch (errorCode) {
                        case -100:
                            $.tiziDialog({content:"每次最多上传5份文档"});
                            break;
                        case -110:
                            $.tiziDialog({content:"文件 [" + file.name + "] 过大！每份文档不能超过20M"});
                            break;
                        case -120:
                            $.tiziDialog({content:"文件 [" + file.name + "] 大小异常！不可以上传大小为0的文件"});
                            break;
                        case -130:
                            $.tiziDialog({content:"文件 [" + file.name + "] 类型不正确！不可以上传错误的文件格式"});
                            break;
                    }
                    return false;
                },
                'onSWFReady':function(){
                },
                'onFallback' : function() {
                    $('.uploadDoc').html(noflash);
                },
                'onUploadError' : function(file, errorCode, errorMsg, errorString) {
                    if(errorCode==-280||errorCode==-290)					{
                        return ;
                    }
                    $.tiziDialog({content:"文件 [" + file.name + "] 上传失败"});
                    return false;
                },
                'onQueueComplete': function(queueData) {
                    top.location.href=baseUrlName+'teacher/user/mydocument/perfect';
                }
            });
        }
    };
    /*完成上传*/
    exports.UploadOver = {
        init:function(){
            this.addOverInfo();
            this.delOverInfo();
            this.delSynInfo();
        },
        //添加信息
        addOverInfo:function(){
            var addBtn_message = $('.addBtn_message');
            //绑定知识点信息
            addBtn_message.eq(0).on('click',function(){
                var add_knows = $('.add_knows').eq(0).clone();
                $('.add_knows').parent().append(add_knows);
            });
            //绑定同步信息信息
            addBtn_message.eq(1).on('click',function(){
                var add_ansyInfo = $('.add_ansyInfo').eq(0).clone();
                $('.add_ansyInfo').parent().append(add_ansyInfo);
            })
        },
        //添加删除
        delOverInfo:function(){
            var del_message = $('.del_message');
            del_message.on('click',function(){
                var index = $(this).index();
                $(this).parent().remove();

            })
        },
        //添加模块删除
        delSynInfo:function(){
            var del_syncInfo = $('.del_syncInfo');
            del_syncInfo.on('click',function(){
                var index = $(this).index();
                $('.md_filesList').eq(index).slideUp("slow",function(){
                    $(this).remove();//移除
                });
            })
        }
    }
    /*我的题库ajax_下拉选择*/
    exports.my_question_ajax = {
        init:function(){
            this.subjecType();
            this.getVersion();
            $(".add_course").on("click",function(){
                objConfirm = $(this);
                exports.my_question_ajax.confirmCourseMsg(objConfirm);
            });
            $(".add_category").on("click",function(){
                objConfirm = $(this);
                exports.my_question_ajax.confirmCategoryMsg(objConfirm);
            });
            this.add_option();
            this.del_option();
            this.answer_panel();
        },
        add_option:function(){
            $("#myadd_option").on("click",function(){
                var next_option = exports.my_question_ajax.getNextOption();
                if (next_option)  {
                    str = '<label class="labelr"> <input class="radior" type="checkbox" name="option_answer[]" ';
                    str += 'value="'+next_option+'">';
                    str += next_option + '</label>';
                    $('#answer_row').append(str);
                }
                exports.my_question_ajax.setLastOption();
            });
        },
        del_option:function(){
            $("#mydel_option").on("click",function(){
                var le = $('.pickList').find('input').last();
                if (le.val() == 'A') {
                    $.tiziDialog({content:"不能再减少了"});
                } else {
                    var p = le.closest('label');
                    p.remove();
                }
                exports.my_question_ajax.setLastOption();
            });
        },
        getNextOption:function(){
            var last_val = this.getLastOption(),
                option_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                pos = option_list.indexOf(last_val);

            if(pos == option_list.length - 1) {

                $.tiziDialog({content:"选项不能再多了"});
                return false;
            } else {
                var next_pos = 0;
                if (pos > parseInt(option_list.length)) {
                    next_pos = option_list.length;
                } else {
                    next_pos = pos + 1;
                }
                return option_list[next_pos];
            }
        },
        setLastOption:function(){
            var le = $('.pickList').find('input').last();
            var last_val = le.val();
            $('#last_option').attr('value', last_val);
        },
        getLastOption:function(){
            var le = $('.pickList').find('input').last();
            var last_val = le.val(),
                option_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            return last_val;
        },
        answer_panel : function(){
            $('.s_qtype').on('change',function(){
                var obj_ele = $(this);
                var values = obj_ele.val();
                if(values >=2 && values<=7){
                    $('#qestion_unselect').hide();
                    $('#qestion_select').show();
                }
                else{
                    $('#qestion_unselect').show();
                    $('#qestion_select').hide();
                }

            });
        },
        getBaseUrl:function()
        {
            return "user/user_question/";
        },
        unCheck:function(str){

            if('请选择' == str || '请选择版本' == str)  {
                $.tiziDialog({content:"您有未选择的项！"});
                return true;
            }
            return false;
        },
        delCourseSelect:function(id,ele){
            var this_obj = $(ele);
            $("#category_id").val("");
            $('p#'+id).remove();
        },
        delCategorySelect:function(id){
            var knowledge_id_obj = $('#knowledge_id');
            var kid_list_obj = knowledge_id_obj.val(),
                kid_list = kid_list_obj.split('|');
            var kpos = kid_list.indexOf(id);
            if (kpos != -1) {
                kid_list.splice(kpos, 1);
            }
            try {
                kid_str = kid_list.join('|');
            } catch (e) {
                //console.log(e);
            }
            knowledge_id_obj.attr('value', kid_str);
            $('p#'+id).remove();
        },
        confirmCategoryMsg:function(ele){
            var objBtn = ele;
            var str = '';
            var sid = '';
            var categoryid_storage = $("#knowledge_id");
            var category_name_obj = $(".knowlage_name");
            var storedval = categoryid_storage.val();
            //if(storedval!=""){$.tiziDialog({content:"您已经添加了教材同步信息！"});return false; }
            var check_res;
            objBtn.prev().find('.tree-item').each(function(d){
                //
                var id = $(this).find('option:selected').val();
                var name= $(this).find('option:selected').html();
                check_res = exports.my_question_ajax.unCheck(name);
                if (check_res) {return false;};
                if (name != '') {
                    str += name + '--';
                } if (id != '') {
                    sid += id + '-';
                }
            });
            if  (check_res) {
                return check_res;
            }
            var val_arr = storedval.split('|');
            var pos = val_arr.indexOf(sid);
            if (pos != -1) {
                $.tiziDialog({content:"您已经添加了该知识点信息"});
                return;
            }
            sid += '|';
            if(storedval) {
                categoryid_storage.attr('value', storedval + sid);
            } else {
                categoryid_storage.attr('value', sid);
            }
            sid = sid.substr(0, sid.length-1);
            category_name_obj.append('<p id="'+sid+'">' + str + '<a href="javascript:void(0);" onclick="exports.my_question_ajax.delCategorySelect(\''+sid+'\',this);" class="del_message">删除</a></p>');
        },
        confirmCourseMsg:function(ele){
            var objBtn = ele;
            var str = '';
            var sid = '';
            var categoryid_storage = $("#category_id");
            var category_name_obj = $(".category_name");
            var storedval = categoryid_storage.val();
            if(storedval!=""){$.tiziDialog({content:"您已经添加了教材同步信息！"});return false; }
            var check_res;
            objBtn.prev().find('.tree-item').each(function(d){
                var id = $(this).find('option:selected').val();
                var name= $(this).find('option:selected').html();
                check_res = exports.my_question_ajax.unCheck(name);
                if (check_res) {return false;};
                if (name != '') {
                    str += name + '--';
                } if (id != '') {
                    sid += id + '-';
                }
            });
            if  (check_res) {
                return check_res;
            }
            categoryid_storage.attr('value', sid);
            sid = sid.substr(0, sid.length-1);
            category_name_obj.append('<p id="'+sid+'">' + str + '<a href="javascript:void(0);" onclick="exports.my_question_ajax.delCourseSelect(\''+sid+'\',this);" class="del_message">删除</a></p>');
        },
        subjecType:function(){
            $('.s_grade').on('change',function(){
                var obj_ele = $(this);
                var _this = exports.my_question_ajax;
                var values = obj_ele.val();
                var base_uri = baseUrlName+_this.getBaseUrl()+'ajax_subject';
                var getData = {'grade_id':values,'ver':(new Date).valueOf()};
                $.tizi_ajax({url: base_uri,
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        if(data.error_code == true){
                            $('.s_subject').html(data.error);
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        },
        getVersion:function(){
            $('.s_subject').on('change',function(){
                var obj_ele = $(this);
                var _this = exports.my_question_ajax;
                var values = obj_ele.val();
                var base_uri = baseUrlName+_this.getBaseUrl()+'subject_ajax_select';
                var getData = {'subject_id':values,'ver':(new Date).valueOf()};
                $.tizi_ajax({url: base_uri,
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        if(data.error_code == true){
                            $('.s_qtype').html(data.qtype);
                            $('.s-knowlage').html(data.category);
                            $('.s-version').html(data.course);
                            $('.s_Group').html(data.groups);
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        },
        delAfter:function(ele){
            var _curSel = ele;
            _curSel.nextAll('.tree-item').remove();
        },
        treeItemClick:function(this_a){

            var thisId = $(this_a).val();
            if(thisId == ""){
                this.delAfter($(this_a));
                return false;
            }
            var selType = $(this_a).find('option:selected').data('type');
            if(selType == "selplus"){
                this.ajax_node_data(thisId,$(this_a));
            }
            else{
                this.delAfter($(this_a));
            }
        },
        ajax_node_data:function(cnselectVal,element){
            var base_url = this.getBaseUrl();
            $.get(baseUrlName + base_url + "ajax_cate_node",{cnselect:cnselectVal,ver:(new Date).valueOf()},function(data){
                if(data.error_code==true){
                    if(element){
                        exports.my_question_ajax.delAfter(element);
                        element.after(data.error);
                    }else{
                        //$(".select-list-content").append(treeList)
                    }
                }else{
                    $.tiziDialog({content:data.error});
                }
            },"json");
        }
    }
});