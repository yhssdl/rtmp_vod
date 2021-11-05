<?php 
require_once(sea_DATA."/config.cache.inc.php");

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


function get_xml_data($element, $tagname) {
	$tags = $element->getElementsByTagName($tagname);   //  取得所有的$tagname
	
	$tag = $tags->item(0);  //  获取第一个以$tagname命名的标签
	if ($tag->hasAttributes()) {    //  获取data属性
		$attribute = $tag->getAttribute("data");
		return $attribute;
	}else {
		return false;
	}
}

function getNodeValue($node,$default=""){
	$count = count($node);
	if($count>0) return $node[0]->nodeValue;
	return $default;
}


function updateVod(){
	global $cfg_basehost,$dsql;
	//加载XML内容
	$url = $cfg_basehost."/xstat";
	$content = curl_get($url);

	$updateSql = "UPDATE `seacms`.`sea_vod` SET `stat` = '0'";
	if(!$dsql->ExecuteNoneQuery($updateSql))
	{
		return;
	}

	$doc=new DOMDocument();
	$doc->loadXML($content);
	$root=$doc->documentElement;
	if($root!=NULL){

		$items = $root->getElementsByTagName('application');
		foreach($items as $key=>$val){
		
			$app_name =  getNodeValue($val->getElementsByTagName('name'));
			$count = getNodeValue($val->getElementsByTagName('count'));
			$can_rec = 0;
			if($count != 0) $can_rec = 1;
		
			$streams =  $val->getElementsByTagName('stream');

			foreach($streams as $key=>$val1){
				$stream_name =  getNodeValue($val1->getElementsByTagName('name'));
				$width =  getNodeValue($val1->getElementsByTagName('width'),"0");
				$height =  getNodeValue($val1->getElementsByTagName('height'),"0");
				$file =  getNodeValue($val1->getElementsByTagName('file'),"");
				$stat = 1;
				if(strlen($file)>0){
					$stat = 2;
				}
				$vod = $dsql->GetOne("select id from `sea_vod` where `app_name` = '$app_name' and `stream_name` = '$stream_name'");

				if(!$vod) {
					$insertSql = "INSERT INTO `sea_vod` (`title`, `app_name`, `stream_name`, `width`, `height`, `can_rec`, `file_name`, `stat`) VALUES ('新直播流', '$app_name', '$stream_name', '$width', '$height', '$can_rec', '$file', '$stat')";
					$dsql->ExecuteNoneQuery($insertSql);
				}else{
					$dsql->ExecuteNoneQuery("UPDATE `sea_vod` SET  `width` = '$width', `height` = '$height', `can_rec` = '$can_rec', `file_name` = '$file', `stat` = '$stat' WHERE `id` = ".$vod['id']);
				}
			}
		}
	}
}



try{
	updateVod();
}
catch(Throwable $e){
	echo '错误: ' .$e->getMessage();
}

?>