define(function(require,exports,module){
    
    //任务分页
    exports.get = function(page_num){
        var task_type = $("#task_type").val();
        var url_f = 'student/home' ;

        switch(task_type){
            case '1':
                url_f = 'student/my/work';
                break;
            case '3':
                url_f = 'student/teacher_share';
                break;
        }
        window.location.href= baseUrlName+url_f+'?page='+page_num;
    }

});
