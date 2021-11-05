<?php 
require_once(dirname(__FILE__)."/config.php");
require_once(sea_DATA."/config.user.inc.php");


if(empty($action))
{
	$action = '';
}


if($action=="add")
{
	CheckPurview();
	//读取视频信息
	$query = "select * from `sea_class_time` ORDER by id DESC";
	
	$vrow = $dsql->GetOne($query);
	if(!is_array($vrow))
	{
		$s1 = "08:00:00";
		$s2 = "08:00:00";
		$s3 = "08:00:00";
		$s4 = "08:00:00";
		$s5 = "08:00:00";
		$s6 = "08:00:00";
		$s7 = "08:00:00";
		
		$e1 = "08:40:00";
		$e2 = "08:40:00";
		$e3 = "08:40:00";
		$e4 = "08:40:00";
		$e5 = "08:40:00";
		$e6 = "08:40:00";
		$e7 = "08:40:00";
	} else {
		$s1 = date("H:i:s",strtotime($vrow['week1e'])+600);
		$s2 = date("H:i:s",strtotime($vrow['week2e'])+600);
		$s3 = date("H:i:s",strtotime($vrow['week3e'])+600);
		$s4 = date("H:i:s",strtotime($vrow['week4e'])+600);
		$s5 = date("H:i:s",strtotime($vrow['week5e'])+600);
		$s6 = date("H:i:s",strtotime($vrow['week6e'])+600);
		$s7 = date("H:i:s",strtotime($vrow['week7e'])+600);
		$e1 = date("H:i:s",strtotime($s1)+2400);
		$e2 = date("H:i:s",strtotime($s2)+2400);
		$e3 = date("H:i:s",strtotime($s3)+2400);
		$e4 = date("H:i:s",strtotime($s4)+2400);
		$e5 = date("H:i:s",strtotime($s5)+2400);
		$e6 = date("H:i:s",strtotime($s6)+2400);
		$e7 = date("H:i:s",strtotime($s7)+2400);
	}
	include(sea_ADMIN.'/templets/admin_cltime_add.htm');
	exit();
}
elseif($action=="edit")
{
	CheckPurview();
	$id = isset($id) && is_numeric($id) ? $id : 0;
	//读取视频信息
	$query = "select * from `sea_class_time` where id='$id' ";
	$vrow = $dsql->GetOne($query);
	if(!is_array($vrow))
	{
		ShowMsg("读取节次信息出错!",$Pirurl);
		exit();
	}

	$id = $vrow['id'];
	$v_title = $vrow['title'];
	$s1 = $vrow['week1s'];
	$s2 = $vrow['week2s'];
	$s3 = $vrow['week3s'];
	$s4 = $vrow['week4s'];
	$s5 = $vrow['week5s'];
	$s6 = $vrow['week6s'];
	$s7 = $vrow['week7s'];
	$e1 = $vrow['week1e'];
	$e2 = $vrow['week2e'];
	$e3 = $vrow['week3e'];
	$e4 = $vrow['week4e'];
	$e5 = $vrow['week5e'];
	$e6 = $vrow['week6e'];
	$e7 = $vrow['week7e'];

	if( $s1== $s2 && $s1== $s3 && $s1== $s4 && $s1== $s5 && $s1== $s6 && $s1== $s7 &&
		$e1== $e2 && $e1== $e3 && $e1== $e4 && $e1== $e5 && $e1== $e6 && $e1== $e7){
		$is_same = true;
	}else{
		$is_same = false;
	}

	include(sea_ADMIN.'/templets/admin_cltime_edit.htm');
	exit();
}
elseif($action=="save")
{
	CheckPurview();
	if(trim($v_title) == '')
	{
		ShowMsg("标题不能为空！",$Pirurl);
		exit();
	}

	$id = isset($id) && is_numeric($id) ? $id : 0;
	$arr = splittime($week1,$Pirurl);
	$s1 = trim($arr[0]);
	$e1 = trim($arr[1]);

	$same_time = isset($same_time) && is_numeric($same_time) ? $same_time : 0;

	if($same_time){
		$s2 = $s3 = $s4 = $s5 = $s6 = $s7 = $s1;
		$e2 = $e3 = $e4 = $e5 = $e6 = $e7 = $e1;
	} else{
		$arr = splittime($week2,$Pirurl);
		$s2 = trim($arr[0]);
		$e2 = trim($arr[1]);

		$arr = splittime($week3,$Pirurl);
		$s3 = trim($arr[0]);
		$e3 = trim($arr[1]);

		$arr = splittime($week4,$Pirurl);
		$s4 = trim($arr[0]);
		$e4 = trim($arr[1]);

		$arr = splittime($week5,$Pirurl);
		$s5 = trim($arr[0]);
		$e5 = trim($arr[1]);

		$arr = splittime($week6,$Pirurl);
		$s6 = trim($arr[0]);
		$e6 = trim($arr[1]);

		$arr = splittime($week7,$Pirurl);
		$s7 = trim($arr[0]);
		$e7 = trim($arr[1]);
	}

	switch (trim($acttype)) 
	{
		case "add":
			$insertSql = "INSERT INTO `sea_class_time` (`title`, `week1s`, `week2s`, `week3s`, `week4s`, `week5s`, `week6s`, `week7s`, `week1e`, `week2e`, `week3e`, `week4e`, `week5e`, `week6e`, `week7e`) VALUES ( '$v_title', '$s1', '$s2', '$s3', '$s4', '$s5', '$s6', '$s7', '$e1', '$e2', '$e3', '$e4', '$e5', '$e6', '$e7');";
			if($dsql->ExecuteNoneQuery($insertSql))
			{
				ShowMsg("添加节次成功","admin_cltime.php?action=list");	
			}
			break;

		case "edit":
			$updateSql = "UPDATE `sea_class_time` SET `title` = '$v_title', `week1s` = '$s1', `week2s` = '$s2', `week3s` = '$s3', `week4s` = '$s4', `week5s` = '$s5', `week6s` = '$s6', `week7s` = '$s7', `week1e` = '$e1', `week2e` = '$e2', `week3e` = '$e3', `week4e` = '$e4', `week5e` = '$e5', `week6e` = '$e6', `week7e` = '$e7' WHERE `id` = $id;";
			if(!$dsql->ExecuteNoneQuery($updateSql))
			{
				ShowMsg('更新预约出错，请检查',$Pirurl);
				exit();
			}	

			ShowMsg("修改节次成功","admin_cltime.php?action=list");	
			break;

		}




	
	exit();
}
elseif($action=="del")
{
	CheckPurview();
	$back=$Pirurl;
	$id = isset($id) && is_numeric($id) ? $id : 0;
	$dsql->ExecuteNoneQuery("delete From `sea_class_time` where id='$id'");

	ShowMsg("节次删除成功",$back);
	exit();
}
else
{
	include(sea_ADMIN.'/templets/admin_cltime_list.htm');
	exit();
}

function splittime($week,$backurl){
	$week = trim($week);
	if($week == '')
	{
		ShowMsg("时间不能为空！",$backurl);
		exit();
	}

	$arr = explode("-",$week);
	if(count($arr)!=2){
		ShowMsg("时间格式有错误！",$backurl);
		exit();	
	}
	if($arr[0]==$arr[1]){
		ShowMsg("开始时间与结束时间不能相同！",$backurl);
		exit();		
	}
	return $arr;
}

?>