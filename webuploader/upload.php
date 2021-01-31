<?php
define("ERR_NORMAL", "ERR_NORMAL");

/**
 * 文件上传
 * User: LiZheng  271648298@qq.com
 * Date: 2019/9/20
 */
class Upload
{

    /**
     * 视频上传接口, 前端的  server 填写此接口的地址即可。
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/23
     */
    public function video(){
        //file_put_contents("d:/lizheng.log", "\n\n"."files".print_r($_FILES,true),8);
        //file_put_contents("d:/lizheng.log", "\n\n"."POST".print_r($_POST,true),8);
        //file_put_contents("d:/lizheng.log", "\n\n"."换行".print_r("123",true),8);

        /**
         * 接收参数
         */
        //根据上下文的不同执行不同的操作，存放到不同的目录(后期可以执行不同的操作，比如生成多个尺寸的缩略图),
        // 这里context 和from 字段是和前台对应的，以及生成存储地址时也用了， 如果修改请注意一起修改。
        $post['context'] = $_POST['context'];//1是用户上传，2是设备自动上传
        $post['from'] = $_POST['from'];      //来源
        $post['id'] = $_POST['id'];
        $post['name'] = $_POST['name'];
        $post['type'] = $_POST['type'];
        $post['lastModifiedDate'] = $_POST['lastModifiedDate'];
        $post['size'] = $_POST['size'];
        $post['chunks'] = $_POST['chunks'];
        $post['chunk'] = $_POST['chunk'];

        $data = array();
        //前台 file -> input框中的 name。 $mark[0]即后台 $_FILE数组的最外层索引
        $mark = array_keys($_FILES);
        // 上传文件的类型
        $fileType = $this->getType($post['type']);
        // 允许的上传类型, 则继续 //允许的上传视频类型
        if(!in_array($fileType, array('mp4', 'wma', 'avi', 'rm', 'rmvb', 'flv', 'mpg', 'mov', 'mkv'))) {
            echo "不支持的文件类型"; exit;  // 输出错误信息，请自定义
        }

        // 根据上下文和文件MD5值，获取一个临时的存储路径，用于存放chunks, 7天清理一次
        $tmpPath = $this -> getTmpPath($post);

        // 实例化文件上传类，并初始化, 主要用于文件切片的暂时存储， 当所有文件切片上传后进行合并以及删除所有的文件切片
        $upload = new Chunk($mark[0], $tmpPath);
        // 上传并接收分片文件
        $data['video_chunk_url'] = $upload -> uploadChunkFile($post);

        // 判断是否每个分片都上传成功
        if(is_array($data['video_chunk_url'])) {
            //如果是数组，则说明是错误， 直接返回错误信息
            $this -> ajax_error(ERR_NORMAL, $data['video_chunk_url']['reason']); exit;
        }else if(!$data['video_chunk_url']) {
            $this -> ajax_error(ERR_NORMAL, '文件'.$post['chunk'].'上传失败'); exit;
        }

        // 判断是否最后一个分片， 如果是， 则合并分片
        if($post['chunk'] == $post['chunks']-1){
            // 根据上下文不同获取文件真正存储的路径
            $path = $this ->getVideoPath($post['context'], $post['from']);
            // 合并分片文件，存储到真正的存储地址， 并删除无用的分片文件。
            $data['video_url'] = $upload -> mergerChunk($post, $tmpPath, $path);
            if(!$data['video_url'])
            {
                $this -> ajax_error(ERR_NORMAL, '合并文件分片时出错！');
            }


            // TODO  生成封面图和缩略图
            // 如需生成封面图， 可使用 ffmpeg， 需要下载该软件，建议直接使用命令调用该软件， 虽然有 php-ffmpeg插件，但貌似不支持php7，
            // 该软件同时支持 视频 和 图片 生成缩略图，下载，配置好环境变量后，只需执行一条命令即可。


            // 进行路径裁剪，去掉/uploads 相对路径（/uploads 我作为文件服务器存放文件的根目录，所有静态文件通过nginx指到此目录）
            $data['video_url'] = substr($data['video_url'], strlen($_SERVER['DOCUMENT_ROOT']));
            //拼接存放图片的服务器域名    绝对路径
            echo  $data['video_url'];
        }
        else
        {
            //echo "uploading";
           // $this -> ajax_succ(array('data' => array()));
        }

    }

    /**
     * 从$_FILE中的type中获取文件类型;   比如由 image/jpeg 得到 jpeg
     * @param $type
     * @return bool|string
     * User: LiZheng  271648298@qq.com
     * Date: 2018/12/15
     */
    private function getType($type){
        return substr($type,strpos($type,'/') + 1);
    }



    /**
     * 根据不同上下文选择不同的存储路径
     * @param $context     图片上下文
     * @param $from        图片来源，来自哪个平台应用
     * @return bool|string
     * User: LiZheng  271648298@qq.com
     * Date: 2018/12/15
     */
    private function getPath($context, $from){
        //服务器类型, WIN是本地，否则linux
        $root_dir = $_SERVER['DOCUMENT_ROOT'];
        //是否存在图片根目录，不存在则创建
        if(!is_dir($root_dir))
        {
            //权限是否OK
            mk_dir($root_dir, 0777, true) && chmod($root_dir, 0777);
        }

        //根据来源，分组
        if(!$from)
        {
            $path = $root_dir.'/common';  //未标明来源from，则放到公共的common文件夹下
        }else
        {
            $path = $root_dir.'/'.$from;  //根据应用的不同，进行区分
        }

        //根据图片用途分组，并根据日期归类
        switch ($context)
        {
            case 0:
                $path .= '/picture/'.date('Y-m-d',time());
                break;
            case 1:
                $path .= '/avatar/'.date('Y-m-d',time());
                break;
            case 2:
                $path .= '/picture/'.date('Y-m-d',time());
                break;
            case 3:
                $path .= '/license/'.date('Y-m-d',time());
                break;
            case 4:
                $path .= '/idcard/'.date('Y-m-d',time());
                break;
            default:
                $path .= 'picture/'.date('Y-m-d',time());
                break;
        }
        return $path;
    }

    /**
     * 根据不同上下文选择不同的存储路径
     * @param $context
     * @param $from
     * @return string
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    private function getVideoPath($context, $from){
        //服务器类型, WIN是本地，否则linux
        $root_dir = $_SERVER['DOCUMENT_ROOT'];

        //根据来源，分组
        if(!$from)
        {
            $path = $root_dir.'/common';  //未标明来源from，则放到公共的common文件夹下
        }else
        {
            $path = $root_dir.'/'.$from;  //根据应用的不同，进行区分
        }

        //根据图片用途分组，并根据日期归类
        switch ($context)
        {
            case 0:
                $path .= '/media/'.date('Y-m-d',time());
                break;
            case 1:
                $path .= '/upload/'.date('Y-m-d',time());
                break;
            case 2:
                $path .= '/device/'.date('Y-m-d',time());
                break;
            default:
                $path .= 'video/'.date('Y-m-d',time());
                break;
        }
        return $path;
    }

    /**
     * 根据不同上下文生成临时存放chunks的目录
     * @param $post
     * @return string
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    private function getTmpPath($post){
        //服务器类型, WIN是本地，否则linux
        $root_dir = $_SERVER['DOCUMENT_ROOT'];

        //根据来源，分组
        if(!$post['from'])
        {
            $path = $root_dir.'/uploads/tmp/common';  //未标明来源from，则放到公共的common文件夹下
        }else
        {
            $path = $root_dir.'/uploads/tmp/'.$post['from'];  //根据应用的不同，进行区分
        }
        //根据图片用途分组，并根据日期归类
        switch ($post['context'])
        {
            case 0:
                $path .= '/video';
                break;
            case 1:
                $path .= '/upload';
                break;
            case 2:
                $path .= '/device';
                break;
            default:
                $path .= '/video';
                break;
        }
        //$path .= $post['md5'];
        return $path;
    }

    public function getThumb($path,$with,$height,$_path,$size,$type){
        $jpgResize = new lib_resizeimage($path, $with, $height, false, $_path.'_'.$size.'_'.$with.'_'.$height.'.'.$type);
        return $_path.'_'.$size.'_'.$with.'_'.$height.'.'.$type;
    }

    /**
     * 自动判断是否错误， 如果错误 -> 调用ajax错误输出
     * @param $result
     * @param $return
     * @param $delete
     * @return bool  在结果是真的情况下，是否直接返回数据
     * User: LiZheng  271648298@qq.com
     * Date: 2018/12/25
     */
    public function ajax_validate($result, $return = false, $delete = 0)
    {
        if($result['result'] == 'fail')
        {
            if(0 === $delete)
            {
                $this -> ajax_error(ERR_NORMAL, $result['reason'], array()); exit;
            }else
            {
                $this -> ajax_error(ERR_NORMAL, '删除失败', array()); exit;
            }
        }
        if($return)
        {
            $this -> ajax_succ(array('msg' => '成功', 'data' => $result['info'])); exit;
        }
        //不会调用ajax_succ，因为返回格式差异化比较大，重用性差
        return true;
    }
    /**
     * ajax正确输出
     * @param array $response  相应信息
     * @param array $response['data']  输出数据
     * @param string $response['msg']  返回提示信息（非必须）
     * User: LiZheng  271648298@qq.com
     * Date: 2018/11/15
     */
    public function ajax_succ($response=array())
    {
        $result = array();
        $result['code'] = 200;
        if(!isset($response['msg']))
        {
            $result['msg'] = '';
        }
        $result = array_merge($result,$response);
        //插入日志
        //$this -> json_output($res);
        self::arrUrlEncode($result);
        $output['code'] = (int)$result['code'];
        echo urldecode ( json_encode ($result) );
        exit();
    }

    private static function arrUrlEncode(&$arr)
    {
        foreach ( $arr as $key => $value )
        {
            if(is_array($value))
            {
                self::arrUrlEncode($arr[$key]);
            }
            else
            {
                $arr[$key] = urlencode ( str_replace(
                    array("\r\n", "\r", "\n","\t", '\r\n'),
                    array('', '', '', '', '<br />'),
                    $value) );
            }
        }
    }

    /**
     * ajax错误输出
     * @param int $code 错误状态码
     * @param string $msg 错误原因
     * @param array $detail 错误原因
     */
    public function ajax_error($code = ERR_NORMAL, $msg = '', $detail = array())
    {
        $result = array();
        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['data'] = $detail;

        //插入日志
        //$this -> json_output($result);

        echo json_encode($result);
        exit;

    }
}


/**
 * @author	LiZheng
 * @todo 文件上传类
 * @version	v1.0.0
 */
class Chunk
{
    protected $fileName;
    protected $maxSize;
    protected $allowMime;
    protected $allowExt;
    protected $uploadPath;
    protected $imgFlag;
    protected $videoFlag;
    protected $fileInfo;
    protected $error;
    protected $ext;
    protected $destination;
    protected $uniName;

    /**
     * lib_upload constructor.
     * @param string $fileName
     * @param string $tmpPath
     * @param bool $videoFlag
     * @param int $maxSize
     * @param array $allowExt
     * @param array $allowMime
     */
    public function __construct($fileName='file',$tmpPath='resources//chunk',$videoFlag=true,$maxSize=5242880,$allowExt=array('mp4','mpeg','webm'),$allowMime=array('application/octet-stream', 'video/mp4','video/mpeg','video/webm')){
        $this->fileName=$fileName;          // 文件数组的名称
        $this->maxSize=$maxSize;            // 最大文件的尺寸
        $this->allowMime=$allowMime;        // 允许的mime类型
        $this->allowExt=$allowExt;          // 允许的后缀
        $this->uploadPath=$tmpPath;         // 上传路径（临时）
        $this->videoFlag=$videoFlag;            // 某个标识
        $this->fileInfo=$_FILES[$this->fileName]; // 文件信息

    }
    /**
     * 检测上传文件是否出错
     * @return boolean
     */
    protected function checkError(){
        if(!is_null($this->fileInfo)){
            if($this->fileInfo['error']>0){
                switch($this->fileInfo['error']){
                    case 1:
                        $this->error='超过了PHP配置文件中upload_max_filesize选项的值';
                        break;
                    case 2:
                        $this->error='超过了表单中MAX_FILE_SIZE设置的值';
                        break;
                    case 3:
                        $this->error='文件部分被上传';
                        break;
                    case 4:
                        $this->error='没有选择上传文件';
                        break;
                    case 6:
                        $this->error='没有找到临时目录';
                        break;
                    case 7:
                        $this->error='文件不可写';
                        break;
                    case 8:
                        $this->error='由于PHP的扩展程序中断文件上传';
                        break;

                }
                return false;
            }else{
                return true;
            }
        }else{
            $this->error='文件上传出错';
            return false;
        }
    }
    /**
     * 检测上传文件的大小
     * @return boolean
     */
    protected function checkSize(){

        if($this->fileInfo['size']>$this->maxSize){
            $this->error='上传文件过大';
            return false;
        }
        return true;
    }
    /**
     * 检测扩展名
     * @return boolean
     */
    protected function checkExt(){
        $this->ext=strtolower(pathinfo($this->fileInfo['name'],PATHINFO_EXTENSION));
        if(!in_array($this->ext,$this->allowExt)){
            $this->error='不允许的扩展名';
            return false;
        }
        return true;
    }
    /**
     * 检测文件的类型
     * @return boolean
     */
    protected function checkMime(){
        if(!in_array($this->fileInfo['type'],$this->allowMime)){
            $this->error='不允许的文件类型';
            return false;
        }
        return true;
    }


    /**
     * 检测是否是真实图片
     * @return bool
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    protected function checkTrueImg(){
        if($this->imgFlag){
            if(!@getimagesize($this->fileInfo['tmp_name'])){
                $this->error='不是真实图片';
                return false;
            }
            return true;
        }
    }
    /**
     * 检测是否通过HTTP POST方式上传上来的
     * @return boolean
     */
    protected function checkHTTPPost(){
        if(!is_uploaded_file($this->fileInfo['tmp_name'])){
            $this->error='文件不是通过HTTP POST方式上传上来的';
            return false;
        }
        return true;
    }
    /**
     *显示错误
     */
    protected function showError(){
        echo('<span style="color:red">'.$this->error.'</span>');
    }
    /**
     * 检测目录不存在则创建
     */
    protected function checkUploadPath(){
        if(!file_exists($this->uploadPath)){
            mkdir($this->uploadPath,0777,true);
        }
    }

    /**
     * 获取chunk的序号
     * @param $post
     * @return mixed
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    protected function getChunkName($post)
    {
        return $post['chunk'];
    }
    /**
     * 产生唯一字符串
     * @return string
     */
    protected function getUniName(){
        return md5(uniqid(microtime(true),true));
    }

    /**
     * 上传文件
     * @param $post
     * @return array|string
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    public function uploadChunkFile($post){
        if($this->checkError()&&$this->checkSize()&&$this->checkExt()&&$this->checkMime()&&$this->checkHTTPPost()){
            $this->checkUploadPath();  //检测目录不存在则创建
            $this->uniName=$this->getChunkName($post);  //产生唯一chunk名称
            $this->destination=$this->uploadPath.'/'.$this->uniName.'.'.$this->ext;
            if(@move_uploaded_file($this->fileInfo['tmp_name'], $this->destination)){
                return  $this->destination;
            }else{
                $this->error='文件移动失败';
                return array('result'=>'fail','reason'=>$this->getError());
            }
        }else{
            return array('result'=>'fail','reason'=>$this->getError());
        }
    }

    /**
     * 合并视频分片
     * @param $post
     * @param $tmpPath
     * @param $path
     * @return string
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    public function mergerChunk($post, $tmpPath, $path)
    {
        $video_url = $path.'/'.$post['name'];
        if(!file_exists($path)){
            mkdir($path,0777,true);
        }
        file_put_contents($video_url, file_get_contents($tmpPath."/0".'.'.$this->ext));
        for ($i=1; $i<$post['chunks'];$i++) {
            file_put_contents($video_url, file_get_contents($tmpPath.'/'.$i.'.'.$this->ext), 8);
        }
        if($this->delDirAndFile($tmpPath)) {
            return $video_url;
        }else{
            return false;
        }
    }

    public function getError()
    {
        return $this->error;
    }


    public function getFilename()
    {
        return $this->uniName.'.'.$this->ext;
    }

    /**
     * 循环删除目录和文件函数
     * @param $dirName
     * @return bool
     * User: LiZheng  271648298@qq.com
     * Date: 2019/9/25
     */
    public function delDirAndFile( $dirName )
    {
        // 打开目录句柄
        if ( $handle = opendir( "$dirName" ) ) {
            // 循环读取目录
            while ( false !== ( $item = readdir( $handle ) ) ) {
                if ( $item != "." && $item != ".." ) {
                    // 判断是否是目录
                    if ( is_dir("$dirName/$item") ) {
                        // 如果是目录，递归调用本函数
                        delDirAndFile("$dirName/$item");
                    } else {
                        // 删除文件
                        if( unlink( "$dirName/$item" ) );// echo "成功删除文件： $dirName/$item \n";
                    }
                }
            }
            // 关闭目录句柄
            closedir($handle);
            // 删除目录
            if(rmdir($dirName)){
                return true;
            }
        }
        return false;
    }
}
$upload = new Upload();
$upload->video();
?>