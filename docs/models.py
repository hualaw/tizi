#coding=utf-8

from django.db import models


# 学科
class Subject(models.Model):
    """
        name: 学科名称
    """
    name = models.CharField(max_length=10, verbose_name=u'名称')


# 各学科的不同版本, 如沪科版，鲁教版等
#class Subject_Category(models.Model):
#    """
#        subject: 对应学科
#        name: 版本名称
#    """
#    subject = models.ForeignKey(Subject)
#    name = models.CharField(max_length=10, verbose_name=u'名称')


# 分类
class Category(models.Model):
    """
        subject: 对应科目
        name: 知识点名称
        lft: 左值
        rgt： 右值
        depth: 深度
    """
    subject = models.ForeignKey(Subject, null=True)
    name = models.CharField(max_length=128, verbose_name=u'名称')
    lft = models.IntegerField()
    rgt = models.IntegerField()
    depth = models.PositiveSmallIntegerField()


# 题型
class Question_Type(models.Model):
    """
        subject: 对应学科
        name： 对应题型名称
    """
    subject = models.ManyToManyField('Subject')
    name = models.CharField(max_length=10, unique=True)
    is_select_type = models.BooleanField(default=False)


# 难度
class Question_Level(models.Model):
    """
        name: 难度名称
        level: 难度级别（几星）
    """
    name = models.CharField(max_length=10)
    level = models.PositiveSmallIntegerField()


# 专题
#class Special(models.Model):
#    """
#        subject: 对应学科
#        name: 名称
#    """
#    subject = models.ForeignKey(Subject)
#    name = models.CharField(max_length=30)
#
#
## 子专题
#class SubSpecial(models.Model):
#    name = models.CharField(max_length=30)
#    special = models.ForeignKey(Special)


# 题目
class Question(models.Model):
    """
        qtype: 对应题型
        level： 对应难度级别
        source： 来源信息
        date: 出题时间
        title： 标题，不一定总存在
        body：题目正文
        analysis： 题目解析
        answer： 题目答案
        category: 对应知识点,且加上索引
    """
    qtype = models.ForeignKey(Question_Type, db_index=True)
    level = models.ForeignKey(Question_Level, db_index=True)
    source = models.CharField(max_length=128)
    date = models.DateTimeField()
    title = models.CharField(max_length=128)
    body = models.TextField()
    analysis = models.TextField(blank=True, null=True)
    answer = models.TextField(blank=True, null=True)
    category = models.ForeignKey(Category, db_index=True)


# 卷子
class Test_Paper(models.Model):
    """
        user: 对应帐户
        subject： 对应学科
        style： 试卷样式（包括作业样式，测验样式， 标准样式， 默认样式等）
        main_title: 主标题
        sub_title: 副标题
        没有装订线字段，因为他不可修改，只能选择显示或者不显示
        secret_sign: 保密标记
        info：试卷信息栏
        student_input: 考生输入栏
        performance： 誊分栏
        pay_attention: 注意事项栏

        is_saved: 确定试卷是否被存档
        is_show_main_title: 是否显示主标题
        is_show_sub_title: 是否显示副标题
        is_show_line: 是否显示装订线
        is_show_secret_sign: 是否显示保密标记
        is_show_info: 是否显示试卷信息栏
        is_show_student_input: 是否显示考生输入栏
        is_show_performance: 是否显示誊分栏
        is_show_pay_attention: 是否显示注意事项栏
    """
    user_id = models.IntegerField()
    subject = models.ForeignKey(Subject)
    style = models.PositiveSmallIntegerField(default=1)
    main_title = models.CharField(max_length=100)
    sub_title = models.CharField(max_length=100)
    secret_sign = models.CharField(max_length=30)
    info = models.CharField(max_length=30)
    student_input = models.CharField(max_length=30)
    pay_attention = models.CharField(max_length=50)
    # 通过bit位来确定是否显示其头信息
    is_saved = models.BooleanField(default=False)
    is_show_main_title = models.BooleanField(default=True)
    is_show_sub_title = models.BooleanField(default=True)
    is_show_line = models.BooleanField(default=True)
    is_show_secret_sign = models.BooleanField(default=True)
    is_show_info = models.BooleanField(default=True)
    is_show_student_input = models.BooleanField(default=True)
    is_show_performance = models.BooleanField(default=True)
    is_show_pay_attention = models.BooleanField(default=True)
    


# 分卷
class Section(models.Model):
    """
        type: 分卷类别，分卷I或者分卷II
        testpaper: 对应试卷类别
        label： 对应卷标
        note： 对应卷注
        question_type_order: 分卷对应的题型顺序，以字符串顺序存储PaperQuestionType的id
        is_show_section_header: 是否显示分卷头部
    """
    # type表示卷1或者卷2
    type = models.PositiveSmallIntegerField()
    testpaper = models.ForeignKey(Test_Paper)
    label = models.CharField(max_length=30)
    note = models.CharField(max_length=50)
    is_show_section_header = models.BooleanField(default=True)
    question_type_order = models.CharField(max_length=100)  # 表示题型的顺序


# 试卷题型
class Paper_Question_Type(models.Model):
    """
        继承自全站的题型表，题中定义的字段为添加信息
        section: 对应分卷
        note：题型注释
        is_delete: 是否被删除
        question_order: 题型对应的题目顺序，以字符串顺序存储id
        is_show_question_type: 是否显示题型
        is_show_performance: 是否显示评分誊栏
    """
    testpaper_id = models.IntegerField()
    section = models.ForeignKey(Section)
    name = models.CharField(max_length=10)
    qtype = models.ForeignKey(Question_Type, blank=True, null=True)
    note = models.CharField(max_length=50)
    is_delete = models.BooleanField(default=False)
    question_order = models.CharField(max_length=100)
    is_show_question_type = models.BooleanField(default=False)
    is_show_performance = models.BooleanField(default=False)
    


# 组卷中的试题
class Paper_Question(models.Model):
    """
        qtype: 对应试卷题型
        question_id: 对应题目id
        is_delete: 是否删除
    """
    qtype = models.ForeignKey(Paper_Question_Type)
    question_id = models.IntegerField()
    testpaper_id = models.IntegerField()
    is_delete = models.BooleanField(default=False)


# 试题回收
class Question_Recycle(models.Model):
    """
        testpaper: 对应试卷
        paperquestion: 对应删除的试题
        paper_question_type_id: 对应题型id
        delete_time: 删除时间
        is_remove: 是否从回收站移除
    """
    testpaper = models.ForeignKey(Test_Paper)
    paperquestion = models.ForeignKey(Paper_Question)
    paper_question_type_id = models.IntegerField()
    delete_time = models.DateTimeField()
    is_remove = models.BooleanField(default=False)


# 试题存档
class Paper_Save_Log(models.Model):
    """
        user: 对应账户
        logname: 存档名称
        logtime: 存档时间
        testpaper; 对应试卷
    """
    user_id = models.IntegerField()
    logname = models.CharField(max_length=100)
    logtime = models.DateTimeField()
    testpaper = models.ForeignKey(Test_Paper)
    is_delete = models.BooleanField(default=False)


# 下载记录
class Paper_Download_Log(models.Model):
    """
        user: 对应账户
        logname： 对应下载名称
        download_time: 下载时间
        ip: 下载时ip
        testpaper: 对应下载试卷
        word_version: 下载时的word版本
        paper_size: 纸张大小
        paper_type: 试卷类型
    """
    user_id = models.IntegerField()
    logname = models.CharField(max_length=100)
    download_time = models.DateTimeField()
    ip = models.CharField(max_length=45)
    testpaper = models.ForeignKey(Test_Paper)
    word_version = models.PositiveSmallIntegerField()
    paper_size = models.PositiveSmallIntegerField()
    paper_type = models.PositiveSmallIntegerField()
    is_delete = models.BooleanField(default=False)
