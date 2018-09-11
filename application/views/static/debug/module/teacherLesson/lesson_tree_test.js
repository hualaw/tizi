define(function(require, exports){
    var lessonValid  = require("module/common/basics/teacherLesson/lessonValid");
    require('tiziDialog');
    //备课首页初始化
    var lessonPrepare = {
        typeCur : $(".current-type"),
        treeList : $(".tree-list"),
        docList : $(".doc-list"),
        treeItem : $(".tree-list a"),
        filterLink : $(".head-type a"),
        filterOrderDate : $("#odate"),
        filterOrderPage : $("#opage"),
        filterOrderSize : $("#osize"),
        downDoc : $(".doc-down"),
        subjectLink : $(".subject-chose"),
        mainContent : $(".child-content .content-wrap"),
        submitBtn : $('#search_submit'),
        tab_name:$('.tab_name').val(),
        //初始化
        init:function(){
            this.treeItem.on("click",function(){
                var this_a = this;
                lessonPrepare.treeItemClick(this_a);
            });
            this.filterLink.click(function(){
                var this_a = this;
                lessonPrepare.filterQuestionClick(this_a);
            });
            this.filterOrderDate.live("click",function(){
                var this_a = this;
                lessonPrepare.filterQuestionClick(this_a);
            });
            this.filterOrderPage.live("click",function(){
                var this_a = this;
                lessonPrepare.filterQuestionPage(this_a);
            });
            this.filterOrderSize.live("click",function(){
                var this_a = this;
                lessonPrepare.filterQuestionSize(this_a);
            });
            this.submitBtn.live("click",function(){
                $(".head-type").find("a").removeClass("current");
                $(".all-type").addClass("current");
                lessonPrepare.submitSearch(1);
            });
             
             
            $('.searchText').live("keypress",function(event){
                if(event.keyCode==13){   
                    $('.searchText').blur();
                    $(".head-type").find("a").removeClass("current");
                    $(".all-type").addClass("current"); 
                    lessonPrepare.submitSearch(1);
                }
            });
        },
        getBaseUrl:function()
        {       
            if(this.tab_name == 'mine'){
                return "lesson/lesson_prep_my/";
            }else if(this.tab_name == 'fav'){
                return "lesson/less_my_fav/";
            }
        },
        getUrlData:function(ele){
            ele = ele || 1;
            // var nselectVal = $(".tree-list .active").data() || {nselect:$(".current-type").data("cselect")};
            var nselectVal = $(".tree-list .active").data() || 0;
            var selectOrderType=1;/*默认降序*/
            var selectOrder =$(".head-order .active").eq(0).data("order")||0; //$(".head-order .active").eq(0).data()||{order:0};
            if(selectOrder=='1'){
                if($("#opage").hasClass("up")){
                    selectOrderType=2;
                }
            }
            if(selectOrder=='2'){
                if($("#osize").hasClass("up")){
                    selectOrderType=2;
                }
            }
            var finalData = $.extend({page:ele},nselectVal,$(".current-type").data(),$(".head-type  .active").eq(0).data(),{order:selectOrder},{otype:selectOrderType});
            return finalData;
        },
        treeItemClick:function(this_a){//解决多次点击统一连接多次
            if($(this_a).hasClass('active')){return false; }
            this.treeList.find("a").removeClass("active");
            $(this_a).addClass("active");
            var urlData = this.getUrlData();
            this.get_document(urlData);
        },
        filterQuestionClick:function(this_a){//解决多次点击统一连接多次
            if($(this_a).attr('class') == 'active'){
                return false;
            }
            $(this_a).parent().find("a").removeClass("active");
            $(this_a).addClass("active");
            var ckeyword = $('.seachResult').find('em').text();
            if(ckeyword){
                this.submitSearch(1);
            }else{
                var urlData = this.getUrlData();
                this.get_document(urlData);
            }
        },
        filterQuestionPage:function(this_a){    
            $(this_a).parent().find("a").removeClass("active");
            $(this_a).addClass("active");
            if($(this_a).hasClass("up")){
                $(this_a).removeClass("up");
                $(this_a).addClass("orther");
            }else if($(this_a).hasClass("orther")){
                $(this_a).removeClass("orther");
                $(this_a).addClass("up");
            }else{
                $(this_a).addClass("orther");
            }
            var ckeyword = $('.seachResult').find('em').text();
            if(ckeyword){
                this.submitSearch(1);
            }else{
                var urlData = this.getUrlData();
                this.get_document(urlData);
            }
        },
        filterQuestionSize:function(this_a){    
            $(this_a).parent().find("a").removeClass("active");
            $(this_a).addClass("active");
            if($(this_a).hasClass("up")){
                $(this_a).removeClass("up");
                $(this_a).addClass("orther");
            }else if($(this_a).hasClass("orther")){
                $(this_a).removeClass("orther");
                $(this_a).addClass("up");
            }else{
                $(this_a).addClass("orther");
            }
            var ckeyword = $('.seachResult').find('em').text();
            if(ckeyword){
                this.submitSearch(1);
            }else{
                var urlData = this.getUrlData();
                this.get_document(urlData);
            }
        },
        get_document:function(getData){
            var base_url = this.getBaseUrl();
            getData['ver'] = (new Date).valueOf();
            var cat_id = $('.cat_id').val();
            var sc = getData['nselect'];
            var _tab_name = (this.tab_name);
            seajs.use('tizi_ajax',function(){
                if(_tab_name=='mine'){
                    var _url = "beike_list/"+cat_id+"/"+sc ;
                }else if(_tab_name == 'fav'){
                    var _url = "fav_list/" + cat_id + "/" + sc;
                }
                $.tizi_ajax({url: baseUrlName + base_url + _url, 
                        type: 'GET',
                        data: {flip:true,subject_id:$('.subject_id').val()},
                        dataType: 'json',
                        success: function(data){
                            if(data.errorcode == true) {
                                //选中sub_cat_id的情况下，切换 3个大tab
                                if(data.tab_url){
                                    // alert(data.tab_url);
                                    $("#navShare").attr("href",baseUrlName+"teacher/lesson/prepare/"+data.tab_url);
                                    $("#navMy").attr("href",baseUrlName+"teacher/lesson/prepare/mine/"+data.tab_url);
                                    $("#navFav").attr("href",baseUrlName+"teacher/lesson/prepare/fav/"+data.tab_url);
                                }
                                $('.lessonTabDiv').html(data.html);
                            }else{
                            }
                        }
                    });
                });
        },
        initScrollBar:function(){
            // 添加备课树形结构滚动条
            var scrollPanel = $(".scrollPanel");
            if(scrollPanel.length>0){
                var scrollTreePanel1 = $("#scrollTreePanel1");
                $("#scrollTreeContent1").height(600);
                $("#scrollTreeContent1 .Scroller-Container").css({position:"absolute",left: "0",top:"0",width:"220px"});
                scrollTreePanel1.removeClass("undis");
                seajs.use('module/teacherLesson/scrollPanel',function(exports){
                    var scroller  = new exports.jsScroller(document.getElementById("scrollTreeContent1"), 210, 600);
                    var scrollbar = new exports.jsScrollbar(document.getElementById("scrollTreePanel1"), scroller, false);
                })
            }
        },
        
        getSearchData:function(ele){
            ele = ele || 1;
            var ckeyword = $('.seachResult').find('em').text().replace(/(^\s*)|(\s*$)/g,'');
            var keywordInput = $('.searchText').val().replace(/(^\s*)|(\s*$)/g,'');
            if(ckeyword && ckeyword == keywordInput ){
                var keyword = ckeyword;
            }else{
                var keyword = keywordInput;
                if(keyword=="请输入关键字"){keyword="";}
                if(keyword==""){
                    return false;
                }
                ga('send', 'event', 'Search-Lesson', 'Search', keyword);
            }
            var subjectId = $('.subject-chose').data('subject');
            var nselectVal = $(".tree-list .active").data() || {nselect:$(".current-type").data("cselect")};
            var finalData = $.extend({page:ele},{skeyword:keyword},{sid:subjectId},nselectVal,$(".current-type").data(),$(".head-type  .active").eq(0).data(),{order:0});
            return finalData;
        },
        submitSearch:function(page){
            var getData = this.getSearchData(page);
            if(getData === false){
                $.tiziDialog({content:"请输入关键字"});
                return false;
            }
            getData['ver'] = (new Date).valueOf();
            $(this.submitBtn).addClass('disabled');
            seajs.use('tizi_ajax',function(){
                $.tizi_ajax({url: baseUrlName+"lesson/lesson_prepare/lesson_search", 
                    type: 'GET',
                    data: getData,
                    dataType: 'json',
                    success: function(data){
                        $(this.submitBtn).removeClass('disabled');
                        if(data.errorcode == true) {
                            $(".doc-list").html(data.html);
                            // 加载老师端左侧背景高度判断
                            require('module/common/method/common/height').leftMenuBg();
                        }else{
                            $.tiziDialog({content:data.error});
                        }
                    }
                });
            });
        },
        sPage:function(page){
            this.submitSearch(page);
        }
    };

    exports.lessonCommonInit = function(){
        lessonPrepare.init();//备课首页初始化外露
    };

});