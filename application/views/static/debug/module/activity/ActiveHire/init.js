//招聘教师
define(function(require,exports){
    exports.init = function(){
        exports.share();//调用分享接口
    }
    exports.share=function(){
        var hireText = "梯子网旗下中小学“在线直播互动课堂”即将上线，现面向全国招募优秀教师!!!";
        window._bd_share_config=
        {
            "common":
                {
                    "bdSnsKey":{},
                    "bdText":hireText,
                    "bdMini":"2",
                    "bdPic":"",
                    "bdStyle":"0",
                    "bdSize":"32"
                },
            "share":{}
        };
        with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
    };
    
});