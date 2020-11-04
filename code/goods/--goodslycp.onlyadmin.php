<?php
require_once('../config.php');

//$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName("/goods/goods.php") . "ENV_GOBACK_URL");
if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
if($isOnlyAdminDisplay=="1"){
    $isOnlyAdminDisplay="1";
}else{
    $isOnlyAdminDisplay="0";
}
$dsql->ExecuteNoneQuery("UPDATE #@__goods_addon_lycp SET isOnlyAdminDisplay='$isOnlyAdminDisplay' where goodsid IN ($id);");
ShowMsg("设置成功！", $$ENV_GOBACK_URL);
exit();
