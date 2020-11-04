<?php
require_once('../config.php');

//$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
if($status=="1"){
    $status="1";
}else{
    $status="0";
}
$dsql->ExecuteNoneQuery("UPDATE #@__goods SET `status`='$status' WHERE id IN ($id);");
ShowMsg("设置成功！", $$ENV_GOBACK_URL);
exit();
