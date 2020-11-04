<?php
//随机生成一个红包
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
set_time_limit(0);
$query = "SELECT clientid FROM `#@__clientdata_coupon` ORDER BY clientid DESC limit 0,1";
$row = $dsql->GetOne($query);
$clientid=$row["clientid"]+1;
dump($clientid);
CreateCoupon(14228);
