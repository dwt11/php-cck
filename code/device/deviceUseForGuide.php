<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("device.functions.php");
require_once(DWTINC . "/fields.func.php");

$t1 = ExecTime();
if (empty($dopost)) $dopost = '';

//保存乘务的信息
if ($dopost == 'guideInfo_save') {
    $sql = "UPDATE `x_device_automobile_uselog` SET `guideid`='$guideid' WHERE (`id`='$device_automobile_uselog_id')";
    $dsql->ExecuteNoneQuery($sql);
    echo "乘务保存成功";
    exit();
}




$whereSql = " WHERE (x_order.sta=1) AND (x_order.ordertype='orderCar') ";

$useguideid = isset($useguideid) ? $useguideid : "0";
if($useguideid!="0") {
    if ($useguideid == "1") {
        $whereSql .= "  AND #@__device_automobile_uselog.guideid='' ";
    }
    if ($useguideid == "2") {
        $whereSql .= "  AND #@__device_automobile_uselog.guideid>0 ";
    }
}


//订单来源
$orderLY = isset($orderLY) ? $orderLY : "";
if($orderLY!="") {
    if ($orderLY == "直通车线路") {
        $whereSql .= "  AND #@__order.desc='直通车线路' ";
    }
    if ($orderLY != "直通车线路") {
        $whereSql .= "  AND #@__order.desc!='直通车线路' ";
    }
}
if (empty($k_goods_name)) $k_goods_name = '';
if ($k_goods_name != "") {
    $whereSql .= "AND ( ";
    $whereSql .= " devicename LIKE '%$k_goods_name%' ";
    $whereSql .= " OR devicecode LIKE '%$k_goods_name%' ";
    $whereSql .= " OR goodsname LIKE '%$k_goods_name%' ";
    $whereSql .= ") ";
}


$startdate = isset($startdate) ? $startdate : "";
if ($startdate != "") {
    $startdate1=$startdate." 00:00:00";
    $startdate2=$startdate." 23:59:59";
    $whereSql .= " AND (
                            (
                                UNIX_TIMESTAMP(from_unixtime(start_date,'%Y-%m-%d  00:00:00'))  <= UNIX_TIMESTAMP('$startdate1')  
                                AND  UNIX_TIMESTAMP(from_unixtime(end_date,'%Y-%m-%d  00:00:00')) >= UNIX_TIMESTAMP('$startdate1')
                            )
                            OR 
                             (
                                UNIX_TIMESTAMP(from_unixtime(start_date,'%Y-%m-%d  00:00:00'))  <= UNIX_TIMESTAMP('$startdate1')  
                                AND  UNIX_TIMESTAMP(from_unixtime(start_date,'%Y-%m-%d  00:00:00')) >= UNIX_TIMESTAMP('$startdate1')
                            AND end_date =''
                            
                            )
                        )";
}
$k_client_realname_tel = isset($k_client_realname_tel) ? $k_client_realname_tel : "";
if ($k_client_realname_tel != "") {
    $whereSql .= "AND ( ";
    $whereSql .= "     realname LIKE '%$k_client_realname_tel%' ";
    $whereSql .= "  OR  tel LIKE '%$k_client_realname_tel%' ";
    $whereSql .= ") ";
}


//司机
if (empty($k_driver_realanme)) $k_driver_realanme = '';
if ($k_driver_realanme != "")  $whereSql .= "AND  EMP1.emp_realname LIKE '%$k_driver_realanme%' ";

//乘务
if (empty($k_guide_realname)) $k_guide_realname = '';
if ($k_guide_realname != "")  $whereSql .= "AND  EMP2.emp_realname LIKE '%$k_guide_realname%' ";



//车牌号
if (empty($k_device_name)) $k_device_name = '';
if ($k_device_name != "")  $whereSql .= "AND  devicename LIKE '%$k_device_name%' ";

//订单号
if (empty($ordernum)) $ordernum = '';
if ($ordernum != "")  $whereSql .= "AND  ordernum LIKE '%$ordernum%' ";


//获得数据表名
//车辆租赁
$sql= "
       
          SELECT  
            #@__device_automobile_uselog.deviceid,#@__device_automobile_uselog.id,
            #@__device_automobile_uselog.orderAddonId,
            #@__device_automobile_uselog.operatorid,
            #@__device_automobile_uselog.lineid,
            #@__device_automobile_uselog.driverid,
            #@__device_automobile_uselog.guideid,
            #@__device.devicecode,#@__device.devicename,
             goods.goodsname,goods.goodscode,
             #@__order_addon_car.tel,#@__order_addon_car.realname,
             #@__order_addon_car.start_date,#@__order_addon_car.end_date,
             #@__order.ordernum,
             #@__order.desc AS orderCarDesc,#@__order.id AS orderCarId,
             EMP1.emp_realname AS driver_realanme,EMP2.emp_realname AS guide_realname 
            FROM #@__device_automobile_uselog 
           LEFT JOIN #@__device ON #@__device_automobile_uselog.deviceid=#@__device.id
           LEFT JOIN #@__order_addon_car ON #@__order_addon_car.id=#@__device_automobile_uselog.orderAddonId
           LEFT JOIN #@__order ON #@__order_addon_car.orderid=#@__order.id
            LEFT JOIN #@__goods goods ON goods.id=#@__order_addon_car.goodsid
            LEFT JOIN #@__emp AS EMP1  ON (EMP1.emp_id = #@__device_automobile_uselog.driverid  )
            LEFT JOIN #@__emp  AS EMP2 ON (EMP2.emp_id = #@__device_automobile_uselog.guideid   )
        
        
            $whereSql
        ORDER BY   #@__device_automobile_uselog.id DESC 
        ";



//dump($sql);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
//GET参数
$dlist->SetParameter('orderLY', $orderLY);
$dlist->SetParameter('k_goods_name', $k_goods_name);
$dlist->SetParameter('k_client_realname_tel', $k_client_realname_tel);
$dlist->SetParameter('k_driver_realanme', $k_driver_realanme);
$dlist->SetParameter('k_guide_realname', $k_guide_realname);
$dlist->SetParameter('k_device_name', $k_device_name);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('ordernum', $ordernum);

//模板
$s_tmplets = 'deviceUseForGuide.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
