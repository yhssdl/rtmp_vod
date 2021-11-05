<?php 
require_once(dirname(__FILE__)."/config.php");
CheckPurview();
if(empty($action))
{
	$action = '';
}

if($action=="add")
{
	if(m_ereg("[^0-9a-zA-Z_@&*#$%^()+=~`!\.-]",$pwd) || m_ereg("[^0-9a-zA-Z_@!\.-]",$username) || m_ereg("[^0-9a-zA-Z_@&*#$%^()+=~`!\.-]",$pwd2))
	{
		ShowMsg("密码或用户名不合法，<br />用户名可以用数字与英文字母字符！","-1",0,3000);
		exit();
	}
	if($pwd!=$pwd2)
	{
		ShowMsg("密码和确认密码不一样，请返回修改！","-1",0,3000);
		exit();
	}
	$row = $dsql->GetOne("Select count(*) as dd from `sea_admin` where name like '$username' ");
	if($row['dd']>0)
	{
		ShowMsg('用户名已存在！','-1');
		exit();
	}
	$groupid = $groupid ? intval($groupid) : 3;
	if($groupid<3){
		$publish = 1;
	}else{
		$publish = isset($publish) && is_numeric($publish) ? $publish : 0;
	}

	$mpwd = md5($pwd);
	$pwd = substr(md5($pwd),5,20);
	if(isset($vod_all)){
		$vlist = '0';
	}else{
		$vlist = "";
		$postedvods = $_POST['vods'];
		foreach ($postedvods as $vod){
			$vlist = $vlist . $vod.",";
		}
	}

	$v_type = isset($v_type) && is_numeric($v_type) ? $v_type : 0;
	if($nickname=="") $nickname = $username;
	$vlist = rtrim($vlist,",");
	$inquery = "Insert Into `sea_admin`(password,name,nickname,groupid,vod_list,state,publish,tid) values('$pwd','$username','$nickname',$groupid,'$vlist',1,$publish,$v_type)";
	$dsql->ExecuteNoneQuery($inquery);
	ShowMsg('成功增加一个用户！','admin_manager.php');
	exit();
}
elseif($action=="edit")
{
	include(sea_ADMIN.'/templets/admin_manager_edit.htm');
	exit;
}
elseif($action=="new")
{

	include(sea_ADMIN.'/templets/admin_manager_new.htm');
	exit;
}
elseif($action=="save")
{
	$pwd = trim($pwd);
	$pwd2 = trim($pwd2);
	if(m_ereg("[^0-9a-zA-Z_@&*#$%^()+=~`!\.-]",$pwd) || m_ereg("[^0-9a-zA-Z_@!\.-]",$username) || m_ereg("[^0-9a-zA-Z_@&*#$%^()+=~`!\.-]",$pwd2))
	{
		ShowMsg("密码或用户名不合法，<br />用户名可以用数字与英文字母字符！","-1",0,3000);
		exit();
	}
	if($pwd!=$pwd2)
	{
		ShowMsg("密码和确认密码不一样，请返回修改！","-1",0,3000);
		exit();
	}
	$pwdm = '';
	if($pwd!='')
	{
		$pwdm = ",pwd='".md5($pwd)."'";
		$pwd = ",password='".substr(md5($pwd),5,20)."'";
	}
	$groupid = $groupid ? intval($groupid) : 3;

	if($groupid<3){
		$publish = 1;
	}else{
		$publish = isset($publish) && is_numeric($publish) ? $publish : 0;
	}

	
	if(isset($vod_all)){
		$vlist = '0';
	}else{
		$vlist = "";
		$postedvods = $_POST['vods'];
		foreach ($postedvods as $vod){
			$vlist = $vlist . $vod.",";
		}
	}
	if($nickname=="") $nickname = $username;
	$vlist = rtrim($vlist,",");
	$query = "Update `sea_admin` set name='$username',nickname='$nickname',groupid='$groupid',vod_list='$vlist',state='$state',publish='$publish',tid='$v_type' $pwd where id='$id'";
	$dsql->ExecuteNoneQuery($query);
	ShowMsg("成功更改一个帐户！","admin_manager.php");
	exit();
}
elseif($action=="del")
{
	$rs = $dsql->ExecuteNoneQuery2("delete from `sea_admin` where id='$id' And id<>1 And id<>'".$cuserLogin->getUserID()."'");
	if($rs>0)
	{
		header("Location:admin_manager.php");
	}
	else
	{
		ShowMsg("不能删除id为1的创建人帐号，不能删除自己！","admin_manager.php",0,3000);
	}
	exit();
}
elseif($action=="delall")
{
	if(empty($e_id))
	{
		ShowMsg("请选择需要删除的链接","-1");
		exit();
	}
	$ids = implode(',',$e_id);
	$dsql->ExecuteNoneQuery("delete from `sea_admin` where id in ($ids) And id<>1 And id<>'".$cuserLogin->getUserID()."'");
	header("Location:admin_manager.php");
	exit();
}
else
{
	include(sea_ADMIN.'/templets/admin_manager.htm');
	exit;
}

function getManagerLevel($groupid)
{
	if($groupid==1){
		return "系统管理员";
	}else if($groupid==2){
		return "网站编辑员";
	}else if($groupid==3){
		return "教师";
	}else{
		return "未知类型";
	}
}

function getManagerState($s)
{
	if($s==1){
		return "已激活";
	}else if($s==0){
		return "已锁定";
	}else{
		return "未知";
	}
}
?>