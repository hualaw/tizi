-- MySQL dump 10.13  Distrib 5.5.32, for debian-linux-gnu (x86_64)
--
-- Host: rdsnuyizmnuyizm.mysql.rds.aliyuncs.com    Database: tizi
-- ------------------------------------------------------
-- Server version	5.5.18-Alibaba-3935-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


--
-- Table structure for table `aq_answer`
--

/*答疑：答案表，包含文字、音频、图片三种格式*/
DROP TABLE IF EXISTS `aq_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aq_question_id` int(10) NOT NULL COMMENT '问题的id',
  `teacher_id` int(10) NOT NULL,
  `content` text NOT NULL COMMENT '音频和图片的话就是url地址',
  `content_type` tinyint(1) NOT NULL COMMENT '1.text 2.audio 3.picture',
  `audio_length` int(10) NOT NULL COMMENT '音频长度,非音频就置零',
  `create_time` int(10) unsigned NOT NULL COMMENT '回答时间',
  `is_del` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_comment`
--

/*答疑：学生对老师的评价*/
DROP TABLE IF EXISTS `aq_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_comment` (
  `id` int(10) unsigned NOT NULL COMMENT '=aq_question.id',
  `user_id` int(10) NOT NULL COMMENT '学生id',
  `teacher_id` int(10) unsigned NOT NULL,
  `content` text NOT NULL COMMENT '评价内容',
  `evaluate` tinyint(3) unsigned NOT NULL COMMENT '获得的星评:1~5',
  `dateline` int(10) unsigned NOT NULL COMMENT '评价时间',
  `is_read` tinyint(1) NOT NULL COMMENT '这条评价是否被该老师读过',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_grade`
--

/*答疑：学科年级对照表*/
DROP TABLE IF EXISTS `aq_grade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_grade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '年级名称',
  `aq_subject_id` int(10) unsigned NOT NULL COMMENT '学科id',
  `grade_type` tinyint(4) NOT NULL COMMENT '1 小学； 2 初中；3 高中',
  PRIMARY KEY (`id`),
  KEY `grade_type` (`grade_type`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_point`
--

/*答疑：考点*/
DROP TABLE IF EXISTS `aq_point`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_point` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `aq_subject_id` int(10) unsigned NOT NULL COMMENT '学科id',
  `grade_type` tinyint(4) NOT NULL COMMENT '1 小学； 2 初中；3 高中',
  PRIMARY KEY (`id`),
  KEY `aq_subject_id` (`aq_subject_id`),
  KEY `grade_type` (`grade_type`)
) ENGINE=MyISAM AUTO_INCREMENT=1763 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_question`
--

/*答疑：问题*/
DROP TABLE IF EXISTS `aq_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` tinyint(3) unsigned NOT NULL COMMENT 'aq_subject.id',
  `user_id` int(10) unsigned NOT NULL COMMENT '学生id，提问者',
  `teacher_id` int(10) NOT NULL COMMENT '回答问题的老师',
  `point_ids` varchar(255) NOT NULL COMMENT '考点id串，逗号隔开',
  `grade` int(11) NOT NULL COMMENT 'aq_grade.id',
  `content` longtext NOT NULL,
  `picture_urls` text NOT NULL COMMENT '学生上传的图片路径，逗号隔开',
  `reward` int(10) unsigned NOT NULL COMMENT '给老师的悬赏点',
  `type` tinyint(1) unsigned NOT NULL COMMENT '1思路不清楚;2答案算不对；3题目看不懂',
  `is_resolved` tinyint(1) NOT NULL COMMENT '1未认领；2已认领；3已回答；4已评价',
  `publish_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `start_date` int(10) unsigned NOT NULL COMMENT '老师抢到问题的时间，等于开始答题的时间',
  `solved_sec` int(10) unsigned NOT NULL COMMENT '解决问题花费的时间',
  `is_del` tinyint(1) NOT NULL,
  `difficulty` tinyint(1) NOT NULL COMMENT '难度等级，1至5',
  `specific` tinyint(1) NOT NULL COMMENT '1就是指定某个老师回答',
  `new_answer` tinyint(1) NOT NULL COMMENT '1为有未读答案 ， 0为没有',
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `reward` (`reward`),
  KEY `type` (`type`),
  KEY `is_resolved` (`is_resolved`),
  KEY `user_id` (`user_id`),
  KEY `grade` (`grade`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_stu_teacher_fav`
--

/*答疑：学生关注老师*/
DROP TABLE IF EXISTS `aq_stu_teacher_fav`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_stu_teacher_fav` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `student_id` int(10) NOT NULL,
  `teacher_id` int(10) NOT NULL,
  `is_del` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stu_teacher` (`student_id`,`teacher_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_subject`
--

/*答疑：学科*/
DROP TABLE IF EXISTS `aq_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_subject` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `listorder` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_sue`
--

/*答疑：投诉表*/
DROP TABLE IF EXISTS `aq_sue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_sue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(10) unsigned NOT NULL,
  `teacher_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `content` text NOT NULL,
  `dateline` int(10) unsigned NOT NULL,
  `reply` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `phone` varchar(50) DEFAULT NULL COMMENT '投诉人输入的手机',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aq_teacher`
--

/*答疑：老师信息表*/
DROP TABLE IF EXISTS `aq_teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aq_teacher` (
  `id` int(11) NOT NULL,
  `aq_teacher` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否允许答疑，1：允许，0：不允许',
  `aq_online` tinyint(4) NOT NULL DEFAULT '0' COMMENT '老师是否在线，1：在线，2：不在线',
  `aq_account_balance` int(10) unsigned NOT NULL COMMENT '学点余额',
  `aq_fields` varchar(255) NOT NULL COMMENT '擅长领域，id串',
  `aq_credit_once` int(10) unsigned NOT NULL COMMENT '每次回答时需要收取的点数',
  `aq_avg_sec` int(10) unsigned NOT NULL COMMENT '平均答题时间',
  `aq_avg_evaluate` int(10) unsigned NOT NULL COMMENT '平均星级评价，页面上显示的1  在这里是 100',
  `aq_answer_count` int(10) unsigned NOT NULL COMMENT '总的答题数',
  `aq_comment_count` int(10) NOT NULL COMMENT '总的收到的评价数',
  `aq_revoke_times` int(10) NOT NULL COMMENT '被撤销答题机会 的次数',
  `avatar_url` varchar(200) NOT NULL COMMENT '头像地址',
  `school` varchar(200) NOT NULL COMMENT '答疑老师的学校和tizi不用打通',
  `subject` int(10) NOT NULL COMMENT '老师在答疑里的科目',
  `weight` int(10) NOT NULL COMMENT 'teacher list 排名权重',
  `grade_type` tinyint(4) NOT NULL COMMENT '== aq_grade.grade_type ; 1.小学 2.初中 3.高中',
  `intro` varchar(500) NOT NULL COMMENT '简介',
  PRIMARY KEY (`id`),
  KEY `aq_online` (`aq_online`),
  KEY `grade_type` (`grade_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_assignment`
--

DROP TABLE IF EXISTS `auth_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_assignment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`id`,`itemid`,`userid`),
  KEY `itemid` (`itemid`),
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `auth_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_item`
--

DROP TABLE IF EXISTS `auth_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` text,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_item_child`
--

DROP TABLE IF EXISTS `auth_item_child`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_item_child` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL,
  `child_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`,`parent_id`,`child_id`),
  KEY `parent_id` (`parent_id`),
  KEY `child_id` (`child_id`),
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `auth_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `auth_item` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

/*知识点目录表*/
DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `subject_id` int(11) DEFAULT NULL COMMENT '科目ID，对应subject',
  `name` varchar(128) NOT NULL COMMENT '知识点名称',
  `lft` int(11) NOT NULL COMMENT '左值',
  `rgt` int(11) NOT NULL COMMENT '右值',
  `depth` smallint(5) unsigned NOT NULL COMMENT '深度',
  `memo` varchar(1024) DEFAULT NULL COMMENT '备注',
  `list_order` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `category_790ef9fb` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13061 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ci_sessions`
--

/*session存储表，由ci框架自动处理*/
DROP TABLE IF EXISTS `ci_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0' COMMENT 'session id',
  `ip_address` varchar(16) NOT NULL DEFAULT '0' COMMENT '用户IP地址',
  `user_agent` varchar(120) NOT NULL COMMENT '客户端信息',
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `user_data` text NOT NULL COMMENT 'session中存储的数据',
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*班级表，用于存放已创建的班级的基本信息*/;
CREATE TABLE `classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '班级ID',
  `classname` varchar(255) NOT NULL COMMENT '班级名称',
  `creator_id` int(10) unsigned NOT NULL COMMENT '创建这个班级的老师ID，外键+索引，user.Id',
  `province_id` int(10) unsigned NOT NULL COMMENT '班级所在的省份/直辖市，冗余，索引，classes_area.Id',
  `city_id` int(10) unsigned NOT NULL COMMENT '班级所在的市，冗余，索引，classes_area.Id',
  `county_id` int(10) unsigned NOT NULL COMMENT '班级所在的县/区，冗余，索引，classes_area.Id',
  `school_id` int(10) unsigned NOT NULL COMMENT '班级所在的学校Id，外键+索引，classes_schools.Id',
  `close_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '班级是否处于关闭状态，0-正常，1-关闭',
  `class_grade` tinyint(4) NOT NULL COMMENT '年级，参考Constant::grade()',
  `class_year` smallint(6) NOT NULL COMMENT '班级的入学年份，如2013，2009',
  `invitation` varchar(30) NOT NULL COMMENT '班级的学生邀请码',
  `invitation_expire` int(10) unsigned NOT NULL COMMENT '班级的学生邀请码过期时间',
  `create_date` int(10) unsigned NOT NULL COMMENT '班级的创建时间',
  `class_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '班级是否允许学生加入',
  `stu_count` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '班级的学生数量',
  `tch_count` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '班级的老师数量',
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `class_grade` (`class_grade`),
  KEY `class_year` (`class_year`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_actionlog`
--

DROP TABLE IF EXISTS `classes_actionlog`;
/*用于存放班级内学生加入退出老师加入退出的操作log*/
CREATE TABLE `classes_actionlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'logId',
  `user_id` int(10) unsigned NOT NULL COMMENT '操作人，外键，user.Id',
  `class_id` int(10) unsigned NOT NULL COMMENT '发生的班级ID，外键，classes.Id',
  `action_id` tinyint(10) unsigned NOT NULL COMMENT '动作，参见model：Classes_actionlog里面的const常量',
  `dateline` int(10) unsigned NOT NULL COMMENT '动作发生的时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `class_id` (`class_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_area`
--

DROP TABLE IF EXISTS `classes_area`;
/*班级区域表，用于存在班级的地域信息*/
CREATE TABLE `classes_area` (
  `id` smallint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地域Id',
  `parentid` int(10) unsigned NOT NULL COMMENT '父Id，外键，classes_area.Id',
  `name` varchar(255) NOT NULL COMMENT '地域名称',
  `level` tinyint(4) NOT NULL COMMENT '地域层级 1.省份/直辖市，2-市，3.区/县',
  `first` char(1) NOT NULL COMMENT '首字母',
  `ismunicipality` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是直辖市还是省份，0-省份，1-直辖市',
  `hasschool` tinyint(4) NOT NULL COMMENT '该地区下是否有学校，0-无，1-有',
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `first` (`first`),
  KEY `ismunicipality` (`ismunicipality`),
  KEY `hasschool` (`hasschool`),
  KEY `parentid` (`parentid`)
) ENGINE=InnoDB AUTO_INCREMENT=3485 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_schools`
--

DROP TABLE IF EXISTS `classes_schools`;
/*班级学校信息表*/
CREATE TABLE `classes_schools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '学校Id',
  `county_id` smallint(5) unsigned NOT NULL COMMENT '学校所在的县/区，外键，classes_schools.Id',
  `schoolname` varchar(255) NOT NULL COMMENT '学校名称',
  `province_id` smallint(5) unsigned NOT NULL COMMENT '学校所在的省份/直辖市，索引，冗余，classes_schools.Id',
  `city_id` smallint(5) unsigned NOT NULL COMMENT '学校所在的市，索引，冗余，classes_schools.Id',
  `status` tinyint(4) NOT NULL COMMENT '是否关闭，1-不关闭',
  `py` varchar(255) NOT NULL COMMENT '学校名称的拼音全称',
  `first_py` varchar(30) NOT NULL COMMENT '学校名称的拼音首字母',
  PRIMARY KEY (`id`),
  KEY `county_id` (`county_id`),
  KEY `province_id` (`province_id`),
  KEY `city_id` (`city_id`)
) ENGINE=InnoDB AUTO_INCREMENT=88187 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_student`
--

DROP TABLE IF EXISTS `classes_student`;
/*班级学生关系表*/;
CREATE TABLE `classes_student` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关系Id',
  `class_id` int(10) unsigned NOT NULL COMMENT '班级名称，外键，classes.Id',
  `user_id` int(10) unsigned NOT NULL COMMENT '学生的user.id，注：非学号',
  `join_date` int(10) unsigned NOT NULL COMMENT '关系建立时间',
  `join_method` tinyint(4) NOT NULL COMMENT '加入的方式，1-通过注册-邀请码加入。2-通过老师生成帐号登录',
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_student_create`
--

DROP TABLE IF EXISTS `classes_student_create`;
/*教师创建的学生帐号表*/;
CREATE TABLE `classes_student_create` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键，无意义',
  `student_id` int(11) unsigned NOT NULL COMMENT '学号，通过classes_stuid触发获得',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '学生的user.id 0-未登录过;>0-该学生的user.id',
  `password` varchar(20) NOT NULL COMMENT '学生的初始密码。明文',
  `class_id` int(10) unsigned NOT NULL COMMENT '登录后默认加入的班级',
  `student_name` varchar(30) NOT NULL COMMENT '创建时候学生的姓名',
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`)
) ENGINE=MyISAM AUTO_INCREMENT=990 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_stuid`
--

DROP TABLE IF EXISTS `classes_stuid`;
/*学生学号触发器*/
CREATE TABLE `classes_stuid` (
  `id` int(10) unsigned NOT NULL COMMENT '当前的ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_teacher`
--

DROP TABLE IF EXISTS `classes_teacher`;
/*班级老师关系表*/;
CREATE TABLE `classes_teacher` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关系Id',
  `class_id` int(10) unsigned NOT NULL COMMENT '班级Id，外键，classes.Id',
  `teacher_id` int(10) unsigned NOT NULL COMMENT '教师的user.Id',
  `subject_id` tinyint(4) NOT NULL COMMENT '教师在班级内的授课subject_id',
  `join_date` int(10) unsigned NOT NULL COMMENT '教师加入的时间',
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `classes_teacher_apply`
--

DROP TABLE IF EXISTS `classes_teacher_apply`;
/*老师申请加入班级表*/;
CREATE TABLE `classes_teacher_apply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请Id',
  `class_id` int(10) unsigned NOT NULL COMMENT '所申请的班级编号',
  `teacher_id` int(10) unsigned NOT NULL COMMENT '申请加入班级的老师Id',
  `subject_id` tinyint(4) NOT NULL COMMENT '申请的科目',
  `apply_status` tinyint(4) NOT NULL COMMENT '申请状态 1：通过 0：未审核 -1：未通过',
  `apply_date` int(10) unsigned NOT NULL COMMENT '申请时间',
  `reply_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级管理猿的反馈时间',
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `teacher_id` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course`
--

/*同步章节目录表*/
DROP TABLE IF EXISTS `course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `subject_id` int(11) DEFAULT NULL COMMENT '科目ID，对应subject',
  `name` varchar(128) NOT NULL COMMENT '章节名称',
  `lft` int(11) NOT NULL COMMENT '左值',
  `rgt` int(11) NOT NULL COMMENT '右值',
  `depth` smallint(5) unsigned NOT NULL COMMENT '深度',
  `list_order` tinyint(4) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=MyISAM AUTO_INCREMENT=38785 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_delete`
--

DROP TABLE IF EXISTS `course_delete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_delete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `depth` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_subject` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13061 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exercise`
--

DROP TABLE IF EXISTS `exercise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercise` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `qtype_id` int(11) NOT NULL COMMENT '题目类型ID，对应question_type',
  `level_id` int(11) NOT NULL COMMENT '题目难度ID，对应question_level',
  `source` varchar(128) NOT NULL COMMENT '题目来源',
  `date` datetime NOT NULL COMMENT '创建时间',
  `title` varchar(128) NOT NULL COMMENT '题目标题',
  `body` longtext NOT NULL COMMENT '题目问题正文',
  `answer` longtext COMMENT '题目答案',
  `analysis` longtext COMMENT '题目解析',
  `asw` varchar(10) NOT NULL DEFAULT '' COMMENT '选择题答案',
  `online` tinyint(4) DEFAULT '0' COMMENT '是否发布',
  /* 1: 发布 */
  /* 21: 含有（如zxxk，学科网等）敏感关键词的下线 */
  /* 22: 一道有多个小题，但只有一个答案的选择题下线 */
  /* 23: 删除题目，非物理删除 */
  PRIMARY KEY (`id`),
  KEY `qtype_id` (`qtype_id`),
  KEY `level_id` (`level_id`),
  KEY `online` (`online`)
) ENGINE=MyISAM AUTO_INCREMENT=11015812 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exercise_analysis`
--

DROP TABLE IF EXISTS `exercise_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercise_analysis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testpoints` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7176973 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exercise_analysis_testpoint`
--

DROP TABLE IF EXISTS `exercise_analysis_testpoint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercise_analysis_testpoint` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testpoint` varchar(255) NOT NULL,
  `point_description` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `testpoint_2` (`testpoint`)
) ENGINE=MyISAM AUTO_INCREMENT=4585 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exercise_category`
--

/*作业题目，知识点目录，对应关系表*/
DROP TABLE IF EXISTS `exercise_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercise_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `category_id` int(11) NOT NULL COMMENT '题目目录ID，对应category',
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_id_2` (`question_id`,`category_id`),
  KEY `question_id` (`question_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5538458 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exercise_course`
--

/*作业题目，同步章节目录，对应关系表*/
DROP TABLE IF EXISTS `exercise_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercise_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `course_id` int(11) NOT NULL COMMENT '题目目录ID，对应course',
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_id_2` (`question_id`,`course_id`),
  KEY `question_id` (`question_id`),
  KEY `category_id` (`course_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6649403 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exercise_text`
--

DROP TABLE IF EXISTS `exercise_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercise_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `answer` longtext,
  `analysis` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11015812 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '如果是非登录用户，就没有这个',
  `from_name` varchar(30) DEFAULT NULL COMMENT '姓名，如果是非登录用户，就有这个',
  `from_email` varchar(100) DEFAULT NULL COMMENT 'email，如果是非登录用户，就有这个',
  `from_phone` varchar(50) DEFAULT NULL COMMENT '手机号，如果是非登录用户，就有这个',
  `content` text NOT NULL COMMENT '内容',
  `create_time` int(10) NOT NULL COMMENT '提交时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_all_save_log`
--

/*用不到了*/
DROP TABLE IF EXISTS `homework_all_save_log`;

--
-- Table structure for table `homework_assign`
--

DROP TABLE IF EXISTS `homework_assign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_assign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paper_id` int(11) DEFAULT NULL COMMENT '作业卷子的id',
  `name` varchar(100) DEFAULT NULL,
  `is_assigned` tinyint(4) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL COMMENT '作业开始时间',
  `deadline` int(11) DEFAULT NULL COMMENT '作业截止时间',
  `get_answer_way` int(11) DEFAULT '1' COMMENT '老师设置的，学生获取答案的方式',
  `count` int(11) DEFAULT NULL,
  `assign_time` int(11) DEFAULT NULL COMMENT '布置作业的时间',
  `class_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT '当作业是布置给某个学生而非班级的时候，才有值',
  `online` tinyint(4) DEFAULT '1' COMMENT '线上或线下作业',
  `description` varchar(255) DEFAULT NULL COMMENT '作业简介',
  `is_other` tinyint(4) DEFAULT '0' COMMENT '标识：其他作业',
  `is_checked` tinyint(4) DEFAULT NULL COMMENT '是否已经检查，1为已检查',
  `submit_type` tinyint(1) DEFAULT '1' COMMENT '提交方式',
  PRIMARY KEY (`id`),
  KEY `homework_assign_paper_id` (`paper_id`) USING BTREE,
  CONSTRAINT `homework_assign_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `homework_paper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=714 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_download_log`
--

/*作业下载记录表*/
DROP TABLE IF EXISTS `homework_download_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_download_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `logname` varchar(100) NOT NULL COMMENT '存档时间',
  `download_time` datetime NOT NULL COMMENT '下载时间',
  `ip` char(45) NOT NULL COMMENT '下载时用户IP',
  `paper_id` int(11) NOT NULL COMMENT '作业ID，对应homework_paper',
  `word_version` varchar(20) DEFAULT NULL COMMENT 'word版本，07，03',
  `paper_size` varchar(30) DEFAULT NULL COMMENT '纸张大小，如A4',
  `paper_type` varchar(30) DEFAULT NULL COMMENT '试卷答案类型或答题卡类型',
  `is_delete` tinyint(1) DEFAULT NULL COMMENT '是否已删除',
  `download_link` varchar(200) DEFAULT NULL COMMENT '内网文件链接',
  PRIMARY KEY (`id`),
  KEY `paper_id` (`paper_id`),
  CONSTRAINT `paper_id` FOREIGN KEY (`paper_id`) REFERENCES `homework_paper` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_offline`
--

DROP TABLE IF EXISTS `homework_offline`;


--
-- Table structure for table `homework_other`
--

DROP TABLE IF EXISTS `homework_other`;


--
-- Table structure for table `homework_paper`
--

/*作业表*/
DROP TABLE IF EXISTS `homework_paper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_paper` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `subject_id` int(11) DEFAULT NULL COMMENT '科目ID，对应subject',
  `name` varchar(255) DEFAULT NULL COMMENT '作业名称，保留字段，未启用',
  `question_type_order` varchar(100) DEFAULT NULL COMMENT '线上线下题型顺序，以字符串顺序存储homwwork_question_type的id',
  `is_saved` tinyint(1) DEFAULT '0' COMMENT '0-缓冲区，1-已存储',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '作业已经布置，锁定后不能编辑',
  `is_recovery` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '0-缓冲区新建状态，1-缓冲区编辑状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1363 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_question`
--

/*作业题目表*/
DROP TABLE IF EXISTS `homework_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `qtype_id` int(11) DEFAULT NULL COMMENT '题目类型ID，对应question_type',
  `question_id` int(11) DEFAULT NULL COMMENT '题目ID，对应question',
  `is_delete` tinyint(4) DEFAULT NULL COMMENT '是否被删除',
  `paper_id` int(11) NOT NULL COMMENT '作业ID，对应homework_paper',
  `question_origin` tinyint(1) DEFAULT '0' COMMENT '试卷来源，0-默认，1-用户上传',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '试题知识点来源',
  `course_id` int(11) NOT NULL DEFAULT '0' COMMENT '试题同步教材来源',
  PRIMARY KEY (`id`),
  KEY `homework_question_paper_Id` (`paper_id`) USING BTREE,
  CONSTRAINT `homework_question_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `homework_paper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=493 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_question_type`
--

/*作业题型表*/
DROP TABLE IF EXISTS `homework_question_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_question_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(10) DEFAULT NULL COMMENT '题型名称，默认同quesiton_type',
  `qtype_id` int(11) DEFAULT NULL COMMENT '题目类型ID，对应question_type',
  `note` varchar(255) CHARACTER SET latin1 DEFAULT NULL COMMENT '题型注释',
  `is_delete` tinyint(4) DEFAULT NULL COMMENT '是否被删除',
  `question_order` varchar(100) DEFAULT NULL COMMENT '题型对应的题目顺序，以字符串顺序存储id',
  `paper_id` int(11) DEFAULT NULL COMMENT '作业ID，对应homework_paper',
  PRIMARY KEY (`id`),
  KEY `homework_question_type_paper_id` (`paper_id`) USING BTREE,
  CONSTRAINT `homework_question_type_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `homework_paper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=559 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homework_save_log`
--

/*作业存档表*/
DROP TABLE IF EXISTS `homework_save_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homework_save_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `logname` varchar(100) DEFAULT NULL COMMENT '存档名称',
  `save_time` datetime DEFAULT NULL COMMENT '存档时间',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已删除',
  `paper_id` int(11) NOT NULL COMMENT '作业ID，对应homework_paper',
  PRIMARY KEY (`id`),
  KEY `paper_id` (`paper_id`) USING BTREE,
  CONSTRAINT `homework_save_log_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `homework_paper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson_course`
--

DROP TABLE IF EXISTS `lesson_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
/*记录备课文档和章节对应关系*/
CREATE TABLE `lesson_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键-自增ID',
  `doc_id` int(11) NOT NULL COMMENT '外键-文档ID',
  `course_id` int(11) NOT NULL COMMENT '外键-章节ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_id_2` (`doc_id`,`course_id`),
  KEY `doc_id` (`doc_id`),
  KEY `category_id` (`course_id`)
) ENGINE=MyISAM AUTO_INCREMENT=297849 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson_document`
--

DROP TABLE IF EXISTS `lesson_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
/*记录备课文档信息*/
CREATE TABLE `lesson_document` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键-自增ID',
  `doc_type` tinyint(1) NOT NULL COMMENT '外键-文档类型',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '外键-上传用户ID',
  `file_name` varchar(255) NOT NULL COMMENT '文件名',
  `file_ext` varchar(10) NOT NULL COMMENT '扩展名',
  `file_size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件字节大小',
  `file_path` varchar(200) DEFAULT NULL COMMENT '文件fastdfs相对路径',
  `page_count` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '总页数',
  `downloads` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `favorites` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '收藏次数',
  `upload_ip` bigint(20) DEFAULT NULL COMMENT '上传IP',
  `upload_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `user_operation` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户操作状态：1：公开显示，2：非公开显示，0：删除',
  `hits` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文档状态：0：等待转换，9：转换中，8：转换失败，7：待审核，3:未通过审核(包含敏感或不良信息),2:未通过审核(内容与学科不符),1:审核通过,20:过小的（空白）文档下线,21:含有学科王的文档下线,22:ppt含有www.zxxk.com临时下线',
  /* 20: 空白文档（<=30K的文档下线） */
  /* 21: 含有（如zxxk，学科网等）敏感关键词的下线 */
  /* 22: 临时下线ppt里面含有www.zxxk.com的文档 */
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `type` (`doc_type`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55504 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson_document_type`
--

DROP TABLE IF EXISTS `lesson_document_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
/*记录备课文档类型信息*/
CREATE TABLE `lesson_document_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键-自增ID',
  `name` varchar(15) NOT NULL COMMENT '类型名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson_download_log`
--

DROP TABLE IF EXISTS `lesson_download_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
/*记录用户下载备课文档信息*/
CREATE TABLE `lesson_download_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键-自增ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `download_name` varchar(100) NOT NULL COMMENT '下载文件名',
  `download_time` int(10) unsigned NOT NULL COMMENT '下载时间',
  `download_ip` char(45) NOT NULL COMMENT '下载IP',
  `download_link` varchar(200) NOT NULL COMMENT '下载链接',
  `doc_id` int(11) unsigned NOT NULL COMMENT '文档ID',
  `is_delete` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除：0:未删除(默认),1:删除',
  `doc_type` tinyint(1) NOT NULL COMMENT '文档类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lesson_preview_doc`
--

DROP TABLE IF EXISTS `lesson_preview_doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
/*记录备课文档转换后的子文件信息*/
CREATE TABLE `lesson_preview_doc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键-自增ID',
  `doc_id` int(11) unsigned NOT NULL COMMENT '原文件id',
  `load_path` varchar(200) NOT NULL COMMENT 'swf文件的oss文件路径',
  `begin_page` mediumint(8) unsigned NOT NULL DEFAULT '1' COMMENT '起始页',
  `end_page` mediumint(8) unsigned NOT NULL DEFAULT '1' COMMENT '结尾页',
  `load_file_size` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  PRIMARY KEY (`id`),
  KEY `fileid` (`doc_id`),
  KEY `begin_pape` (`begin_page`)
) ENGINE=MyISAM AUTO_INCREMENT=1340850 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_download_log`
--

/*组卷下载记录表*/
DROP TABLE IF EXISTS `paper_download_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_download_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `logname` varchar(100) NOT NULL COMMENT '存档时间',
  `download_time` datetime NOT NULL COMMENT '下载时间',
  `ip` char(45) NOT NULL COMMENT '下载时用户IP',
  `testpaper_id` int(11) NOT NULL COMMENT '试卷ID，对应paper_testpaper',
  `word_version` varchar(20) DEFAULT NULL COMMENT 'word版本，07，03',
  `paper_size` varchar(30) DEFAULT NULL COMMENT '纸张大小，如A4',
  `paper_type` varchar(30) DEFAULT NULL COMMENT '试卷答案类型或答题卡类型',
  `is_delete` tinyint(1) DEFAULT NULL COMMENT '是否已删除',
  `download_link` varchar(200) DEFAULT NULL COMMENT '内网文件链接',
  PRIMARY KEY (`id`),
  KEY `paper_download_log_5d203ab2` (`testpaper_id`),
  CONSTRAINT `testpaper_id_refs_id_82703432` FOREIGN KEY (`testpaper_id`) REFERENCES `paper_testpaper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_question`
--

/*组卷试卷题目表*/
DROP TABLE IF EXISTS `paper_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `qtype_id` int(11) NOT NULL COMMENT '题目类型ID，对应question_type',
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否删除',
  `testpaper_id` int(11) unsigned NOT NULL COMMENT '试卷ID，对应paper_testpaper，冗余查询',
  `question_origin` tinyint(1) DEFAULT '0' COMMENT '试卷来源，0-默认，1-用户上传',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '试题知识点来源',
  `course_id` int(11) NOT NULL DEFAULT '0' COMMENT '试题同步教材来源',
  PRIMARY KEY (`id`),
  KEY `paper_question_e3506b83` (`qtype_id`)
) ENGINE=InnoDB AUTO_INCREMENT=504 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_question_recycle`
--

/*试卷回收站，未启用*/
DROP TABLE IF EXISTS `paper_question_recycle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_question_recycle` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `qtype_id` int(11) NOT NULL COMMENT '题目类型ID，对应question_type',
  `testpaper_id` int(11) NOT NULL COMMENT '试卷ID，对应paper_testpaper',
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `delete_time` datetime NOT NULL COMMENT '删除时间',
  `is_remove` tinyint(1) NOT NULL COMMENT '是否从回收站移除',
  `paper_question_id` int(11) NOT NULL COMMENT '组卷试卷ID，对应paper',
  PRIMARY KEY (`id`),
  KEY `question_recycle_5d203ab2` (`testpaper_id`),
  CONSTRAINT `testpaper_id_refs_id_f824d0b9` FOREIGN KEY (`testpaper_id`) REFERENCES `paper_testpaper` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_question_type`
--

/*组卷试卷题型表*/
DROP TABLE IF EXISTS `paper_question_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_question_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `section_id` int(11) NOT NULL COMMENT '分卷ID，对应paper_section',
  `name` varchar(10) NOT NULL DEFAULT '' COMMENT '题型名称，默认同quesiton_type',
  `qtype_id` int(11) DEFAULT NULL COMMENT '题目类型ID，对应question_type',
  `note` varchar(50) NOT NULL COMMENT '题型注释',
  `is_delete` tinyint(1) NOT NULL COMMENT '是否被删除',
  `question_order` varchar(100) NOT NULL COMMENT '题型对应的题目顺序，以字符串顺序存储id',
  `testpaper_id` int(11) unsigned NOT NULL COMMENT '试卷ID，对应paper_testpaper，冗余查询',
  `is_show_question_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示题型',
  `is_show_performance` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示评分誊栏',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=786 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_save_log`
--

/*组卷存档记录表*/
DROP TABLE IF EXISTS `paper_save_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_save_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `logname` varchar(100) NOT NULL COMMENT '存档名称，同试卷名称',
  `save_time` datetime NOT NULL COMMENT '存档时间',
  `testpaper_id` int(11) NOT NULL COMMENT '试卷ID，对应paper_testpaper',
  `is_delete` tinyint(1) DEFAULT NULL COMMENT '是否已删除',
  PRIMARY KEY (`id`),
  KEY `paper_save_log_5d203ab2` (`testpaper_id`),
  CONSTRAINT `testpaper_id_refs_id_08233046` FOREIGN KEY (`testpaper_id`) REFERENCES `paper_testpaper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_section`
--

/*组卷试卷分卷表*/
DROP TABLE IF EXISTS `paper_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` tinyint(1) NOT NULL COMMENT '分卷类型1-分卷I，2-分卷II',
  `testpaper_id` int(11) NOT NULL COMMENT '试卷ID，对应paper_testpaper',
  `label` varchar(30) NOT NULL COMMENT '卷标，默认分卷I，分卷II',
  `note` varchar(50) NOT NULL COMMENT '卷注，默认分卷I注释，分卷II注释',
  `question_type_order` varchar(100) NOT NULL COMMENT '分卷题型顺序，以字符串顺序存储paper_question_type的id',
  `is_show_section_header` tinyint(1) DEFAULT NULL COMMENT '是否显示分卷头部',
  PRIMARY KEY (`id`),
  KEY `section_5d203ab2` (`testpaper_id`),
  CONSTRAINT `testpaper_id` FOREIGN KEY (`testpaper_id`) REFERENCES `paper_testpaper` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paper_testpaper`
--

/*组卷试卷表*/
DROP TABLE IF EXISTS `paper_testpaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paper_testpaper` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `subject_id` int(11) NOT NULL COMMENT '科目ID，对应subject',
  `style` tinyint(1) NOT NULL DEFAULT '1' COMMENT '样式类型1-默认，2-标准，3-测验，4-作业',
  `main_title` varchar(100) NOT NULL COMMENT '主标题',
  `is_show_main_title` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示主标题',
  `sub_title` varchar(100) NOT NULL COMMENT '副标题',
  `is_show_sub_title` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示副标题',
  `secret_sign` varchar(10) NOT NULL COMMENT '保密标记',
  `is_show_secret_sign` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示保密标记',
  `is_show_line` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示装订线',
  `info` varchar(30) NOT NULL COMMENT '试卷信息栏',
  `is_show_info` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示试卷信息栏',
  `student_input` varchar(80) NOT NULL COMMENT '考生输入栏',
  `is_show_student_input` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示考生输入栏',
  `is_show_performance` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示眷分栏',
  `pay_attention` varchar(50) NOT NULL COMMENT '注意事项栏',
  `is_show_pay_attention` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示注意事项栏',
  `is_saved` tinyint(1) DEFAULT '0' COMMENT '0-缓冲区，1-已存储',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '作业已经布置，锁定后不能编辑',
  `is_recovery` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '0-缓冲区新建状态，1-缓冲区编辑状态',
  PRIMARY KEY (`id`),
  KEY `test_paper_56bb4187` (`subject_id`),
  CONSTRAINT `subject_id_refs_id_961f6840` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1604 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parents_kids`
--

DROP TABLE IF EXISTS `parents_kids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parents_kids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_user_id` int(11) unsigned DEFAULT NULL,
  `kid_user_id` int(11) unsigned DEFAULT NULL,
  `relation_ship` varchar(200) DEFAULT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_kid` (`parent_user_id`,`kid_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question`
--

/*组卷题目库*/
DROP TABLE IF EXISTS `question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `qtype_id` int(11) NOT NULL COMMENT '题目类型ID，对应question_type',
  `level_id` int(11) NOT NULL COMMENT '题目难度ID，对应question_level',
  `source` varchar(128) NOT NULL COMMENT '题目来源',
  `date` datetime NOT NULL COMMENT '创建时间',
  `title` varchar(128) NOT NULL COMMENT '题目标题',
  `body` varchar(255) NOT NULL COMMENT '题目问题正文',
  `analysis` varchar(255) NOT NULL COMMENT '题目解析',
  `answer` varchar(255) NOT NULL COMMENT '题目答案',
  `asw` varchar(10) NOT NULL COMMENT '选择题答案',
  `online` tinyint(4) DEFAULT '0' COMMENT '是否发布',
  /* 1：发布 */
  /* 21: 含有（如zxxk，学科网等）敏感关键词的下线 */
  /* 22: 20140111 下线category_id=178，英语语音 */
  /* 24: 20140403 布置作业从exercise到question。匹配不出asw的题目下线 */
  /* 25: 20140409 题型不符合学科，下线题目 */
  PRIMARY KEY (`id`),
  KEY `question_e3506b83` (`qtype_id`),
  KEY `question_b8f3f94a` (`level_id`),
  KEY `online` (`online`)
) ENGINE=MyISAM AUTO_INCREMENT=1023593 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_category`
--

/*组卷题目，知识点目录，对应关系表*/
DROP TABLE IF EXISTS `question_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `category_id` int(11) NOT NULL COMMENT '题目目录ID，对应category',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `category_question` (`question_id`,`category_id`),
  KEY `question_id_idx` (`question_id`),
  KEY `category_id_idx` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2652389 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_category_delete`
--

DROP TABLE IF EXISTS `question_category_delete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_category_delete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `category_question` (`question_id`,`category_id`),
  KEY `question_id_idx` (`question_id`),
  KEY `category_id_idx` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3572385 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_course`
--

/*组卷题目，同步章节目录，对应关系表，未启用*/
DROP TABLE IF EXISTS `question_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `category_id` int(11) NOT NULL COMMENT '题目目录ID，对应course',
  PRIMARY KEY (`id`) COMMENT '',
  UNIQUE KEY `category_question` (`category_id`,`question_id`) COMMENT '',
  KEY `course_question` (`question_id`) COMMENT '',
  CONSTRAINT `course_id` FOREIGN KEY (`category_id`) REFERENCES `course_delete` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `course_question` FOREIGN KEY (`question_id`) REFERENCES `question_delete` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13500 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_delete`
--

DROP TABLE IF EXISTS `question_delete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_delete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qtype_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `source` varchar(128) NOT NULL,
  `date` datetime NOT NULL,
  `title` varchar(128) NOT NULL,
  `body` longtext NOT NULL,
  `body_detail` longtext,
  `answer` longtext,
  `answer_detail` longtext,
  `analysis` longtext,
  PRIMARY KEY (`id`),
  KEY `question_e3506b83` (`qtype_id`),
  KEY `question_b8f3f94a` (`level_id`),
  CONSTRAINT `level_id_refs_id_3e4d1f63` FOREIGN KEY (`level_id`) REFERENCES `question_level` (`id`),
  CONSTRAINT `qtype_id_refs_id_0ae76d37` FOREIGN KEY (`qtype_id`) REFERENCES `question_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1701011 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_level`
--

/*题目难度*/
DROP TABLE IF EXISTS `question_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(10) NOT NULL COMMENT '难度名称',
  `level` smallint(5) unsigned NOT NULL COMMENT '难度值1-5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_level`
--

/*题目组卷数目统计*/
DROP TABLE IF EXISTS `question_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL COMMENT '题目ID，对应question',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '题目组卷数量',
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `question_origin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '题目来源',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=664 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_maps`
--

DROP TABLE IF EXISTS `question_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `origin` varchar(255) NOT NULL,
  `newpath` varchar(255) NOT NULL,
  `http_code` int(11) NOT NULL,
  `filesize` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `origin` (`origin`),
  KEY `http_code` (`http_code`)
) ENGINE=MyISAM AUTO_INCREMENT=2053426 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_text`
--

DROP TABLE IF EXISTS `question_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `analysis` longtext,
  `answer` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1023593 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_type`
--

/*问题类型*/
DROP TABLE IF EXISTS `question_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(10) NOT NULL COMMENT '问题类型名称',
  `is_select_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是线上作业选择题题型',
  `is_section_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是组卷选择题题型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_type_subject`
--

/*问题类型，科目，对应关系表*/
DROP TABLE IF EXISTS `question_type_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_type_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `question_type_id` int(11) NOT NULL COMMENT '题目类型ID，对应question_type',
  `subject_id` int(11) NOT NULL COMMENT '科目ID，对应subject',
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_type_id` (`question_type_id`,`subject_id`),
  KEY `subject_id_refs_id_89130800` (`subject_id`),
  CONSTRAINT `question_type_id_refs_id_38fc0f27` FOREIGN KEY (`question_type_id`) REFERENCES `question_type` (`id`),
  CONSTRAINT `subject_id_refs_id_89130800` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

/*用户登录session统计表，每次登录后产生记录*/
DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `session_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'session id',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
  `user_type` tinyint(1) DEFAULT NULL COMMENT '用户类型',
  `email` varchar(100) DEFAULT NULL COMMENT '用户email',
  `phone` varchar(11) DEFAULT NULL COMMENT '用户phone',
  `uname` varchar(255) DEFAULT NULL COMMENT '用户用户名',
  `name` varchar(30) DEFAULT NULL COMMENT '用户姓名',
  `student_id` varchar(30) DEFAULT NULL COMMENT '学生学号',
  `ip` bigint(20) unsigned DEFAULT NULL COMMENT '用户ip',
  `generate_time` datetime DEFAULT NULL COMMENT '登录时间',
  `expire_time` datetime DEFAULT NULL COMMENT '未启用',
  `user_data` text COMMENT '用户数据，json格式',
  `switch_id` int(11) DEFAULT NULL COMMENT '家长学生切换记录',
  PRIMARY KEY (`id`),
  KEY `sid_expiretime` (`session_id`,`expire_time`),
  KEY `userid_expiretime` (`user_id`,`expire_time`),
  KEY `sid_userid_expiretime` (`session_id`,`user_id`,`expire_time`),
  CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2697 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_data`
--

DROP TABLE IF EXISTS `student_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_data` (
  `1` int(20) NOT NULL AUTO_INCREMENT,
  `uid` int(20) NOT NULL,
  `sex` tinyint(1) NOT NULL DEFAULT '1',
  `aq_account_balance` int(10) NOT NULL,
  `ip` varchar(50) DEFAULT NULL COMMENT 'ip',
  `area` varchar(100) DEFAULT NULL COMMENT '地区',
  `qq` varchar(50) DEFAULT NULL COMMENT 'QQ',
  PRIMARY KEY (`1`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_homework`
--

DROP TABLE IF EXISTS `student_homework`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_homework` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL COMMENT '学生 uid',
  `s_answer` text,
  `correct_num` int(5) NOT NULL DEFAULT '0',
  `question_num` int(5) NOT NULL DEFAULT '0',
  `start_time` int(11) DEFAULT '0',
  `end_time` int(11) DEFAULT '0',
  `second_time` int(11) NOT NULL DEFAULT '0',
  `expend_time` int(11) DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `is_look` tinyint(1) NOT NULL DEFAULT '0',
  `is_break` tinyint(1) NOT NULL DEFAULT '0',
  `is_submit` tinyint(1) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  `rank` int(5) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '成绩',
  `submit_type` tinyint(1) DEFAULT '1' COMMENT '提交方式(1,手动提交;2,系统提交)',
  PRIMARY KEY (`id`),
  KEY `student_homework_assign_id` (`assignment_id`) USING BTREE,
  KEY `student_homework_student_id` (`student_id`) USING BTREE,
  CONSTRAINT `student_homework_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `homework_assign` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2423 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_homework_comment`
--

DROP TABLE IF EXISTS `student_homework_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_homework_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_homework_id` int(11) NOT NULL,
  `content` text,
  `comment_time` int(11) NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student_homework_comment_id` (`student_homework_id`) USING BTREE,
  CONSTRAINT `student_homework_comment_ibfk_1` FOREIGN KEY (`student_homework_id`) REFERENCES `student_homework` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student_reset_password`
--

/*学生找回密码*/
DROP TABLE IF EXISTS `student_reset_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_reset_password` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `uname` varchar(255) DEFAULT NULL COMMENT '用户用户名',
  `student_id` varchar(30) DEFAULT NULL COMMENT '学生学号',
  `name` varchar(30) DEFAULT NULL COMMENT '学生姓名',
  `email` varchar(40) DEFAULT NULL COMMENT '学生email，暂时未启用',
  `phone` varchar(11) DEFAULT NULL COMMENT '学生phone，无验证',
  `submit_time` datetime DEFAULT NULL COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject`
--

/*科目，带年级，如：初中数学*/
DROP TABLE IF EXISTS `subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(10) NOT NULL COMMENT '科目名称',
  `type` tinyint(2) DEFAULT NULL COMMENT '科目类型ID，对应subject_type',
  `grade` tinyint(2) DEFAULT NULL COMMENT '年级1-小学，2-初中，3-高中',
  `online` tinyint(1) DEFAULT 0 COMMENT '是否上线',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subject_type`
--

/*科目类型，如：数学*/
DROP TABLE IF EXISTS `subject_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subject_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(10) NOT NULL COMMENT '科目类型名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*用户表*/
DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `verified` tinyint(1) DEFAULT NULL COMMENT '是否已激活',
  `email` varchar(100) DEFAULT NULL COMMENT '用户email',
  `email_verified` tinyint(1) DEFAULT NULL COMMENT '用户email是否已验证',
  `phone` varchar(50) DEFAULT NULL COMMENT '用户phone，为NULL，存储在thrif中',
  `phone_verified` tinyint(1) DEFAULT NULL COMMENT '用户phone是否已验证',
  `phone_mask` varchar(11) DEFAULT NULL COMMENT '用户phone显示，中间4位为*',
  `uname` varchar(255) DEFAULT NULL COMMENT '用户用户名',
  `password` varchar(50) DEFAULT NULL COMMENT '用户密码，使用salt$sha1(salt+md5(password))方法加密',
  `name` varchar(30) DEFAULT NULL COMMENT '用户真实姓名',
  `student_id` varchar(30) DEFAULT NULL COMMENT '学生学号',
  `user_type` tinyint(1) DEFAULT NULL COMMENT '用户类型2-学生，3-老师，4-家长',
  `avatar` tinyint(1) DEFAULT '0' COMMENT '用户上传头像',
  `register_subject` int(11) DEFAULT NULL COMMENT '注册学科',
  `register_grade` int(11) DEFAULT NULL COMMENT '注册年级',
  `register_time` datetime DEFAULT NULL COMMENT '注册时间',
  `register_ip` bigint(20) DEFAULT NULL COMMENT '注册IP',
  `register_origin` tinyint(1) DEFAULT NULL COMMENT '注册来源 0*-web，2*-ios，3*-androd，4*-crm',
  `is_lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已锁定，锁定后不能登录',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `email_phone` (`email`,`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=1000068 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_parent_data`
--

/*家长信息表*/
DROP TABLE IF EXISTS `user_parent_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_parent_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别',
  `age` int(11) NOT NULL DEFAULT '0' COMMENT '年龄',
  `birthday` DATE DEFAULT NOT NULL DEFAULT '0000-00-00' COMMENT '生日',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_statistics`
--

/*用户数量统计*/
DROP TABLE IF EXISTS `user_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_statistics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `teacher` int(11) NOT NULL DEFAULT '0',
  `student` int(11) NOT NULL DEFAULT '0',
  `parent` int(11) NOT NULL DEFAULT '0',
  `school` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_teacher_data`
--

/*老师信息表*/
DROP TABLE IF EXISTS `user_teacher_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_teacher_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别',
  `age` int(11) NOT NULL DEFAULT '0' COMMENT '年龄',
  `paper_download_default` varchar(255) DEFAULT NULL COMMENT '组卷下载默认',
  `card_download_default` varchar(255) DEFAULT NULL COMMENT '答题卡下载默认',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `verify`
--

/*验证表，现在使用redis进行验证，当redis不可用时，使用mysql存储*/
DROP TABLE IF EXISTS `verify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verify` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
  `user_type` tinyint(1) DEFAULT NULL COMMENT '用户类型',
  `email` varchar(100) DEFAULT NULL COMMENT '用户email',
  `phone` varchar(50) DEFAULT NULL COMMENT '用户phone',
  `phone_mask` varchar(11) DEFAULT NULL COMMENT '用户phone显示，中间4位为*',
  `type` tinyint(1) DEFAULT NULL COMMENT '验证码类型1-email，2-phone',
  `code_type` tinyint(1) DEFAULT NULL COMMENT '验证类型1-注册，2-修改密码，3-修改email，4-修改phone',
  `authcode` varchar(128) DEFAULT NULL COMMENT '验证码',
  `has_verified` tinyint(1) DEFAULT NULL COMMENT '是否已验证过',
  `generate_time` datetime DEFAULT NULL COMMENT '验证码生成时间',
  `verified_time` datetime DEFAULT NULL COMMENT '完成验证时间',
  PRIMARY KEY (`id`),
  KEY `code_verify_type` (`email`,`has_verified`,`type`),
  KEY `email_ctype_verify_gentime` (`email`,`code_type`,`has_verified`,`generate_time`),
  KEY `phone_ctype_verify_gentime` (`phone`,`code_type`,`has_verified`,`generate_time`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wrong_question`
--

DROP TABLE IF EXISTS `wrong_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wrong_question` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `question_id` int(11) unsigned NOT NULL COMMENT '错题id',
  `category_id` int(11) unsigned NOT NULL COMMENT '知识点id',
  `assignment_id` int(11) unsigned NOT NULL COMMENT '作业id',
  `modified` date NOT NULL COMMENT '该作业题目做错的时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_question` (`user_id`,`question_id`,`category_id`,`assignment_id`) USING BTREE,
  KEY `homework_question` (`assignment_id`,`question_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=324 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wrong_statistics`
--

DROP TABLE IF EXISTS `wrong_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wrong_statistics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `assignment_id` int(11) unsigned NOT NULL COMMENT '作业id',
  `category_id` int(11) unsigned NOT NULL COMMENT '知识点id',
  `subject_id` int(11) unsigned NOT NULL COMMENT '科目库id',
  `wrong_nums` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '错题数量',
  `nums` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '该知识点该天做题量',
  `created` datetime NOT NULL COMMENT '做题日期',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_category_date` (`user_id`,`subject_id`,`category_id`,`assignment_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wrong_success_question`
--

DROP TABLE IF EXISTS `wrong_success_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wrong_success_question` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `question_id` int(11) unsigned NOT NULL COMMENT '问题id',
  `category_id` int(11) unsigned NOT NULL COMMENT '知识点id',
  `latest_success` tinyint(1) unsigned NOT NULL COMMENT '最新一次是否做对，1做对，0做错',
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `use_question_category` (`user_id`,`question_id`,`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-11-05 15:00:28
