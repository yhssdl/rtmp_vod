<?php 
if(!defined('InEmpireBak'))
{
	exit();
}
 ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>下载压缩包</title>
<link rel="stylesheet" type="text/css" href="../layui/css/layui.css" />
<link rel="stylesheet" type="text/css" href="../css/x.css"/>
</head>

<body>
<table class="layui-table" style="min-width:1170px;">
<tr align="center" >
	<td > 下载压缩包【目录： 
        <?php  echo $p ?>
        】</td>
  </tr>
  <tr align="center" > 
    <td> 
      [<a href="<?php  echo $file ?>">下载压缩包</a>]</td>
  </tr>
  <tr align="center" > 
    <td> 
      [<a href="phome.php?f=<?php  echo $f ?>&phome=DelZip" onclick="return confirm('确认要删除？');">删除压缩包</a>]</td>
  </tr>
  <tr align="center" >
    <td>
    （<font color="#FF0000">说明：安全起见，下载完毕请马上删除压缩包．</font>）</td>
  </tr>
</table>
</body>
</html>