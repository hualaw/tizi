define(function(require,exports){
    //学生 专项练习开始
    require("tizi_ajax");
    require("tiziDialog");
    exports.init = function(){
		exports.tabChange();
		//exports.subject();
		exports.recordCategory();
	}
    //标签切换功能
	exports.tabChange = function(){
        var content_box = $("#content_box");
        $("#content_box .md_hd a").each(function(){
            var that = $(this);
            var index = that.index();
            that.on("click",function(){
                $(this).addClass("active").siblings().removeClass("active");
                $("#content_box .md_bd ul").eq(index).removeClass("undis").siblings().addClass("undis");
            })
        })
	}

	exports.subject = function(){
		
		var url = window.location.href;
		var myRe = new RegExp(".*#([1-9])");
		var myArray = myRe.exec(url);
        var subject_id;
		if(myArray!== null && typeof myArray[1] !== undefined){
            exports.changeTabByHistory(myArray[1]);
		}else{
            $.ajax({
                url:baseUrlName+'practice/get_category',       
                dataType:'json',
                success:function(data){
                    if(data.status == 99)
                        exports.changeTabByHistory(data.msg);
                }
            })
        
        }
	}

    exports.recordCategory = function(){
        require('cookies');
        var _md_hd = $(".md_hd");
        _md_hd.find("a").click(function(){
            var sub_id = $(this).attr("sub_id");
            $.cookies.set('_pksub', sub_id, { hoursToLive : 10 * 365, domain: baseCookieDomain });
            // exports.recordCategoryReq(sub_id);
            // _pksub=subid
        })
        
    }

    exports.recordCategoryReq = function(sub_id){

        $.tizi_ajax({
            url: baseUrlName+'practice/record_sub',
            data: {'sub_id':sub_id},
            type:'POST',
            dataType: 'json',
            success:function(data){
                
            }
        })
        
    }

    exports.changeTabByHistory = function(subject_id){

        if(subject_id){
            $(".md_hd").find("a").each(function(){
                if($(this).attr("sub_id") == subject_id){
                    $(this).click();
                }
            })
        }
    
    }

});
