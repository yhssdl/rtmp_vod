<?php 
require_once(dirname(__FILE__)."/config.php");
require_once(sea_INC."/charset.func.php");
//require_once(sea_DATA."/config.user.inc.php");
AjaxHead();
if(empty($action))
{
	$action = '';
}
$id = empty($id) ? 0 : intval($id);

if($action=="gettopicdes")
{
	$query="select des from `sea_topic` where id='$id'";
	$row=$dsql->GetOne($query);
	if(!$row)
	{
		echo "err";
	}else{
		echo htmlspecialchars(stripslashes(decodeHtml($row['des'])));
	}
}
elseif($action=="submittopicdes")
{
	$f_id = empty($f_id) ? 0 : intval($f_id);
	$f_des=gbutf8(UnicodeUrl2Gbk(TrimMsg($f_des)));
	$upquery="update `sea_topic` set des='$f_des' where id='$f_id'";
	if(!$dsql->ExecuteNoneQuery($upquery))
	{
		echo "err";
	}else{
		echo "ok";
	}
}
elseif($action=="submitstate")
{
	$upquery="update `sea_data` set v_state='$state' where v_id='$id'";
	if(!$dsql->ExecuteNoneQuery($upquery)){
		echo "err";
	}else{
		echo "submitok";
	}
}
elseif($action=="select")
{
	echo makeTopicSelect("topicselect","...请选择专题...",$topicid);
	echo "<input type=\"button\" value=\"确定\" onclick='submitVideoTopic($id)' /> <input type=\"button\" value=\"取消\" onclick='closeWin()' />";
}
elseif($action=="submittopic")
{
	$upquery="update `sea_data` set v_topic='$topic' where v_id='$id'";
	if(!$dsql->ExecuteNoneQuery($upquery)){
		echo "err";
	}else{
		echo "submitok";
	}
}
elseif($action=='commend'){
	if($type)
	$upquery="update `sea_news` set n_commend='$commendid' where n_id='$id'";
	else
	$upquery="update `sea_data` set v_commend='$commendid' where v_id='$id'";
	if(!$dsql->ExecuteNoneQuery($upquery))
	{
		echo "err";
	}else{
		echo "submitok";
	}
}
else if($action=='cut'){
	set_time_limit(300);

	if($mode==1){
		$fullname =  getFullPath($filename);
		$newname = $fullname.".tmp";
		if(!rename($fullname,$newname)){
			echo "视频文件可能被锁定，暂时无法修改。";
			die;
		}
	
		$command = "ffmpeg -ss $stime -accurate_seek -i $newname -to $etime -codec copy -copyts -avoid_negative_ts 1 $fullname";
		exec($command);
	
		if(file_exists($fullname)){
			unlink($newname);
			echo "视频文件已经剪切成功。";
			clearstatcache();
	
		}else{
			rename($newname,$fullname);
			echo "视频文件剪切失败。";
		}
	}else{
		$csqlStr = "SELECT pid FROM sea_subscribe WHERE id='$id' LIMIT 1";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('s_list');
		while($row=$dsql->GetObject('s_list'))
		{

			$fullname =  getFullPath($filename);
			$jpgfile = substr($fullname, 0, strrpos($fullname, ".")).".jpg";

			$command = "ffmpeg -ss $stime -t 0.001 -loglevel quiet -i $fullname  -f image2 -y $jpgfile";
			exec($command);
			if(file_exists($jpgfile)){
				$jpgfile = substr($filename, 0, strrpos($filename, ".")).".jpg";
				$updateSql = "UPDATE sea_subscribe SET v_pic = '$jpgfile' WHERE id = $id";
				$dsql->ExecuteNoneQuery($updateSql);
				if($row->pid>0){
					$updateSql = "UPDATE sea_data SET v_pic = '$jpgfile' WHERE v_id = $row->pid";
					$dsql->ExecuteNoneQuery($updateSql);
				}
				echo $jpgfile;
				exit();
			}
		}


		echo "截图失败。";
	}


}
else if($action=='cut_pic'){
	$fullname =  getFullPath($filename);
	if(image_resize($fullname,$x,$y,$width,$height)){
		echo "图片裁剪成功。";
	}
	exit();
}
else if($action=='nickname'){

	$userid = $cuserLogin->getUserID();
	$oldname = $cuserLogin->getNickName();
	if($oldname==$nickname){
		echo "新的昵称不能与旧的相同";
	}else{
		$upquery="update `sea_admin` set nickname='$nickname' where id='$userid'";
		if(!$dsql->ExecuteNoneQuery($upquery))
		{
			echo "err";
		}else{
			$cuserLogin->setNickName($nickname);
			echo "昵称修改成功。";
		}
	}


}
else if($action=='repassword'){
	$userid = $cuserLogin->getUserID();
	$dsql->SetQuery("Select password From `sea_admin` where id='$userid' and state='1'");
	$dsql->Execute();
	$row = $dsql->GetObject();	
	$pwd = substr(md5($old_pass),5,20);
	if(!isset($row->password))
	{
		echo "修改密码错误。";
		exit();
	}
	else if($pwd!=$row->password)
	{
		echo "旧密码错误，请重新尝试。";
		exit();
	}
	$pwd = substr(md5($new_pass),5,20);
	$upquery="update `sea_admin` set password='$pwd' where id='$userid'";
	if(!$dsql->ExecuteNoneQuery($upquery))
	{
		echo "数据库写入错误。";
		exit();
	}
	echo "密码修改成功。";

}
elseif($action=='flv2mp4'){
	$dsql->SetQuery("SELECT sea_subscribe.id,sea_subscribe.tid,sea_subscribe.start,sea_subscribe.title,sea_subscribe.user,sea_subscribe.file_name,sea_type.tname FROM sea_subscribe  LEFT JOIN sea_type ON sea_subscribe.tid = sea_type.tid WHERE id=$sid");
	$dsql->Execute();
	$row = $dsql->GetObject();
	if($row){
		
		$fullname =  getFullPath($row->file_name);
		if($cfg_movevodfile){
			$path = dirname($fullname)."/".$row->tname;
			mkdirs($path);
			$str_user = str_replace([' ',':','?','"','<','>','|','\\','/','*','$','&'],'_',$row->user);
			$str_title = str_replace([' ',':','?','"','<','>','|','\\','/','*','$','&'],'_',$row->title);
			$mp4file = $path."/".date("YmdHi",strtotime($row->start))."_".$str_user."_".$str_title.".mp4";
		}else{
			$mp4file = substr($fullname,0,strrpos($fullname, '.')).".mp4";
		}
		if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
			$ffmpeg = str_replace('\\','/',realpath(dirname(__FILE__)))."/ffmpeg";
		}else{
			$ffmpeg = "ffmpeg";
		}

		$command = "$ffmpeg -loglevel quiet -i $fullname -vcodec copy -acodec copy $mp4file";
		exec($command) ;
		if(file_exists($mp4file)){
			$flvsize = filesize($flvfile);
			$mp4size = filesize($mp4file);
			if($mp4size*2>$flvsize){
				$outfile = $mp4file;
				
				$outfile = getRelativePath($outfile);

				$updateSql = "UPDATE sea_subscribe SET file_name = '$outfile' WHERE id = $sid";
				$dsql->ExecuteNoneQuery($updateSql);

				$v_playdata = 'Xgplayer$$第1集$'.$outfile.'$xg';
				$updateSql = "UPDATE sea_playdata SET body = '$v_playdata' WHERE v_id = $pid";
				if($dsql->ExecuteNoneQuery($updateSql))
				{
					unlink($fullname);
				}
				exit("转码成功。");
			}
		}	
	}
	echo "转码出现错误。";
}
elseif($action=='live_main'){
	$sqlStr="select id, stat ,tid,pid,publish from sea_subscribe where id in ($idgroup)  ORDER BY FIND_IN_SET( id, '$idgroup')";
	$dsql->SetQuery($sqlStr);
	$dsql->Execute('vod_list');
	$stats = explode(",",$stats);
	$i =0;
	$msg="";
	$groupid = $cuserLogin->getgroupid();
	while($row=$dsql->GetObject('vod_list'))
	{		
		
		$text = "";
		switch($row->stat){
			case 0:
				$text = "<font color='green'>预约</font>";
				break;
			case 1:
				$text = "<font color='red'>正在录制</font>";
				break;
			case 2:
				if($row->tid==0)
					$text = "<a href='?action=edit&id=$row->id&publish=1'><font color='fuchsia'>等待分类</font></a>";
				else
					$text = "<font color='ORANGE'>等待转码</font>";
				break;

			case 3:
				$text = "<font color='ORANGE'>正在转码</font>";
				break;
					
			case 4:
				if($groupid ==3)
					$text = "<font color='BLUE'>录制完毕</font>";
				else
				{
					if($row->publish==1)
						$text = "<font color='#FF00FF'>等待发布</font>";
					else
						$text = "<a href='?action=edit&id=$row->id&publish=1'><font color='fuchsia'>点击发布</font></a>";
				}
					
				break;
			case 5:
				$cc=$dsql->GetOne("select  v_addtime,v_enname from  `sea_data`  where v_id = $row->pid");
				$contentUrl=getContentLink($row->tid,$row->pid,"",date('Y-n',$cc['v_addtime']),$cc['v_enname']);
				$text = "<a href='$contentUrl' title='查看' target='_blank'><font color='BLUE'>发布完毕</font></a>";
				break;		
			case 6:
				$text = "<font color='GRAY'>录制错误</font>";
				break;																			
		}

		$msg = $msg . $row->id .",".$stats[$i].",".$row->stat.",".$row->pid.",".$text.";";
		$i++;
	}
	echo $msg;
}
elseif($action=='get_stat'){
	require_once(dirname(__FILE__)."/admin_vod_update_inc.php");
	$groupid = $cuserLogin->getgroupid();
	$userid = $cuserLogin->getUserID();
	$vodlist = $cuserLogin->getUserVodList();
	if($groupid==3  && $vodlist!='0'){
		$yy_dsh = $dsql->GetOne("select count(*) as dd from sea_subscribe where aid='$userid' and stat=0");	
		$vod_dsh = $dsql->GetOne("select count(*) as dd from sea_vod where id in($vodlist) and stat>0");
	}else if($groupid==3){
		$yy_dsh = $dsql->GetOne("select count(*) as dd from sea_subscribe where aid='$userid' and stat=0");	
		$vod_dsh = $dsql->GetOne("select count(*) as dd from sea_vod where stat>0");
	}
	else{
		$yy_dsh = $dsql->GetOne("select count(*) as dd from sea_subscribe where stat=0");	
		$vod_dsh = $dsql->GetOne("select count(*) as dd from sea_vod where stat>0");	
	}

	$yy = $yy_dsh['dd'];

	$vod = $vod_dsh['dd'];
	echo $yy.";".$vod;
}
elseif($action=='vod_list'){
	require_once(dirname(__FILE__)."/admin_vod_update_inc.php");
	$sqlStr="select id, stat  from sea_vod where id in ($idgroup)  ORDER BY FIND_IN_SET( id, '$idgroup')";
	$dsql->SetQuery($sqlStr);
	$dsql->Execute('vod_list');
	$stats = explode(",",$stats);
	$i =0;
	$msg="";
	
	while($row=$dsql->GetObject('vod_list'))
	{
		$msg = $msg . $row->id .",".$stats[$i].",".$row->stat.";";
		$i++;
	}
	echo $msg;
}
elseif($action=='vod'){
	$row=$dsql->GetOne("select title,app_name,stream_name from sea_vod where id='$id'");
	$title = $row['title'];
	$app_name=$row['app_name'];
	$stream_name = $row['stream_name'];
	
	
	if($commendid){
		$url = makeStartUrl($app_name, $stream_name);
		$filename = curl_get_html($url);
		$stat = 1;
		if(strlen($filename) <= 0){
			$stat = 0;
		}else{
			$v_name = $title.date('YmdHi',time());
			$v_stime =date('Y-m-d H:i:s',time());
			$v_etime = date('Y-m-d H:i:s', strtotime('+1 hours'));
			$groupid = $cuserLogin->getgroupid();
			$userid = $cuserLogin->getUserID();
			if($groupid==3){
				$tid = $cuserLogin->gettypeid();
				$user = $cuserLogin->getNickName();
			}else{
				$tid = 0;
				$user = "佚名";
			}
			$publish = 0;
			$insertSql = "insert into sea_subscribe(title,vid,tid,aid,publish,start,v_pic,end,v_publisharea,v_director,user,file_name,stat) values ('$v_name','$id','$tid','$userid','$publish','$v_stime','','$v_etime','','','$user','$filename','1')";
			$dsql->ExecuteNoneQuery($insertSql);
		}
		$updateSql = "UPDATE sea_vod SET file_name = '$filename', stat = '$stat' WHERE id = $id";
		if(!$dsql->ExecuteNoneQuery($updateSql))
		{
			echo "更新数据库错误！";
		}else{
			echo "stop";
		}	
	}
	else{
		$url = makeStopUrl($app_name, $stream_name);
		$filename = curl_get_html($url);
		if(strlen($filename) > 0){
			$row=$dsql->GetOne("select id from sea_subscribe where stat = '1' and file_name='$filename'");
			if($row){
				$id=$row['id'];
				$v_etime = date('Y-m-d H:i:s',time());
				$updateSql = "update sea_subscribe set end = '$v_etime', stat = '2' where id=$id";
				$dsql->ExecuteNoneQuery($updateSql);
			}else{
				$v_name = $title.date('Ymd',time());
				$v_stime = date('Y-m-d H:i:s',time());
				$insertSql = "insert into sea_subscribe(title,vid,tid,publish,start,v_pic,end,v_publisharea,v_director,user,file_name,stat) values ('$v_name','$id','0','0','$v_stime','','$v_stime','','','','$filename','2')";
				$dsql->ExecuteNoneQuery($insertSql);
			}
		}
		$updateSql = "UPDATE sea_vod SET file_name = '$filename', stat = '0' WHERE id = $id";
		if(!$dsql->ExecuteNoneQuery($updateSql))
		{
			echo "更新数据库错误！";
		}else{
			echo "record";
		}	
	}

	
}
elseif($action=='publish'){
	$row=$dsql->GetOne("select title,app_name,stream_name from sea_vod where id='$id'");
	$title = $row['title'];
	$app_name=$row['app_name'];
	$stream_name = $row['stream_name'];
	$url = makeUrl($app_name,$stream_name);

	$v_pic ="/pic/vod.jpg";
	$year =date('Y');
	$t = time();
	$tid = 23;
	$v_enname = Pinyin($title);
	$v_letter = strtoupper(substr($v_enname,0,1));

	#加入直播到数据库
	$insertSql = "insert into sea_data(tid,v_name,v_letter,v_state,v_topic,v_hit,v_money,v_vip,v_rank,v_actor,v_color,v_publishyear,v_publisharea,v_pic,v_spic,v_gpic,v_addtime,v_note,v_tags,v_lang,v_score,v_scorenum,v_director,v_enname,v_commend,v_extratype,v_jq,v_nickname,v_reweek,v_douban,v_mtime,v_imdb,v_tvs,v_company,v_dayhit,v_weekhit,v_monthhit,v_len,v_total,v_daytime,v_weektime,v_monthtime,v_ver,v_psd,v_try,v_longtxt,v_digg,v_tread) values ('$tid','$title','$v_letter','0','0','0','0','','0','','#FF0000','$year','','$v_pic','$v_pic','','$t','','','','0','0','','$v_enname','5','','','','','0','0','0','','','0','0','0','','','$t','$t','$t','','','0','','0','0')";
	if($dsql->ExecuteNoneQuery($insertSql))
	{
		$v_id = $dsql->GetLastID();
		$v_playdata = 'Xgplayer$$第1集$'.$url.'$xg';
		$dsql->ExecuteNoneQuery("INSERT INTO `sea_playdata`(`v_id`,`tid`,`body`,`body1`) VALUES ('$v_id','$tid','$v_playdata','')");
		cache_clear(sea_ROOT.'/data/cache');
		echo "直播视频《".$title."》已经成功发布到了前台首页，您可以在后台视频管理中继续编辑详细的参数。";
	} else
	{
		echo "直播视频发布失败！";
	}
	
}
elseif($action=="updatepic")
{
	require_once(sea_DATA."/config.ftp.php");
	$row=$dsql->GetOne("select count(*) as dd from sea_data where instr(v_pic,'#err')=0".($app_ftp==0?"":" and instr(v_pic,'$app_ftpurl')=0")." and instr(v_pic,'http')<>0");
	$num=$row['dd'];
	echo $num;
}
elseif($action=="checkrepeat")
{
	$v_name=iconv('utf-8','utf-8',$_GET["v_name"]); 
	$row=$dsql->GetOne("select count(*) as dd from sea_data where v_name='$v_name'");
	$num=$row['dd'];
	if($num==0){echo "ok";}else{echo "err";}
}
elseif($action=="getselflabel")
{
	$query="select tagcontent from `sea_mytag` where aid='$id'";
	$row=$dsql->GetOne($query);
	if(!$row)
	{
		echo "err";
	}else{
		echo htmlspecialchars($row['tagcontent']);
	}
}
elseif($action=="checkuser")
{
	if(m_ereg("[^0-9a-zA-Z_@!\.-]",$username)) exit('no');
	$row=$dsql->GetOne("select count(*) as dd from sea_admin where name='$username'");
	$num=$row['dd'];
	if($num==0){echo "0";}else{echo "1";}
}
elseif($action=="updatecache")
{
	cache_clear(sea_ROOT.'/data/cache');
	require_once(sea_ROOT.'/data/config.cache.inc.php');
	if($cfg_cachetype=='redis'){
	$redis = new Redis();
	$redis->connect($cfg_redishost,$cfg_redisport);
	if($cfg_redispsw !=""){$redis->auth($cfg_redispsw);}
	$redis->flushall();
	}
	echo "ok";
}
elseif($action=="clearColHis")
{
	delFile(sea_ROOT.'/data/cache/collect_xml.php');
	echo "ok";
}
elseif($action=="locks")
{
	$row=$dsql->GetOne("select password as dd from sea_admin where name='$uname'");
	$spwd=$row['dd'];
	$upwd = substr(md5($upwd),5,20);
	if($spwd==$upwd){echo "ok";}else{echo "err";}
}
else
{
echo "ok";
}

//通过curl调用网页，并获取网页内容。
function curl_get_html($url){
	$testurl = $url;
	$ch = curl_init();  
	curl_setopt($ch, CURLOPT_URL, $testurl);  
	//参数为1表示传输数据，为0表示直接输出显示。
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//参数为0表示不带头文件，为1表示带头文件
	curl_setopt($ch, CURLOPT_HEADER,0);
	$output = curl_exec($ch); 
	curl_close($ch); 
	return $output;
}

function makeStartUrl($app_name, $stream_name){
	global $cfg_basehost;
	return $cfg_basehost."/control/record/start?app=$app_name&name=$stream_name&rec=all";
}

function makeStopUrl($app_name, $stream_name){
	global $cfg_basehost;
	return $cfg_basehost."/control/record/stop?app=$app_name&name=$stream_name&rec=all";
}	

function makeUrl($app,$stream){
	if($app=="flv"){
		$url = "/flv?app=flv&stream=".$stream."&s=.flv";
	} else if($app=="hls") {
		$url = "/hls/".$stream."/index.m3u8";
	}else if($app=="dash") {
		$url = "/dash/".$stream."/index.mpd";
	}
	return $url;
}


//根据相对路径，获取完整路径。
function getFullPath($filename){
	$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__).'/../'));
	return $wwwroot.$filename;
}

//根据完整路径，获取相对路径。
function getRelativePath($filename){
	if(strlen($cfg_cmspath)>0){
		$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__).'/../../'));
	}else{
		$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__).'/../'));	
	}
	$wwwlen = strlen($wwwroot);
	if(strncmp($wwwroot,$filename,$wwwlen)==0){
		return substr($filename,$wwwlen);
	}
	return $filename;
}

//图片裁剪
function image_resize($filename, $x, $y,$width,$height){
	if ($width < 1 || $height < 1)
    {
        echo "裁剪宽度或高度过小!";
        return false;
    }
	if (!file_exists($filename))
    {
        echo "被裁剪的图片文件未找到！";
        return false;
    }

	$src_img = imagecreatefromjpeg($filename);
	//$w = imagesx($src_img);
    //$h = imagesy($src_img);
	$new_img = imagecreatetruecolor($width, $height);
	if(!imagecopyresampled($new_img, $src_img,0, 0, $x, $y, $width, $height, $width, $height)){
		echo "图片裁剪失败！";
        return false;
	}

	if(!imagejpeg($new_img, $filename, 75))
	{
		echo "图片裁剪失败！";
        return false;
	}
	return true;
}

function mkdirs($dir, $mode = 0777)
{
	if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
	if (!mkdirs(dirname($dir), $mode)) return FALSE;
	return @mkdir($dir, $mode);
}

?>