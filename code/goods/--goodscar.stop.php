<?php
require_once('../config.php');

//$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName("/goods/goods.php") . "ENV_GOBACK_URL");
if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
if($islock=="1"){
    $islock="1";
}else{
    $islock="0";
}
$dsql->ExecuteNoneQuery("UPDATE #@__goods_addon_car SET islock='$islock' where goodsid IN ($id);");
ShowMsg("设置成功！", $$ENV_GOBACK_URL);
exit();
