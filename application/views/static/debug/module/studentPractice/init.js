// 学生专项挑战
define(function(require){
    var page_name = $(".mainContainer").attr("pagename");
    switch(page_name){
        case 'studentSpecial_index' :
            require('module/studentPractice/studentSpecial_index').init();
            break;
        case 'studentSpecial_high2' :
            require('module/studentPractice/studentSpecial_action').init();
            break;
    }
});
