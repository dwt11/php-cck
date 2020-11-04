<?php
require_once('../config.php');

if (empty($dopost)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = trim(preg_replace("#[^0-9]#", '', $id));

if ($dopost == 'del') {
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    if ($id == '') {
        ShowMsg("参数无效！", $$ENV_GOBACK_URL);
        exit();
    }

    $row = $dsql->GetOne("SELECT `jbnum` FROM `#@__clientdata_extractionlog` WHERE id='$id'");
    $jbnum100 = $row['jbnum'];

    //删除前 恢复扣除用户的金币
    $istrue = Update_jb($clientid, "$jbnum100", "删除提现明细，恢复金币", 0, $CUSERLOGIN->userID);

    $query = "UPDATE `#@__clientdata_extractionlog` SET `isdel`='1' WHERE (`id`='$id')";

    if ($dsql->ExecuteNoneQuery($query)) {

        ShowMsg("成功删除！", $$ENV_GOBACK_URL);
        exit();

    } else {
        ShowMsg("删除失败！", $$ENV_GOBACK_URL);
        exit();

    }

}





