<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


$whereSql = " WHERE (#@__lycp_temp_money.isdel=0  ) ";

//商品名称
if (empty($k_goods_name)) $k_goods_name = '';
if ($k_goods_name != "")  $whereSql .= "AND  goods.goodsname LIKE '%$k_goods_name%' ";


//乘车人
if (empty($k_client_realname_tel)) $k_client_realname_tel = '';
if ($k_client_realname_tel != "") {
    $whereSql .= "AND  ( #@__lycp_temp_money.realname LIKE '%$k_client_realname_tel%' ";
    $whereSql .= " or #@__lycp_temp_money.tel LIKE '%$k_client_realname_tel%' ";
    $whereSql .= " or #@__lycp_temp_money.idcard LIKE '%$k_client_realname_tel%' )";

}


//司机
if (empty($k_driver_realanme)) $k_driver_realanme = '';
if ($k_driver_realanme != "")  $whereSql .= "AND  EMP1.emp_realname LIKE '%$k_driver_realanme%' ";


//车牌号
if (empty($k_device_name)) $k_device_name = '';
if ($k_device_name != "")  $whereSql .= "AND  #@__device.devicename LIKE '%$k_device_name%' ";




$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";


if ($startdate != "") {
    $startdate1 = $startdate . " 00:00:00";
    $startdate1 = $startdate;
    $whereSql .= " AND #@__lycp_temp_money.appttime>=UNIX_TIMESTAMP('$startdate1')";
}

if ($enddate != "") {
    $enddate1 = $enddate . " 23:59:59";
    //$enddate1=$enddate;
    $whereSql .= " AND #@__lycp_temp_money.appttime<=UNIX_TIMESTAMP('$enddate1')";
}





$query = "
SELECT #@__lycp_temp_money.*,goods.goodsname,goods.goodscode,goods.litpic, 
#@__device.devicename,
        
         
        EMP1.emp_realname AS driver_realanme 
        FROM #@__lycp_temp_money
 INNER JOIN #@__goods goods ON goods.id=#@__lycp_temp_money.goodsid
  LEFT  JOIN #@__emp AS EMP1  ON (EMP1.emp_id = #@__lycp_temp_money.emp_id  )
 LEFT JOIN #@__device ON #@__device.id=#@__lycp_temp_money.deviceid

$whereSql
  ORDER BY   #@__lycp_temp_money.id DESC  ";
  // ORDER BY   #@__lycp_temp_money.seatNumber asc ,#@__order.createtime DESC ";





 //dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('k_goods_name', $k_goods_name);
$dlist->SetParameter('k_client_realname_tel', $k_client_realname_tel);
$dlist->SetParameter('k_driver_realanme', $k_driver_realanme);
 $dlist->SetParameter('k_device_name', $k_device_name);
$dlist->SetParameter('enddate', $enddate);
$dlist->SetParameter('startdate', $startdate);

//模板
if (empty($s_tmplets)) $s_tmplets = 'apptTempMoney.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

