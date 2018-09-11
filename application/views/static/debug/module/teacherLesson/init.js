// 这是老师备课的脚本入口文件
define(function(require){
	require("placeHolder").JPlaceHolder.init();
    require('mustache');
	//文档tab切换
    require('module/common/method/common/tab').Tab('.lessonTxtTab','','.lessonTabBox');
    

    var tab_name = $('.tab_name').val();
    if(!tab_name){return;}
    switch(tab_name){
        case 'public'://共享文档页
            seajs.use('module/teacherLesson/lesson_prepare',function(ex){
                //备课初始化
                ex.lessonInit();
				//动态高亮
				ex.activeHightLight();
            });             
            break;
        case 'mine': // 我的文件
            seajs.use('module/teacherLesson/lesson_prep_my',function(my){
                my.init();
            });
            seajs.use('module/teacherLesson/lesson_tree_test',function(les_com){
                les_com.lessonCommonInit();
            });
			seajs.use('module/teacherLesson/lesson_prepare',function(ex){
				//动态高亮
				ex.activeHightLight();
            });	
            break;
        case 'fav':
            seajs.use('module/teacherLesson/lesson_prep_fav',function(my){
                my.init();
            });
            seajs.use('module/teacherLesson/lesson_tree_test',function(les_com){
                les_com.lessonCommonInit();
            }); 
			seajs.use('module/teacherLesson/lesson_prepare',function(ex){
				//动态高亮
				ex.activeHightLight();
            });	
            break;
        case 'upload': // 我的文件
            seajs.use('module/teacherLesson/upload_file',function(ex){
                //上传我的文件
                ex.dropBoxFieldFn();
                //确认上传
                //级联加载
                ex.confirmUploadFn();
                ex.getSubjecType();
                ex.getAjaxVersion();
                ex.getAjaxGrades();
                ex.getAjaxNodes();
                //设置隐藏域值
                ex.setHiddenValue();
				ex.cancleUpload();
            });
			break;
		case 'preview'://备课预览
			//备课初评星
            require('module/teacherLesson/lessonStar').starHover();
            require('module/teacherLesson/lessonStar').starClick();
            seajs.use('module/teacherLesson/lesson_prepare',function(ex){
                // 预览 - 收藏
                ex.store();
				ex.docDown();
            });        
            break; 
        case 'preview_mine'://备课 私有文件 预览
            seajs.use('module/teacherLesson/lesson_prepare',function(ex){
                // 预览 - 收藏
                ex.docDown();
            });        
            break;    
        default:
            break;
    }

   
  
    


    

})