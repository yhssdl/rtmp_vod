<?php 
require_once(dirname(__FILE__)."/config.php");
require_once(sea_DATA."/config.user.inc.php");
require_once(dirname(__FILE__)."/admin_vod_update_inc.php");


if(empty($action))
{
	$action = '';
}

if($action=="add")
{
	if(isset($_GET['vid']))
		$v_id = $_GET['vid'];
	if(empty($v_id))
	{
		$v_id = 0;
	}

	if(isset($_GET['id']))
	$id = $_GET['id'];
	if(empty($id))
	{
		$id = 0;
	}

	if(isset($_GET['sd'])){
		$v_sdate = $_GET['sd'];
	}else{
		$v_sdate = date("Y-m-d");	
	}

	if(isset($_GET['ed'])){
		$v_edate = $_GET['ed'];
	} else {
		$v_edate = $v_sdate;		
	}

	if(isset($_GET['st'])){
		$v_stime = $_GET['st'];
	} else {
		$v_stime = date("H:i:00",strtotime(date("Y-m-d H:i:s")." + 1 minutes"));
	}

	if(isset($_GET['et'])){
		$v_etime = $_GET['et'];
	} else {
		$v_etime = date("H:i:00",strtotime(date("Y-m-d H:i:s")." + 41 minutes"));
	}

	include(sea_ADMIN.'/templets/admin_live.htm');
	exit();
}
elseif($action=="edit")
{
	$id = isset($id) && is_numeric($id) ? $id : 0;
	//读取视频信息
	$query = "select d.* from `sea_subscribe` d where d.id='$id' ";
	$vrow = $dsql->GetOne($query);
	if(!is_array($vrow))
	{
		ShowMsg("读取预约信息出错!","-1");
		exit();
	}

	$groupid = $cuserLogin->getgroupid();
	$userid = $cuserLogin->getUserID();
	
	if($groupid==3 && $vrow['aid']!= $userid){
		ShowMsg("对不起，你没有权限编辑该项目！<br/><br/><a href='javascript:history.go(-1);'>点击此返回上一页&gt;&gt;</a>",'javascript:;');
		exit();
	}

	$id = $vrow['id'];
	$v_name = $vrow['title'];
	$v_id = $vrow['vid'];
	$tid = $vrow['tid'];
	$v_pic = $vrow['v_pic'];
	$v_publish =  $vrow['publish'];
	if(isset($_GET['publish'])){
		$v_publish = $_GET['publish'];
	}

	$v_sdate = date("Y-m-d", strtotime($vrow['start']));
	$v_edate = date("Y-m-d", strtotime($vrow['end']));

	$v_stime = date("H:i:s",strtotime($vrow['start']));
	$v_etime = date("H:i:s",strtotime($vrow['end']));


	$v_publisharea = $vrow['v_publisharea'];
	$v_director = $vrow['v_director'];
	$v_actor = $vrow['user'];
	$stat = $vrow['stat'];

	include(sea_ADMIN.'/templets/admin_live_edit.htm');
	exit();
}
elseif($action=="save")
{
	if(trim($v_name) == '')
	{
		ShowMsg("视频标题不能为空！","-1");
		exit();
	}
	if(empty($v_type))
	{
		ShowMsg("请选择视频分类！","-1");
		exit();
	}

	$id = isset($id) && is_numeric($id) ? $id : 0;

	$v_stime = $v_sdate." ".$v_stime;
	$v_etime = $v_edate." ".$v_etime;

	$ts = strtotime($v_stime);
	$te = strtotime($v_etime);

	$msg = testDateTime($ts,$te,$id,$v_id);
	if($msg !== "") {
		ShowMsg($msg,"-1");
		exit();
	}

	$userID = $cuserLogin->getUserID();
    $groupid = $cuserLogin->getgroupid();
	$publish =  $cuserLogin->getpublish();

	$v_publish = isset($v_publish) && is_numeric($v_publish) ? $v_publish : 0;
	if($groupid==3 && $publish==0) $v_publish = 0;

	$tid = empty($v_type) ? 0 : intval($v_type);
	$v_name = htmlspecialchars(cn_substrR($v_name,250));
	$v_name = str_replace(array('\\','()','\''),'/',$v_name);
	$v_actor = htmlspecialchars(cn_substrR($v_actor,200));
	$v_actor = str_replace('%', ' ', $v_actor);
	if($v_actor=="" OR empty($v_actor)){$v_actor="佚名";}
	$v_publisharea = cn_substrR($v_publisharea,20);
	$v_director = htmlspecialchars(cn_substrR($v_director,200));
	$v_director = str_replace('%', ' ', $v_director);
	if($v_director=="" OR empty($v_director)){$v_director="";}

	
	$v_pic = cn_substrR($v_pic,255);

	switch (trim($acttype)) 
	{
		case "add":
			$insertSql = "insert into sea_subscribe(title,vid,tid,aid,publish,start,v_pic,end,v_publisharea,v_director,user) values ('$v_name','$v_id','$tid','$userID','$v_publish','$v_stime','$v_pic','$v_etime','$v_publisharea','$v_director','$v_actor')";
			if($dsql->ExecuteNoneQuery($insertSql))
			{
				if($id==-1)
					ShowMsg('添加预约成功。','admin_cltime.php?action=list');
				else
					selectMsg("添加成功,是否继续添加","admin_live.php?action=add","admin_live.php?action=list");
				
			}
			else
			{
				$gerr = $dsql->GetError();
				ShowMsg("把数据保存到数据库主表 `sea_subscribe` 时出错。".str_replace('"','',$gerr),"javascript:;");
				exit();
			}
		break;
		case "edit":
			$updateSql = "update sea_subscribe set title = '$v_name',vid = '$v_id',tid = '$tid',publish = '$v_publish',start = '$v_stime',v_pic = '$v_pic',end = '$v_etime',v_publisharea = '$v_publisharea',v_director = '$v_director',user = '$v_actor' where id=$id";
			if(!$dsql->ExecuteNoneQuery($updateSql))
			{
				ShowMsg('更新预约出错，请检查',-1);
				exit();
			}

			ShowMsg("预约更新成功","admin_live.php?action=list");
			exit();
			
			break;
	}
}
elseif($action=="del")
{
	$back=$Pirurl;
	$id = isset($id) && is_numeric($id) ? $id : 0;
	$userid = $cuserLogin->getUserID();
	$groupid = $cuserLogin->getgroupid();
	if($groupid==3)
		$dsql->ExecuteNoneQuery("delete From `sea_subscribe` where aid='$userid' and id='$id'");
	else
		$dsql->ExecuteNoneQuery("delete From `sea_subscribe` where id='$id'");
	ShowMsg("预约删除成功",$back);
	exit();
}
elseif($action=="delall")
{
	$back=$Pirurl;
	if(empty($e_id))
	{
		ShowMsg("请选择需要删除的预约","-1");
		exit();
	}
	$ids = implode(',',$e_id);
	$groupid = $cuserLogin->getgroupid();
	$userid = $cuserLogin->getUserID();
	if($groupid==3)
		$dsql->ExecuteNoneQuery("delete From `sea_subscribe` where aid='$userid' and id in(".$ids.")");
	else
		$dsql->ExecuteNoneQuery("delete From `sea_subscribe` where id in(".$ids.")");
	ShowMsg("预约删除成功",$back);
	exit();
}
else
{
	if(empty($type)) $type = "";
	if(empty($area)) $area = "";
	if(empty($keyword)) $keyword = "";
	require_once(sea_DATA."/config.ftp.php");
	include(sea_ADMIN.'/templets/admin_live_main.htm');
	exit();
}

function getAreaSelect($selectName,$strSelect,$areaId)
{
	$publishareatxt=sea_DATA."/admin/publisharea.txt";
	$publisharea = array();
	if(filesize($publishareatxt)>0)
	{
		$publisharea = file($publishareatxt);
	}
	$str = '<select name="v_publisharea" id="v_publisharea">';
	if(!empty($strSelect)) $str .= "<option value=''>".$strSelect."</option>";
	foreach($publisharea as &$area)
	{
		$area=str_replace("\r\n","",$area);
		if(!empty($areaId) && ($area==$areaId)) $str .= "<option value='".$area."' selected>$area</option>";
		else $str .= "<option value='".$area."'>$area</option>";
	}
	if(!in_array($areaId,$publisharea)&&!empty($areaId))
	$str .= "<option value='".$areaId."' selected>$areaId</option>";
	$str .= '</select>';
	return $str;
}


function zzget($txt)
{
	if($txt=='area'){$txt=sea_DATA."/admin/publisharea.txt";}
	elseif($txt=='year'){$txt=sea_DATA."/admin/publishyear.txt";}
	elseif($txt=='yuyan'){$txt=sea_DATA."/admin/publishyuyan.txt";}
	elseif($txt=='ver'){$txt=sea_DATA."/admin/verlist.txt";}
	else{return '<option value=0>无内容</option>';exit();}
	$cc = array();
	if(filesize($txt)>2)
	{
		$cc = file($txt);
	}
	if($txt=='year'){
		$sum = count($cc);
		if($sum<1){
			$year = date('Y');
			$cc = array($year,$year-1,$year-2,$year-3,$year-4,$year-5,$year-6,$year-7,$year-8,$year-9);
		}
	}
	$str = "";
	if(!empty($strSelect)) $str .= "<option value=''>".$strSelect."</option>";
	foreach($cc as &$cc)
	{
		$cc=str_replace("\r\n","",$cc);
		$str .= "<option value='".$cc."'>$cc</option>";
	}
	return $str;
}

function testDateTime($ts,$te,$id,$v_id){
	global $dsql;
	if($te<=$ts){

		return "预约的结束时间必须晚于开始时间。";
	}

	$csqlStr = "SELECT id,title,user  FROM sea_subscribe WHERE stat<2 AND $ts >= unix_timestamp(start) and $ts < unix_timestamp(end) AND id<>$id AND vid = $v_id LIMIT 1";
	$dsql->SetQuery($csqlStr);
	$dsql->Execute('vod_list');
	$row = $dsql->GetOne($csqlStr);
	while($row=$dsql->GetObject('vod_list')){
		if($row->user!="")
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title<br><br>执教：$row->user";
		else
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title";
    }

	$csqlStr = "SELECT id,title,user  FROM sea_subscribe WHERE stat<2 AND $te > unix_timestamp(start) and $te <= unix_timestamp(end) AND id<>$id AND vid = $v_id LIMIT 1";
	$dsql->SetQuery($csqlStr);
	$dsql->Execute('vod_list');
	$row = $dsql->GetOne($csqlStr);
	while($row=$dsql->GetObject('vod_list')){
		if($row->user!="")
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title<br><br>执教：$row->user";
		else
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title";
    }

	$csqlStr = "SELECT id,title,user  FROM sea_subscribe WHERE stat<2 AND unix_timestamp(start) >= $ts  and unix_timestamp(start) < $te AND id<>$id AND vid = $v_id LIMIT 1";
	$row = $dsql->GetOne($csqlStr);
	$dsql->SetQuery($csqlStr);
	$dsql->Execute('vod_list');
	$row = $dsql->GetOne($csqlStr);
	while($row=$dsql->GetObject('vod_list')){
		if($row->user!="")
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title<br><br>执教：$row->user";
		else
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title";
    }
	
	$csqlStr = "SELECT id,title,user  FROM sea_subscribe WHERE stat<2 AND unix_timestamp(end) > $ts  and unix_timestamp(end) <= $te AND id<>$id AND vid = $v_id LIMIT 1";
	$row = $dsql->GetOne($csqlStr);
	$dsql->SetQuery($csqlStr);
	$dsql->Execute('vod_list');
	$row = $dsql->GetOne($csqlStr);
	while($row=$dsql->GetObject('vod_list')){
		if($row->user!="")
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title<br><br>执教：$row->user";
		else
			return "当前时间段已经有预约存在。<br><br>ID号：$row->id<br><br>标题：$row->title";
    }

	return "";
}


?>