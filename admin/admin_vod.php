<?php 

require_once(dirname(__FILE__)."/config.php");
require_once(sea_DATA."/config.user.inc.php");
require_once(dirname(__FILE__)."/admin_vod_update_inc.php");

if(empty($action))
{
	$action = '';
}

if($action=="save")
{
	$id = $_POST["v_id"];
	$id = isset($id) && is_numeric($id) ? $id : 0;
	$page = $_GET['page'];
	$v_name = $_POST["v_name"];
	$playid = $_POST["playid"];
	$updateSql = "UPDATE `seacms`.`sea_vod` SET `title` = '$v_name' WHERE `sea_vod`.`id` = $id";
	$dsql->SetQuery($updateSql);

	if(!$dsql->ExecuteNoneQuery($updateSql))
	{
		ShowMsg('更新直播频道标题出错，请检查',-1);
		exit();
	}
	include(sea_ADMIN.'/templets/admin_vod.htm');
	exit();
}else if($action=="cut"){
	$id = isset($id) && is_numeric($id) ? $id : 0;
	include(sea_ADMIN.'/templets/admin_vod_cut.htm');
	exit();
}else if($action=="list"){
	include(sea_ADMIN.'/templets/admin_vod_list.htm');
	exit();
}
elseif($action=="del"){
	$back=$Pirurl;
	$id = isset($id) && is_numeric($id) ? $id : 0;
	if($dsql->ExecuteNoneQuery("delete From `sea_vod` where id='$id'")){
		ShowMsg("直播频道删除成功",$back);
		exit();
	}else{
		ShowMsg("直播频道删除失败",$back);
		exit();	
	}
}
elseif($action=="delall"){
	$back=$Pirurl;
	if(empty($e_id))
	{
		ShowMsg("请选择需要删除的频道","-1");
		exit();
	}
	$ids = implode(',',$e_id);
	if($dsql->ExecuteNoneQuery("delete From `sea_vod` where id in(".$ids.")")){
		ShowMsg("直播频道批量删除成功",$back);
		exit();
	}else{
		ShowMsg("直播频道批量删除失败",$back);
		exit();
	}
}
else{
	if(isset($_GET['playid'])){
		$playid = $_GET['playid'];
	}else {
		$playid = 0;
	}
	if(empty($_GET['page']))
	{
		$page = 1;
	}else{
		$page = $_GET['page'];
	}
	include(sea_ADMIN.'/templets/admin_vod.htm');
	exit();
}

?>