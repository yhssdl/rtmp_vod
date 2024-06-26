<?php 
if(!defined('sea_INC'))
{
	exit("Request Error!");
}

$dcollect = $col = new Collect();
class Collect
{
	/*
	 * 官方资源库采集入库
	 * $video xml单个simplexml数据 
	 * $localId 入库后本地id
	 * */
	public function xml_db($video,$localId)
	{  
		$v_data['v_name'] =  htmlspecialchars($video->name);//视频名称
		$v_data['v_name'] = str_replace(array('\\','()','\''),'/',$v_data['v_name']);
		$v_data['v_pic'] = (String)$video->pic;//视频图片地址
		$v_data['v_state'] = (String)$video->state;//视频连载状态
		$v_data['v_lang'] = (String)$video->lang;//视频学期
		$v_data['v_publisharea'] =(String) $video->area;//视频年级
		$v_data['v_publishyear'] = (String)$video->year;//视频年份
		$v_data['v_note'] = (String)$video->note;//视频备注
		$v_data['v_tags'] = htmlspecialchars($video->keywords);//视频关键词
		$v_data['v_nickname'] = htmlspecialchars($video->nickname);//视频别名
		$v_data['v_reweek'] =(String) $video->reweek;//视频更新周期
		$v_data['v_douban'] = (String)$video->douban;//视频豆瓣评分
		$v_data['v_mtime'] = (String)$video->mtime;//视频时光网评分
		$v_data['v_imdb'] = (String)$video->imdb;//视频imdb评分
		$v_data['v_tvs'] = (String)$video->tvs;//视频上映电视台
		$v_data['v_company'] = (String)$video->company;//视频发行公司
		$v_data['v_ver'] = (String)$video->ver;//视频版本
		$v_data['v_longtxt'] =(String) $video->longtxt;//视频备用备注信息
		$v_data['v_actor'] = htmlspecialchars($video->actor);//视频演员
		$v_data['v_actor'] = str_replace('%', ' ', $v_data['v_actor']);
		$v_data['v_director'] = htmlspecialchars($video->director);//视频导演
		$v_data['v_director']  = str_replace('%', ' ', $v_data['v_director'] );
		$v_data['v_des'] = htmlspecialchars($video->des);//视频简介
		$v_data['v_total'] = (String)$video->episode;//总集数
		$v_data['v_len'] = (String)$video->len;//视频时长
		$v_data['v_total'] = (String)$video->total;//视频集数
		$v_data['v_jq'] = (String)$video->jq;//课程分类
		if($v_data['v_actor']=="" OR empty($v_data['v_actor'])){$v_data['v_actor']="内详";}
		if($v_data['v_director']=="" OR empty($v_data['v_director'])){$v_data['v_director']="内详";}
		//$flag = $video->dl->dd['flag'];//视频前缀，属于哪个资源库
		//$v_data['v_playdata'] = $flag."$$".$video->dl->dd;//视频数据地址
		$zzt=count($video->dl->dd);
		$playerKindsfile=sea_ROOT.'/data/admin/playerKinds.xml';
					$xml = simplexml_load_file($playerKindsfile);
					if(!$xml){$xml = simplexml_load_string(file_get_contents($playerKindsfile));}
					$id=0;
					$z=array();
					foreach($xml as $player){
					$k=$player['postfix'];
					$z["$k"]=$player['flag'];
					}
		for($i=0;$i<$zzt;$i++)
		{
		   if($video->dl->dd[$i]['flag']=='down')
		      {$v_data['v_downdata'] .= "下载地址一$$".$video->dl->dd[$i]."$$$";} 
		   else	
		      {
				  $f=$video->dl->dd[$i]['flag'];
				  $flag=$z["$f"];
				  $v_data['v_playdata'] .= $flag."$$".$video->dl->dd[$i]."$$$";
			  } 
		} 
		$v_data['v_playdata'] = substr($v_data['v_playdata'],0,-3);
		$v_data['v_downdata'] = substr($v_data['v_downdata'],0,-3);
		
		
		$v_data['v_enname'] = Pinyin($v_data['v_name']);
		$v_data['v_letter'] = strtoupper(substr($v_data['v_enname'],0,1));
		
		// 视频关键词过滤 跳过采集
		global $cfg_cjjump;
		if($cfg_cjjump !=""){
			$jumparr = explode('|',$cfg_cjjump);
			foreach ($jumparr as $value)
					  {
						  if(strpos($v_data['v_name'],$value) !== false){return "数据<font color=red>".$v_data['v_name']."</font>含过滤词,跳过采集<br>";}
					  }
		}
		// 视频关键词替换
		global $cfg_cjreplace;
		if($cfg_cjreplace !=""){		
			$r0 = explode('|',$cfg_cjreplace);
			$r = array();
			foreach($r0 as $data){$r[]=explode('=',$data);}
			foreach($r as $d){$v_data['v_name']=str_replace($d['0'],$d['1'],$v_data['v_name']);}
		}
		if(is_numeric($localId))
		{		
			$v_data['v_ismake'] = 0;
			$v_data['tid'] = $localId;
			return $this->_into_database($v_data);
		}else 
		{
			echo "数据<font color=red>".$v_data['v_name']."</font>未绑定分类，跳过采集<br>";
			//return $this->_into_tempdatabase($v_data);
		}
	}
	
	/*
	 * 自定义采集入库
	 * $listconf:列表采集规则配置
	 * $itemconf:单条数据采集规则配置
	 * $commconf:采集全局配置
	 * $row :单条数据url,id,pic
	 * $loopstr:单条数据采集规则配置
	 * $echo_id:排序位
	*/
	public function collect_db($listconf,$itemconf,$commconf,$row,$loopstr,$echo_id)
	{
		global $dsql;
		$html = cget($row['url'],$commconf['sock']);
		$lurl = '<a href="'.$row['url'].'" target="_blank">'.$row['url'].'</a>';
		if($html){
			$html = ChangeCode($html,$commconf['coding']);
			$pdate = getAreaValue($loopstr,"pdate",$html,$listconf["removecode"]);
			//判断时间处理
			if((!$commconf['getherday'])||($commconf['getherday']&&$this->isLeftDates($pdate,$commconf['getherday']))){
				if(trim($row['pic'])!=''){
					$v_data['v_pic']=$row['pic'];
				}else{
					$v_data['v_pic']=FillUrl($row['url'],getAreaValue($loopstr,"pic",$html,$listconf["removecode"]));
				}
				if($commconf['autocls']){
					$tname=getAreaValue($loopstr,"cls",$html,$listconf["removecode"]);
					$tid=$this->getTidFromCls($tname);
					$v_data['tname'] = $tname;
				}else{
					$tid=$commconf['classid'];
				}
			    $v_data['v_from'] = $row['url'];
			    $v_data['v_name']=getAreaValue($loopstr,"name",$html,$listconf["removecode"]);
			    $v_data['v_name']=$this->filterWord($v_data['v_name'],0);
				$v_data['v_enname']=Pinyin($v_data['v_name']);
				$v_data['v_name'] =  htmlspecialchars($v_data['v_name']);
				$v_data['v_name'] = str_replace(array('\\','()','\''),'/',$v_data['v_name']);
				
			    
			    $v_data['v_letter'] = strtoupper(substr( $v_data['v_enname'],0,1));
			    $v_data['v_state']=getAreaValue($loopstr,"state",$html,$listconf["removecode"]);
			    $v_data['v_actor']=getAreaValue($loopstr,"actor",$html,$listconf["removecode"]);
				$v_data['v_actor'] =  htmlspecialchars($v_data['v_actor']);
				$v_data['v_actor'] = str_replace('%', ' ', $v_data['v_actor']);
				
			    $v_data['v_director']=getAreaValue($loopstr,"director",$html,$listconf["removecode"]);
				$v_data['v_director'] =  htmlspecialchars($v_data['v_director']);
				$v_data['v_director'] = str_replace('%', ' ', $v_data['v_director']);
				
			    $v_data['v_note']=getAreaValue($loopstr,"note",$html,$listconf["removecode"]);
				$v_data['v_jq']=getAreaValue($loopstr,"jq",$html,$listconf["removecode"]);
			    $v_data['v_des']=getAreaValue($loopstr,"des",$html,$listconf["removecode"]);
			    $v_data['v_des']=$this->filterWord($v_data['v_des'],1);
				$v_data['v_des'] =  htmlspecialchars($v_data['v_des']);
			    $v_data['v_publishyear']=getAreaValue($loopstr,"pyear",$html,$listconf["removecode"]);
			    $v_data['v_publisharea']=getAreaValue($loopstr,"parea",$html,$listconf["removecode"]);
			    $v_data['v_lang']=getAreaValue($loopstr,"plang",$html,$listconf["removecode"]);
				$v_data['tid']=$tid;
				if($v_data['v_actor']=="" OR empty($v_data['v_actor'])){$v_data['v_actor']="内详";}
				if($v_data['v_director']=="" OR empty($v_data['v_director'])){$v_data['v_director']="内详";}
				
			   global $cfg_cj_rq,$cfg_cj_rq_s,$cfg_cj_rq_e,$cfg_cj_dc,$cfg_cj_dc_s,$cfg_cj_dc_e,$cfg_cj_pf,$cfg_cj_pf_s,$cfg_cj_pf_e;
				//pf
				if($cfg_cj_pf=='1'){$v_data['v_scorenum'] = 1; $v_data['v_score'] = mt_rand($cfg_cj_pf_s,$cfg_cj_pf_e);}
				//dc
				if($cfg_cj_dc=='1'){$v_data['v_digg'] = mt_rand($cfg_cj_dc_s,$cfg_cj_dc_e); $v_data['v_tread'] = mt_rand($cfg_cj_dc_s,$cfg_cj_dc_e);}
				//rq
				if($cfg_cj_rq=='1'){$v_data['v_hit'] = mt_rand($cfg_cj_rq_s,$cfg_cj_rq_e);}
					   
			  //开始处理播放区域
			  if(trim($itemconf["splay"])==1){
				  $playareahtml=Geturlarray($html,getrulevalue($loopstr,"plista").'[内容]'.getrulevalue($loopstr,"plistb"));
			  }else{
				  $playareahtml[0]=$html;
			  }
			  //结束处理播放区域
			  
			  //获取下载地址开始
			  $downurlarray=array();
			  $downa=getrulevalue($loopstr,"downa");
			  $downb=getrulevalue($loopstr,"downb");
			  $down_trim=getrulevalue($loopstr,"down_trim");
			  if(trim($downa) !='' && trim($downb) != ''){
				  $downurlarray[]=Geturlarray($html,$downa.'[内容]'.$downb,$down_trim);
			  }
			  //获取下载地址结束
			  
			  //获取播放地址开始
			  $playlinka=getrulevalue($loopstr,"playlinka");
			  $playlinkb=getrulevalue($loopstr,"playlinkb");
			  $playlink_trim=getrulevalue($loopstr,"playlink_trim");
			  $msrca=getrulevalue($loopstr,"msrca");
			  $msrcb=getrulevalue($loopstr,"msrcb");
			  $msrc_trim=getrulevalue($loopstr,"msrc_trim");
			  $playurlarray=array();
			  $weburl=array();
			  $weburltemp=array();
			  if(trim($itemconf["playgetsrc"])==1 && trim($playlinka) !='' && trim($playlinkb) != ''){
				  foreach($playareahtml as $sv){
					$weburltemp=Geturlarray($sv,$playlinka.'[内容]'.$playlinkb,$playlink_trim);
					$weburl[]=$weburltemp;
				  }
				  $playurlarray=Getplayurlarr($weburl,$msrca.'[内容]'.$msrcb,$msrc_trim,$row['url'],$commconf['sock'],$commconf['coding']);
			  }else{
				  if(trim($msrca) !='' && trim($msrcb) != ''){
					  foreach($playareahtml as $sv){
					  $weburl[]=Geturlarray($sv,$msrca.'[内容]'.$msrcb,$msrc_trim);
					  }
					  $playurlarray=$weburl;
				  }
			  }
			  //获取播放地址结束
			  unset($weburl);
			  unset($weburltemp);
			  //截取分集名称开始
			  $parta=getrulevalue($loopstr,"parta");
			  $partb=getrulevalue($loopstr,"partb");
			  $part_trim=getrulevalue($loopstr,"part_trim");
			  $partarray=array();
			  $webparttemp=array();
			  if(trim($itemconf["getpart"])==1 && trim($parta) !='' && trim($partb) != ''){
			  	  	foreach($playareahtml as $sv){
						$webparttemp=Geturlarray($sv,$parta.'[内容]'.$partb,$part_trim);
						$webpart[]=$webparttemp;
					}
					$partarray=$webpart;
				  }
				  //截取分集名称结束
				  unset($webpart);
				  unset($webparttemp);
				  //播放器获取开始
				  if($itemconf["serveron"]==2){
					  $server[0]=$commconf['playfrom'];
				  }else{
					  $servera=getrulevalue($loopstr,"servera");
					  $serverb=getrulevalue($loopstr,"serverb");
					  $server_trim=getrulevalue($loopstr,"server_trim");
					  if($itemconf["serveron"]==1) $server=Geturlarray($playareahtml,$servera.'(.*)'.$serverb,$server_trim);
					  if($itemconf["serveron"]==0) $server=Geturlarray($html,$servera.'(.*)'.$serverb,$server_trim);
				  }
				  //播放器获取结束
				  //根据播放来源生成地址开始
				  if($itemconf["serveron"]==2 && $commconf['playfrom']==''){
					  $geturl='';
					  foreach($playurlarray as $psv){
						  $geturltemp='';
						  foreach($psv as $ppsv){
							  $geturltemp=$geturltemp.$ppsv.'#';
						  }
						  $geturltemp=rtrim($geturltemp,'#');
						  $geturl=$geturl.$geturltemp.'$$$';
					  }
					  $geturl=rtrim($geturl,'$$$');
				  }else if($itemconf["serveron"]==2 && $commconf['playfrom']!=''){
					  if($itemconf["getpart"]==2)
					  {
						  $geturl='';
						  foreach($playurlarray as $psv){
							  $geturltemp='';
							  foreach($psv as $ppsv){
								  $geturltemp .= $ppsv.'#';
							  }
							  $geturltemp=rtrim($geturltemp,'#');
							  $geturl .= $commconf['playfrom'].'$$'.$geturltemp.'$$$';
						  }
						  $geturl=rtrim($geturl,'$$$');
					  }else
					  {
					  	$geturl=transferUrlatr($commconf['playfrom'],$playurlarray,$partarray);
					  }
				  }else{
					  if($itemconf["getpart"]==2)
					  {
						  $geturl='';
						  foreach($playurlarray as $k=>$psv){
							  $geturltemp='';
							  foreach($psv as $ppsv){
								  $geturltemp .= $ppsv.'#';
							  }
							  $geturltemp=rtrim($geturltemp,'#');
							  $geturl .= $server[$k].'$$'.$geturltemp.'$$$';
						  }
						  $geturl=rtrim($geturl,'$$$');
					  }else
					  {
						  $geturl=transferUrlarr($server,$playurlarray,$partarray);
					  }
				  }
				  //根据播放来源生成地址结束
				  //生成下载地址
				  $v_data['v_downdata'] = GenerateDownUrl($listconf["downfrom"],$downurlarray,$partarray);
				  $v_data['v_playdata'] = $geturl;
				  unset($playurlarray);
				  unset($downurlarray);
				  if(empty($v_data['v_name']))
				  {
					  return "{$echo_id} ".$lurl."\t<font color=red>视频名为空，跳过保存</font>.<br>";
				  }
				  
				  // 视频关键词过滤 跳过采集
				  global $cfg_cjjump;
				  if($cfg_cjjump !=""){
					  $jumparr = explode('|',$cfg_cjjump);
					  foreach ($jumparr as $value)
					  {
						  if(strpos($v_data['v_name'],$value) !== false){return "{$echo_id} ".$lurl."\t<font color=red>含过滤词,跳过采集</font>.<br>";}
					  }
				  }
				// 视频关键词替换
				global $cfg_cjreplace;
				if($cfg_cjreplace !=""){				
					$r0 = explode('|',$cfg_cjreplace);
					$r = array();
					foreach($r0 as $data){$r[]=explode('=',$data);}
					foreach($r as $d){$v_data['v_name']=str_replace($d['0'],$d['1'],$v_data['v_name']);}
				} 
			      $sql = "update `sea_co_url` set succ='1' where uid=".$row['uid'];
				  $dsql->ExecuteNoneQuery($sql);
				  
				  if($tid&&$itemconf["intodatabase"]){
					  return $this->_into_database($v_data,$echo_id);
				  }else
				  {
				  	  return $this->_into_tempdatabase($v_data,$echo_id);
				  }
			   }else{
				   return "{$echo_id} ".$lurl."\t<font color=red>只采集最近".$commconf['getherday']."天的数据，跳过保存</font>.<br>";
			   }
			}else{
				$sql = "update `sea_co_url` set err=err+1 where uid=".$row['uid'];
				$dsql->ExecuteNoneQuery($sql);
				return "{$echo_id} ".$lurl."\t<font color=red>远程读取失败</font>.<br>";
			}
	}
	
	public function _insert_database($v_data)
	{
		global $dsql,$cfg_cj_rq,$cfg_cj_rq_s,$cfg_cj_rq_e,$cfg_cj_dc,$cfg_cj_dc_s,$cfg_cj_dc_e,$cfg_cj_pf,$cfg_cj_pf_s,$cfg_cj_pf_e;
		
		//pf
		if($cfg_cj_pf=='1'){$v_data['v_scorenum'] = 1; $v_data['v_score'] = mt_rand($cfg_cj_pf_s,$cfg_cj_pf_e);}
		//dc
		if($cfg_cj_dc=='1'){$v_data['v_digg'] = mt_rand($cfg_cj_dc_s,$cfg_cj_dc_e); $v_data['v_tread'] = mt_rand($cfg_cj_dc_s,$cfg_cj_dc_e);}
		//rq
		if($cfg_cj_rq=='1'){$v_data['v_hit'] = mt_rand($cfg_cj_rq_s,$cfg_cj_rq_e);}
		
		$v_data['v_pic'] = gatherPicHandle($v_data['v_pic']);
		$v_des = $v_data['v_des'];
		$v_playdata = $v_data['v_playdata'];
		$v_downdata = $v_data['v_downdata'];
		unset($v_data['v_des']);
		unset($v_data['v_playdata']);
		unset($v_data['v_downdata']);
		if(insert_record('sea_data',$v_data))
		{
			$v_id = $dsql->GetLastID();
			$desdata=array('v_id'=>$v_id,'tid'=>$v_data['tid'],'body'=>$v_des);
			$playdata=array('v_id'=>$v_id,'tid'=>$v_data['tid'],'body'=>$v_playdata,'body1'=>$v_downdata);
			insert_record('sea_content',$desdata);
			insert_record('sea_playdata',$playdata);
			return "数据<font color=red>".$v_data['v_name']."</font>已经采集成功<br>";
		}
	}
	
	public function _into_database($v_data,$i=0)
	{
		global $cfg_gatherset,$dsql; 
		$autocol_str = !empty($v_data['v_from']) ? $i.' <a href="'.$v_data['v_from'].'" target="_blank">'.$v_data['v_from'].'</a>的' :'';
		//数据部分处理
		$v_data['v_name'] = str_replace(array('\\','()','\''),'/',$v_data['v_name']);
		if($v_data['v_actor']=="" OR empty($v_data['v_actor'])){$v_data['v_actor']="内详";}
		if($v_data['v_director']=="" OR empty($v_data['v_director'])){$v_data['v_director']="内详";}


		$v_data['v_addtime'] = time();
		unset($v_data['v_id']);
		unset($v_data['v_from']);
		unset($v_data['tname']);
		
		/*if(strpos($v_data['v_name'],'/')!==FALSE)
		{
			$titleArray=explode('/',$v_data['v_name']);
			foreach($titleArray as $v_title){
				if(!empty($v_title)) $v_where.=" or locate('/$v_title/',concat('/',d.v_name,'/'))";
			}
			$v_where=ltrim($v_where," or ");
			$v_where="(".$v_where.")";
		}else 
		{*/
			$v_where="d.v_name='".$v_data['v_name']."'";
//		}
		
		$v_sql="select d.v_id,d.v_pic,d.v_isunion,d.v_publishyear,d.v_publisharea,d.v_lang,d.v_director,d.v_actor,p.body as v_playdata ,p.body1 as v_downdata from sea_data d left join sea_playdata p on p.v_id=d.v_id where $v_where order by d.v_id desc";
		$rs = $dsql->GetOne($v_sql);
		//if 同名
		if(is_array($rs))
		{
			
			//if 勾选[开启分类识别]
			if(strpos($cfg_gatherset,'0')!==false)
			{
				
				$v_sql1="select d.v_id,d.v_isunion,d.v_publishyear,d.v_publisharea,d.v_lang,d.v_director,d.v_actor,p.body as v_playdata,p.body1 as v_downdata,d.v_pic from sea_data d left join sea_playdata p on p.v_id=d.v_id where $v_where and d.tid='".$v_data['tid']."' order by d.v_id desc";
				$rs1 = $dsql->GetOne($v_sql1);
				if(is_array($rs1))
				{
					if($rs1['v_isunion']=='1')
					{
						return $autocol_str."数据<font color=red>".$v_data['v_name']."</font>处于锁定状态,不更新数据<br>";
					}
															
					if($v_data['v_publishyear'] != $rs1['v_publishyear'] && (strpos($cfg_gatherset,'4')!==false))
					{
						$rs1['v_playdata']='';$rs1['v_downdata']='';
						return $autocol_str.$this->_insert_database($v_data);//年代 新增数据
					}
					if($v_data['v_publisharea'] != $rs1['v_publisharea'] && (strpos($cfg_gatherset,'5')!==false))
					{
						$rs1['v_playdata']='';$rs1['v_downdata']='';
						return $autocol_str.$this->_insert_database($v_data);//年级 新增数据
					}
					if($v_data['v_lang'] != $rs1['v_lang'] && (strpos($cfg_gatherset,'6')!==false))
					{
						$rs1['v_playdata']='';$rs1['v_downdata']='';
						return $autocol_str.$this->_insert_database($v_data);//学期 新增数据
					}
					$v_data['v_director']=str_replace(array(' ',',','/'),'',$v_data['v_director']);
					$rs1['v_director']=str_replace(array(' ',',','/'),'',$rs1['v_director']);
					if($v_data['v_director'] != $rs1['v_director'] && (strpos($cfg_gatherset,'7')!==false))
					{
						$rs1['v_playdata']='';$rs1['v_downdata']='';
						return $autocol_str.$this->_insert_database($v_data);//导演 新增数据
					}
					$v_data['v_actor']=str_replace(array(' ',',','/'),'',$v_data['v_actor']);
					$rs1['v_actor']=str_replace(array(' ',',','/'),'',$rs1['v_actor']);
					if($v_data['v_actor'] != $rs1['v_actor'] && (strpos($cfg_gatherset,'8')!==false))
					{
						$rs1['v_playdata']='';$rs1['v_downdata']='';
						return $autocol_str.$this->_insert_database($v_data);//主演 新增数据
					}
					
					$v_data['v_playdata'] = gatherIntoLibTransfer($rs1['v_playdata'],$v_data['v_playdata']);
					$v_data['v_downdata'] = gatherIntoLibTransfer($rs1['v_downdata'],$v_data['v_downdata']);
					if($v_data['v_downdata']==$rs1['v_downdata']&&$v_data['v_playdata']==$rs1['v_playdata'] && (strpos($cfg_gatherset,'3')!==false))
					{
						return $autocol_str.'数据<font color=red>'.$v_data['v_name'].'</font>地址无变化,无需更新<br>';
					}

					return $autocol_str.$this->update_movie_info($rs1,$v_data);


				}else
				{
					//if 勾选[开启不添加新视频]
				   if(strpos($cfg_gatherset,'1')!==false)
				   {
					  return $autocol_str."数据<font color=red>".$v_data['v_name']."</font>跳过，您开启了不添加新视频功能<br>";
				   }
				   //else 不勾选[开启不添加新视频]
				   return $autocol_str.$this->_insert_database($v_data);
				}
			}else
			{
				if($rs['v_isunion']=='1')
				{
					return "数据<font color=red>".$v_data['v_name']."</font>处于锁定状态,不更新数据<br>";
				}
				
				if($v_data['v_publishyear'] != $rs['v_publishyear'] && (strpos($cfg_gatherset,'4')!==false))
				{
					$rs['v_playdata']='';$rs['v_downdata']='';
					return $autocol_str.$this->_insert_database($v_data);//年代 新增数据
				}
				if($v_data['v_publisharea'] != $rs['v_publisharea'] && (strpos($cfg_gatherset,'5')!==false))
				{
					$rs['v_playdata']='';$rs['v_downdata']='';
					return $autocol_str.$this->_insert_database($v_data);//年级 新增数据
				}
				if($v_data['v_lang'] != $rs['v_lang'] && (strpos($cfg_gatherset,'6')!==false))
				{
					$rs['v_playdata']='';$rs['v_downdata']='';
					return $autocol_str.$this->_insert_database($v_data);//学期 新增数据
				}
				$v_data['v_director']=str_replace(array(' ',',','/'),'',$v_data['v_director']);
				$rs['v_director']=str_replace(array(' ',',','/'),'',$rs['v_director']);
				if($v_data['v_director'] != $rs['v_director'] && (strpos($cfg_gatherset,'7')!==false))
				{
					$rs['v_playdata']='';$rs['v_downdata']='';
					return $autocol_str.$this->_insert_database($v_data);//导演 新增数据
				}
				$v_data['v_actor']=str_replace(array(' ',',','/'),'',$v_data['v_actor']);
				$rs['v_actor']=str_replace(array(' ',',','/'),'',$rs['v_actor']);
				if($v_data['v_actor'] != $rs['v_actor'] && (strpos($cfg_gatherset,'8')!==false))
				{
					$rs['v_playdata']='';$rs['v_downdata']='';
					return $autocol_str.$this->_insert_database($v_data);//主演 新增数据
				}
				
				$v_data['v_playdata'] = gatherIntoLibTransfer($rs['v_playdata'],$v_data['v_playdata']);
				$v_data['v_downdata'] = gatherIntoLibTransfer($rs['v_downdata'],$v_data['v_downdata']);
				if($v_data['v_downdata']==$rs['v_downdata']&&$v_data['v_playdata']==$rs['v_playdata'] && (strpos($cfg_gatherset,'3')!==false))
				{
					return $autocol_str.'数据<font color=red>'.$v_data['v_name'].'</font>地址无变化,无需更新<br>';
				}				
				return $autocol_str.$this->update_movie_info($rs,$v_data);				
			}
		}//else 不 同名
		else{
			
			//if 开启不添加新视频
			if(strpos($cfg_gatherset,'1')!==false)
			{
				return $autocol_str."数据<font color=red>".$v_data['v_name']."</font>跳过，您开启了不添加新视频功能<br>";
			}
			// else 以新视频添加
			return $autocol_str.$this->_insert_database($v_data);
		}
	}
	
	public function _into_tempdatabase($v_data,$i=0)
	{
		global $dsql;
		$v_data['v_addtime'] = time();
		$autocol_str = !empty($v_data['v_from']) ? $i.'. <a href="'.$v_data['v_from'].'" target="_blank">'.$v_data['v_from'].'</a>' :'';
		$db = !empty($v_data['v_from'])  ? 'sea_co_data':'sea_temp';
		if($db == 'sea_temp')
		{
			unset($v_data['tname']);
			$v_data['tid'] = '000000';
		}
		/*if(strpos($v_data['v_name'],'/')!==FALSE)
		{
			$titleArray=explode('/',$v_data['v_name']);
			foreach($titleArray as $v_title){
				if(!empty($v_title)) $v_where.=" or locate('/$v_title/',concat('/',v_name,'/'))>0 ";
			}
			$v_where=ltrim($v_where," or ");
			$v_where="(".$v_where.")";
		}else 
		{*/
			//$v_data['v_name'] = str_replace(array('HD','BD','DVD','VCD','TS','【完结】','【】','[]','()','\''),'',$v_data['v_name']);
			$v_where="v_name='".$v_data['v_name']."'";
//		}
		
		$v_sql="select v_id,v_playdata,v_pic from ".$db." where $v_where order by v_id desc";
		$rs = $dsql->GetOne($v_sql);
		//if 不同名
		if(!is_array($rs))
		{
			$v_data['v_pic'] = gatherPicHandle($v_data['v_pic']);
			insert_record($db,$v_data);
			return $autocol_str."数据<font color=red>".$v_data['v_name']."</font>已经采集成功<br>";
		}//else 同名
		else{
			$v_data['v_playdata'] = gatherIntoLibTransfer($rs['v_playdata'],$v_data['v_playdata']);
			$v_data = $this->update_pic($v_data,$rs);
			update_record($db,"where v_id=".$rs['v_id'],$v_data);
			return $autocol_str."数据<font color=red>".$v_data['v_name']."</font>已存在,更新数据<br>";
			
		}
	}
	
	function update_playdata_only($rs,$v_data)
	{
		update_record('sea_data',"where v_id=".$rs['v_id'],array('v_ismake'=>'0','v_state'=>$v_data['v_state'],'v_note'=>$v_data['v_note'],'v_addtime'=>time()));
		update_record('sea_playdata',"where v_id=".$rs['v_id'],array('body'=>$v_data['v_playdata'],'body1'=>$v_data['v_downdata']));
		return "数据<font color=red>".$v_data['v_name']."</font>已存在,仅更新地址及状态<br>";
	}
	
	function update_movie_info($rs,$v_data)
	{
		global $cfg_gatherset;
		$v_des = $v_data['v_des'];
		$v_playdata = $v_data['v_playdata'];
		$v_downdata = $v_data['v_downdata'];	
		unset($v_data['tid']);
		unset($v_data['v_hit']);	
		if(strpos($cfg_gatherset,'C')!==false){}else{unset($v_data['v_pic']);}
		if(strpos($cfg_gatherset,'D')!==false){}else{unset($v_data['v_state']);}
		if(strpos($cfg_gatherset,'E')!==false){}else{unset($v_data['v_lang']);}
		if(strpos($cfg_gatherset,'F')!==false){}else{unset($v_data['v_publisharea']);}
		if(strpos($cfg_gatherset,'G')!==false){}else{unset($v_data['v_publishyear']);}
		if(strpos($cfg_gatherset,'H')!==false){}else{unset($v_data['v_note']);}
		if(strpos($cfg_gatherset,'I')!==false){}else{unset($v_data['v_tags']);}
		if(strpos($cfg_gatherset,'J')!==false){}else{unset($v_data['v_nickname']);}
		if(strpos($cfg_gatherset,'K')!==false){}else{unset($v_data['v_reweek']);}
		if(strpos($cfg_gatherset,'L')!==false){}else{unset($v_data['v_douban']);}
		if(strpos($cfg_gatherset,'M')!==false){}else{unset($v_data['v_mtime']);}
		if(strpos($cfg_gatherset,'N')!==false){}else{unset($v_data['v_imdb']);}
		if(strpos($cfg_gatherset,'O')!==false){}else{unset($v_data['v_tvs']);}
		if(strpos($cfg_gatherset,'P')!==false){}else{unset($v_data['v_company']);}
		if(strpos($cfg_gatherset,'Q')!==false){}else{unset($v_data['v_ver']);}
		if(strpos($cfg_gatherset,'R')!==false){}else{unset($v_data['v_longtxt']);}
		if(strpos($cfg_gatherset,'S')!==false){}else{unset($v_data['v_actor']);}
		if(strpos($cfg_gatherset,'T')!==false){}else{unset($v_data['v_director']);}
		if(strpos($cfg_gatherset,'V')!==false){}else{unset($v_data['v_total']);}
		if(strpos($cfg_gatherset,'W')!==false){}else{unset($v_data['v_len']);}
		if(strpos($cfg_gatherset,'X')!==false){}else{unset($v_data['v_jq']);}
		if(strpos($cfg_gatherset,'Y')!==false){unset($v_data['v_addtime']);}
		
		unset($v_data['v_des']);
		unset($v_data['v_playdata']);
		unset($v_data['v_downdata']);
		update_record('sea_data',"where v_id=".$rs['v_id'],$v_data);
		update_record('sea_data',"where v_id=".$rs['v_id'],array('v_ismake'=>'0'));
		if(strpos($cfg_gatherset,'Y')!==false){unset($v_data['v_addtime']);}else{update_record('sea_data',"where v_id=".$rs['v_id'],array('v_addtime'=>time()));}
		if(strpos($cfg_gatherset,'A')!==false)
		{update_record('sea_playdata',"where v_id=".$rs['v_id'],array('body'=>$v_playdata));}
		if(strpos($cfg_gatherset,'B')!==false)
		{update_record('sea_playdata',"where v_id=".$rs['v_id'],array('body1'=>$v_downdata));}
		if(strpos($cfg_gatherset,'U')!==false)
		{update_record('sea_content',"where v_id=".$rs['v_id'],array('body'=>$v_des));}
		return "数据<font color=red>".$v_data['v_name']."</font>已存在,更新数据<br>";
	}
	
	function update_movie_info_pic($rs,$v_data)
	{
		
		$v_des = $v_data['v_des'];
		$v_playdata = $v_data['v_playdata'];
		$v_downdata = $v_data['v_downdata'];
		unset($v_data['v_des']);
		unset($v_data['v_playdata']);
		unset($v_data['v_downdata']);
		update_record('sea_data',"where v_id=".$rs['v_id'],$v_data);
		update_record('sea_data',"where v_id=".$rs['v_id'],array('v_ismake'=>'0','v_addtime'=>time()));
		update_record('sea_playdata',"where v_id=".$rs['v_id'],array('body'=>$v_playdata,'body1'=>$v_downdata));
		update_record('sea_content',"where v_id=".$rs['v_id'],array('body'=>$v_des));
		return "数据<font color=red>".$v_data['v_name']."</font>已存在,更新数据,更新图片。<br>";
	}
	
	function update_pic($v_data,$rs)
	{
		global $cfg_gatherset;
		if(strpos($cfg_gatherset,'4'))
		{
			unset($v_data['v_pic']);
		}
		
		return $v_data;
	}
	
	function isLeftDates($pdate,$getherday)
	{
		if(empty($pdate)) return false;
		$date2 = time();
		$dateArr=explode("-",$pdate);
		$date1Int = mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]);
		$daysleft = floor(($date2-$date1Int) / 86400);
		if($getherday>=$daysleft) return true; else return false;
	}
	
	function getTidFromCls($name)
	{
		global $dsql;
		$trow = $dsql->GetOne("select sysclsid from sea_co_cls where clsname='$name'");
		if(is_array($trow)) return $trow['sysclsid'];
		else return 0;
	}
	
	function filterWord($string,$rCol)
	{
		global $dsql;
		if($string=='')
		return $string;
		$sql = "SELECT rColumn,uesMode,sFind,sReplace,sStart,sEnd FROM sea_co_filters WHERE Flag=1 and cotype=0";
		$dsql->SetQuery($sql);
		$dsql->Execute('filterWord');
		while ($row =$dsql->GetArray('filterWord'))
		{
			if($row['rColumn']==$rCol)
			{
				if($row['uesMode']==1)
				$string=preg_replace("/".addslashes($row['sStart'])."([\s\S]+?)".addslashes($row['sEnd'])."/ig", $row['sReplace'], $string);
				else
				$string=str_replace($row['sFind'], $row['sReplace'], $string);
			}
		}
		return $string;
	}
	
}