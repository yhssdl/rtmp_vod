<?php 
if(!defined('InEmpireBak'))
{
	exit();
}
$onclickword='(点击转向恢复数据)';
$change=(int)$_GET['change'];
if($change==1)
{
	$onclickword='(点击选择)';
}
 ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>管理备份目录</title>
<link rel="stylesheet" type="text/css" href="../layui/css/layui.css" />
<link rel="stylesheet" type="text/css" href="../css/x.css"/>
<script>
function ChangePath(pathname)
{
  var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.document.getElementById('mypath').value = pathname;
    parent.layer.close(index);
}
</script>
</head>

<body>

<form name="ebakredata" method="post" action="phomebak.php" onsubmit="return confirm('确认要恢复？');">
<input name="phome" type="hidden" id="phome" value="ReData">  

<table class="layui-table" style="min-width:1170px;">
  <tr class="header" style="background-color: #F6F6F6;" > 
    <td width="42%"> <div align="center">备份目录名
        <?php  echo $onclickword ?>
    </div></td>
    <td width="16%"> <div align="center">查看说明文件</div></td>
    <td width="42%"><div align="center">操作</div></td>
  </tr>
  <?php 
  while($file=@readdir($hand))
  {
	if($file!="."&&$file!=".."&&is_dir($bakpath."/".$file))
	{
		if($change==1)
		{
			$showfile="<a href='#ebak' onclick=\"javascript:ChangePath('$file');\" title='$file'>$file</a>";
		}
		else
		{
			$showfile="<a href='phome.php?phome=PathGotoRedata&mypath=$file' title='$file'>$file</a>";
		}
   ?>
  <tr> 
    <td> <div align="left"><img src="images/dir.gif" width="19" height="15">&nbsp; 
        <?php  echo $showfile ?> </div></td>
    <td> <div align="center"> [<a href="<?php  echo $bakpath."/".$file."/readme.txt" ?>" target=_blank>查看备份说明</a>]</div></td>
    <td><div align="center">[<a href="phome.php?phome=DoZip&p=<?php  echo $file ?>&change=<?php  echo $change ?>")>打包并下载</a>]&nbsp;&nbsp;[<a href="phome.php?phome=DelBakpath&path=<?php  echo $file ?>&change=<?php  echo $change ?>" onclick="return confirm('确认要删除？');">删除备份</a>]</div></td>
  </tr>
  <?php 
     }
  }
   ?>
  <tr> 
    <td colspan="3"><font color="#666666">(说明：如果备份目录文件较多建议直接从FTP下载备份,备份文件存储位置：后台目录/ebak/bdata)</font></td>
  </tr>
</table>
</body>
</html>