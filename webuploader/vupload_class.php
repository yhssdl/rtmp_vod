<?php
/**
 *
 * 版权所有：重庆市环境保护信息中心
 * 作    者：Sqc
 * 日    期：2016-12-06
 * 版    本：1.0.0
 * 功能说明：用于视频等上传。
 *
 **/
namespace Org\Util;
Class Vupload
{   
    //要配置的内容
    private $save_path = "/uploads/media";
    private $allowtype = array('jpg', 'gif', 'png', 'mp4', 'mp3','3gp','rmvb','mov','avi','m4v');
    private $maxsize = 1024 * 1024 * 1024 * 10;//最大文件大小10G
    private $israndname = false; //是不是使用随机文件名，为true时使用原文件名
 
    private $originName;
    private $tmpFileName;
    private $fileType;
    private $fileSize;
    private $newFileName;
    private $errorNum = 0;
    private $errorMess = "";
    private $indexOfChunk = 0;
 

    /**
     * 用于设置成员属性($path, $allowtype, $maxsize, $israndname)
     * 可以通过连贯操作一次设置多个属性值
     * @param $key  成员属性（不区分大小写）
     * @param $val  为成员属性设置的值
     * @return object 返回自己对象$this, 可以用于连贯操作
     */
    function set($key, $val){
        $key = strtolower($key);
        if (array_key_exists($key, get_class_vars(get_class($this)))){
            $this->setOption($key, $val);
        }
        return $this;
    }
 
    /**
     * 调用该方法上传文件
     * Enter description here ...
     * @param $fileField    上传文件的表单名称
     *
     */
    function upload($fileField, $info){
    
        $this->checkChunk($info);
        
        if (!$this->checkFilePath($_SERVER['DOCUMENT_ROOT'].$this->save_path)){
            $this->errorMess = $this->getError();
            return false;
        }      
        
        //将文件上传的信息取出赋给变量
        $name = $_FILES[$fileField]['name'];
        $tmp_name = $_FILES[$fileField]['tmp_name'];
        $size = $_FILES[$fileField]['size'];
        $error = $_FILES[$fileField]['error'];
 
 
        //设置文件信息
        if ($this->setFiles($name, $tmp_name, $size, $error)){
        
            //如果是分块，则创建一个唯一名称的文件夹用来保存该文件的所有分块
            $path = $_SERVER['DOCUMENT_ROOT'].$this->save_path;

            if($info){
                $path .= '/'. md5($name);
                    if(!$this->checkFilePath($path)){
                    $this->errorMess = $this->getError();
                    return false;
                }
            }
            //创建一个对应的文件，用来记录上传分块文件的修改时间，用于清理长期未完成的垃圾分块
            touch($path.'.tmp');

            if($this->checkFileSize() && $this->checkFileType()){
                $this->setNewFileName(true);
                if ($this->copyFile($path . '/' .$this->newFileName)){
                    return $this->newFileName;
                }
            }
        }
 
        $this->errorMess = $this->getError();
        return false;
    }
 
    //合并文件
    public function chunksMerge($uniqueFileName, $chunksTotal, $fileExt,$size){

        if (!$this->checkFilePath($_SERVER['DOCUMENT_ROOT'].$this->save_path)){
            $this->errorMess = $this->getError();
            return false;
        }

        $this->setOption('originName', $uniqueFileName);

        $md5_uniqueFileName = md5($uniqueFileName);
        $targetDir = $_SERVER['DOCUMENT_ROOT'].$this->save_path.'/'.$md5_uniqueFileName;
        //检查对应文件夹中的分块文件数量是否和总数保持一致
        if($chunksTotal >= 1 && (count(scandir($targetDir)) - 2) == $chunksTotal){
            //同步锁机制
            $lockFd = fopen($_SERVER['DOCUMENT_ROOT'].$this->save_path.'/'.$md5_uniqueFileName.'.lock', "w");
            if(!flock($lockFd, LOCK_EX | LOCK_NB)){
                fclose($lockFd);
                return false;
            }
 
            $this->fileType = $fileExt;
            //创建日期分类文件夹
            if(!$this->checkFilePath($_SERVER['DOCUMENT_ROOT'].$this->save_path.'/'.date('Y-m-d'))){
                $this->errorMess = $this->getError();
                return false;
            }

            $this->setNewFileName(false);
            
            //进行合并
            $finalName = $_SERVER['DOCUMENT_ROOT'].$this->save_path.'/'.($this->setOption('newFileName', date('Y-m-d').'/'.$this->newFileName));
            $file = fopen($finalName, 'wb');
            $bytes = 0;
            for($index = 0; $index < $chunksTotal; $index++){
                $tmpFile = $targetDir.'/'.$index;
                $chunkFile = fopen($tmpFile, 'rb');
                $content = fread($chunkFile, filesize($tmpFile));
                fclose($chunkFile);
                $bytes += fwrite($file, $content);
 
                //删除chunk文件
                unlink($tmpFile);
            }
            fclose($file);

            if($bytes != $size){
                unlink($finalName);
                return false;
            }

            //删除chunk文件夹
            rmdir($targetDir);
            unlink($_SERVER['DOCUMENT_ROOT'].$this->save_path.'/'.$md5_uniqueFileName.'.tmp');
 
            //解锁
            flock($lockFd, LOCK_UN);
            fclose($lockFd);
            unlink($_SERVER['DOCUMENT_ROOT'].$this->save_path.'/'.$md5_uniqueFileName.'.lock');
 
            return $this->newFileName;
 
        }
        return false;
    }
 
    //获取上传后的文件名称
    public function getFileName(){
        return $this->newFileName;
    }
 
    //上传失败后，调用该方法则返回，上传出错信息
    public function getErrorMsg(){
        return $this->errorMess;
    }
 
    //设置上传出错信息
    public function getError(){
        $str = "上传文件<font color='red'>{$this->originName}</font>时出错：";
        switch ($this->errorNum) {
            case 4:
                $str.= "没有文件被上传";
                break;
            case 3:
                $str.= "文件只有部分被上传";
                break;
            case 2:
                $str.= "上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值";
                break;
            case 1:
                $str.= "上传的文件超过了php.ini中upload_max_filesize选项限制的值";
                break;
            case -1:
                $str.= "未允许的类型";
                break;
            case -2:
                $str.= "文件过大， 上传的文件夹不能超过{$this->maxsize}个字节";
                break;
            case -3:
                $str.= "上传失败";
                break;
            case -4:
                $str.= "建立存放上传文件目录失败，请重新指定上传目录";
                break;
            case -5:
                $str.= "必须指定上传文件的路径";
                break;
 
            default:
                $str .= "未知错误";
        }
        return $str."<br>";
    }
 
 
    //设置和$_FILES有关的内容
    private function setFiles($name="", $tmp_name="", $size=0, $error=0){
        $this->setOption('errorNum', $error);
        if ($error) {
            return false;
        }
        $this->setOption('originName', $name);
        $this->setOption('tmpFileName', $tmp_name);
        $aryStr = explode(".", $name);
        $this->setOption("fileType", strtolower($aryStr[count($aryStr)-1]));
        $this->setOption("fileSize", $size);
        return true;
    }
 
    
    private function checkChunk($info){
        if(isset($info['chunks']) && $info['chunks'] > 0){
            $this->setOption("isChunk", true);
            
            if(isset($info['chunk']) && $info['chunk'] >= 0){
                $this->setOption("indexOfChunk", $info['chunk']);
                
                return true;
            }
            
            //throw new Exception('分块索引不合法');
        }
        
        return false;
    } 
 
    //为单个成员属性设置值
    private function setOption($key, $val){
        $this->$key = $val;
        return $val;
    }
 
    //设置上传后的文件名称
    private function setNewFileName($isChunk){
        if($isChunk){     //如果是分块，则以分块的索引作为文件名称保存
            $this->setOption('newFileName', $this->indexOfChunk);
        }elseif($this->israndname) {
            $this->setOption('newFileName', $this->proRandName());
        }else{
            $this->setOption('newFileName', $this->originName);
        }
    }
 
    //检查上传的文件是否是合法的类型
    private function checkFileType(){
        if (in_array(strtolower($this->fileType), $this->allowtype)) {
            return true;
        }else{
            $this->setOption('errorNum', -1);
            return false;
        }
    }
 
 
    //检查上传的文件是否是允许的大小
    private function checkFileSize(){
        if ($this->fileSize > $this->maxsize) {
            $this->setOption('errorNum', -5);
            return false;
        }else{
            return true;
        }
    }
 
    //检查是否有存放上传文件的目录
    private function checkFilePath($target){
        
        if (empty($target)) {
            $this->setOption('errorNum', -5);
            return false;
        }
        
        if (!file_exists($target) || !is_writable($target)) {
            if (!@mkdir($target, 0755)) {
                $this->setOption('errorNum', -4);
                return false;
            }
        }

        return true;
    }
 
    //设置随机文件名
    private function proRandName(){
        $fileName = date('YmdHis')."_".rand(100,999);
        return $fileName.'.'.$this->fileType;
    }
 
    //复制上传文件到指定的位置
    private function copyFile($path){
        if (!$this->errorNum) {
            if (@move_uploaded_file($this->tmpFileName, $path)) {
                return true;
            }else{
                $this->setOption('errorNum', -3);
                return false;
            }
        }else{
            return false;
        }
    }
}
