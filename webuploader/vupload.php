<?php
set_time_limit(0);
//关闭缓存
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('../data/config.cache.inc.php');
if(strlen($cfg_cmspath)>0){
	$save_path = "/".$cfg_cmspath.$cfg_upload_dir."/media";
}else{
	$save_path = "/$cfg_upload_dir/media";
}

require_once('vupload_class.php');

$uploader =  new \Org\Util\Vupload;
$uploader->set('save_path', $save_path);
//用于断点续传，验证指定分块是否已经存在，避免重复上传
if (isset($_POST['status'])) {
    if ($_POST['status'] == 'chunkCheck') {
        $target =  $_SERVER['DOCUMENT_ROOT'].$save_path . '/' . md5($_POST['name']) . '/' . $_POST['chunkIndex'];
		
        if (file_exists($target) && filesize($target) == $_POST['size']) {
            die('{"ifExist":1}');
        }
        die('{"ifExist":0}');
    } elseif ($_POST['status'] == 'chunksMerge') {
        if ($path = $uploader->chunksMerge($_POST['name'], $_POST['chunks'], $_POST['ext'], $_POST['size'])) {
            die('{"status":1, "path": "' . $save_path . '/' . $path . '"}');
        }
        die('{"status":0}');
    }
}

if (($path = $uploader->upload('file', $_POST)) !== false) {
    die('{"status":1, "path": "' . $save_path . '/' . $path . '"}');
}
die('{"status":0}');
