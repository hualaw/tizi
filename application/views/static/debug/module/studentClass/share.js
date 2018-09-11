define(function(require, exports){ 
   require("tiziDialog");
   require('tizi_ajax');
   var Teadownload = require("tizi_download");

//班级分享翻页 定义
    exports.init_sharelist_page = {
        initPage:function(){
            if(typeof wordbooklist_page != 'function'){
                sharelist_page = function(page){
                    exports.page(page);                 
                }
            }
        }
    };            
    exports.page = function(page){
        exports.get_files(exports.getUrlData(page));
    } 
    exports.getUrlData=function(ele){
        ele = ele || 1;
        var finalData = $.extend({page:ele});
        return finalData;
    } 
    exports.get_files = function(getData){
        getData['ver'] = (new Date).valueOf();
        // seajs.use('tizi_ajax',function(){ //不知为何这里要use一下
        // require('tizi_ajax');
        $.tizi_ajax({
            'url' : baseUrlName + 'class/student_class/resource_page',
            'type' : 'GET',
            'dataType' : 'json',
            'data' : {page:getData['page'],flip:true},
            success : function(data){
                if (data.errorcode == true){
                    $('.stuClassBox').html(data.html);
                     exports.download_share();
                }else{
                    $.tiziDialog({content:'系统忙，请稍后再试'});
                }
            },
            error : function(){
                $.tiziDialog({content:'系统繁忙，请稍后再试'});
            }
        });
        // });
    } 
/*班级 分享文件list   分页 相关function  over */

    //在班级文件 预览页面  下载button
    exports.download_class_share = function(){
        $('.download_share').live('click',function(){
            var file_name = $(this).attr('file_name');
            var file_id = $(this).attr('file_id');
            var share_id = $(this).attr('share_id');
            $.tizi_ajax({
                    url: baseUrlName + "teacher/cloud/download_verify", 
                    type: 'POST',
                    data: {'file_id' :file_id,'file_name':file_name},
                    dataType: 'json',
                    success: function(data){
                        if(data.errorcode == false) {
                            $.tiziDialog({content:data.error});
                        }else{
                            if(data.file_type==1){
                                // var down_url=baseUrlName + "student/cloud/download?url="+data.file_path
                                // +'&file_name='+data.file_encode_name+'&file_id='+data.file_id+'&share_id='+share_id;
                                // Teadownload.force_download(down_url,data.fname,true);
                                var down_url=baseUrlName + "teacher/cloud/download/?url="+data.file_path
                                    +'&file_name='+data.file_encode_name+'&file_id='+data.file_id+'&share_id='+share_id;
                                // Teadownload.down_confirm_box(down_url,data.fname,false,share_id);    
                                Teadownload.down_confirm_box(data.file_path,data.fname,false,share_id);    //ghl的下载接口
                            }else{
                                // stu_force_download(data.url,data.fname,true,true,share_id);
                                Teadownload.down_confirm_box(data.url,data.fname,true,share_id);
                            }
                        }
                    }
            });
        });
   };

   //班级空间文件列表  下载
   exports.download_share = function(){
        $('.downloadBtn').click(function(){
            var file_name = $(this).attr('file_name');
            var file_id = $(this).attr('file_id');
            var share_id = $(this).attr('share_id');
            $.tizi_ajax({
                    url: baseUrlName + "teacher/cloud/download_verify", 
                    type: 'POST',
                    data: {'file_id' :file_id,'file_name':file_name},
                    dataType: 'json',
                    success: function(data){
                        if(data.errorcode == false) {
                            $.tiziDialog({content:data.error});
                        }else{
                            if(data.file_type==1){
                                // var down_url=baseUrlName + "student/cloud/download?url="+data.file_path
                                // +'&file_name='+data.file_encode_name+'&file_id='+data.file_id+'&share_id='+share_id;
                                // Teadownload.force_download(down_url,data.fname,true);
                                var down_url=baseUrlName + "teacher/cloud/download/?url="+data.file_path
                                    +'&file_name='+data.file_encode_name+'&file_id='+data.file_id+'&share_id='+share_id;
                                // Teadownload.down_confirm_box(down_url,data.fname,false,share_id);
                                Teadownload.down_confirm_box(data.file_path,data.fname,false,share_id);//ghl的下载接口
                            }else{
                                // stu_force_download(data.url,data.fname,true,true,share_id);
                                Teadownload.down_confirm_box(data.url,data.fname,true,share_id);
                            }
                        }
                    }
            });
        });
   };

   //学生的force下载
    // var stu_force_download = function(url,fname,openbox,is_qiniu,share_id){
    //     if(is_qiniu == 'undefined'){
    //         url = url + '&session_id=' + $.cookies.get(baseSessID);
    //     }
    //     var ie_ver = ie_version();
    //     if(openbox == true || ie_ver==6.0 ||ie_ver==7.0 || ie_ver==8.0){
    //         if(fname == '' || fname == undefined) fname = "是否下载？";
    //         else fname = "是否下载《" + fname + "》？";
    //         $.tiziDialog({
    //             content: fname,
    //             ok:null,
    //             cancel:false,
    //             dblclick:false,
    //             icon:null,
    //             button:[{
    //                 name:'点击下载',
    //                 href:url,
    //                 className:'aui_state_highlight clickDown',
    //                 target:'_self'
    //             }]
    //         });
    //         $('.clickDown').click(function(){
    //             if(is_qiniu==true){
    //                 $.tizi_ajax({
    //                         url: baseUrlName + "resource/cloud_base/add_download_count", 
    //                         type: 'POST',
    //                         data: {'share_id':share_id},
    //                         dataType: 'json',
    //                         success: function(data){  }
    //                 });
    //             };
    //         });
    //         return false;
    //     }
    //     window.location.href=url;
    // };
    // var ie_version = function() {
    //     //var userAgent = window.navigator.userAgent.toLowerCase();
    //     var ie = $.browser.msie;
    //     var version = $.browser.version;
    //     if(ie) return version;
    //     else return false;
    // };




});