$('.logoutCheck').live('click',function(){
    var redirect = $(this).attr('dest');
    if(!redirect) redirect = $(this).attr('href');
    $.tizi_ajax({
        url: loginUrlName + 'logout/check',
        type: "get",
        dataType: "jsonp",
        data: {'redirect':redirect},
        success: function(data) {
            if(data.errorcode){
                if(data.redirect == 'reload'){
                    window.location.reload();
                }else if(data.redirect){
                    window.location.href=data.redirect;
                }
            }else{
                window.location.reload();
            }
        }  
    });
});

var Common = {
    //头部右侧下拉菜单
    headerNav:function(json){
        var _id = json.id,_ul = json.ul,_dis = json.dis;
        $(_id).hover(function(){
            $(this).find(_ul).addClass(_dis);   
        },function(){
            //$(this).find(_ul).removeClass(_dis);
        })
    }
};
/*引入原有jquery.tizi.ajax.js开始*/
(function($){
  $.tizi_callback = function(data,success){
    if(data == undefined) return;
    if(data.login === false){
      /*
      $.tiziDialog({
        content:data.error,
        ok:function(){
          window.location.href = baseUrlName;
        },
        close:function(){
          window.location.href = baseUrlName;
        }
      });
      */
      window.location.href = baseUrlName;
      return false;
    }else if(data.token === false){
      /*
      $.tiziDialog({
        content:data.error,
        ok:function(){
          window.location.reload();
        },
        close:function(){
          window.location.reload();
        }
      });
      */
      window.location.reload();
      return false;
    }else{
      if(data.token != '' && data.token != undefined) {
        basePageToken = data.token;
      }

      if(success == undefined) success = function(data){};
      success(data);
    }
  }
    $.tizi_token = function(options_data,type,serialize,callback_name){
        if(serialize === true){
            var len = options_data.length
            options_data[len] = {'name':'ver','value':(new Date).valueOf()};
            if(type.toLocaleLowerCase() == 'post'){
                options_data[len+1] = {'name':'token','value':basePageToken};
                options_data[len+2] = {'name':'page_name','value':basePageName};
            }
            if(callback_name){
                var len = options_data.length
                options_data[len] = {'name':'callback_name','value':callback_name};
            }
        }else{
            options_data['ver']=(new Date).valueOf();
            if(type.toLocaleLowerCase() == 'post'){
                options_data['token']=basePageToken;
                options_data['page_name']=basePageName;
            }
            if(callback_name){
                options_data['callback_name']=callback_name;
            }
        }
        return options_data;
    }
    $.tizi_ajax = function(options,serialize){  
        var defaults = {  
            url: "",  
            type: "POST",
            data: {},
            dataType: "json",
            async: true,
            success: function(){},
            error: function(){}
        };
        var options = $.extend(defaults, options);
        
        if(options['dataType']=='jsonp'){
            options['jsonp']='callback';
            options['data'] = $.tizi_token(options['data'],options['type'],serialize,options['jsonp']);

            var success=options['success'];
            callback=function(data){$.tizi_callback(data,success);}
            options['success']=function(){};
            options['error']=function(){};
        }else{          
            options['dataType']='json';
            options['data'] = $.tizi_token(options['data'],options['type'],serialize);
            
            var success=options['success'];
            options['success']=function(data){$.tizi_callback(data,success);}
        }

        $.ajax(options);
    }
})(jQuery);
/*引入原有jquery.tizi.ajax.js结束*/
// 答疑js开始
var TeacherAq={};
TeacherAq.answerAdmin = {
  //认领问题内容展示脚本js
  claimQuestionFn:function(){
    $('.claimQuestion input').click(function(){
       $.tizi_ajax({
               'url' : baseUrlName+'teacher/aq_teacher/take_question',
               'type' : 'POST',
               'dataType' : 'json',
               'data' : {
                    'question_id' : $('#question_id').val()
               },
               success : function(json, status){
                    if (json.errorcode == 1){
                        window.location = baseUrlName+'aq_teacher/aq_teacher/question_detail/'+$('#question_id').val();
                    } else {
                         $.tiziDialog({content:json.error}); // alert 
                    }
               },
               error : function(){
                    $.tiziDialog({content:"系统忙，请稍后再试"});
               }
          });
      
    })  
  },
  //待领取问题列表点击区域连接到问题详细页面
  aq_questionListFn:function(){
    $('.aq_questionList .box').click(function(){
      //window.open($(this).attr('url')); 
      window.location.href=$(this).attr('url');
    });
    $('.aq_questionList .box').hover(function(){
      $(this).find('.bd').css('color','#298d6a'); 
    },function(){
      $(this).find('.bd').css('color','');  
    })
  },
   //我的问题列表点击区域连接到问题详细页面
  aq_my_questionListFn:function(){
    $('.aQuestionCon .cf').click(function(){
      window.open($(this).attr('url')); 
    });
    $('.aQuestionCon li').hover(function(){
      $(this).find('p').css('color','#298d6a').css('cursor','pointer'); 
    },function(){
      $(this).find('p').css('color','').css('cursor','default');
    })
  },

  //老师答疑 评价分页
  aq_comment_pagination:function(offset){
    urls = baseUrlName+"aq_teacher/aq_teacher/comments";
    if(offset=='' || offset==null || offset==undefined){
      offset = '';
    }
    content = '';
    $.ajax({
              type: "GET",
              dataType: "text",
              url: urls,
        async:false,
                  data: {'rf':true,'offset':offset},
                  success: function(data) {
                  content = data;
                  if(data!='') {$(".comment_content").html(content);}
              }
      });
  },

  //老师答疑 我的问题列表
  aq_my_question_pagination:function(offset){
    urls = baseUrlName+"aq_teacher/aq_teacher/my_question";
    if(offset=='' || offset==null || offset==undefined){
      offset = '';
    }
    content = '';
    $.ajax({
              type: "GET",
              dataType: "text",
              url: urls,
        async:false,
                  data: {'rf':true,'offset':offset,'is_resolved':$('#is_resolved').val()},
                  success: function(data) {
                  content = data;
                  if(data!='') {$(".aQuestionCon").html(content);}
              }
      });
  },
  //老师上传图片答案
  aq_upload_pic_answer : function(){
        var that = this;
        $(".aqupload").each(function(){
            var node = $(this);
            var loading = staticUrlName+'image/answerquestion/loading.gif';
            var upload_id = node.attr('id');
            $('#'+upload_id).uploadify({
                'formData' : $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post'),
                'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                'uploader' : baseUrlName+'upload/taqask?id='+upload_id, //需要上传的url地址
                'multi'    : false,
                'buttonClass'     : 'choseFileBtn',
                'buttonText' :"上传文件",
                'fileTypeExts' : '*.jpg; *.png;*.gif',
                'fileSizeLimit' : '2048KB',
                'fileObjName' : upload_id,
                'width'           :102,
                'height' :28,
                'overrideEvents': ['onSelectError','onDialogClose'],
                onUploadStart:function(file){
                    $('.'+upload_id).html('<img src="'+loading+'" class="imgloading"/>');
                },
                onSWFReady:function(){
                },
                onFallback : function() {
                    $('.imgTips').html(noflash);
                },
                onSelectError : function(file, errorCode, errorMsg) {
                    switch (errorCode) {
                        case -110:
                            $.tiziDialog({content:"文件 [" + file.name + "] 过大！每张图片不能超过2M"});                        
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
                onUploadSuccess: function(file, data, response) {
                    var json = JSON.parse(data);
                    if(json.code == 1){
                        $('.'+upload_id).removeClass('red');
                        $('.'+upload_id).html('<img src="'+json.img_path+'" class="picture_urls"/>');
                        $('#_picture').val(json.img_path);
                    }else{
                        $('.'+upload_id).html('<b>'+json.msg+'</b>');
                        $('.'+upload_id).addClass('red');
                    };
                    $('.'+upload_id).siblings('.clearpic').show();//显示删除图标
                    $('.'+upload_id).siblings('.clearpic').on('click',function(){
                        $('.'+upload_id).find("img").remove();
                        $('.'+upload_id).siblings('.clearpic').hide();//隐藏删除图标
                        $('.'+upload_id).removeClass('red');
                        $("#_picture").val("");
                    });
                },
                onUploadComplete: function(file) {
                }
            });
        }); 
  },

  // 老师上传头像
  aq_teacher_avatar:function(){
        var that = this;
        $(".aqupload").each(function(){
            var node = $(this);
            var loading = staticUrlName+'image/answerquestion/loading.gif';
            var upload_id = node.attr('id');
            $('#'+upload_id).uploadify({
                'formData' : $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post'),
                'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                'uploader' : baseUrlName+'upload/taqask?id='+upload_id, //需要上传的url地址
                // 'uploader' : baseUrlName+'aq_teacher/aq_teacher/upload?id='+upload_id, //需要上传的url地址
                'multi'    : false,
                'buttonClass'     : 'choseFileBtn',
                'buttonText' :"更换头像",
                'fileTypeExts' : '*.jpg; *.png;*.gif',
                'fileSizeLimit' : '512KB',
                'fileObjName' : upload_id,
                'width'           :102,
                'height' :28,
                'overrideEvents': ['onSelectError','onDialogClose'],
                onUploadStart:function(file){
                },
                onSWFReady:function(){
                },
                onFallback : function() {
                    $('.imgTips').html(noflash);
                },
                onSelectError : function(file, errorCode, errorMsg) {
                    switch (errorCode) {
                        case -110:
                            $.tiziDialog({content:"文件 [" + file.name + "] 过大！每张图片不能超过0.5M"});                        
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
                onUploadSuccess: function(file, data, response) {
                    var json = JSON.parse(data);
                    var img_path = '';
                    if(json.code == 1){
                        img_path = json.img_path
                        $('#_picture').val(img_path);
                        //上传到oss成功
                        $.tizi_ajax({
                             'url' : baseUrlName+'aq_teacher/aq_teacher/edit_avatar',
                             'type' : 'POST',
                             'dataType' : 'json',
                             'data' : {
                                  'url' : img_path
                             },
                             success : function(json, status){
                                   $('.'+upload_id).html('<img src="'+img_path+'" class="picture_urls"/>');
                             },
                             error : function(){
                             }
                        });
                    }else{
                        $('.'+upload_id).html('<b>'+json.msg+'</b>');
                        $('.'+upload_id).addClass('red');
                    };
                },
                onUploadComplete: function(file) {
                }
            });
        });
  },
//判断考点是否超过3个
  aq_teacher_bindCheckInput:function(){
        var that = this;
        var count=0;
        $('#subject_point input').click(function(){
                var oinput = $('#subject_point input');
                that.aq_teacher_point_input_checked();
                $(this).attr('checked')?(count+=1):(count-=1);
                if(count > 3){
                    this.checked = '';
                    count-=1
                    that.aq_teacher_point_input_checked();
                }else if(count == 3){
                    that.aq_teacher_point_input_checked();
                }else{
                    for(var i=0; i<oinput.length; i++){
                      oinput[i].parentNode.style.color = '#000';
                    }
                }    
        });
    },
  // 添加点击锁定效果
    aq_teacher_point_input_checked:function(){
        var count = 0;
        var point_input = $('#subject_point input');
        for( var i=0; i<point_input.length; i++){
            var arr = [];
            if(point_input[i].checked == ''){
                arr.push(point_input[i]);
            }else{
                count+=1;        
            }
            for(var j=0; j<arr.length; arr++){
                arr[j].checked = '';
                arr[j].parentNode.style.color = '#ccc';                         
            }
         }
    },//这个逗号没有的话，就会立刻显示名字
  //提交答案
  aq_teacher_add_answer:function(){
    $('.writedownanswer').click(function(){
      var str="";  
      $("input[name='checkbox']:checked").each(function(){  
          str+=$(this).val()+",";  
      });
    //添加禁用
     TeacherAq.answerAdmin.setBtnClear($(this));
    // alert(str);
      $.tizi_ajax({
              'url' : baseUrlName+'aq_teacher/aq_teacher/add_answer',
               'type' : 'POST',
               'dataType' : 'json',
               'data' : {
                    'question_id' : $('#question_id').val(),
                    'text_answer':$("#content").html(),
                    'picture_url':$('#_picture').val(),
                    'diff':$('input[name="radio_diff"]:checked').val(),
                    'points':str
               },
               success : function(json, status){
                    if (json.errorcode == 1){
                         $.tiziDialog({content:"操作成功",okc:function(){window.location.reload();}});
                         window.location.reload();
                    } else {
                          $.tiziDialog({content:json.error,okc:function(){window.location.reload();}});
                          window.location.reload();
                    }
               },
               error : function(){
                    $.tiziDialog({content:"系统忙，请稍后再试",okc:function(){window.location.reload();}});
                    window.location.reload();
               }
      });
    })
  },
  //添加btn重复提交禁用功能
  setBtnClear:function(ele){
    var ele = ele;
    ele.css({'background':"#eee","color":"#2a8d6a","border":"1px solid #ddd"});
    ele.attr("disabled",true); 
  },
  scrollTitleMsgFn:function(){
    var _text='[新问题]有新的题目，请查看... ';
    var _timerID;
   function scrollTitleMsg() {
        clearTimeout(_timerID)
        document.title=_text.substring(1,_text.length)+_text.substring(0,1);
        _text=document.title.substring(0,_text.length);
        _timerID = setTimeout(scrollTitleMsg, 1000);
      }
   scrollTitleMsg();
  }
}
// 答疑js结束
