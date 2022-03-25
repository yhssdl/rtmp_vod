<?php
	//本文件需要到定时调用，一般可10秒左右调用一次，用于预约录像的处理。	

	set_time_limit(300);
	require_once("include/common.php");

	//管理员后台通过AJAX调用刷新
	$admin = 0;
	if(!empty ($_GET['admin'])){
		$admin = $_GET['admin'];
	}

	session_start();
	if(isset($_SESSION['admin_time'])){
		$t1 = time() - $_SESSION['admin_time'];
		if($t1<=60 && $admin==0){
			//前台显示网页时，后台已经调用在调用，就不再处理，直接返回。
			$dt = date("H:i:s");
			$t1 = 60 - (time() % 60);
			echo "<div style='text-align:center;padding:20px;'><h2>当前服务器时间:$dt</h2>本页面用于处理自动预约录像，请不要关闭，$t1 秒后会自动刷新。<br>当前后台正在自动处理预约录像中。</div>";
			fresh_page($t1*1000);
			die;
		}
	}

	$msg = "";

	if($admin) $_SESSION['admin_time']= time();
	session_commit();

	//处理预约录课的自动结束
	$st = getNextStopTime();
	if($mt<=0){
		runStopRecord();
	}

	//处理自动开始预约录课
	$et = getNextStartTime();
	if($et<=0)
	{
		runStartRecord();
	}

	//将录制下来的flv文件转为MP4文件
	runFlv2Mp4();
	//将录制的结果发布到网站
	runPublish();


	if($admin){
		$nt = 10 - (time() % 10);
		if($st>0 && $st<$nt) $nt = $st;
		if($et>0 && $et<$nt) $nt = $et;
		echo $nt;
	}else{		
		$dt = date("H:i:s");
		$t1 = 60 - (time() % 60);
		echo "<div style='text-align:center;padding:20px;'><h2>当前服务器时间:$dt</h2>页面用于处理自动预约录像，请不要关闭，$t1 秒后会自动刷新。</div>";
		fresh_page($t1*1000);
	}



	function fresh_page($time){	
		//<!--JS 页面自动刷新 -->
		echo ("<script type=\"text/javascript\">");
		echo ("function fresh_page()");
		echo ("{");
		echo ("window.location.reload();");
		echo ("}"); 
		echo ("setTimeout('fresh_page()',$time);");      
		echo ("</script>");

	}


	//获取最近预约的开始时间
	function getNextStartTime(){
		global $dsql;
		$t1 = date('Y-m-d H:i:s',strtotime("-300 second"));
		$t2 = date('Y-m-d H:i:s');
		$csqlStr = "SELECT start FROM sea_subscribe WHERE stat=0 AND start BETWEEN '$t1' AND '$t2' ORDER BY start ASC  LIMIT 1";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('vod_list');
		while($row=$dsql->GetObject('vod_list'))
		{
			$t1 = strtotime($row->start) - time();
			return $t1;
		}
		return false;
	}

		//获取最近预约的结束时间
		function getNextStopTime(){
			global $dsql;
			$csqlStr = "SELECT end FROM sea_subscribe WHERE stat=1 AND unix_timestamp(NOW()) >= unix_timestamp(end) ORDER BY end ASC  LIMIT 1";
			$dsql->SetQuery($csqlStr);
			$dsql->Execute('vod_list');
			while($row=$dsql->GetObject('vod_list'))
			{
				$t1 = strtotime($row->end) - time();
				return $t1;
			}
			return false;
		}

			
	//处理预约录像的开始任务
	function runStartRecord(){
		global $dsql;
		$t1 = date('Y-m-d H:i:s',strtotime("-300 second"));
		$t2 = date('Y-m-d H:i:s');
		$csqlStr = "SELECT sea_subscribe.id,sea_subscribe.title,sea_subscribe.start,sea_vod.app_name,sea_vod.stream_name  FROM sea_subscribe LEFT JOIN sea_vod ON sea_subscribe.vid = sea_vod .id WHERE sea_subscribe.stat=0 AND sea_subscribe.start BETWEEN '$t1' AND '$t2' ORDER BY sea_subscribe.start ASC";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('vod_list');
		while($row=$dsql->GetObject('vod_list'))
		{
			$id = $row->id;
			$app_name = $row->app_name;
			$stream_name = $row->stream_name;
			$url = makeStartUrl($app_name, $stream_name);
			$filename = curl_get($url);
			$stat = 1;
			if(strlen($filename) <= 0){
				$stat = 6;
			}
			$updateSql = "UPDATE sea_subscribe SET file_name = '$filename', stat = '$stat' WHERE stat =0 and id = $id";
			if(!$dsql->ExecuteNoneQuery($updateSql))
			{
				//echo "更新预约数据库错误！";
			}else{
				//echo "开始录像:$stat  $app_name/$stream_name   ";
			}
		}
	}

			
	//处理预约录像的开始任务
	function runStopRecord(){
		global $dsql;
		$csqlStr = "SELECT sea_subscribe.id,sea_subscribe.title,sea_subscribe.tid,sea_subscribe.file_name,sea_subscribe.start,sea_subscribe.v_pic,sea_subscribe.end,sea_subscribe.v_publisharea,sea_subscribe.v_director,sea_subscribe.user,sea_vod.app_name,sea_vod.stream_name  FROM sea_subscribe LEFT JOIN sea_vod ON sea_subscribe.vid = sea_vod .id WHERE  sea_subscribe.stat=1 AND unix_timestamp(NOW()) >= unix_timestamp(sea_subscribe.end) ORDER BY sea_subscribe.end ASC";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('vod_list');
		while($row=$dsql->GetObject('vod_list'))
		{
			$id = $row->id;
			$app_name = $row->app_name;
			$stream_name = $row->stream_name;
			$url = makeStopUrl($app_name, $stream_name);
			$filename = curl_get($url);
			if(strlen($filename) <= 0){
				$filename = $row->file_name;
			}

			$updateSql = "UPDATE sea_subscribe SET file_name = '$filename', stat = '2' WHERE stat = 1 and id = $id";
			if(!$dsql->ExecuteNoneQuery($updateSql))
			{
				//echo "更新预约数据库错误！";
			}else{
				//echo "停止录像:$title  $app_name/$stream_name  ";
			}
			
		}
	}

	//处理FLV录像转为MP4格式
	function runFlv2Mp4(){
		global $dsql,$cfg_movevodfile,$cfg_screenshot;
		$csqlStr = "SELECT id,tid,title,user,v_pic,start,file_name FROM sea_subscribe WHERE stat=2 and tid>0  ORDER BY id ASC LIMIT 1";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('vod_list');
		while($row=$dsql->GetObject('vod_list'))
		{
			$id = $row->id;
			$flvfile = getFullPath($row->file_name);
	
			if($cfg_movevodfile){
				$path = dirname($flvfile)."/".getTidName($row->tid);
				mkdirs($path);
				$mp4file = $path."/".date("YmdHi",strtotime($row->start))."_".$row->user."_".fixFileName($row->title).".mp4";
				if($cfg_screenshot && $row->v_pic=="") $jpgfile = $path."/".date("YmdHi",strtotime($row->start))."_".$row->user."_".fixFileName($row->title).".jpg";
			}else{
				$mp4file = substr($flvfile,0,strrpos($flvfile, '.')).".mp4";		
				if($cfg_screenshot && $row->v_pic=="") $jpgfile = substr($flvfile,0,strrpos($flvfile, '.')).".jpg";		
			}

			$updateSql = "UPDATE sea_subscribe SET stat = '3' WHERE stat = 2 AND id = $id";
			if(!$dsql->ExecuteNoneQuery($updateSql))
			{
				return "更新预约数据库错误！";
			}


			if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
				$ffmpeg = str_replace('\\','/',realpath(dirname(__FILE__)))."/setup/ffmpeg";
			}else{
				$ffmpeg = "ffmpeg";
			}


			$command = "$ffmpeg -loglevel quiet -i $flvfile -vcodec copy -acodec copy $mp4file";
			$outfile = $flvfile;
			exec($command) ;
			if(file_exists($mp4file)){
				$flvsize = filesize($flvfile);
				$mp4size = filesize($mp4file);
				if($mp4size*2>$flvsize){
					$outfile = $mp4file;
					unlink($flvfile);
				}
			}


			if($cfg_screenshot && $row->v_pic=="") {
				if($row->v_pic==""){
					$command = "$ffmpeg -ss 1.0 -t 0.001 -loglevel quiet -i $outfile -f image2 -y $jpgfile";
					exec($command);
					if(file_exists($jpgfile)){
						$jpgfile = getRelativePath($jpgfile);
					}else{
						$jpgfile = "";
					}
				}else{
					$jpgfile = $row->v_pic;
				}				
			}


			$outfile = getRelativePath($outfile);

			if($cfg_screenshot  && $row->v_pic=="") 
				$updateSql = "UPDATE sea_subscribe SET file_name = '$outfile',v_pic = '$jpgfile', stat = '4' WHERE stat = 3 AND id = $id";
			else
				$updateSql = "UPDATE sea_subscribe SET file_name = '$outfile', stat = '4' WHERE stat = 3 AND id = $id";
			if(!$dsql->ExecuteNoneQuery($updateSql))
			{
				//echo "更新预约数据库错误！";
			}else{
				//echo "转码成功。   ";
			}
		}
		//return "";
	}

	#根据目录号，获取目录名
	function getTidName($tid){
		global $dsql;
		
		$csqlStr = "SELECT `tname` FROM `sea_type` WHERE `tid`=$tid LIMIT 1";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('type_name');
		$row = $dsql->GetOne($csqlStr);
		while($row=$dsql->GetObject('type_name')){
			return $row->tname;
			
		}
		return "默认";
	}

	function fixFileName($filename){
		$filename = strtr($filename," ","_");
		$filename = strtr($filename,"/","_");
		$filename = strtr($filename,"\\","_");
		$filename = strtr($filename,":","_");
		$filename = strtr($filename,"?","_");
		$filename = strtr($filename,"*","_");
		$filename = strtr($filename,">","_");
		$filename = strtr($filename,"<","_");
		$filename = strtr($filename,"|","_");
		$filename = strtr($filename,"\"","_");
		$filename = strtr($filename,"$","_");
		$filename = strtr($filename,"&","_");
		return $filename;			
	}

	function mkdirs($dir, $mode = 0777)
	{
		if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
		if (!mkdirs(dirname($dir), $mode)) return FALSE;
		return @mkdir($dir, $mode);
	}

	//处理等待发布的信息
	function runPublish(){
		global $dsql;
		$csqlStr = "SELECT id,title,tid,file_name,v_pic,v_publisharea,v_director,user  FROM sea_subscribe WHERE  stat='4' and publish='1' LIMIT 1";
		$dsql->SetQuery($csqlStr);
		$dsql->Execute('vod_list');
		while($row=$dsql->GetObject('vod_list'))
		{

			//先将状态设置为已发布状态，防止重复发布。
			$updateSql = "UPDATE sea_subscribe SET stat = '5' WHERE id = $row->id";
			$dsql->ExecuteNoneQuery($updateSql);

			$year =date('Y');
			$v_lang = "上学期";
			$month = date('m');
			if ($month>1 && $month<=8)
				$v_lang = "下学期";
			$t = time();
			$v_enname = Pinyin($row->title);
			$v_letter = strtoupper(substr($v_enname,0,1));

			#加入课程到数据库
			$insertSql = "insert into sea_data(tid,v_name,v_letter,v_state,v_topic,v_hit,v_money,v_vip,v_rank,v_actor,v_color,v_publishyear,v_publisharea,v_pic,v_spic,v_gpic,v_addtime,v_note,v_tags,v_lang,v_score,v_scorenum,v_director,v_enname,v_commend,v_extratype,v_jq,v_nickname,v_reweek,v_douban,v_mtime,v_imdb,v_tvs,v_company,v_dayhit,v_weekhit,v_monthhit,v_len,v_total,v_daytime,v_weektime,v_monthtime,v_ver,v_psd,v_try,v_longtxt,v_digg,v_tread) values ('$row->tid','$row->title','$v_letter','0','0','0','0','','0','$row->user','#FF0000','$year','$row->v_publisharea','$row->v_pic','','','$t','','','$v_lang','0','0','$row->v_director','$v_enname','0','','','','','0','0','0','','','0','0','0','','','$t','$t','$t','','','0','','0','0')";
			if($dsql->ExecuteNoneQuery($insertSql))
			{
				$v_id = $dsql->GetLastID();
				$v_playdata = 'Xgplayer$$第1集$'.$row->file_name.'$xg';
				$dsql->ExecuteNoneQuery("INSERT INTO `sea_playdata`(`v_id`,`tid`,`body`,`body1`) VALUES ('$v_id','$row->tid','$v_playdata','')");
				cache_clear(sea_ROOT.'/data/cache');
				$updateSql = "UPDATE sea_subscribe SET pid = '$v_id',file_name = '$row->file_name' WHERE id = $row->id";
				$dsql->ExecuteNoneQuery($updateSql);
			}
			break;
		}
	}

	function cache_clear($dir) {
		$dirname=$dir;
		$handle = opendir($dirname);
		if($handle){
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..') {
					$dir = $dirname .'/' . $file;
					is_dir($dir) ? cache_clear($dir) : unlink($dir);
				}
			}
			closedir($handle);			
		}

 }


	//根据相对路径，获取完整路径。
	function getFullPath($filename){
		global $cfg_cmspath;
		if($filename[0]=='/') return $filename;
		if(strlen($cfg_cmspath)>0){
			$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__).'/../../'));		
		}else{
			$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__).'/../'));		
		}

		return $wwwroot."/".$filename;
	}

	//根据完整路径，获取相对路径。
	function getRelativePath($filename){
		global $cfg_cmspath;
		if(strlen($cfg_cmspath)>0){
			$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__).'/../'));
		}else{
			$wwwroot = str_replace('\\','/',realpath(dirname(__FILE__)));	
		}
		$wwwlen = strlen($wwwroot);
		if(strncmp($wwwroot,$filename,$wwwlen)==0){
			return substr($filename,$wwwlen);
		}
		return $filename;
	}


	//通过curl调用网页，并获取网页内容。
	function curl_get($url){
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

?>