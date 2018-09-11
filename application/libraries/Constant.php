<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(LIBPATH.'libraries/Constant.php');

Class Constant extends CI_Constant{
    
    const USERDATA_STUDENT="student/user/mygrade";
    const USERDATA_STUDENT_UNAME="student/user/myuname";
    const USERDATA_TEACHER="teacher/user/mysubject";
    const USERDATA_PARENT="";

	const FOOTBALL_TRAIN_QUES_NUM = 10;
	const ZHUANGYUAN_VIDEO_TIMEOUT = 86400;

	/*zujuan question wise select question error code*/
	const ERROR_WISE_MAXID_PARAM_EMPTY = -1;    //智能选题获取最大题目id参数为空
	const ERROR_WISE_NO_RECORDS  	   = -2;    //智能选题获取记录为空

	/*zujuan page max num when user not login*/
	const PAGE_NUM_NOT_LOGIN = 2;

	/*classes manage*/
	const TEACHER_CLASS_MAX_NUM = 200;//老师最大创建班级数
	const CLASS_MAX_HAVING_STUDENT = 200;	//一个班级最多拥有的学生人数
	const CREATE_STUDENT_LIMIT = 200;		//一次最多创建的学生数量
    const STUDENT_PAPER_PER_PAGE = 10;
    const STUDENT_HOMEWORK_PER_PAGE = 10;

	/*出错本掌握程度参数*/
	const WRONG_GUESS_PROBABILITY = 0.2; //纯靠猜做出某题的概率
	const WRONG_DISTINCT_EXTENDS  = 0.5; //题目区分度
	
	/*出错本配置参数*/
	const WRONG_CONFIG_QUESTION_LIMITS   = 30;  //计算掌握程度每个知识点要获取的题量
	const WRONG_CONFIG_QUESTION_MINS     = 5;   //计算该知识点掌握程度需要的最小题量
	const WRONG_CONFIG_SUCCESS_MIN_RATIO = 0.3; //若做题正确率低于此值则返回最低等级1
	
	/*无出错本科目的id*/
	const WRONG_NO_SUBJECT_IDS  = "1,10";	//目前语文无错题本
	
	const WRONG_DISPLAY_NUMS = 16; //错题本显示的知识点名字的最大长度

	/*zujuan question num per page*/
    const QUESTION_PER_PAGE = 20;
    const SEARCH_PER_PAGE = 20;
    const NOTICE_PER_PAGE = 50;
    const LESSON_PER_PAGE = 30;
    const LESSON_SEARCH_PER_PAGE = 30;
    const INTELLIGENT_PER_PAGE = 100;
    const STU_HOMEWORK_PER_PAGE = 5;
    const PARENT_HOMEWORK_PER_PAGE = 5;
    const STU_VIDEO_PER_PAGE = 5;
    const PAGE_LIMIT = 100;
    //试题来源
    const QUESTION_ORIGIN_QUESTION = 0;
    const QUESTION_ORIGIN_MYQUESTION = 1;
    const QUESTION_ORIGIN_EXERCISE = 3;
    const QUESTION_ORIGIN_MYEXERCISE = 4;

    const LOCK_TYPE_ASSIGN = 1;
    const LOCK_TYPE_ARCHIVE = 2;
    const LOCK_TYPE_DOWNLOAD = 3;
    const LOCK_TYPE_DELETE = -1;
    /*  家长慧页面大小设置 */
    
    //家长慧页面大小数量
    const PARENT_ARTICLE_PER_PAGE = 10;
    
    /*zujuan question limit*/
    const PAPER_QUESTION_LIMIT = 50;
    const HOMEWORK_QUESTION_LIMIT = 50;

    /*zujuan download limit*/
    const PAPER_DOWNLOAD_LIMIT = 20;
    const PAPER_DOWNLOAD_MONTH_LIMIT = 30;
    const PAPER_DOWNLOAD_CAPTCHA_LIMIT = 3;

    const HOMEWORK_DOWNLOAD_LIMIT = 20;
    const HOMEWORK_DOWNLOAD_MONTH_LIMIT = 30;
    const HOMEWORK_DOWNLOAD_CAPTCHA_LIMIT = 3;

    const DOCUMENT_DOWNLOAD_LIMIT = 20;//备课文档每天最大下载次数
    const DOCUMENT_DOWNLOAD_MONTH_LIMIT = 60;//备课文档每月最大下载次数
    const DOCUMENT_DOWNLOAD_CAPTCHA_LIMIT = 3;

    const MAX_DOWNLOAD_TIMEOUT = 60;//下载执行时间(s)
    const MAX_CONNECT_TIMEOUT = 5;//下载连接服务器时间(s)
    //const AUTH_DOWNLOAD_START = 23;//必须验证手机号才允许下载的时间段
    //const AUTH_DOWNLOAD_END = 8;//必须验证手机号才允许下载的时间段

    /*zujuan qcount timeout*/
    const REDIS_QCOUNT_TIMEOUT = 604800;//页面总数页面缓存的缓存时间，上线测试暂定24小时，上线后更改为7天
    const REDIS_INTELLIGENT_TIMEOUT = 604800;//智能选题结果保存七天

    /*lesson prepare*/
	const PAGE_NUM_LOAD_NEXT = 2;//加载下一段的页面
	const WAITE_CONVERSION = 0;//等待转换
	const DOING_CONVERSION = 9;//转换中
	const FAIL_CONVERSION = 8;//转换失败
	const WAITE_VERIFY = 7;//等待审核
	const FAIL_VERIFY_CONTENT = 3;//审核未通过，包含敏感信息
	const FAIL_VERIFY_NOT_MATCH_SUBJECT = 2;//审核未通过，和学科不匹配
	const SUCCESS_VERIFY = 1;//审核成功
	const REDIS_DOCCOUNT_TIMEOUT = 86400;
	const REDIS_DOCTYPE_TIMEOUT = 86400;
	const DOC_PAGES_LIMIT = 100;//备课文档最大页码限制
	const MAKE_SWF_API_SECRET = 'f5622db310714022b14c6af034eefc3f2df241f0';
    const RELATED_TOP_DOC_NUM = 10;//相关推荐显示数量
	
    /*答疑相关*/
    const AQ_RESOLVED_NOTPULL		= 1;			//答疑问题解决状态-未领取
    const AQ_RESOLVED_PULLED		= 2;			//答疑问题解决状态-已认领，待解答
    const AQ_RESOLVED_ANSWERED		= 3;			//答疑问题解决状态-已回答
    const AQ_RESOLVED_COMMENTTED	= 4;			//答疑问题解决状态-已评价
    const AQ_TYPE_THINKING			= 1;			//答疑问题疑问原因-思路不清晰
    const AQ_TYPE_ANSWER			= 2;			//答疑问题疑问原因-答案算不对
    const AQ_TYPE_QUESTION			= 3;			//答疑问题疑问原因-题目看不懂
    const AQ_GET_QUESTION_MAX		= 200;			//一个知识点列表最多获取多少道题目

    const AQ_ANSWER_CONTENT_TYPE_TEXT = 1;
    const AQ_ANSWER_CONTENT_TYPE_AUDIO = 2;
    const AQ_ANSWER_CONTENT_TYPE_PIC = 3;

    /*答疑 问题 来源*/
    const AQ_QUESTION_SOURCE_APP = 1; //通过移动app问的
    const AQ_QUESTION_SOURCE_BROWSWER = 2; //在网页上问的
    
    /*初高中小学定义*/
    const GRADE_TYPE_JUNIOR			= 1;			//小学
    const GRADE_TYPE_MIDDLE			= 2;			//初中
    const GRADE_TYPE_HIGH			= 3;			//高中

    //每页班级数
    const CLASSES_MANAGE_PER_PAGE = 10;
    
    //班级管理excel导入学生的相关配置
    const CM_XLS_MAX_COL			= 20;			//横行最大挖掘
    const CM_XLS_MAX_ROW			= 201;			//列最大挖掘

    //给新注册的家长帐号绑定演示用的学生帐号
    const DEMO_KID_USER_ID = 1001354;

    /*我的试题、我的文档相关常量*/
    const QUESTION_GROUP = 1;//试题分组
    const DOCUMENT_GROUP = 2;//文档分组
    const REDIS_GROUP_DATA_TIMEOUT = 604800;//用户分组统计过期时间7天
    const QUESTION_GROUP_MAX_NUM = 50;//我的试题用户最大分组数量

    /*题目纠错 分类*/
    const FEEDBACK_SOURCE_PAPER = 1; //试卷题目   /*tizi 3.0后组卷和作业共用 question表*/
    const FEEDBACK_SOURCE_EXERCISE = 2; //作业题目
    const FEEDBACK_SOURCE_PRACTICE = 3; //专项题目

    /*网盘 */
    const CLOUD_DIR_NUM_MAX = 100;//一个用户能创建的目录总数
    // const CLOUD_DISK_SIZE = 10737418240;//网盘总容量 10GB = 10*1024*1024*1024
    const ClOUD_ONE_FILE_SIZE = 209715200;//200*1024*1024;//单个文件的大小不能超过200M

    const CLOUD_FILE_PER_PAGE_NUM = 30;//每页显示数量
    const CLOUD_CLASSFILE_PER_PAGE_NUM = 10;//班级分享，每页显示数量

    /*文件类型常量*/
    const CLOUD_FILETYPE_DOC = 1;////文档
    const CLOUD_FILETYPE_PIC = 2;//图片
    const CLOUD_FILETYPE_VIDEO = 3;//视频
    const CLOUD_FILETYPE_AUDIO = 4;//音频
    const CLOUD_FILETYPE_OTHER = 5;//其他
    const CLOUD_PIC_TYPES = "jpg,gif,png,bmp,jpeg";//合法的图片类型
    const CLOUD_VIDEO_TYPES = "rmvb,rm,flv,f4v,mp4,wmv,avi,mpg,mov,swf";//合法的视频类型
    const CLOUD_VIDEO_TYPES_JWPLAYER = "rmvb,rm,flv,f4v,mp4,wmv,avi,mpg,mov";//JSPLAYER能播放的视频类型
    const CLOUD_AUDIO_TYPES = "mp3,wav,ape,wma,m4a,aac";//合法的音频类型
    const CLOUD_OTHER_TYPES = "";//合法的其他类型
    const CLOUD_DOC_TYPES = 'doc,docx,ppt,pptx,xls,xlsx,wps,et,dps,xls,pdf,xlsx,txt';
    /*音视频预设集，上传非mp3/4的文件后请求七牛做转换*/
    const PRESET_VIDEO = 'mp4/vb/2048k';
    const PRESET_VIDEO_LOW = 'mp4/vb/512k';
    const PRESET_VIDEO_ORIGIN = 'mp4';
    const PRESET_AUDIO = 'mp3/aq/9';

    /*教师认证*/
    const APPLY_STATUS_NOT_ALLOW = -1; //没有完成4个条件，没资格申请认证
    const APPLY_STATUS_ALLOW = 0; //有资格申请认证，但还没申请
    const APPLY_STATUS_ING = 1; // 已经申请，等待审核
    const APPLY_STATUS_SUCC = 2; //审核 pass
    const APPLY_STATUS_FAIL = 3; //审核 rejected

    /*我的资源库  */
    const RESOURCE_TYPE_JXSJ = 1;//教学设计
    const RESOURCE_TYPE_DXA = 2;//导学案
    const RESOURCE_TYPE_SJ = 3;//试卷
    const RESOURCE_TYPE_KJ = 4;//课件
    const RESOURCE_TYPE_SC = 5;//素材
    const RESOURCE_TYPE_WK = 6;//微课
    const RESOURCE_TYPE_Q = 7;//作业/习题
    const RESOURCE_TYPE_OTHER = 8;//其他

    const RES_LIST_PAGESIZE = 30;//备课 我的文件 显示条数

    const RES_DIR_CATEGORY = 1;//知识点目录
    const RES_DIR_COURSE = 2;//同步章节目录

    const RES_SOURCE_UPLOAD = 1 ; //上传
    const RES_SOURCE_FAV = 2 ; //收藏

    const ZUOYE_LIMIT_IN_A_DAY = 20;//老师对一个班级每天只能布置这么多作业

    public static function resource_source($id = 0){
        $type = array(
                self::RES_SOURCE_UPLOAD       => '上传',//1
                self::RES_SOURCE_FAV        => '收藏',//2
        );
        return $id === 0 ? $type : (isset($type[$id]) ? $type[$id] : null);
    }

    public static function resource_type($id = 0){
        $type = array(
                self::RESOURCE_TYPE_JXSJ       => '教学设计',//1
                self::RESOURCE_TYPE_DXA        => '导学案',//2
                self::RESOURCE_TYPE_KJ    => '课件',//4
                self::RESOURCE_TYPE_SC    => '素材',//5
                self::RESOURCE_TYPE_Q    => '作业/习题',//7
                self::RESOURCE_TYPE_SJ      => '试卷',//3
                self::RESOURCE_TYPE_WK    => '微课',//6
                self::RESOURCE_TYPE_OTHER    => '其他',//8
        );
        return $id === 0 ? $type : (isset($type[$id]) ? $type[$id] : null);
    }

	function __construct()
	{
	
	}
	
	/**
	 * 获取年级
	 */

	public static function grade($grade_id = 0){

		$grade = array(
			1 	=> '七年级',
			2 	=> '八年级',
			3 	=> '九年级',
			4 	=> '高一',
			5 	=> '高二',
			6 	=> '高三',
			7	=> "一年级",
			8 	=> "二年级",
			9 	=> "三年级",
			10 	=> "四年级",
			11 	=> "五年级",
			12 	=> "六年级"
		);

		return $grade_id === 0 ? $grade : (isset($grade[$grade_id]) ? $grade[$grade_id] : null);

	}

	public static function testpaper_mirror()
	{
		$mirror = new stdclass();
		$mirror->id 					= null;
		$mirror->user_id 				= 0;
		$mirror->subject_id 			= 0;
		$mirror->style 					= 1;
		$mirror->main_title 			= "2013-2014学年度xx学校xx月考卷";
		$mirror->is_show_main_title		= 1;
		$mirror->sub_title 				= "试卷副标题";
		$mirror->is_show_sub_title		= 1;
		$mirror->secret_sign 			= "绝密★启用前";
		$mirror->is_show_secret_sign	= 1;
		$mirror->is_show_line 			= 1;
		$mirror->info 					= "考试范围：xxx；考试时间：100分钟；命题人：xxx";
		$mirror->is_show_info 			= 1;
		$mirror->student_input 			= "学校：___________姓名：___________班级：___________考号：___________";
		$mirror->is_show_student_input 	= 1;
		$mirror->is_show_performance	= 1;
		$mirror->pay_attention 			= "1. 答题前填写好自己的姓名、班级、考号等信息<br />2. 请将答案正确填写在答题卡上";
		$mirror->is_show_pay_attention  = 1;
		$mirror->is_saved 				= 0;
		$mirror->is_locked 				= 0;
		$mirror->is_recovery 			= 0;
		return $mirror;
	}

	public static function testpaper_section_mirror()
	{
		$mirror = array(1 => new stdclass(), 2 => new stdclass());
		$mirror[1]->id 						= null;
		$mirror[1]->type 					= 1;
		$mirror[1]->testpaper_id 			= 0;
		$mirror[1]->label 					= "分卷I";
		$mirror[1]->note 					= "分卷I 注释";
		$mirror[1]->question_type_order		= "";
		$mirror[1]->is_show_section_header	= 1;
		$mirror[2]->id 						= null;
		$mirror[2]->type 					= 2;
		$mirror[2]->testpaper_id 			= 0;
		$mirror[2]->label 					= "分卷II";
		$mirror[2]->note 					= "分卷II 注释";
		$mirror[2]->question_type_order		= "";
		$mirror[2]->is_show_section_header	= 1;
		return $mirror;
	}
    
	public static function homework_mirror()
	{
		$mirror = new stdclass();
		$mirror->id 					= null;
		$mirror->user_id 				= 0;
		$mirror->subject_id 			= 0;
		$mirror->name 					= "";
		$mirror->question_type_order 	= "";
		$mirror->question_order 	= "";
		$mirror->is_saved 				= 0;
		$mirror->is_locked 				= 0;
		$mirror->is_recovery 			= 0;
		return $mirror;
	}
	
    //practice_category_info.grade
    public static function practice_grade(){
        $grade = array(
            //小学
            1  => '小学综合',       
            2  => '一年级',
            3  => '二年级',
            4  => '三年级',
            5  => '四年级',
            6  => '五年级',
            7  => '六年级',
            //初中
            8  => '初中综合',
            9  => '初一',
            10  => '初二',
            11 => '初三',
            //高中
            12 => '高中综合',
            13 => '高一',
            14 => '高二',
            15 => '高三',
        );   
        return $grade;

    }

    public static function practice_grade_hide_midhigh(){
        $grade = array(
            //小学
            1  => '小学综合',       
            2  => '一年级',
            3  => '二年级',
            4  => '三年级',
            5  => '四年级',
            6  => '五年级',
            7  => '六年级',
            // //初中
            // 8  => '初中综合',
            // 9  => '初一',
            // 10  => '初二',
            // 11 => '初三',
            // //高中
            // 12 => '高中综合',
            // 13 => '高一',
            // 14 => '高二',
            // 15 => '高三',
        );   
        return $grade;
    }

    public static function practice_question_type(){
        $question_type = array(
            0=>'简答题',
            3=>'选择题',
            18=>'填空题',
            19=>'计算题',
            20=>'解答题',
            25=>'阅读理解',
        );
        return $question_type;
    }

    public static function version(){
        
        $versions = array(
            1=>'人教版',
            2=>'苏教版',
            3=>'课改版',
            4=>'人教版',
        
        );
        return $versions;
    }

	public static function document_icon($file_ext)
	{
		$icon_class = array(
			'doc'	=>'word',
			'docx'	=>'word',
			'pdf'	=>'pdf',
			'ppt'	=>'ppt',
			'pptx'	=>'ppt',
			'pps'	=>'ppt'
			);
		return !empty($file_ext) && isset($icon_class[$file_ext]) ? $icon_class[$file_ext] : $icon_class['doc'];
	}
	
	/**
	 * 答疑相关（aq_*）
	 * @final 2013/10/09
	 * @author jiangwuzhang
	 */
	public static function aq_question_type($type = 0){
		
		$type_def = array(
				self::AQ_TYPE_THINKING	=> '思路不清楚',
				self::AQ_TYPE_ANSWER	=> '答案算不对',
				self::AQ_TYPE_QUESTION	=> '题目看不懂'
		);
		
		return $type === 0 ? $type_def : (isset($type_def[$type]) ? $type_def[$type] : null);
	}
	
	public static function aq_is_resolved($id = 0){
		$is_resolved = array(
				self::AQ_RESOLVED_NOTPULL		=> '未认领',
				self::AQ_RESOLVED_PULLED		=> '已认领',
				self::AQ_RESOLVED_ANSWERED		=> '已回答',
				self::AQ_RESOLVED_COMMENTTED	=> '已评价'
		);
		return $id === 0 ? $is_resolved : (isset($is_resolved[$id]) ? $is_resolved[$id] : null);
	}

    //答疑：题目的难易程度
    public static function aq_question_difficulty($id = 0){
        $diff = array(
            1   => '容易',
            2    => '较易',
            3    => '一般',
            4  => '较难',
            5  => '困难'
        );
        return $id === 0 ? $diff : (isset($diff[$id]) ? $diff[$id] : null);
    }

    public static function practice_game_help(){
    
        return array(
        
            3=>'总共10道题，来比比谁做对的更多！',
            8=>'总共10道题，来比比谁做对的更多！',
            7=>'总共10道题，来比比谁做对的更多！',
            2=>'60秒内比一比，谁做对的题目更多！',
            4=>'60秒内比一比，谁做对的题目更多！',
            5=>'60秒内比一比，谁做对的题目更多！',
            6=>'60秒内比一比，谁做对的题目更多！',
        );

    }

    //practice_category_info.game_type
	public static function practice_game_rule($type = ''){
		
		$rules = array(
			1=>'用最少的时间答对所有题目',
			2=>'累计答对更多的题目',
			3=>'规定时间以内答对更多的题目'
		);
		$default_rule = '连续答对更多的题目';
		return empty($type) || !isset($rules[$type]) ? $default_rule : $rules[$type];

	}


    public static function practice_url(){
        $url = array(
            1=>site_url().'practice/training/',//传统做题首页
            2=>site_url().'practice/game/',//单词串串烧
        );
        return $url;
    }

    //practice_category_info.p_c_type  每个是一款游戏   p_c_type==1就是非游戏
    public static function practice_game_info(){
        
        $game_info = array(

            3=>array('pig','PigsAcrossTheRiver'),
            8=>array('treasurehunt','UnderseaTreasureHunt'),
            7=>array('fish','CatchFish'),
            2=>array('penguin','KillPenguinALife'),
            4=>array('sheep','SaveTheSheep'),
            5=>array('magician','Halloween'),
            6=>array('startrek','StarTrek'),
            9=>array('farm','Farm'),
            10=>array('china','PlayInTheChina'),
            11=>array('ratio','Ratio'),
            12=>array('thousandcharacter','NewThousandWords'),
        );
        return $game_info;

    }
    //做游戏链接
    public static function practice_game_url($id){
        $url = site_url().'student/practice/game/'.$id;
        return $url;
    }

    //游戏选项数量
    public static function practice_option_num($id){
        $game_option = array(
            2=>3,
            3=>4,
            4=>3,
            5=>3,
            6=>3,
            7=>3,
            8=>3,
        );
        if(isset($game_option[$id])){
            return $game_option[$id];
        }
        return false;
    }

    public static function test_account_number(){
        return array(1001354);
    }
    
    public static function practice_game_single(){
        $ids = array(
            66,79,579
        );
        return $ids;
    }

    public static function grade_type($grade_type_id = 0){
        $grade_type = array(
    		self::GRADE_TYPE_JUNIOR		=> '小学',
    		self::GRADE_TYPE_MIDDLE		=> '初中',
    		self::GRADE_TYPE_HIGH		=> '高中'
    	);
    	return $grade_type_id === 0 ? $grade_type : (isset($grade_type[$grade_type_id]) ? $grade_type[$grade_type_id] : null);
    }
    
    public static function grade_video($grade_id = 0){
		$grade_video = array(
			1	=> "小学1-3年级",
			2	=> "小学4-6年级",
			3	=> "初中",
			4	=> "高中"
		);
		return $grade_id === 0 ? $grade_video : (isset($grade_video[$grade_id]) ? $grade_video[$grade_id] : null);
	}

	public static function level_name($level_id,$is_return_arr = false)
	{
		$level = array(
			1 => '容易',
			2 => '较易',
			3 => '一般',
			4 => '较难',
			5 => '困难',
		);
		if($is_return_arr){
			return $level;
		}
		return isset($level[$level_id])?$level[$level_id]:'';
	}


	/*新备课文档类型*/
	public static function new_doc_type($type_id,$is_replace = false)
	{
        if($is_replace){
            $type = array(
                self::RESOURCE_TYPE_JXSJ  => 1,
                self::RESOURCE_TYPE_DXA   => 4,
                self::RESOURCE_TYPE_KJ    => 2,
                self::RESOURCE_TYPE_SC    => 6,
                self::RESOURCE_TYPE_Q     => 3,
                self::RESOURCE_TYPE_SJ    => 11,
                self::RESOURCE_TYPE_WK    => 12,
                self::RESOURCE_TYPE_OTHER => 8
            );
            return $type_id === 0 ? $type : (isset($type[$type_id]) ? $type[$type_id] : null);
        }else{
            switch ($type_id) {
                case '3':
                    $result = array(3,5,7);
                    break;
                default:
                    $result = $type_id;
                    break;
            }
            return $result;
        }
	}
	
	/**
	 * 反推送备课类型
	 */ 
	public static function new_doc_type_prev($doc_type_id){
		$data = array();
		$data[1] = '教学设计';
		$data[2] = '课件';
		$data[4] = '导学案';
		$data[6] = '素材';
		$data[3] = '作业/习题';
		$data[5] = '作业/习题';
		$data[7] = '作业/习题';
		$data[8] = '其他';
        $data[9] = '说课稿';
        $data[11] = '试卷';
        $data[12] = '微课';
		return isset($data[$doc_type_id]) ? $data[$doc_type_id] : '';
	}
	
    //获取随机的作业备注
    public static function get_homework_ps($num=1,$is_return_arr = false)
    {
        $level = array(
            0 => '不叫一日闲过，坚持就有收获！',
            1 => '你很聪明，潜力无穷！',
            2 => '拥有梦想只是一种智力，实现梦想才是一种能力。',
            3 => '今天多几分钟的努力，明天少几小时的烦恼。',
            4 => '若想知道路途有多远，最好现在就出发。',
            5 => '只要迈出一步，便会发现五彩斑斓。',
            6 => '做对的事情比把事情做对重要。',
            7 => '再长的路，一步步也能走完，再短的路，不迈开双脚也无法到达。',
            8 => '学习之前，喝杯热茶！',
            9 => '阳光的脚步又移了一寸，我们离目标又近了一步！',
            10 => '微笑向前，遇见最美好的自己。',
            11 => '坚持一会儿，马上可以休息啦！',
            12 => '天气干燥，学习时别忘了喝杯水哦！',
            13 => '学习久了，要让眼睛休息一会儿哦～',
            14 => '做一顿热气腾腾的晚餐，奖励辛劳用功的你！  ',
            15 => '天晴，要带上微笑；天阴，要记起天晴。',
            16 => '任何时候开始努力，都为时不晚。',
        );
        if($is_return_arr){
            return $level;
        }
        return isset($level[$num])?$level[$num]:'';
    }

    //题目纠错  错误类型
    public static function q_wrong_type($level_id,$is_return_arr = false)
    {
        $level = array(
            1 => '题目类型',
            2 => '题目答案',
            3 => '题目解析',
            4 => '题目知识点',
            5 => '其他',
        );
        if($is_return_arr){
            return $level;
        }
        return isset($level[$level_id])?$level[$level_id]:'';
    }

    //网盘 上传的文件的类型
    public static function cloud_filetype($level_id,$is_return_arr = false)
    {
        $level = array(
            self::CLOUD_FILETYPE_DOC=> '文档',
            self::CLOUD_FILETYPE_PIC => '图片',
            self::CLOUD_FILETYPE_VIDEO => '视频',
            self::CLOUD_FILETYPE_AUDIO => '音频',
            self::CLOUD_FILETYPE_OTHER => '其他',
        );
        if($is_return_arr){
            return $level;
        }
        return isset($level[$level_id])?$level[$level_id]:'';
    }

}
/* End of file Constant.php */
/* Location: ./application/libraries/Constant.php */
