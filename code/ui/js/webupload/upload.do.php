<?php
require_once("../../../config.php");

$upfile_name = AdminUpload('file', 'imagelit', 0, false, "", $dirname_plus);
//  echo $this->getFileExt()."-----";
//echo $upfile;

$sueecss = "false";
$info = "";
if ($upfile_name == '-1') {
    $info = "�Ҳ����ϴ��ļ����С����";
} else if ($upfile_name == '-2') {
    $info = "δ֪����";
} else if ($upfile_name == '0') {
    $info = "�ļ����ʹ���";
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