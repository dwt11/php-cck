<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();


$createtime = time();
$jbordercode = $CLIENTID . $createtime . ($jbnum * 1000);
$jbnum100=$jbnum*100;
//$jbnum100=100;



$sqladdorder = "INSERT INTO `#@__clientdata_jb_pay_t` ( `clientid`, `jbnum`, `createtime`, `code`, `stat`)
              VALUES ('$CLIENTID', '$jbnum100', '$createtime', '$jbordercode', '0');";
$aa=$dsql->ExecuteNoneQuery($sqladdorder);
//dump($aa);
//dump($sqladdorder);



$openId = GetClientOpenID($CLIENTID);//获取 客户信息 openid
//dump($openId);
/*---------------------------------------------微信支付*/
$jsApiParameters= GetJsApiParameters("在线充值", $jbordercode, $jbnum100, $openId, "http://xxxx.com/lyapp/pay/wx_notify_jb.php");
echo(json_encode($jsApiParameters));

//echo $jsApiParameters;