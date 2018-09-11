define(function(require,exports){
    exports.addPaperHaveErrorAjax = function(data){
        if(data.errorcode == false) {
            $.tiziDialog({content:data.error});
        }else{
            var content = "<p>纠错信息提交成功！</p><p>非常感谢您的反馈，我们会尽快处理。</p>";
            $.tiziDialog({
                content:content,
                icon:"succeed",
                ok:function(){
                    window.location.reload();
                }
            });
        }
    }
});
