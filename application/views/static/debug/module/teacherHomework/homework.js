define(function(require,exports){
	require('tiziDialog');

    exports.homework = function (){
        $(".gameList").click(function (){
            $.tiziDialog({
                title:false,
                ok:false,
                icon:false,
                content:$('.homeworkHtml').html(),
            });
        })
    }
});