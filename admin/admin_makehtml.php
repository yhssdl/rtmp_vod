<?php 
/*
	[seacms1.0] (C)2011-2012 seacms.net
*/
ob_implicit_flush();
require_once(dirname(__FILE__)."/config.php");
require_once(sea_INC.'/main2.class.php');
set_time_limit(0);

//CheckPurview(); //如需要禁止编辑员生成权限，去掉此注释即可。
if(empty($action))
{
	$action = '';
}

$id = empty($id) ? 0 : intval($id);

if($action=="index")
{
	echoHead();
	echo makeIndex($by);
	
}
elseif($action=="map")
{
	echoHead();
	echo makeAllmovie($by);
	
}
elseif($action=="js")
{
	echoHead();
	makeVideoJs($by);
	
}
elseif($action=="site")
{
	checkRunMode();
	echo "<script language='javascript'>self.location='?action=allcontent&action2=allcontent&action3=".$action3."&by=".$by."';</script>";
}
elseif($action=="newssite")
{
	checkNewsRunMode();
	echo "<script language='javascript'>self.location='?action=allnewscontent&action2=allnewscontent&action3=".$action3."&by=".$by."';</script>";
}
elseif($action=="day")
{
	checkRunMode();
	echoHead();
	makeDay();
}
elseif($action=="newsday")
{
	checkNewsRunMode();
	echoHead();
	makeNewsDay();
	
}
elseif($action=="single")
{
	checkRunMode();
	echoHead();
	echo makeContentById($id);
	
	if(empty($from)){
		echo "<script>"; 
		echo "location.href='$Pirurl'"; 
		echo "</script>"; 		
	}else{
		echo "<script>"; 
		echo "location.href='$from'"; 
		echo "</script>"; 		
	}
	exit();
}
elseif($action=="selected")
{
	checkRunMode();
	if(empty($e_id))
	{
		showMsg('请选择要生成的视频','-1');
	}
	echoHead();
	foreach($e_id as $id)
	{
		echo makeContentById($id);
	}
	if(empty($from)){
		alertMsg("生成完毕！",$Pirurl);
	}else{
		alertMsg("生成完毕！",$from);
	}
	
}
elseif($action=="singleNews")
{
	checkNewsRunMode();
	echoHead();
	makeArticleById($id);
	
	if(empty($from)){
		echo "<script>"; 
		echo "location.href='$Pirurl'"; 
		echo "</script>-->"; 		
	}else{
		echo "<script>"; 
		echo "location.href='$from'"; 
		echo "</script>"; 		
	}
	exit();
}
elseif($action=="selectednews")
{
	checkNewsRunMode();
	if(empty($e_id))
	{
		showMsg('请选择要生成的新闻','-1');
	}
	echoHead();
	foreach($e_id as $id)
	{
		makeArticleById($id);
	}
	if(empty($from)){
		alertMsg("生成完毕！",$Pirurl);
	}else{
		alertMsg("生成完毕！",$from);
	}
	
}
elseif($action=="content")
{
	checkRunMode();
	if(empty($channel))
	{
		ShowMsg("请选择分类！",-1);
		exit();
	}
	echoHead();
	makeContentByChannel($channel,false);
	
}
elseif($action=="newscontent")
{
	checkNewsRunMode();
	if(empty($channel))
	{
		ShowMsg("请选择分类！",-1);
		exit();
	}
	echoHead();
	makeArticleByChannel($channel,false);
	
}
elseif($action=="allcontent")
{
	checkRunMode();
	$makenoncreate=isset($makenoncreate)?$makenoncreate:0;
	$curTypeIndex=$index;
	$typeIdArray = getTypeIdArrayBySort(0);
	$typeIdArrayLen = count($typeIdArray);
	if (empty($curTypeIndex)){
		$curTypeIndex=0;
	}else{
		if(intval($curTypeIndex)>intval($typeIdArrayLen-1)){
			if (empty($action3)){
				alertMsg ("生成全部内容页完成","");
				exit();
			}elseif($action3=="site"){
				echo "<script language='javascript'>self.location='?action=allchannel&action3=".$action3."';</script>";
				exit();
			}
		}
	}
	$typeId = $typeIdArray[$curTypeIndex];
	if(empty($typeId)) {
		exit("分类丢失"); 
	}else {
		echoHead();
		makeContentByChannel($typeId,false,$makenoncreate);
		
	}
}
elseif($action=="allnewscontent")
{
	checkNewsRunMode();
	$curTypeIndex=$index;
	$typeIdArray = getTypeIdArrayBySort(0,1);
	$typeIdArrayLen = count($typeIdArray);
	if (empty($curTypeIndex)){
		$curTypeIndex=0;
	}else{
		if(intval($curTypeIndex)>intval($typeIdArrayLen-1)){
			if (empty($action3)){
				alertMsg ("生成全部内容页完成","");
				exit();
			}elseif($action3=="site"){
				echo "<script language='javascript'>self.location='?action=allpart&action3=".$action3."';</script>";
				exit();
			}
		}
	}
	$typeId = $typeIdArray[$curTypeIndex];
	if(empty($typeId)){
		exit("分类丢失"); 
	}
	else {
		echoHead();
		makeArticleByChannel($typeId,false);
		
	}
}
elseif($action=="daysview")
{
	checkRunMode();
	$ntime = gmmktime(0, 0, 0, gmdate('m'), gmdate('d'), gmdate('Y'));
	$limitday = $ntime - ($days * 24 * 3600);
	$sql = "SELECT v_id FROM sea_data where v_addtime>".$limitday;
	$dsql->SetQuery($sql);
	$dsql->Execute('makedaysview');
	echoHead();
	while($row=$dsql->GetObject('makedaysview'))
	{
		echo makeContentById($row->v_id);
		@ob_flush();
		@flush();
	}
	unset($row);
	
}
elseif($action=="newsdaysview")
{
	checkNewsRunMode();
	$ntime = gmmktime(0, 0, 0, gmdate('m'), gmdate('d'), gmdate('Y'));
	$limitday = $ntime - ($days * 24 * 3600);
	$sql = "SELECT n_id FROM sea_news where n_addtime>".$limitday;
	$dsql->SetQuery($sql);
	$dsql->Execute('makenewsdaysview');
	echoHead();
	while($row=$dsql->GetObject('makenewsdaysview'))
	{
		makeArticleById($row->n_id);
		@ob_flush();
		@flush();
	}
	unset($row);
	
}
elseif($action=="channel")
{
	if($page<=1) $page =1 ;
	checkRunMode();
	if(empty($channel))
	{
		exit("请选择分类！");
	}
	echoHead();
	makeChannelById($channel);
	
}
elseif($action=="newschannel")
{
	if($page<=1) $page =1 ;
	checkNewsRunMode();
	if(empty($channel))
	{
		exit("请选择分类！");
	}
	echoHead();
	makeNewsChannelById($channel);
	
}
elseif($action=="allchannel")
{
	checkRunMode();
	$curTypeIndex=$index;
	$typeIdArray = getTypeIdArrayBySort(0);
	$typeIdArrayLen = count($typeIdArray);
	if (empty($curTypeIndex)){
		$curTypeIndex=0;
	}else{
		if(intval($curTypeIndex)>intval($typeIdArrayLen-1)){
			if (empty($action3)){
				alertMsg ("生成所有栏目全部搞定","");
				exit();
			}elseif($action3=="site"){
				echoHead();
				echo makeIndex();
				echo makeAllmovie();
				
				alertMsg ("一键生成全部搞定","");
				exit();
			}
		}
	}
	$typeId = $typeIdArray[$curTypeIndex];
	if(empty($typeId)){
		exit("分类丢失");
	}else{
		echoHead();
		makeChannelById($typeId);
		
	}
}
elseif($action=="allpart")
{
	checkNewsRunMode();
	$curTypeIndex=$index;
	$typeIdArray = getTypeIdArrayBySort(0,1);
	$typeIdArrayLen = count($typeIdArray);
	if (empty($curTypeIndex)){
		$curTypeIndex=0;
	}else{
		if(intval($curTypeIndex)>intval($typeIdArrayLen-1)){
			if (empty($action3)){
				alertMsg ("生成所有栏目全部搞定","");
				exit();
			}elseif($action3=="site"){
				echoHead();
				echo makeIndex('news');
				echo makeAllmovie('news');
				
				alertMsg ("一键生成全部搞定","");
				exit();
			}
		}
	}
	$typeId = $typeIdArray[$curTypeIndex];
	if(empty($typeId)){
		exit("分类丢失");
	}else{
		echoHead();
		makeNewsChannelById($typeId);
		
	}
}
elseif($action=="lengthchannel")
{
	checkRunMode();
	if(empty($channel))
	{
		exit("请选择分类！");
	}
	if (empty($startpage)){
		exit("请填写起始页");
	}elseif(!is_numeric($startpage)){
		exit("请正确填写起始页");
	}
	if (empty($endpage)){
		exit("请填写结束页");
	}elseif(!is_numeric($endpage)){
		exit("请正确填写结束页");
	}
	$startpage = intval($startpage);
	$endpage = intval($endpage);
	if ($startpage<=0) exit("您输入的页数小于0");
	if ($startpage>$endpage) exit("您输入的结束页小于开始页");
	echoHead();
	makeLengthChannelById($channel,$startpage,$endpage);
	
}
elseif($action=="lengthpart")
{
	checkNewsRunMode();
	if(empty($channel))
	{
		exit("请选择分类！");
	}
	if (empty($startpage)){
		exit("请填写起始页");
	}elseif(!is_numeric($startpage)){
		exit("请正确填写起始页");
	}
	if (empty($endpage)){
		exit("请填写结束页");
	}elseif(!is_numeric($endpage)){
		exit("请正确填写结束页");
	}
	$startpage = intval($startpage);
	$endpage = intval($endpage);
	if ($startpage<=0) exit("您输入的页数小于0");
	if ($startpage>$endpage) exit("您输入的结束页小于开始页");
	echoHead();
	makeLengthPartById($channel,$startpage,$endpage);
	
}
elseif($action=="channelbyids")
{
	checkRunMode();
	echoHead();
	makeChannelByIDS();
	
}
elseif($action=="partbyids")
{
	checkNewsRunMode();
	echoHead();
	makePartByIDS();
	
}
elseif($action=="topic")
{
	checkRunMode();
	if(empty($topic))
	{
		exit("请选择专题!");
	}
	echoHead();
	makeTopicById($topic);
	
}
elseif($action=="alltopic")
{
	checkRunMode();
	echoHead();
	
	$numPerPage=10;
	$page = isset($page) ? intval($page) : 1;
	if($page==0) $page=1;
	
	$csqlStr="select count(*) as dd from sea_topic";
    $row = $dsql->GetOne($csqlStr);
    if(is_array($row)){
        $TotalResult = $row['dd'];
    }else{
        $TotalResult = 0;
    }
    $TotalPage = ceil($TotalResult/$numPerPage);
    
    $limitstart = ($page-1) * $numPerPage;
    if($limitstart<0) $limitstart=0;
	
	if($page > $TotalPage){echo '<br>全部专题生成完毕';exit();}
	else{
		$sql="select id from sea_topic ORDER BY  id DESC limit $limitstart,$numPerPage";
		//die($sql);
		$dsql->SetQuery($sql);
		$dsql->Execute('al');
		//$row=$dsql->GetObject('al');
		$rows=array();
		while($rowr=$dsql->GetObject('al')){$rows[]=$rowr->id;}
		unset($rowr);
		//print_r($rows);
		if(!is_array($rows)) exit("不存在专题");
		for($i=0;$i<count($rows);$i++){
			makeTopicById($rows[$i]);
		}
		$nextpage=$page+1;
		echo "<br>暂停".$cfg_stoptime."秒后继续生成<script language=\"javascript\">setTimeout(\"makeNextPage();\",".$cfg_stoptime."000);function makeNextPage(){location.href='?action=alltopic&page=".$nextpage."';}</script>";
	
	}
	
}
elseif($action=="topicindex")
{
	checkRunMode();
	echoHead();

	makeTopicIndex($page);
	
}
elseif($action=="custom")
{
	$customtemplate = $custom;
	if (empty($customtemplate)) exit("请选择模板");
	echoHead();
	makeCustomInfo($customtemplate);
	

}
elseif($action=="customs")
{
	$customtemplate = $custom;
	if (empty($customtemplate)) exit("请选择模板");
	echoHead();
	for($i=0;$i<count($customtemplate);$i++)
	{
		makeCustomInfo($customtemplate[$i]);
	}
		
}
elseif($action=="allcustom")
{
	$templetdird = $cfg_basedir."templets/".$cfg_df_style."/".$cfg_df_html."/";
	$dh = dir($templetdird);
	echoHead();
	while($filename=$dh->read())
	{
	if(strpos($filename,"elf_")>0) makeCustomInfo($filename);
	}
	

}
elseif($action=="baidu")
{
	echoHead();
	echo makeBaidu();
	
}
elseif($action=="baidun")
{
	echoHead();
	echo makeBaidun();
	
}
elseif($action=="google")
{
	echoHead();
	echo makeGoogle();
	
}
elseif($action=="googlen")
{
	echoHead();
	echo makeGooglen();
	
}
elseif($action=="rss")
{
	echoHead();
	echo makeRss();
	
}
elseif($action=="rssn")
{
	echoHead();
	echo makeRssn();
	
}
elseif($action=="baidux")
{
	echoHead();
	echo makeBaidux();
	
}
elseif($action=="baiduxn")
{
	echoHead();
	echo makeBaiduxn();
	
}
else
{
include(sea_ADMIN.'/templets/admin_makehtml.htm');
exit();
}
?>