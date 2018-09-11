define(function(require,exports){
    //学生 专项练习开始
    require("tizi_ajax");
    require("tiziDialog");
    // var Teadownload = require("tizi_download");
    var practice = {
        vnum     : $('.vnum'),
        subMark  : $('.exercise_subject .sub_mark'),
        progress : $('.count table').find('.progress'),
        type_choose : $('.type_choose'),
        analysisBtn : $('.analysis_btn'),
        subjectCards  : $('.subject_answer_card_btn span'),
        answerCardBox : $('.subject_answer_card_box'),
        subjectDelete : $('.exercise_subject .delete'),
        specialTestTitle :$('.special_test_title'),
        exercise_subject : $('.exercise_subject'),
        exerciseSubjectBox : $('.exercise_subject_box'),
        // 答题数进度条
        itemProgressBar : function(){ 
            var td2 = this.progress;
            var td3 = this.progress.next();
            td2.each(function(){
                var count = $(this).attr('count');
                var countAll = $(this).attr('countAll');
                $(this).find('span').html(count+'/'+countAll);
                $(this).find('strong').width(count/countAll*$(this).width());
            });
        },
        // 掌握程度
        graspLever : function(){ 

        },
        // tab切换
        tabs : function(tabTit,on,tabCon){
            $(tabCon).each(function(){
                $(this).children().eq(0).show();
            });
            $(tabTit).children().click(function(){
                $(this).addClass(on).siblings().removeClass(on);
                var index = $(tabTit).children().index(this);
                $(tabCon).children().eq(index).show().siblings().hide();
            });
        },
        // table隔行换色 
        changeBackColor : function(table){
            $(table).find('tr:even').addClass('gray');
        },
        // 展开解析
        analysis : function(){ 
            this.analysisBtn.click(function(){
                $(this).parent().siblings('.detial').toggle();
                $(this).parent().prev().toggle();
                $(this).toggleClass('analysis_btn_click');
                if($(this).hasClass('analysis_btn_click')){
                    $(this).html('收起解析');
                }else{
                    $(this).html('展开解析');
                }
            });
        },
        // 删除此题-排序
        deleteSort : function(){
            var subject_num = this.exerciseSubjectBox.find('.sub-title-nu');
            subject_num.each(function(index,element){
                $(element).text(index+1);
            });
        },
        // 删除此题 
        deleteSubject : function(){
            var _index = $(this).index();
            this.subjectDelete.click(function(){
                practice.exerciseSubjectBox.find('.exercise_subject').eq(_index).remove();
                practice.deleteSort();
            });
        },
        // 题目标注
        subjectMark : function(){ 
            this.subMark.click(function(){
                $(this).toggleClass('active');
                var pid = $(this).parents(".exercise_subject").find("input[name='pid']").val();
                var order = $(this).parents(".exercise_subject").find(".sub-title-nu").html();
                if(pid == undefined || pid == '') return;
                $.tizi_ajax({
                    url: baseUrlName+'student/practice/mark',
                    data:{
                        'pid':pid
                    },
                    type:'POST',
                    success:function(data){
                        if($(".subject_answer_card_box .type_choose").find("li:eq("+(order-1)+")").find(".card_mark").length < 1){
                            $(".subject_answer_card_box .type_choose").find("li:eq("+(order-1)+")").append("<span class='card_mark'></span>");
                        }else{
                            $(".subject_answer_card_box .type_choose").find("li:eq("+(order-1)+")").find(".card_mark").remove();
                        }
                    }
                })

            })
        },
        // 答题卡显示隐藏
        subjectCard : function(){ 
            this.subjectCards.click(function(){
                $(this).toggleClass('card_show');
                practice.answerCardBox.toggle();
            });
        },
        // 点击答题卡跳转到相应题目
        subjecToTop : function(){ 
            this.type_choose.find('li').each(function(){
                var _index = $(this).index();
                $(this).click(function(){
                    if($('.exercise_subject_box').eq(0).is(':hidden')){
                        $('.subject_type li').eq(0).click();
                    }
                    var _top = practice.exercise_subject.eq(_index).offset().top;
                    $(document).scrollTop(_top);
                });
            });
        },
        // 题目难易度分布 开始
        ListShow : (function(){ 
            var b, c, g, j;
            function a(k) {
                b = k.id;
                g = k.percent;
                j = k.width;
                styleData = h();
                bindItems = d()
            }
            function d() {
                var o = [];
                m = $(".vote-item-wrap");
                for (var n = 0,
                    k = m.length; n < k; n++) {
                        o.push(m[n].children[1]);
                    }
                return o
            }
            function h() {
                var o = [];
                //var n = ["#5dbc5b", "#6c81b6", "#9eb5f0", "#a5cbd6", "#aee7f8", "#c2f263", "#d843b3", "#d8e929", "#e58652", "#e7ab6d", "#ee335f", "#fbe096", "#ffc535"];
                var n = ["#ff9900"];
                var q = n.slice();
                for (var p = 0,
                        l = g.length; p < l; p++) {
                            var k = Math.floor(Math.random() * q.length);
                            o.push(q[k]);
                            q.splice(k, 1);
                            if (q.length == 0) {
                                q = n.slice()
                            }
                        }
                return o
            }
            function f(l, k) {
                $(l.children[0]).css("background-color", k.color);
                $(l.children[1]).css({
                    'background-color': k.color,
                    'width': '0px'
                });
                $(l.children[2]).css("background-color", k.color);
            }
            function i() {
                var n = [];
                var l = [];
                for (var m = 0,
                        k = g.length; m < k; m++) {
                            f(bindItems[m], {
                                color: styleData[m]
                            });
                            n.push(bindItems[m].children[1]);
                            l.push(Math.round(g[m] * j))
                        }
                e(n, 0, l, c)
            }
            function e(p, o, l, n) {
                for (var r = 0,
                        q = g.length; r < q; r++) {
                            $(p[r]).animate({
                                width: l[r]
                            },
                            "slow");
                        }
            }
            return {
                init: a,
                go: i
            }
        })(),
        // 统计报告 题目难易度分布条的初始化
        percentsBar : function(){ 
            var all_item  = parseInt($('.vnum').eq(0).attr('itemCount'))+parseInt($('.vnum').eq(1).attr('itemCount'))+parseInt($('.vnum').eq(2).attr('itemCount'))+parseInt($('.vnum').eq(3).attr('itemCount'))+parseInt($('.vnum').eq(4).attr('itemCount'));//总数
            var percent_1 = $('.vnum').eq(0).attr('itemCount')/all_item; //容易
            var percent_2 = $('.vnum').eq(1).attr('itemCount')/all_item; //较易
            var percent_3 = $('.vnum').eq(2).attr('itemCount')/all_item; //一般
            var percent_4 = $('.vnum').eq(3).attr('itemCount')/all_item; //较难
            var percent_5 = $('.vnum').eq(4).attr('itemCount')/all_item; //困难
            var percents  = [percent_1,percent_2,percent_3,percent_4,percent_5];// 进度条百分比分布数值
            this.ListShow.init({ 
                id: 'appVoteBox',
                percent:percents, 
                width: 130 - 2 
            });
        },
        // 百分比计算
        percentsFn : function(){
            this.vnum.each(function(){
                var all_item  = parseInt($('.vnum').eq(0).attr('itemCount'))+parseInt($('.vnum').eq(1).attr('itemCount'))+parseInt($('.vnum').eq(2).attr('itemCount'))+parseInt($('.vnum').eq(3).attr('itemCount'))+parseInt($('.vnum').eq(4).attr('itemCount'));//总数
                var this_item = $(this).attr('itemCount');
                if(all_item == 0){
                    all_item = 1;
                }
                var this_item_p = parseInt(this_item)/all_item;
                $(this).html(this_item +'（'+ (this_item_p*100).toFixed(2) +'%）');
            });
        },
        //flash 数学
        countdown : {
            flash_import:function(){
                //var source_url = 'http://192.168.11.121/application/views/static/js/tools/practice_flash/pig/';
                var id = $("#p_c_id").val();
                var path = $("#path").val();
                var source_url = staticBaseUrlName+'flash/practice_flash/'+path+'/';
                var data_url =  baseUrlName+'student/game1/get_question/'+id;
                var option = ['A','B','C','D'].slice(0,$(".option_num").val());
                var game_help = $(".game_help").val();
                var subject_id = $(".subject_id").val();
                var data = [data_url,source_url,option,game_help,subject_id];
                return data;
            },
            //数学
            flash_submit:function(id,result,selected_option){
                var submit_url = baseUrlName+'student/game1/submit';
                $.tizi_ajax({
                    url:submit_url,
                    data:{
                        "id":id,
                    "result":result,
                    "selected_option":selected_option
                    },
                    type:'POST',
                    success:function(data){

                    }
                })
            }
        },
        challenge : {
            flash_import : function(){
                var id = $("#p_c_id").val();
                var path = $("#path").val();
                var source_url = staticBaseUrlName+'flash/practice_flash/'+path+'/';
                var data_url =  baseUrlName+'student/game2/get_question/'+id;
                var option = ['A','B','C','D'].slice(0,$(".option_num").val());
                var game_help = $(".game_help").val();
                var subject_id = $(".subject_id").val();
                var data = [data_url,source_url,option,game_help,subject_id];
                return data;
            },
            flash_submit:function(id,result,selected_option){
                var submit_url = baseUrlName+'student/game2/submit';
                $.tizi_ajax({
                    url:submit_url,
                    data:{
                        "id":id,
                    "result":result,
                    "selected_option":selected_option
                    },
                    type:'POST',
                    success:function(data){

                    }
                })
            }       
        },
        game_v3 : {
            flash_import : function(){
                var id = $("#p_c_id").val();
                var path = $("#path").val();
                var token = $(".token").attr("id");
                var pname = $(".pname").attr("id");
                var source_url = staticBaseUrlName+'flash/practice_flash/'+path+'/';
                var data_url =  baseUrlName+'student/game3/get_question/'+id;
                var submit_url = baseUrlName+'student/game3/submit';
                var data = [data_url,source_url,token,pname,submit_url,10,5,5];

                return data;
            },
            flash_submit:function(id,result,selected_option,time){
                var submit_url = baseUrlName+'practice/game3/submit';
                $.tizi_send({
                    url:submit_url,
                    data:'id='+id+"&result="+result+"&selected_option="+selected_option,
                    type:'POST',
                    success:function(data){

                    }
                })
            }
        },
        favorites: {
            mark: function(){
                $('.exercise_subject .sub_mark').click(function(){
                    $(this).parents(".exercise_subject").fadeOut(500);
                });
            }
        },
        //flash 英语，语文
        //题目难易度分布 数据
        getPracticeAjax : function(){
            //report
            var p_c_id = $("input[name='p_c_id']").val();
            if(p_c_id !== undefined){
                $.ajax({
                    url: baseUrlName+'student/practice_test/report/'+p_c_id,
                    type:'GET',
                    success:function(data){
                        data = eval("("+data+")");           
                        $(".all_num").html(data.total_num);
                        $(".average_num").html(data.total_num);
                        $(".report_rank").html(data.rank);
                        $(".report_user_num").html(data.user_num);
                        mastery = data.mastery;
                        $.each(mastery,function(key,val){
                            var tpl = "<tr> <td class='indent'>"+val.name+"</td> <td class='progress' count='"+val.correct_num+"' countAll='"+val.total_num+"'><span>"+val.correct_num+"/"+val.total_num+"</span><strong></strong></td> <td><span class='level star_"+val.mastery+"'></span></td></tr>";
                            $(".mastery").append(tpl);
                        });
                        //$(".mastery").parent().css('display','block');
                        for(i=0;i<5;i++){
                            var di = data.difficulty[i];
                            $("#voteItem"+i).find(".vnum").attr("itemcount",di);
                        }
                    } 
                })
            }
        },
        getPracticeData : function(){
            var p_c_id = $("input[name='p_c_id']").val();
            /*
               $(".tab_list").find("li").click(function(){
               num = $(".tab_list").find("li").index(this);
               $.ajax({
               url:'/practice/practice_test/tab/'+num,
               type:'GET',
               success:function(data){

               }
               })
               });
               */
            $(".tab_list li:eq(1)").click(function(){
                practice.percentsBar();// 统计报告 题目难易度分布条的初始化
                practice.ListShow.go();//题目难易度分布
                practice.percentsFn(); //统计报告 难易度分布 百分比计算
                practice.itemProgressBar();//答题数进度条
                $('.mainContainer').removeAttr('style');
            })
            $(".tab_list li:eq(2)").click(function(){
                $.ajax({
                    url: baseUrlName+'student/practice_test/record/'+p_c_id,
                type:'GET',
                success:function(data){
                    data = eval("("+data+")");           
                    nav_tpl = "<tr> <td class='gray w130'>时间</td> <td class='gray w120'>类型</td> <td class='gray w130'>题目总数</td> <td class='gray w120'>正确数量</td> <td class='gray w120'>解析</td> <td class='gray'>报告</td></tr>";
                    $(".record").html(nav_tpl);
                    $.each(data,function(key,val){

                        if(val.status == 1){
                            a_u = "<a href='"+val.a_u+"'>查看解析</a>";
                            r_u = "<a href='"+val.r_u+"'>查看报告</a>";   
                        }else{
                            a_u = '未完成';
                            r_u = "<a href='"+val.r_u+"'>继续完成</a>";   
                        }

                        var tpl = "<tr> <td>"+val.start_time+"</td> <td>"+val.s_p_type+"</td> <td>"+val.question_num+"</td><td>"+val.correct_num+"</td> <td>"+a_u+"</td> <td>"+r_u+"</td> </tr>";
                        $(".record").append(tpl);


                    })
                    $('.mainContainer').removeAttr('style');
                }
                })

            })
            $(".tab_list li:eq(3)").click(function(){
                $.ajax({
                    url: baseUrlName+'student/practice_test/wrong_questions/'+p_c_id,
                type:'GET',
                success:function(data){
                    data = eval("("+data+")");           
                    var nav_tpl = "<tr> <td class='gray w430 indent'>知识点名称</td> <td class='gray w100'>错题数</td> <td class='gray w100'>查看题目</td> <td class='gray'>练习</td> </tr>";
                    $(".wrong_questions").html(nav_tpl);
                    $.each(data,function(key,val){
                        var tpl = "<tr><td class='indent'>"+val.name+"</td> <td>"+val.num+"</td> <td><a href='"+baseUrlName+"student/practice/wrong_questions_show/"+p_c_id+"/"+key+"'>查看题目</a></td> <td><a href='"+baseUrlName+"student/practice/training/"+p_c_id+"?c="+key+"'>练习5道题</a></td> </tr>";
                        $(".wrong_questions").append(tpl);
                    })
                    $('.mainContainer').removeAttr('style');
                } 
                })           
            })
            $(".tab_list li:eq(4)").click(function(){
                $.ajax({
                    url: baseUrlName+'student/practice_test/favorites/'+p_c_id,
                type:'GET',
                success:function(data){
                    data = eval("("+data+")");
                    var nav_tpl = "<tr> <td class='gray w530 indent'>知识点名称</td> <td class='gray w100'>错题数</td> <td class='gray'>查看题目</td></tr>";
                    $(".favorites").html(nav_tpl);

                    $.each(data,function(key,val){
                        var tpl = "<tr><td class='indent'><span>"+val.name+"</span></td> <td>"+val.wrong_num+"</td> <td><a href='"+baseUrlName+"student/practice/favorites_show/"+p_c_id+"/"+val.id+"'>查看题目</a></td></tr>";
                        $(".favorites").append(tpl);
                    })
                    $('.mainContainer').removeAttr('style');
                } 
                }) 
            }) 
        },
        practice_training :{
            v_num :  'v='+new Date().getTime(),
            per_save : function(){
                $(".question_option").find("li").click(function(){
                    if($(this).hasClass("active")) return false;
                    var ac_len = $(this).parents("ul").find("li.active").length;
                    if(!ac_len){
                        var c_num = parseInt($(".c_num").text())+1;
                        $(".c_num").text(c_num);
                    }
                    var s_p_id = $("input[name='s_p_id']").val();
                    var question_id = $(this).parents(".homeworkBlock").find(".q_id").val();
                    var input = $(this).text();
                    var order = $(this).parents(".homeworkBlock").find(".order").val();
                    $.tizi_ajax({
                        url:baseUrlName+'student/practice_training/per_save',
                        data:{
                            's_p_id':s_p_id,
                            'pid':question_id,
                            'input':input,
                            "order":order
                        },
                        type:"POST",
                        dataType : 'json',
                        success:function(data){

                        }
                    })
                })  
            },
            get_answer : function(){
                var id = $("input[name='s_p_id']").val();
                $.ajax({
                    url: baseUrlName+'student/practice_training/get_answer?'+this.v_num,
                    type:'GET',
                    data:'id='+id,
                    dataType:"json",
                    success:function(data){
                        data = eval("("+data.msg+")");
                        if(data != ''){
                            $.each(data,function(key,val){
                                $(".question_"+val.order).find(".question_option").find("li:eq("+(Number(val.index)-1)+")").addClass("active");
                            })
                        }
                        $("#course_content_online").find(".c_num").text($("#course_content_online").find("li.active").length);
                        practice.set_question_num();
                    }
                })
            },
            test:function(){
                var pause_tpl = '<p>暂停啦.</p>';
                var next_continue_tpl = 
                    '<p>确认要离开此页面?</p>';
                var url = $(".backhome").attr("href");
                var s_p_id = $("input[name='s_p_id']").val();

                $(".nextDo").click(function(){
                    $.tiziDialog({
                        content:next_continue_tpl,
                        ok:function(){
                            var p_c_id = $("#p_c_id").val();
                            $.tizi_ajax({
                                url:baseUrlName+"student/practice_training/pause",
                                data:{
                                    "s_p_id":s_p_id
                                },
                                dataType:"json",
                                type:"POST",
                                success:function(data){
                                    if(data.status == 2){
                                        var tip_msg = '您需要帮助您的孩子创建一个帐号后才可以进行专项练习,请点击页面顶部右侧的 "学生专区" 进行创建';
                                        var back_url = $(".backhome").attr("href");
                                        $.tiziDialog({
                                            content:tip_msg,
                                            ok:function(){
                                                window.location.href=back_url;        
                                            },
                                            cancel:true
                                        })
                                    }else{
                                        window.location.href= url;
                                    }
                                }
                            })
                        },
                        cancel:true
                    })
                })
                //时钟
                var se;
                var m = Number($(".minute").val());
                var s = Number($(".second").val());
                time_update = function (){
                    if(s>0 && (s%60)==0){
                        m+=1;s=0;
                    }
                    if(s<10){
                        s = '0' + s;
                    }
                    if(m<10){
                        m = '0' + m;
                    }
                    t= m+":"+s;
                    s = Number(s);
                    m = Number(m);
                    $(".showtime").html(t);
                    s+=1;
                }
                function startclock(time_update){
                    se=setInterval("time_update()",1000);
                }
                function pauseclock(){
                    clearInterval(se);
                }
                $(".over").click(function(){
                    submit_tpl = '确认要提交吗?';
                    $.tiziDialog({
                        okVal:'确认提交',
                        cancel:true,
                        content:submit_tpl,
                        ok:function(){
                            var id = $("input[name='s_p_id']").val(); 
                            $.tizi_ajax({
                                url: baseUrlName+"student/practice_training/submit",
                                data:{
                                    "id":id
                                },
                                type:"POST",
                                success:function(data){
                                    if(data.status == 99){
                                        id = data.msg;
                                        window.location.href=baseUrlName+'student/practice/training/report/'+id;        
                                    }else if(data.status == 2){
                                        var tip_msg = '您需要帮助您的孩子创建一个帐号后才可以进行专项练习,请点击页面顶部右侧的 "学生专区" 进行创建';
                                        var back_url = $(".backhome").attr("href");
                                        $.tiziDialog({
                                            content:tip_msg,
                                            ok:function(){
                                                window.location.href=back_url;        
                                            },
                                            cancel:true
                                        })

                                    }
                                }
                            });
                        }
                    });
                })
                startclock();
            }
        },
        initHover:function(){
            //根据右侧内容id判断左侧菜单当前内容
            if($('.mainContainer').attr('pagename') != undefined){
                $('#memberMenu li').each(function(i){
                    if($('.mainContainer').attr('pagename') == $('#memberMenu li').eq(i).attr('pagename')){
                        $('#memberMenu li').eq(i).attr('class','active');   
                    }
                })
            }
        },
        categoryTab:function(){
            var url = window.location.href;
            var map =['/record/','/wrong_questions/'];
            $(".PraRight").find("a:eq(0)").addClass("titleActive");
            for(var i=0;i<map.length;i++){
                if( url.indexOf(map[i]) != -1 ){
                    $(".PraRight").find("a").removeClass("titleActive");
                    $(".PraRight").find("a:eq("+(i+1)+")").addClass("titleActive");
                }
            }
        },
        set_question_num:function(){

            var n = $(".homeworkBlock").length; 
            $(".a_num").text(n);
            var c = $(".course_info_item").find("li.active").length;
            $(".c_num").text(c);
        },

        // 初始化
        init : function(){
            practice.getPracticeData();
            practice.tabs(".tab_list","active",".tab-bd");//专项练习 tab切换
            practice.tabs(".subject_type","active",".practice_version");//题目类别 tab切换
            practice.tabs(".nav_ul","active",".tab-bd-3");//左侧导航 tab切换
            practice.changeBackColor('.table1');// table隔行换色1
            practice.changeBackColor('.table2');// table隔行换色2  
            practice.deleteSort(); //删除此题-排序
            practice.analysis(); //展开解析
            practice.subjectMark(); // 题目标注
            practice.deleteSubject(); // 删除此题
            practice.subjectCard(); // 答题卡显示隐藏
            practice.subjecToTop(); //点击答题卡跳转到相应题目
            practice.getPracticeAjax(); // 获取题目难易度分布的数据
            practice.initHover(); // 获取题目难易度分布的数据



            //右侧标题加class  这个是临时加的 后台加上之后会删了
            practice.categoryTab(); 
        }
    };
    exports.practice = practice;
    //学生 专项练习开始

    /*学生端做作业do_homework.js开始*/
    var errorCorrection = {
        haveErrorUploadImages:function(){        
            var that = this;
            var fbupload = $(".haveErrorForm").find(".fbupload"); //替换防止反复提取
            fbupload.each(function(){
                var node = $(this);
                var loading = staticUrlName+'image/answerquestion/loading.gif';
                var upload_id = node.attr('id');
                $('#'+upload_id).uploadify({
                    'formData' : $.tizi_token({'session_id':$.cookies.get(baseSessID)},'post'),
                    'swf'      : staticBaseUrlName+staticVersion+'lib/uploadify/2.2/uploadify.swf',
                    'uploader' : baseUrlName+'upload/feedback?id='+upload_id, //需要上传的url地址
                    'multi'    : false,
                    'buttonClass': 'choseFileBtn',
                    'buttonText' :"上传图片",
                    'fileTypeExts' : '*.jpg; *.png;*.gif;*.bmp',
                    'fileSizeLimit' : '2048KB',
                    'fileObjName' : upload_id,
                    'button_image_url':baseUrlName,
                    'width'           :102,
                    'height' :28,
                    'overrideEvents': ['onSelectError','onDialogClose'],
                    onUploadStart:function(file){
                        $('.haveErrorForm .'+upload_id).html('<img src="'+loading+'" class="imgloading"/>');
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
                        var upload_id_img = $('.haveErrorForm .'+upload_id);
                        if(json.code == 1){
                            var img_path = json.img_path;
                            that.drawImage(img_path,92,71,upload_id);
                            upload_id_img.removeClass('red');
                            upload_id_img.addClass('upladpicSpan').removeClass("upladpic");
                            // //往页面上的#picture_urls写
                            // var ps = $('#picture_urls').val();
                            // ps+=(img_path)+',';
                            // $('#picture_urls').val(ps);
                            // console.log($('#picture_urls').val());
                        }else{
                            upload_id_img.html('<b>'+json.msg+'</b>');
                            upload_id_img.addClass('red');
                        };
                        upload_id_img.siblings('.clearpic').show();//显示删除图标
                        upload_id_img.siblings('.clearpic').on('click',function(){
                            upload_id_img.find("img").remove();
                            upload_id_img.siblings('.clearpic').hide();//隐藏删除图标
                            upload_id_img.removeClass('red');
                            upload_id_img.removeClass('upladpicSpan').addClass("upladpic");;//移除背景图片
                        });
                    },
                    onUploadComplete: function(file) {
                    }
                });
            });
        },
        errorReport: function (){
            var paperTip = $(".paperTip");
            var question_id = '';
            var category_id = '';
            paperTip.live('click',function(){
                require.async(upload_flash);
                question_id = $(this).attr("data-question-id"); //获取唯一题目id.
                category_id = $(this).attr("data-category-id");
                $(".haveErrorQId").val(question_id);//注入隐藏域id
                $(".haveErrorQIdSpan").html(question_id);//显示id
                $.tiziDialog({
                    id:'createHaveError',
                    title:'题目纠错',
                    content:$('#haveErrorPop').html().replace('haveErrorForm_beta','haveErrorForm'),
                    icon:null,
                    width:400,
                    init:function(){
                        //添加上传图片脚本
                        Student.errorCorrection.haveErrorUploadImages();
                    },
                    ok:function(){
                        $('.haveErrorForm').submit(); //提交
                        return false;
                    },
                    cancel:true,
                    close:function(){
                        $(".haveErrorForm").find(".aqupload").each(function(){
                            var upload_id = $(this).attr('id');
                            $('#'+upload_id).uploadify('destroy');
                        });
                        $(".choseFile .error").remove();
                    }
                });
                //添加验证
                Common.valid.paperHaveError.addPaperHaveError();
            });
        },
        drawImage:function(src,width,height,upload_id){
            var image=new Image();
            var Img = new Image();//返回值
            image.src=src;
            image.onload = function(){
               if(image.width>width||image.height>height){
                    //现有图片只有宽或高超了预设值就进行js控制 
                    w=image.width/width;
                    h=image.height/height;
                    if(w>h){
                        //宽比高大 
                        //定下宽度为width的宽度 
                        Img.width=width; 
                        //以下为计算高度 
                        Img.height=image.height/w;
                    }else{
                        //高比宽大 
                        //定下宽度为height高度 
                        Img.height=height; 
                        //以下为计算高度 
                        Img.width=image.width/h; 
                    }
                }else{
                    h=image.height/height;
                    Img.width=image.width/h;
                    Img.height=height;
                }
                $('.haveErrorForm .'+upload_id).html('<img src="'+src+'" class="picture_urls" width="'+Img.width+'" height="'+Img.height+'"/>');
             }
       }
    }
    exports.errorCorrection = errorCorrection;
    /*学生端做作业do_homework.js结束*/

    //学生个人中心开始
    var personal = {
        //评论弹出层
        commentWin : function(){
            var ele = $('.comment span');
            var close  = $('.commentClose');
            ele.each(function(){
                $(this).live('click',function(){
                    var l = $(this).position().left-200;
                    var t = $(this).position().top-100;
                    var win = $(this).parent().next().children();
                    win.show().css({'left':l+'px','top':t+'px'});
                });
            });
            //关闭弹出层
            close.each(function(){
                $(this).live('click',function(){
                    $(this).parent().hide();
                });
            });
        },
        // tab 选项卡
        homeworkTab : function(tabTit, on, tabCon){
            $(tabCon).each(function() {
                $(this).children().eq(0).show().siblings().hide();
            });
            $(tabTit).each(function() {
                $(this).children().eq(0).addClass(on);
            });
            $(tabTit).children().click(function() {
                $(this).addClass(on).siblings().removeClass(on);
                var index = $(tabTit).children().index(this);
                $(tabCon).children().eq(index).show().siblings().hide();
            });
        },
        //做作业页面导航滚动
        fellowNav : function(){
            var _courseID;
            var _box = $('#course_info_nav'),
                _top = _box.offset().top,
                _courseID = $('#course_comment_courseID').val();

            setCourseTabPositon(_top);
            $(window).scroll(function(){
                var _winTop = $(window).scrollTop();
                setCourseTabPositon(_top, _winTop);
                setCourseHandle(_winTop);
            }); 
            //tab
            $('#course_info_nav li.detail').click(function(){
                var _id = $(this).attr('con');
                if(_id){
                    setCourseHandleCurrent($(this));
                    courseInfo('#' + _id);
                }
            });
            /*
             * 设置tab的位置
             */
            function setCourseTabPositon(top, winTop){
                var _t = winTop || $(window).scrollTop(),
                    _box = $('#course_info_nav');
                if(_t > top){
                    _box.addClass('tab_scoll');
                    var isIE6 = /MSIE 6\./.test(navigator.userAgent) && !window.opera;
                    if (isIE6) {
                        _box.animate({
                            top: _t
                        },{
                            duration: 600,
                            queue: false
                        });
                    }
                }else{
                    _box.removeClass('tab_scoll');
                }       
            }
            /*
             * 课程信息
             */
            function courseInfo(id){
                var _box = $(id),
                    _top = (id == '#course_content') ? 39 : 39,
                    _top = _box.offset().top - _top;
                $(window).scrollTop(_top);
            }
            /*
             * 根据滚动的位置，显示tab当前状态
             */
            function setCourseHandle(top){
                var _top = top || $(window).scrollTop();
                var _tabs = $('#course_info_nav li.detail'),
                    _item = $('#course_info_box .course_info_item'),
                    _lens = _item.length;
                _item.each(function(i){
                    var _thisTop = $(this).offset().top - 40,
                    _next = $(this).next('.course_info_item'),

                    _tab = _tabs.eq(i);
                if(_top < _thisTop && i == 0){
                    setCourseHandleCurrent(_tabs.first());
                    return;
                }
                if((_top > _thisTop || _top == _thisTop) && _next.length == 0){
                    setCourseHandleCurrent(_tabs.last());
                    return;
                }
                if(_next.length > 0){
                    var _nextTop = _next.offset().top - 40;
                    if((_top > _thisTop || _top == _thisTop) && _top < _nextTop){
                        setCourseHandleCurrent(_tab);
                        return;
                    }
                }

                });
            }
            /*
             * 设置tab样式当前状态
             */
            function setCourseHandleCurrent(d){
                d.addClass('current').siblings('.detail').removeClass('current');
            } 
        },
        //我已在纸上完成
        paperComplete : function(){
            $('.allDone').live('click',function(){
                $(this).addClass('clicked');
            });
        },
        // 答案选择
        answerSelect : function(){
            $('.result').find('li').each(function(){
                $(this).live('click',function(){
                    $(this).addClass('active').siblings().removeClass('active');

                });
            });
        },
        //加入新班级弹出层
        addNewClass : function(){
            $('#addNewClass').live('click',function(){
                Common.comValidform.checkStudent(function(){
                    $.tiziDialog({
                        id:'addNewClassWinID',
                    title:'加入班级',
                    content:$('#addNewClassWin').html().replace('addNewClassWinForm_beta','addNewClassWinForm'),
                    icon:null,
                    width:500,
                    ok : function(){
                        $('.addNewClassWinForm').submit();
                        return false;
                    },
                    cancel:true
                    });
                    // 调用加入新班级 验证
                    Common.valid.student.addNewClassWin();
                });                     
            });
        },
        //账户绑定设置按钮
        setShowHideInput:function(){
            var btn_set = $('.btn_set');
            btn_set.on('click',function(){
                var index = $(this).index();
                $(this).parents('.md_stuAccount').find("form").toggle();
            });
        },
        //账户绑定设置按钮
        setShowHideForm:function(){
            var act_reset = $('.act_reset');
            act_reset.on('click',function(){
                var index = $(this).index();
                var form = $(this).parents('.md_stuAccount').find("form");
                form.hide();
            });
        },
        //点赞分享文件
        love_share:function(){
            $('.zan').live('click',function(){
                var share_id = $(this).attr('share_id');
                var that = $(this);
                $.tizi_ajax({
                    'url' : baseUrlName + 'student/cloud/love',
                    'type' : 'POST',
                    'dataType' : 'json',
                    'data' : {'share_id' :share_id},
                    success : function(json){
                        if(json.errorcode==1){
                            var love = Number(that.parents().find('.get_love').attr('value'))+1;
                            that.parents().find('.get_love').text('获赞('+love+')');
                            that.remove();
                        }else{
                            $.tiziDialog({content:json.error});
                        }
                    },
                    error : function(){
                     $.tiziDialog({content:'系统繁忙，请稍后再试'});
                    }
                });
            });
        },
        //学生的force下载
        // stu_force_download : function(url,fname,openbox,is_qiniu,share_id){
        //     if(is_qiniu == 'undefined'){
        //         url = url + '&session_id=' + $.cookies.get(baseSessID);
        //     }
        //     if(is_qiniu==true){
        //         $.tizi_ajax({
        //                 url: baseUrlName + "student/cloud/add_download_count", 
        //                 type: 'POST',
        //                 data: {'share_id':share_id},
        //                 dataType: 'json',
        //                 success: function(data){  }
        //         });
        //     }
        //     var ie_ver = Common.Download.ie_version();
        //     if(openbox == true || ie_ver==6.0 ||ie_ver==7.0 || ie_ver==8.0){
        //         if(fname == '' || fname == undefined) fname = "是否下载？";
        //         else fname = "是否下载《" + fname + "》？";
        //         $.tiziDialog({
        //             content: fname,
        //             ok:false,
        //             icon:null,
        //             button:[{
        //                 name:'点击下载',
        //                 href:url,
        //                 className:'aui_state_highlight',
        //                 target:'_self'
        //             }]
        //         });
        //         return false;
        //     }
        //     window.location.href=url;
        // },
        //下载分享文件   deprecated  2014-07-30
        download_share:function(){
            // $('.download_share').live('click',function(){
            //     //var file_name = $(this).attr('file_name');
            //     var file_id = $(this).attr('file_id');
            //     var share_id = $(this).attr('share_id');
            //     // alert(share_id)
            //     $.tizi_ajax({
            //             url: baseUrlName + "student/cloud/downverify", 
            //             type: 'POST',
            //             data: {'file_id' :file_id,'share_id':share_id},
            //             dataType: 'json',
            //             success: function(data){
            //                 if(data.errorcode == false) {                       
            //                     $.tiziDialog({content:data.error});
            //                 }else{
            //                     if(data.file_type==1){
            //                         var down_url=baseUrlName + "student/cloud/download?url="+data.file_path
            //                         +'&file_name='+data.file_encode_name+'&file_id='+data.file_id+'&share_id='+share_id;
            //                         Common.Download.force_download(down_url,data.fname,true);
            //                     }else{
            //                         Student.personal.stu_force_download(data.url,data.fname,true,true,share_id);
            //                     }
                                
            //                 }
            //             }
            //     });
            // });
        }
    };
    exports.personal = personal;
    //学生个人中心结束

    //调用做作业
    exports.do_homework = function(){
        var back_pause_tpl = 
            '<p>暂停啦.</p>';
        var pause_tpl = '<p>暂停啦.</p>';
        var next_continue_tpl = 
            '确认要离开此页面?';
        var aid = $("input[name='aid']").val();
        $.ajax({
            url:baseUrlName+"student/do_homework/get_answer?"+ new Date().getTime(),
            data:"aid="+aid,
            type:"GET",
            dataType:"json",
            success:function(data){
                data = eval("("+data.msg+")");
                var online = data.online;
                var offline = data.offline;
                if(online !=''){
                    $.each(online,function(key,val){
                        $(".question_"+val.question_id).find(".question_option").find("li:eq("+(Number(val.index)-1)+")").addClass("active");
                    })
                }
                if(offline != ''){
                    for(var i= 0;i< offline.length;i++){
                        $(".question_"+offline[i]).find(".allDone").addClass("clicked");
                    }
                }
                simulate.set_question_num()
                //Student.set_question_num();
            }
        })
        $(".nextDo").click(function(){

            $.tiziDialog({
                content:next_continue_tpl,
            ok:function(){
                var aid = $("input[name='aid']").val();
                $.tizi_ajax({
                    url:baseUrlName+"student/do_homework/pause",
                    data:{
                        "aid":aid
                    },
                    type:"POST",
                    success:function(data){
                        window.location.href= baseUrlName+'student/home';       
                    }
                })
            },
            cancel:true
            })
        })
        $(".question").find(".answer_option").click(function(){
            $(this).parents(".question").find(".answer_option").attr("checked",false);
            $(this).attr("checked","checked");
        })
        function update_break_status(sta){
            var aid = $("input[name='aid']").val();
            $.tizi_ajax({
                url:baseUrlName+"student/do_homework/"+sta,
                data:{
                    "aid":aid
                },
                type:"POST",
                dataType:"json",
                success:function(data){
                }
            })
        }
        $(".over").click(function(){
            var question_done_num = $(".question_option .active").length;
            var online_question_num = $(".question_option").length;
            if(question_done_num < online_question_num){
                $.tiziDialog({
                    content:'请完成所有选择题后再提交'
                })             
                return;
            }
            var submit_title = '';
            var submit_tpl = '确认要提交作业吗';
            $.tiziDialog({
                content:submit_tpl,
                ok:function(){
                    submit_work();
                },
                cancel:true
            })
        })
        $(".question_option").find("li").click(function(){

            if($(this).hasClass("active")) return false;
            var aid = $("input[name='aid']").val();
            var ac_len = $(this).parents("ul").find("li.active").length;
            if(!ac_len){
                var c_num = parseInt($(".c_num").text())+1;
                $(".c_num").text(c_num);
            }
            var question_id = $(this).parents(".homeworkBlock").find(".q_id").val();
            var input = $(this).text();
            $.tizi_ajax({
                url:baseUrlName+"student/do_homework/online_question_save",
                data:{
                    'aid':aid,
                'q_id':question_id,
                'input':input
                },
                type:"POST",
                dataType : 'json',
                success:function(data){
                }
            })
        })  

        $(".allDone").click(function(){
            if($(this).hasClass("clicked")) return false;
            var c_num = parseInt($(".c_num").text())+1;
            $(".c_num").text(c_num);
            var aid = $("input[name='aid']").val();
            var question_id = $(this).parents(".homeworkBlock").find(".q_id").val();
            $.tizi_ajax({
                url:baseUrlName+"student/do_homework/paperwork_question_save",
                data:{
                    'aid':aid,
                'q_id':question_id
                },
                type:"POST",
                dataType : 'json',
                success:function(data){

                }
            })
        })

        var oSpan = $(".clock");
        var timer = null;

        function countDown(){
            var date_arr = $(".deadline").val().split("-");

            var s1 = Date.parse(date_arr[1]+'/'+date_arr[2]+'/'+date_arr[0]+' '+date_arr[3]+':'+date_arr[4]+':'+date_arr[5]);
            var s2 = parseInt(new Date().getTime());

            var s=parseInt((s1-s2)/1000);
            var days=parseInt(s/86400);
            s%=86400;
            var hours=parseInt(s/3600);
            s%=3600;
            var mins=parseInt(s/60);
            s%=60
            var timeLeft=days+'天'+hours+'小时'+mins+'分钟'+s+'秒';
            if(days<=0&&hours<=0&&mins<=0&&s<=0){
                clearInterval(timer);
                oSpan.html("已到时");
                //submit_work();//remove auto commit
            }else{
                oSpan.html(timeLeft);
            }
        }
        countDown();
        timer = setInterval(countDown, 1000);   

        function submit_work(){
            var aid = $("input[name='aid']").val(); 
            $.tizi_ajax({
                url: baseUrlName+"student/do_homework/submit",
                data:{
                    "aid":aid
                },
                type:"POST",
                dataType:"json",
                success:function(data){
                    if(data.status == 99){
                        window.location.href = baseUrlName+"student/homework/report/"+data.msg;
                    }else if(data.status == 2){
                        $.tiziDialog({
                            content:'请完成所有选择题后再提交'
                        })
                    }else{
                        window.location.reload();
                    }
                }
            })
        }


    }

    /*单独答题页面simulate.js开始*/
    var simulate ={
        online_question_save: function(){
        
            $(".question_option").find("li").click(function(){
                if($(this).hasClass("active")) return false;
                var aid = $("input[name='aid']").val();
                var ac_len = $(this).parents("ul").find("li.active").length;
                if(!ac_len){
                    var c_num = parseInt($(".c_num").text())+1;
                    $(".c_num").text(c_num);
                }
            });
        },
        offline_question_save: function(){
        
            $(".allDone").click(function(){

                if($(this).hasClass("clicked")) return false;
                var c_num = parseInt($(".c_num").text())+1;
                $(".c_num").text(c_num);
            });
        },
        clock:function(){
            var se;
            var m = Number($(".minute").val());
            var s = Number($(".second").val());
            time_update = function (){
                if(m==999&&s==59){
                    clearInterval(se);
                    return;
                }
                if(s>0 && (s%60)==0){m+=1;s=0;}
                if(s<10){
                    s = '0' + s;
                }
                if(m<10){
                    m = '0' + m;
                }
                t= m+":"+s;
                s = Number(s);
                m = Number(m);
                $(".showtime").html(t);
                s+=1;
            }
            function startclock(time_update){
                se=setInterval("time_update()",1000);
            }
            startclock();
        },
        submit:function(){
            $(".over").click(function(){
                submit_tpl = '确认要提交吗?';
                $.tiziDialog({
                    okVal:'确认提交',
                    cancel:true,
                    content:submit_tpl,
                    ok:function(){
                        var id = $("input[name='aid']").val(); 
                        var start_time = $("input[name='start_time']").val(); 
                        var online_data = new Array();
                        var offline_data = new Array();
                        $(".homeworkBlock").find("li").each(function(){
                            var question_id;
                            var input;
                            if($(this).hasClass("active")){
                                question_id = $(this).parents(".homeworkBlock").find(".q_id").val();
                                input = parseInt($(this).parents("ul").find("li").index($(this)))+1;
                                online_data.push(question_id+','+input);
                            }
                        })
                        $(".homeworkBlock").find(".allDone").each(function(){
                            if($(this).hasClass("clicked")){
                                var question_id = $(this).parents(".homeworkBlock").find(".q_id").val();
                                offline_data.push(question_id);
                            }
                        })
                        $.tizi_ajax({
                            url: baseUrlName+"student/simulate/submit",
                            data:{
                                "id":id,
                            "online_data":online_data,
                            "offline_data":offline_data,
                            "start_time":start_time
                            },
                            type:"POST",
                            success:function(data){
                                if(data.status == 99){
                                    window.location.href=baseUrlName+'teacher/homework/demo/report/'+id;
                                }else if(data.status == 2){
                                    $.tiziDialog({
                                        okVal:'确定',
                                        time:2,
                                        content:data.msg
                                    });  
                                }
                            }
                        });
                    }
                });
            })
        },
        set_question_num : function(){
            var n = $(".homeworkBlock").length; 
            $(".a_num").text(n);
            var c = parseInt($(".course_info_item").find("li.active").length);
            var o_c = parseInt($(".course_info_item").find(".allDone.clicked").length);
            $(".c_num").text(c+o_c);
        }
    }
    exports.simulate = simulate;
    /*单独答题页面simulate.js结束*/
    //报告页面提交线下作业
    exports.do_paper_work = function(){

        $(".allDone").click(function(){
            if($(this).hasClass("clicked")) return false;
            var _this=$(this);
            var aid = $("input[name='aid']").val();
            var question_id = $(this).parents(".homeworkBlock").find(".q_id").val();
            $.tizi_ajax({
                url:baseUrlName+"student/do_homework/paperwork_question_submit",
                data:{
                    'aid':aid,
                'q_id':question_id
                },
                type:"POST",
                dataType : 'json',
                success:function(data){
                    _this.parent().find(".notice").remove();
                }
            })
        })
    }

    
    
});
