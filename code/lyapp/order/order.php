<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
CheckRank();

$whereSql = "  ";
//是否交易过
$sta = isset($sta) ? $sta : "";
if ($sta != "") {
    if ($sta == 0) $whereSql .= "AND #@__order.sta=0 ";//未支付
    if ($sta == 1) $whereSql .= "AND #@__order.sta=1 ";//未支付
}

$query = "SELECT #@__order.ordertype,#@__order.ordernum,#@__order.total,#@__order.isdel,#@__order.createtime,#@__order.sta,#@__order.id
          ,#@__order.paynum,#@__order.jfnum,#@__order.jbnum
          FROM
          #@__order 
          WHERE #@__order.clientid='$CLIENTID'  AND  (#@__order.isdel=0 OR #@__order.isdel=4 )   $whereSql ORDER BY #@__order.createtime DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 5;
$dlist->SetTemplate("order.htm");
$dlist->SetSource($query);
$dlist->Display();



