<?php 
if(!defined('InEmpireBak'))
{
	exit();
}
 ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>恢复数据</title>
<link rel="stylesheet" type="text/css" href="../layui/css/layui.css" />
<link rel="stylesheet" type="text/css" href="../css/x.css"/>
<script src="../layui/layui.js"></script>
<script language="JavaScript">
	layui.use('layer', function(){
	var layer = layui.layer;
	});        

//全屏弹出层
var showv=function (url) {
        var index = layer.open({
            type: 2,          //默认弹出层类型
            offset: '100px',
            shade: 0.5,
			      isOutAnim: false,
            title:"选择备份目录",  //弹出层角标名称
            content: url,  //弹出层页面地址
            area: ['90%', '400px'],    //弹出层大小
        });
        
    }
</script>
</head>

<body>


  <form name="ebakredata" method="post" action="phomebak.php" onsubmit="return confirm('确认要恢复？');">
   <input name="phome" type="hidden" id="phome" value="ReData">  
  <table class="layui-table" style="min-width:1170px;">
    <tr bgcolor="#FFFFFF"> 
      <td width="34%" height="25">恢复数据源文件：</td>
      <td width="66%" height="25"> 
        <?php  echo $bakpath ?>
        / 
        <input name="mypath" type="text" id="mypath" size=30 value="<?php  echo $mypath ?>"> <input type="button" class="layui-btn layui-btn-primary layui-btn layui-btn-xs" name="Submit2" value="选择备份" onclick="showv('ChangePath.php?change=1');"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="25" valign="top">要导入的数据库：</td>
      <td height="25"> <select name="add[mydbname]" size="1" id="add[mydbname]" style="width:360px">
          <?php  echo $db ?>
        </select></td>
    </tr>
    <tr bgcolor="#FFFFFF">
      <td height="25">恢复选项：</td>
      <td height="25">每组恢复间隔： 
        <input name="add[waitbaktime]" type="text" id="add[waitbaktime]" value="0" size="2">
        秒</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="25" colspan="2"> <div align="left"> 
          <input type="submit" class="layui-btn" name="Submit" value="开始恢复">
        </div></td>
    </tr>
	</form>
  </table>
</body>
</html>