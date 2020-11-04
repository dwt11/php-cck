<?php
require_once("../../../config.php");

$upfile_name = AdminUpload('file', 'imagelit', 0, false, "", $dirname_plus);
//  echo $this->getFileExt()."-----";
//echo $upfile;

$sueecss = "false";
$info = "";
if ($upfile_name == '-1') {
    $info = "找不到上传文件或大小超限";
} else if ($upfile_name == '-2') {
    $info = "未知错误";
} else if ($upfile_name == '0') {
    $info = "文件类型错误";
} else {

    $sueecss = "true";


}

$response = array(
    'success' => true,
    'filePath' => $upfile_name,
    'info' => $info
    // 'fileSize' => $_FILES['file']['size'],
    //'fileSuffixes' => $pathInfo['extension'],
    //'file_id' => ''
);

die(json_encode($response));
 

// Return Success JSON-RPC response
die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');