<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
require_once("service.function.php");
$t1 = ExecTime();

CheckRank();
$sta = isset($sta) ? $sta : "1";//默认显示正常发车的
$whereSql = "";
//未出行，预约时间是当前时间以后的
$nowtime = time();
if ($sta == '1') {
    $whereSql .= " AND #@__order_addon_lycp.appttime>={$nowtime} ";
    $orderby = " ASC";//未出游 升序排列
}
//已出行，预约时间是当前时间以后的
if ($sta == '2') {
    $whereSql .= " AND #@__order_addon_lycp.appttime<{$nowtime}";
    $orderby = " desc";//已出游 倒序排列
}


$query = getApptSQL($CLIENTID, $whereSql, $orderby,$sta);
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 10;
$dlist->SetParameter('sta', $sta);
if($sta==1)$dlist->SetTemplate("lycpAppt_w.htm");
if($sta==2)$dlist->SetTemplate("lycpAppt_y.htm");
$dlist->SetSource($query);
$dlist->Display();

$t2 = ExecTime();
//echo $t2-$t1;

