<?php 
define('sea_ADMIN', preg_replace("|[/\\\]{1,}|",'/',dirname(__FILE__) ) );
require_once(sea_ADMIN."/../include/common.php");
require_once(dirname(__FILE__)."/admin_vod_update_inc.php");

if(empty($action))
{
	$action = '';
}

if($action=="save")
{
    //echo "视频来源：".$v_id."<br>";
    //echo "课程标题：".$v_name."<br>";
    //echo "预约日期：".$v_sdate."<br>";
    //echo "预约节次：".$v_classtime."<br>";
    //echo "课程分类：".$v_type."<br>";
    //echo "执教教师：".$v_actor."<br>";
    //echo "年级：".$v_publisharea."<br>";
    //echo "班级：".$v_director."<br>";

    if(trim($v_name) == ''){
		ShowMsg("视频标题不能为空！","-1");
		exit();
    }

	if(empty($v_type))
	{
		ShowMsg("请选择视频分类！","-1");
		exit();
	}

    $week = date("w",strtotime("$v_sdate"));
    if($week==0) $week = 7;

    $weeks = "week$week"."s";
    $weeke = "week$week"."e";


    $sqlStr="select $weeks,$weeke from sea_class_time where id = $v_classtime";


    $row = $dsql->GetOne($sqlStr);
    if(!is_array($row)){
		ShowMsg("当前课程节次时间获取失败，请联系管理员！","-1");
		exit();
    }

	$v_stime = $v_sdate." ".$row[$weeks];
	$v_etime = $v_sdate." ".$row[$weeke];



	$ts = strtotime($v_stime);
	$te = strtotime($v_etime);



    $msg = testDateTime($ts,$te,0,$v_id);
	if($msg !== "") {
		ShowMsg($msg,"-1");
		exit();
	}


	$userID = 0;
    $v_publish = 0;

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

	$v_pic = "";
    $insertSql = "insert into sea_subscribe(title,vid,tid,aid,publish,start,v_pic,end,v_publisharea,v_director,user) values ('$v_name','$v_id','$tid','$userID','$v_publish','$v_stime','$v_pic','$v_etime','$v_publisharea','$v_director','$v_actor')";
    if($dsql->ExecuteNoneQuery($insertSql))
    {
        $errmsg = "预约成功，您可以关闭该网页！";
    }
    else
    {
        $errmsg = "预约失败，请联系系统管理员！";
    }
    include(sea_ADMIN.'/templets/vod_msg.htm');
    exit();


}else {

    $errmsg = "当前二维码已经失效，请联系系统管理员！";
    if(strlen($key)<13) {
        include(sea_ADMIN.'/templets/vod_msg.htm');
        exit();
    }

    $row = $dsql->GetOne("SELECT `id`,`title`,`stat` FROM `sea_vod` where id='$id' and uniqid='$key' and pub_rec='1'");
    if(!is_array($row)){
        include(sea_ADMIN.'/templets/vod_msg.htm');
        exit();
    }
    $v_sdate = date("Y-m-d");
	include(sea_ADMIN.'/templets/vod_reserve.htm');
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

function getClassTimeSelect()
{
    global $dsql;
    $sqlStr="select id,title from sea_class_time";
    $dsql->SetQuery($sqlStr);
    $dsql->Execute('time_list');
    $str = "<select name='v_classtime' id='v_classtime' lay-verify='required' lay-search><option value=''>请选择课程节次</option>";
    while($row=$dsql->GetObject('time_list'))
    {
	    $str .= "<option value='".$row->id."'>$row->title</option>";
    }
    $str .= "</select>";
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