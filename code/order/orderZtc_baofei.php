<?php
/*订单报废
会将送给上级的金币和积分扣除;
<br>会将送给自己的金币和积分扣除;
<br>会将所获得的会员类型删除;
<br>用户支付的现金\金币\积分不会恢复


订单isdel设定为10

*/

require_once('../config.php');

$orderid = trim(preg_replace("#[^0-9]#", '', $orderid));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($orderid == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
$orerid = $CUSERLOGIN->userID;
$return_str = BaofeiOrder($orderid,  $orerid);
ShowMsg($return_str, $$ENV_GOBACK_URL);
exit();
