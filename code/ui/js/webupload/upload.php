<?php
require_once("../../../config.php");
if (!isset($pater_input_name) || $pater_input_name == "") {
    echo "无上级INPUT名称,不可用";
    exit;
}

if (!isset($oldpic)) $oldpic = "";//旧图片
if (!isset($fileSize)) $fileSize = "";//大小限制
if (!isset($fileType)) $fileType = "";//大小限制
if (!isset($dirname_plus)) $dirname_plus = "";//文件要保存的目录,在uploads目录下


$photo = "defaultpic.gif";
if ($oldpic != "") $photo = $oldpic;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<link href="../../css/bootstrap.min.css" rel="stylesheet">
<link href="webuploader.css" rel="stylesheet">
<body style="  ">
<div id="uploader-demo" class="file-item thumbnail" style="background-color:#ffffff;min-height: 170px;max-height:170px;min-width: 200px;max-width: 200px;text-align: center">
    <!--用来存放item-->
    <div id="fileList">
        <div id="WU_FILE_0" style="min-height: 110px;min-width:110px;">
            <img src="<?php echo $photo; ?>" style="width: 110px;height: 110px">
        </div>
    </div>
    <div style="margin-top: 10px;">
        <a id="filePicker">选择图片</a>
        <a id="upBtn" class="webuploader-pick" style="display: none">开始上传</a><!---->
    </div>
</div>
<script src="../jquery.min.js"></script>
<script src="webuploader.min.js"></script>
<script>
    var fileSize = '<?php echo $fileSize?>';
    ;//1MB//文件大小
    var fileType = '<?php echo $fileType?>';
    ;//1MB//文件类型  jpg可压缩,其他图片不可以 gif,jpg,jpeg,bmp,png,php
    var pater_input_name = '<?php echo $pater_input_name?>';
    var dirname_plus = '<?php echo $dirname_plus?>';
</script>


<script src="config.js"></script>
<script src="../plugins/layer/layer.min.js"></script>

</body>
</html>