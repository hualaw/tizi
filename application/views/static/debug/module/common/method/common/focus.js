define(function(require, exports) {
    // 左右切换滚动的幻灯片
    exports.LeftRightScroll = function() {
        var fs_A2 = function(id,Btn,ul,ms){
         this.id = $(id);
         this.Btn = $(Btn);
         this.ul = $(ul);
         this.ulLi = $(ul).find("li");
         this.ul.css("width",$(ul).find("li").eq(0).width()*$(ul).find("li").length + "px");
         this.nextTarget = 0;
         this.autoTimer = null;
         this.ms = ms;
        };
        fs_A2.prototype = {
         start:function(){
             var _this = this;
             this.Btn.each(function(){
                 var index = _this.Btn.index(this);
                 $(this).hover(function(){
                     _this.showSlides(index);
                     _this.nextTarget = index;
                 })  
             });
             this.id.hover(function(){
                 clearInterval(_this.autoTimer);
             },function(){
                 _this.autoTimer = setInterval(function(){
                     _this.autoPlay();   
                 },_this.ms);
             });
             clearInterval(this.autoTimer);
             this.autoTimer = setInterval(function(){
                 _this.autoPlay();   
             },this.ms);
         },
         showSlides:function(index){
             this.Btn.eq(index).addClass("active").siblings().removeClass("active");
             this.ul.stop().animate({"margin-left":-this.ul.find("li").eq(0).width()*index + "px"});
         },
         autoPlay:function(){
             this.nextTarget++;
             if(this.nextTarget > this.ulLi.length - 1){
                 this.nextTarget = 0;
             }   
             this.showSlides(this.nextTarget);
         }   
        };
        new fs_A2("#wind_A2","#btn a","#wind_A2 ul",5000).start();
    };
    
});