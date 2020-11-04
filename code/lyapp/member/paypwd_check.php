<?php
require_once(dirname(__FILE__) . "/../include/config.php");

if (empty($dopost)) $dopost = '';
CheckRank();
$paypwd = substr(md5($paypwd), 5, 20);
$sql = "SELECT clientid FROM `#@__client_pw`  WHERE  clientid='$CLIENTID' and paypwd='$paypwd' ";
$chRow = $dsql->GetOne($sql);
//dump($sql);
if(is_array($chRow)){
    echo "支付密码正确";
}
exit();
