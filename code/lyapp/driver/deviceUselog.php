<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");


CheckRank();
$sta = isset($sta) ? $sta : "1";//默认显示正常发车的
$whereSql = " WHERE (x_order.sta=1) AND (x_order.ordertype='orderCar') AND #@__emp_client.clientid='$CLIENTID' AND #@__device_automobile_uselog.id>1145 ";//原旧的数据不再显示
//未出行，预约时间是当前时间以后的
if ($sta == '1') {
    $whereSql .= " AND #@__device_automobile_uselog.start_date>=UNIX_TIMESTAMP(now()) ";
    $orderby = " ASC";//未出游 升序排列
}
//已出行，预约时间是当前时间以后的
if ($sta == '2') {
    $whereSql .= " AND #@__device_automobile_uselog.start_date<UNIX_TIMESTAMP(now())";
    $orderby = " DESC";//已出游 倒序排列
}




$sql= "
       
          SELECT  
            #@__device_automobile_uselog.id,#@__device_automobile_uselog.deviceid,
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
            LEFT JOIN #@__emp_client    ON (#@__emp_client.emp_id=#@__device_automobile_uselog.driverid OR #@__emp_client.emp_id=#@__device_automobile_uselog.guideid )
    
        
            $whereSql
        ORDER BY   #@__device_automobile_uselog.start_date $orderby 
        ";


//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 10;
$dlist->SetParameter('sta', $sta);
$dlist->SetTemplate("deviceUselog.htm");
$dlist->SetSource($sql);
$dlist->Display();


