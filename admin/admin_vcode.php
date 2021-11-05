<?php 
header('Content-Type:text/html;charset=utf-8');
require_once(dirname(__FILE__)."/config.php");
CheckPurview();
if($action=="set")
{
	$v2=$_POST['v'];
	$open=fopen("../data/admin/adminvcode.txt","w" );
	fwrite($open,$v2);
	fclose($open);
	ShowMsg("成功保存设置!","admin_vcode.php");
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理登陆验证码</title>
<link rel="stylesheet" type="text/css" href="layui/css/layui.css" />
<link rel="stylesheet" type="text/css" href="css/x.css"/>
<script src="js/common.js" type="text/javascript"></script>
<script src="js/main.js" type="text/javascript"></script>
<script src="layui/layui.js" type="text/javascript"></script>
</head>
<body>
<div class="layui-fluid">
  <div class="r_content pd15">
    <div style="overflow-x: auto">
<form action="admin_vcode.php?action=set" method="post" class="layui-form">	

<?php  $v1=file_get_contents("../data/admin/adminvcode.txt"); ?>
  <div class="layui-form-item">
    <label class="layui-form-label">验证码：</label>
    <div class="layui-input-block">
      <input type="radio" name="v" value="1" title="开启" <?php  if($v1==1) echo 'checked';?>>
      <input type="radio" name="v" value="0" title="关闭" <?php  if($v1==0) echo 'checked';?>>
    </div>
  </div>
	  
  <div class="layui-form-item">
	<div class="layui-input-block">
	<input type="submit" name="Submit" value="确定提交" lay-submit="" class="layui-btn"/>
	</div>
  </div>  

* 如果修改无效，请检查/data/admin/adminvcode.txt文件权限是否可写。

</form>
</div>
</div>
</div>
</body>
</html>