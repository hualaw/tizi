define(function(require,exports){
	// 用户心声滚动脚本
	var wind = function(id,w,ul,li,prev,next,ms){
		this.id = $(id);
		this.w = this.id.find(w);
		this.ul = $(ul);
		this.ulLi = this.ul.find(li);
		this.prev = $(prev);
		this.next = $(next);
		this.nextTarget = 0;
		this.autoTimer = null;
		this.ms = ms;
	};
	wind.prototype = {
		start:function(){
			var _this = this;
			this.prev.click(function(){
				_this.prevFn();	
			});
			this.next.click(function(){
				_this.nextFn();	
			});
			this.id.hover(function(){
				clearInterval(_this.autoTimer);
				_this.prev.show();
				_this.next.show();
			},function(){
				_this.autoTimer = setInterval(function(){
					_this.autoPlay();	
				},_this.ms);
				_this.prev.hide();
				_this.next.hide();
			});
			clearInterval(this.autoTimer);
			this.autoTimer = setInterval(function(){
				_this.autoPlay();	
			},_this.ms);
		},
		showSlides:function(index){
			this.ul.animate({"left":-(this.ulLi.width()+14)*index + "px"})
		},
		prevFn:function(){
			this.nextTarget--;	
			if(this.nextTarget < 0){
				this.nextTarget = 0;	
			}
			this.showSlides(this.nextTarget);
		},	
		nextFn:function(){
			this.nextTarget++;
			this.lastItem();
			this.showSlides(this.nextTarget);
		},
		lastItem:function(){
			if(this.nextTarget > 4){
				this.nextTarget = 4;
			}	
		},
		autoPlay:function(){
			this.nextTarget++;
			this.lastItem();	
			this.showSlides(this.nextTarget);
		}	
	}

	new wind("#windS1",".w","#windS1Ul","li","#prev","#next",5000).start();
});