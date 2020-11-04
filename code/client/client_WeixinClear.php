<?php
/**
 *
 *
 * @version        $Id: dep_del.php 1 14:31 2010年7月12日
 * @package        DwtX.Administrator

 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once('../config.php');

$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

$sql = "SELECT  openid        FROM #@__client_depinfos             WHERE #@__client_depinfos.clientid='$id'";
$row = $dsql->GetOne($sql);
if (is_array($row)) {
    $openid=$row["openid"];
    if($openid!="") {
        $query = "UPDATE `#@__client_depinfos` SET `openid`='' WHERE (`clientid`='$id')";
        $dsql->ExecuteNoneQuery($query);
        ShowMsg("微信解绑成功！", $$ENV_GOBACK_URL);
    }else{
        ShowMsg("微信解绑失败,未找到此用户的微信信息！", $$ENV_GOBACK_URL);
    }
}else{
    ShowMsg("微信解绑失败,未找到此用户的微信信息！", $$ENV_GOBACK_URL);
}


exit();





