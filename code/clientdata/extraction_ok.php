<?php
require_once('../config.php');

if (empty($id)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

$passtime = time();
$query = "UPDATE `#@__clientdata_extractionlog` SET status='1' , passtime='$passtime',operatorid='{$CUSERLOGIN->userID}' WHERE id='$id'";

if ($dsql->ExecuteNoneQuery($query)) {

    ShowMsg("审核通过！", $$ENV_GOBACK_URL);
    exit();

} else {
    ShowMsg("审核失败！", $$ENV_GOBACK_URL);
    exit();

}






