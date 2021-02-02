
_extensions = 'mp4,rmvb,mov,avi,m4v';
_mimeTypes = 'video/*,audio/*,application/*';
_backEndUrl = '/webuploader/vupload.php';
_chunkSize = 5 * 1024 * 1024;        //分块大小




var $WebUpload = function (pickerid, infoid, inputbox) {
    this.pickerid = pickerid;
    this.infoid = infoid;
    this.inputbox = inputbox;
};


$WebUpload.prototype = {
    /**
    * 初始化webUploader
    */
    init: function () {

        WebUploader.Uploader.register({
            "before-send": "beforeSend"
        }, {
            beforeSend: function (block) {
                //分片验证是否已传过，用于断点续传
                var task = new $.Deferred();

                $.ajax({
                    type: "POST"
                    , url: _backEndUrl
                    , data: {
                        status: "chunkCheck"
                        , name: block.file.name
                        , chunkIndex: block.chunk
                        , size: block.end - block.start
                    }
                    , cache: false
                    , timeout: 1000 //todo 超时的话，只能认为该分片未上传过
                    , dataType: "json"
                }).then(function (data, textStatus, jqXHR) {
                    if (data.ifExist) {   //若存在，返回失败给WebUploader，表明该分块不需要上传
                        task.reject();
                    } else {
                        task.resolve();
                    }
                }, function (jqXHR, textStatus, errorThrown) {    //任何形式的验证失败，都触发重新上传
                    task.resolve();
                });

                return $.when(task);
            }
        });

        var uploader = this.create();
        this.bindEvent(uploader);

        return uploader;
    },

    /**
     * 创建webuploader对象
     */
    create: function () {
        var webUploader = WebUploader.create({
            swf: "Uploader.swf",
            server: _backEndUrl,     //服务器处理文件的路径
            pick: this.pickerid,        //指定选择文件的按钮，此处放的是id
            resize: false,
            disableGlobalDnd: true, //[可选] [默认值：false]是否禁掉整个页面的拖拽功能，如果不禁用，图片拖进来的时候会默认被浏览器打开。
            compress: false,
            prepareNextFile: true,
            chunked: true,
            chunkSize: _chunkSize,
            chunkRetry: 3,    //[可选] [默认值：2]如果某个分片由于网络问题出错，允许自动重传多少次？
            threads: 5,      //[可选] [默认值：3] 上传并发数。允许同时最大上传进程数。
            //formData: function () { return $.extend(true, {}, _userInfo); },
            //fileNumLimit: 100,
            fileSizeLimit: 1024 * 1024 * 1024 * 10,// 验证文件总大小是否超出限制（10G）, 超出则不允许加入队列。根据需要进行设置。除了前面几个，其它都是可选项
            fileSingleSizeLimit: 1024 * 1024 * 1024 * 10, // 验证单个文件大小是否超出限制（10G）, 超出则不允许加入队列。
            duplicate: true,
            auto: true,
            accept: {
                title: '大文件上传',  //文字描述
                extensions: _extensions,     //允许的文件后缀，不带点，多个用逗号分割。,jpg,png,
                mimeTypes: _mimeTypes,      //多个用逗号分割。image/*,
            },
        });
        return webUploader;
    },


    /**
     * 绑定事件
     */
    bindEvent: function (bindedObj) {
        var me = this;

        /**
         * 验证文件格式以及文件大小
         */
        bindedObj.on("error", function (type, handler) {
            if (type == "Q_TYPE_DENIED") {
                alert('请上传MP4格式的视频');
            } else if (type == "F_EXCEED_SIZE") {
                alert('视频大小不能超过10G');
            }
        });

        bindedObj.on("fileQueued", function (file) {
            var $info = $(me.infoid);
            $info.find('.percentage').width('0%');
            $info.find('.text').text('0%');
            $info.find('.progress').fadeIn();
        });


        bindedObj.on("uploadProgress", function (file, percentage) {
            var $info = $(me.infoid);
            $info.find('.text').text(Math.round(percentage * 100) + '%');
            $info.find('.percentage').width(Math.round(percentage * 100) + '%');

            if (Math.round(percentage * 100) == 100) {
                $info.find('.text').text('文件合并中，请稍候!');
            }
        });


        bindedObj.on("uploadComplete", function (file) {
            var chunksTotal = 0;
            if ((chunksTotal = Math.ceil(file.size / _chunkSize)) > 0) {
                //合并请求
                $.ajax({
                    type: "POST"
                    , url: _backEndUrl
                    , data: {
                        status: "chunksMerge"
                        , name: file.name
                        , chunks: chunksTotal
                        , size: file.size
                        , ext: file.ext
                    }
                    , cache: false
                    , dataType: "json"
                }).then(function (data, textStatus, jqXHR) {
                    var $info = $(me.infoid);
                    $info.find('.progress').fadeOut();
                    if (data && data.path) {
                        text = $(me.inputbox).val();
                        if (text.length > 0)
                            $(me.inputbox).val(text + "\n" + data.path);
                        else
                            $(me.inputbox).val(data.path);
                
                    }

                }, function (jqXHR, textStatus, errorThrown) {

                });
            } else {
                var $info = $(me.infoid);
                $info.find('.progress').fadeOut();
                if (data && data.path) {
                    text = $(me.inputbox).val();
                    if (text.length > 0)
                        $(me.inputbox).val(text + "\n" + data.path);
                    else
                        $(me.inputbox).val(data.path);
                }
            }
        });
    }
}

