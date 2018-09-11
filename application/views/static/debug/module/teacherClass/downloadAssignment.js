define(function(require,exports){
    require('tizi_ajax');
    require('tiziDialog');

    exports.homeworkDownload = {
        downWord : $(".down-word-btn"),
        init:function(){
            var _this = this;
            //学生  下载 布置好的作业
            $(".downhw").click(function(){
                // a = _this.stu_has_download($(this).attr('assid'));//置位is_download
                _this._downhw($(this).attr('assid'),'student');
            });
            //老师  下载 布置好的作业 
            $(".teacherdownhw").click(function(){
                _this._downhw($(this).attr('assid'),'teacher');
            });
        },
        //学生/老师  下载 布置好的作业  
        _downhw:function(slid,role){
            if(this.downWord.attr('disabled') == 'disabled'){
                return false;
            };
            var _icon = '';
            var content = '是否确定下载此试卷？';
            var width = 360;
            _icon = 'question';
            $.tiziDialog({
                title:"下载作业",
                content:content,
                icon:_icon,
                width:width,
                init:function(){                
                    $('.paperCaptchaWord').addClass('undis');
                },
                ok:function(){              
                    this_a = exports.homeworkDownload;
                    var downWord = $("a[slid='"+slid+"'][class='down-word-btn']");
                    downWord.html('下载中');
                    downWord.attr('disabled','disabled');
                    this_a.hw_download(slid,role,'paper',function(){
                        this_a = exports.homeworkDownload;
                        downWord.html('下载');
                        downWord.removeAttr('disabled');
                    });
                    //是学生就去置位 is_download==1
                    if(role=='student'){
                        $.tizi_ajax({
                            url: baseUrlName + 'student/task/download_hw_mark',
                            type: 'POST',
                            data: {assid:slid},
                            dataType: 'json',
                            success: function(data){
                                return data.errorcode;
                            }
                        });
                    }
                },
                cancel:true
            });
        },
        //教师班级空间，学生端  下载作业
        hw_download:function(slid,role,download_type,unlock){ // role could be student / teacher
            var paper_version = 'docx';//$('.downWin').find("input[type=radio][name="+download_type+"_version]:checked").val(); // this.setDownloadChecked(download_type,'version',paper_version);
            var paper_type = role ;//$('.downWin').find("input[type=radio][name="+download_type+"_type]:checked").val();// this.setDownloadChecked(download_type,'type',paper_type);
            var post_data = {
                'paper_version': paper_version, 
                'paper_type': 'student',//老师下载的也没有答案  //paper_type,
                'save_log_id':slid,
                'download_type':download_type,
                'assid':slid
            };
            var paper_size = 'A4';//$('.downWin').find("input[type=radio][name=paper_size]:checked").val(); // this.setDownloadChecked(download_type,'size',paper_size);
            post_data['captcha_name'] = 'PaperDownBox';
            post_data['captcha_word'] = $('.PaperDownBoxWord').val();
            post_data['paper_style'] = 'default';
            post_data['paper_size'] = paper_size;
            baseuri = baseUrlName + 'paper/download/down_assignment';

            $.tizi_ajax({
                url: baseuri, 
                type: 'POST',
                data: post_data,
                dataType: 'json',
                success: function(data){
                    unlock();
                    if(data.errorcode == false) {                       
                        $.tiziDialog({content:data.error});
                    }else{
                        //var url=baseUrlName + 'download/paper?url='+data.url
                        //    +'&file_name='+data.file_name+'&download_type='+download_type;
                        var durl=data.durl + '?fileUrl=' + data.dlink + '&fileName=' 
                            + data.file_name + '&token=' + data.dtoken;
                        ga('send', 'event', 'Download-Paper-'+download_type+'-'+role+'-Assigned', 'Download', data.fname);
                        require('tizi_download').force_download(durl,data.fname);
                    }
                }
            })
        } 
    }
});