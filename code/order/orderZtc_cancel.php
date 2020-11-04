<?php
require_once('../config.php');

$orderid = trim(preg_replace("#[^0-9]#", '', $orderid));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($orderid == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

$orerid = $CUSERLOGIN->userID;
$return_str = CancelOrderNoPay($orderid, $clientid = 0, $orerid);

ShowMsg($return_str, $$ENV_GOBACK_URL);
exit();






