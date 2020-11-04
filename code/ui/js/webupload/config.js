//如果没有默认参数,请再设定一下
if (!fileSize) fileSize = 3;//1MB//文件大小
if (!fileType) fileType = "jpg";//1MB//文件类型  jpg可压缩,其他图片不可以 gif,jpg,jpeg,bmp,png,php
if (!dirname_plus) dirname_plus = "";//1MB//文件类型  jpg可压缩,其他图片不可以 gif,jpg,jpeg,bmp,png,php
// 图片上传demo
jQuery(function () {
    var $ = jQuery,
        //$list = $('#fileList'),
        $upBtn = $('#upBtn'),
        /*  // 优化retina, 在retina下这个值是2
          ratio = window.devicePixelRatio || 1,
          // 缩略图大小我像素
        /*
                  thumbnailWidth = 100 * ratio,
                  thumbnailHeight = 100 * ratio,
          */
        thumbnailWidth = 110,
        thumbnailHeight = 110,

        // Web Uploader实例
        uploader;

    // 初始化Web Uploader
    uploader = WebUploader.create({
        //文件大小
        fileSizeLimit: 1024 * 1024 * fileSize,
        // 选完文件后，是否自动上传。
        auto: false,
        duplicate: true,//是否可重复上传
        // swf文件路径
        swf: 'Uploader.swf',
        // 文件接收服务端。
        server: 'upload.do.php?dirname_plus=' + dirname_plus,
        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#filePicker',
        // 只允许选择文件，可选。
        accept: {
            title: 'Images',
            extensions: fileType,
            mimeTypes: 'image/*'
        },
        //配置图片上传前压缩
        compress: {
            //大于此分辨率启用压缩
            width: 800,
            height: 800,

            // 图片质量，只有type为`image/jpeg`的时候才有效。
            quality: 80,

            // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
            allowMagnify: false,

            // 是否允许裁剪。
            crop: false,

            // 是否保留头部meta信息。
            preserveHeaders: true,

            // 如果发现压缩后文件大小比原来还大，则使用原来图片
            // 此属性可能会影响图片自动纠正功能
            noCompressIfLarger: false,

            // 单位字节，如果图片大小小于此值，不会采用压缩。
            //大于1024*500KB启用压缩
            compressSize: 0
        }
    });

    // 当有文件添加进来的时候
    uploader.on('fileQueued', function (file) {
        console.log(file);
        /*var $li = $(
                '<div id="' + file.id + '" class="file-item thumbnail">' +
                '<img>' +
                /!*'<div class="info">' + file.name + '</div>' +*!/
                '</div>'
            ),
            $img = $li.find('img');
        // $list为容器jQuery实例
        $list.empty();
        $list.append($li);*/

        $imgdiv = $('#WU_FILE_0');
        $imgdiv.empty();
        $imgdiv.append($('<img>'));//重新加一个IMG
        $img = $imgdiv.find('img');
        // 创建缩略图
        // 如果为非图片文件，可以不用调用此方法。
        // thumbnailWidth x thumbnailHeight 为 100 x 100
        uploader.makeThumb(file, function (error, src) {
            if (error) {
                $img.replaceWith('<span  ><b>不能预览</b></span>');
                return;
            }
            $img.attr('src', src);
            $upBtn.show();
        }, thumbnailWidth, thumbnailHeight);
    });
    /**
     * 验证文件格式以及文件大小
     */
    uploader.on("error", function (type) {
        console.log(type);
        if (type == "Q_TYPE_DENIED") {
            layer.msg("请上传" + fileType + "格式文件");
        } else if (type == "Q_EXCEED_SIZE_LIMIT") {
            layer.msg("文件大小不能超过" + fileSize + "MB");
        } else {
            layer.msg("上传出错！请检查后重新上传！错误代码" + type);
        }
    });
    /*// 文件上传过程中创建进度条实时显示。
    uploader.on('uploadProgress', function (file, percentage) {
        var $li = $('#' + file.id),
            $percent = $li.find('.progress span');

        // 避免重复创建
        if (!$percent.length) {
            $percent = $('<p class="progress"><span></span></p>')
                .appendTo($li)
                .find('span');
        }

        $percent.css('width', percentage * 100 + '%');
    });*/

    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on('uploadSuccess', function (file, response) {
        console.log(response);
        if (response.success) {
            layer.msg("上传成功");
            window.parent.$("#" + pater_input_name).val(response.filePath);
            $upBtn.hide();
        } else {
            layer.msg("上传失败");
            window.parent.$("#" + pater_input_name).val("");
        }
    });

    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function (file) {

    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadComplete', function (file) {
        $('#' + file.id).find('.progress').remove();
    });

    //点击上传按钮后开始上传
    $upBtn.on('click', function () {
        uploader.upload();
    });

});