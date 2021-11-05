<?php 
if(!defined('InEmpireBak'))
{
	exit();
}
 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>信息提示</title>
<style>body{background:#f9fafd;color:#818181}a {text-decoration: none}.mac_msg_jump{width:90%;max-width:420px;min-height:60px;margin:5% auto 0;border-radius: 2px;min-height: 200px;box-shadow:1px 1px 50px rgba(0,0,0,.3);}.mac_msg_jump .title{margin-bottom:11px}.mac_msg_jump .text{margin-top: 50px;font-size:14px;color:#555;font-weight: normal;}.msg_jump_tit{height: 22px;padding: 10px;line-height: 22px;font-size: 14px;background:#eee;color: #333;text-align: left;border-bottom:1px solid #F0F0F0;}</style>
</head>
在

<body leftmargin="0" topmargin="0">
<center>
<script>
      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='<?php  echo $gotourl ?>'; pgo=1; }
      }
document.write("<br /><div class='mac_msg_jump'><div class='msg_jump_tit'>系统提示</div><div class='text'>");
document.write("<img style='height: 28px;margin-bottom: 8px;'; src='../../pic/i2.png'><br><?php  echo $error ?>");
document.write("<br /><br /><a href='<?php  echo $gotourl ?>'><font style='color:#999;font-size:12px;'>[点击这里手动跳转]</font></a><br/></div></div>");
setTimeout('JumpUrl()',3000);</script>
</center>
</body>