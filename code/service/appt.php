<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


$whereSql = " WHERE (#@__order.isdel=0 OR #@__order.isdel=4 ) AND  #@__order_addon_lycp.isdel=0  AND #@__order.sta=1  ";
//dump($sta);
$sta = isset($sta) ? $sta : "1";//默认显示未出行的
//$line_where = "";//搜索条件中线路的查询SQL
//未出行，预约时间是当前时间以后的
$nowtime_int=time();
if ($sta == '1') {
    $whereSql .= " AND  #@__order_addon_lycp.appttime>=$nowtime_int ";
}
//已出行，预约时间是当前时间以后的
if ($sta == '2') {
    $whereSql .= " AND  #@__order_addon_lycp.appttime<$nowtime_int ";
}

//商品名称
if (empty($k_goods_name)) $k_goods_name = '';
if ($k_goods_name != "")  $whereSql .= "AND  goods.goodsname LIKE '%$k_goods_name%' ";


//乘车人姓名
if (empty($k_client_realname)) $k_client_realname = '';
if ($k_client_realname != "") {
    $whereSql .= " AND    #@__order_addon_lycp.realname = '$k_client_realname' ";
    //$whereSql .= " or #@__order_addon_lycp.tel LIKE '%$k_client_realname_tel%' ";
    //$whereSql .= " or #@__order_addon_lycp.idcard LIKE '%$k_client_realname_tel%' )";

}
//乘车人电话
if (empty($k_client_tel)) $k_client_tel = '';
if ($k_client_tel != "") {
    $whereSql .= " AND   #@__order_addon_lycp.tel = '$k_client_tel' ";
    //$whereSql .= " or #@__order_addon_lycp.tel LIKE '%$k_client_realname_tel%' ";
    //$whereSql .= " or #@__order_addon_lycp.idcard LIKE '%$k_client_realname_tel%' )";

}

/*
//司机
if (empty($k_driver_realanme)) $k_driver_realanme = '';
if ($k_driver_realanme != "")  $whereSql .= "AND  EMP1.emp_realname LIKE '%$k_driver_realanme%' ";

//乘务
if (empty($k_guide_realname)) $k_guide_realname = '';
if ($k_guide_realname != "")  $whereSql .= "AND  EMP2.emp_realname LIKE '%$k_guide_realname%' ";*/

//上车站点
if (empty($k_tjsite)) $k_tjsite = '';
if ($k_tjsite != "")  $whereSql .= "AND  #@__order_addon_lycp.tjsite LIKE '%$k_tjsite%' ";
/*
//车牌号
if (empty($k_device_name)) $k_device_name = '';
if ($k_device_name != "")  $whereSql .= "AND  #@__device.devicename LIKE '%$k_device_name%' ";*/

//订单号
if (empty($ordernum)) $ordernum = '';
if ($ordernum != "")  $whereSql .= "AND  ordernum LIKE '%$ordernum%' ";

//检票
if (!isset($iscc)) $iscc = '';
if ($iscc != "")  $whereSql .= "AND  iscc ='$iscc' ";



$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";


if ($startdate != "") {
    $startdate1 = $startdate . " 00:00:00";
    $startdate1_int =GetMkTime($startdate1);
    $whereSql .= " AND #@__order_addon_lycp.appttime>=$startdate1_int";
}

if ($enddate != "") {
    $enddate1 = $enddate . " 23:59:59";
    $enddate1_int =GetMkTime($enddate1);
    //$enddate1=$enddate;
    $whereSql .= " AND #@__order_addon_lycp.appttime<=$enddate1_int";
}





$query = "
SELECT 
#@__order_addon_lycp.realname,
#@__order_addon_lycp.tel,
#@__order_addon_lycp.idcard,
#@__order_addon_lycp.orderlistztcid,
#@__order_addon_lycp.seatNumber,
#@__order_addon_lycp.tjsite,
#@__order_addon_lycp.iscc,
#@__order_addon_lycp.info,
#@__order_addon_lycp.infodate,
#@__order_addon_lycp.infooperatorid,
goods.goodsname,goods.goodscode,goods.litpic, 
        #@__line.gotime,lycp.gosite,#@__line.tmp,
        #@__order.desc,#@__order.createtime ,#@__order.ordernum
        FROM #@__order_addon_lycp
INNER JOIN #@__line  ON #@__line.id=#@__order_addon_lycp.lineid
INNER JOIN #@__goods goods ON goods.id=#@__line.goodsid
INNER JOIN #@__goods_addon_lycp lycp ON goods.id=lycp.goodsid
INNER JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid


$whereSql
  ORDER BY   #@__order.createtime DESC  ";
  // ORDER BY   #@__order_addon_lycp.seatNumber asc ,#@__order.createtime DESC ";





  //dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('k_goods_name', $k_goods_name);
$dlist->SetParameter('k_client_realname', $k_client_realname);
$dlist->SetParameter('k_client_tel', $k_client_tel);
//$dlist->SetParameter('k_driver_realanme', $k_driver_realanme);
//$dlist->SetParameter('k_guide_realname', $k_guide_realname);
$dlist->SetParameter('k_tjsite', $k_tjsite);
//$dlist->SetParameter('k_device_name', $k_device_name);
$dlist->SetParameter('enddate', $enddate);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('sta', $sta);
$dlist->SetParameter('ordernum', $ordernum);
$dlist->SetParameter('iscc', $iscc);

//模板
if (empty($s_tmplets)) $s_tmplets = 'appt.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

