<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");

if (empty($keyword)) $keyword = "";
if ($keyword != "") $wheresql = "";
/*---------------------
 function action_save(){ }
 ---------------------*/
CheckRank();

if (empty($dopost)) $dopost = '';
$isscry = false;//是否司乘人员
$query = "SELECT COUNT(emp_id)AS dd,emp_id  FROM #@__emp_client WHERE clientid='$CLIENTID'   ";
$rowscry = $dsql->GetOne($query);
//没有信息就不显示 161101
if (isset($rowscry["dd"]) && $rowscry["dd"] > 0) {
    $isscry = true;
    $emp_id = $rowscry["emp_id"];
}

if (!DEBUG_LEVEL && !$isscry) {
    echo("<font size='32px'>无权检票</font>");
    exit;

}

//已经乘车
if ($dopost == 'savecc') {
    $sql = "UPDATE `#@__order_addon_lycp` SET `iscc`='1'  WHERE id='$orderaddonlycpid' ";
    $dsql->ExecuteNoneQuery($sql);
    echo "操作成功";
    exit();
}
//未乘车
if ($dopost == 'savewcc') {
    $sql = "UPDATE `#@__order_addon_lycp` SET `iscc`='0'  WHERE id='$orderaddonlycpid' ";
    $dsql->ExecuteNoneQuery($sql);
    echo "操作成功";
    exit();
}


$nowdate = date("Y-m-d");
//$nowdate = ("2017-07-26");//调试用
//根据车辆ID,获取"线路预约"发给租赁车辆的订单号
//查询出在旅游产品订单中,在用的车辆租赁订单ID
$sql = "
        SELECT  #@__order_addon_car.orderid AS ordercarid
        FROM #@__device_automobile_uselog
        INNER JOIN #@__order_addon_car ON #@__device_automobile_uselog.orderAddonId=#@__order_addon_car.id
        INNER JOIN #@__order_addon_lycp ON #@__order_addon_lycp.orderCarId=#@__order_addon_car.orderid
        WHERE  deviceid='$deviceid'   AND  FROM_UNIXTIME(#@__device_automobile_uselog.start_date,'%Y-%m-%d') ='{$nowdate}' 
";
$row = $dsql->GetOne($sql);
if ($row) {
    $ordercarid = $row["ordercarid"];
} else {
    echo "此车辆无出行信息";
    exit;
}

//dump($orderid);

$goodsid = 0;
$goodsname = "当前日期此车辆无行程";
$sql = "
        SELECT  #@__goods.goodsname,#@__goods.id,#@__order_addon_lycp.lineid,#@__order_addon_lycp.appttime
        FROM #@__order_addon_lycp
        LEFT JOIN #@__goods ON #@__goods.id=#@__order_addon_lycp.goodsid
         WHERE #@__order_addon_lycp.isdel=0
        AND orderCarId='$ordercarid' 
        GROUP BY goodsid
        ORDER BY #@__order_addon_lycp.id
";
$row = $dsql->GetOne($sql);
if ($row) {
    $goodsname = $row["goodsname"];
    $goodsid = $row["id"];
    $lineid = $row["lineid"];
    $appttime = $row["appttime"];
}


$query = "
SELECT #@__order_addon_lycp.id,#@__order_addon_lycp.iscc,#@__order_addon_lycp.realname,#@__order_addon_lycp.tjsite,#@__order_addon_lycp.tel,#@__order_addon_lycp.idcard,#@__order_addon_lycp.orderlistztcid,#@__order_addon_lycp.seatNumber
,#@__order_addon_ztc.idpic
FROM #@__order_addon_lycp
LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
LEFT JOIN #@__order_addon_ztc  ON #@__order_addon_lycp.orderlistztcid=#@__order_addon_ztc.id
WHERE (#@__order.isdel=0 OR #@__order.isdel=4 )  AND  #@__order_addon_lycp.isdel=0  AND #@__order.sta=1 
        AND orderCarId='$ordercarid' 
ORDER BY seatNumber ASC
";
//dump($query);

$dlist = new DataListCP();
$dlist->pageSize = 300;
$dlist->SetTemplate("appQuery.htm");
$dlist->SetSource($query);
$dlist->Display();


