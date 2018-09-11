<?php
header("content-type:text/html;charset=utf-8");
$conn = mysql_connect("192.168.11.12", "tizi", "tizi");
mysql_select_db("new_zujuan");
mysql_query("SET NAMES utf8");

$t = array();
$query = mysql_query("SELECT * FROM classes_agents_user WHERE create_id is NULL order by id desc");
while ($res = mysql_fetch_assoc($query)){
	$t[] = $res;
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
	<h3>临时添加学校帐号_按老师添加</h3>
	<form action="http://master.tizi.com/crm/crm_agents/t_add" method="POST">
		选择老师:<br/>
		<select name="user_id">
			<?php foreach ($t as $value){?>
			<option value="<?php echo $value["user_id"];?>"><?php echo $value["username"];?></option>
			<?php }?>
		</select>
		<br/><br/>
		
		班级名称:<br/>
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