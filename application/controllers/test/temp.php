<?php
header("content-type:text/html;charset=utf-8");
$conn = mysql_connect("192.168.11.12", "tizi", "tizi");
mysql_select_db("new_zujuan");
mysql_query("SET NAMES utf8");

$subject = array();
$query = mysql_query("SELECT * FROM subject_type");
while ($res = mysql_fetch_assoc($query)){
	$subject[] = $res;
}

$grade = array();
$query = mysql_query("SELECT * FROM grade");
while ($res = mysql_fetch_assoc($query)){
	$grade[] = $res;
}
?>
<html>
<head></head>
<body>
	<h3>临时添加学校帐号</h3>
	<form action="http://master.tizi.com/crm/crm_agents/build" method="POST">
		选择学校:<br/>
		<select name="school_id">
			<option value="201365">东方红小学</option>
		</select>
		<br/><br/>
		
		老师姓名&科目:<br/>
		<input type="text" name="t_name" />
		<select name="subject_id">
			<?php foreach ($subject as $value){?>
			<option value="<?php echo $value["id"];?>"<?php if ($value["id"] == 3){echo " selected=\"selected\"";}?>><?php echo $value["name"];?></option>
			<?php }?>
		</select><br/><br/>
		
		班级名称&年级:<br/>
		<input type="text" name="classname" />
		<select name="class_grade">
			<?php foreach ($grade as $value){?>
			<option value="<?php echo $value["id"];?>"><?php echo $value["name"];?></option>
			<?php }?>
		</select><br/><br/>
		
		学生姓名，一行一个:<br/>
		<textarea name="s_name" style="height:400px;"></textarea>
		<br/><br/><input type="submit" value="提交"/>
	</form>
</body>
</html>