<?php 
header('Content-Type:text/html;charset=utf-8');
require_once(dirname(__FILE__)."/config.php");
CheckPurview();
if($action=="set")
{
	$v= $_POST['v'];
	$ip = $_POST['ip'];
	$open=fopen("../data/admin/ip.php","w" );
	$str='<?php  ';
	$str.='$v = "';
	$str.="$v";
	$str.='"; ';
	$str.='$ip = "';
	$str.="$ip";
	$str.='"; ';
	$str.=" ?>";
	fwrite($open,$str);
	fclose($open);
	ShowMsg("成功保存设置!","admin_ip.php?v=$v");
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>后台IP安全设置</title>
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
<form action="admin_ip.php?action=set" method="post"class="layui-form">	
<?php  require_once("../data/admin/ip.php"); ?>
  <div class="layui-form-item">
    <label class="layui-form-label">功能开关：</label>
    <div class="layui-input-block">
      <input type="radio" name="v" value="1" title="开启" <?php  if($v==1) echo 'checked';?>>
      <input type="radio" name="v" value="0" title="关闭" <?php  if($v==0) echo 'checked';?>>
    </div>
  </div>
  
	<div class="layui-form-item">
		<label class="layui-form-label">允许IP：</label>
		<div class="layui-input-block">
			<textarea id="ip" name="ip" class="layui-textarea"><?php  echo $ip;?></textarea>
		</div>
		
	</div> 
	<div class="layui-word-aux">*允许设置多个ip，每行一个<br>开启该功能后，将只允许已登记的ip地址访问后台，如上网ip不固定，请勿开启。<br>如果上网ip变化导致无法登陆后台，请手动修改/data/admin/ip.php文件内容，$v = "0"表示关闭该功能。<br><br><br></div>
	
	
	<div class="layui-form-item">
		<div class="layui-input-block">
		<input type="submit" name="Submit" value="确定提交" lay-submit="" class="layui-btn"/>
		</div>
	</div>  
</form>
</div>
</div>
</div>
</body>
</html>