<?php
/**
 */
require_once("../config.php");

//刷新不断获取新的订单


require_once("../include/role.class.php");
$roleCheck = new roleClass();
$isztc = $roleCheck->RoleCheckToBool("order/orderZtc.php");//用于权限判断
$islycp = $roleCheck->RoleCheckToBool("order/orderLine.php");//用于权限判断
$iscar = $roleCheck->RoleCheckToBool("order/orderCar.php");//用于权限判断
$isczk = $roleCheck->RoleCheckToBool("order/orderCzk.php");//用于权限判断
$ishyk = $roleCheck->RoleCheckToBool("order/orderHyk.php");//用于权限判断
//旅游线路 乘务派遣
$isCW = $roleCheck->RoleCheckToBool("device/deviceUseForGuide.php");//用于权限判断

//dump("ztc".$isztc);
//dump("islycp".$islycp);
//dump("iscar".$iscar);
//dump("isczk".$isczk);
//dump("ishyk".$ishyk);

$whereSQL = "";
if ($isztc) $whereSQL .= "ordertype='orderZtc'";
if ($islycp) {
    if ($isztc) $whereSQL .= " OR ";
    $whereSQL .= "ordertype='orderLycp'";
}
if ($iscar) {
    if ($isztc || $islycp) $whereSQL .= " OR ";
    $whereSQL .= "ordertype='orderCar'";
}
if ($isczk) {
    if ($isztc || $islycp || $iscar) $whereSQL .= " OR ";
    $whereSQL .= "ordertype='orderCzk'";
}
if ($ishyk) {
    if ($isztc || $islycp || $iscar || $isczk) $whereSQL .= " OR ";
    $whereSQL .= "ordertype='orderHyk'";
}
if ($isCW) {
    $whereSQL .= "  AND 1=1 ";//这个是为了兼容,如果只有乘务管理时,不被中途退出
}
if ($whereSQL == "") {
    echo "";
    exit;
}



$role_sql="";
$usertypename = $GLOBALS['CUSERLOGIN']->getUserTypeName();
//dump($usertypename);
$isskd = strpos($usertypename, "售卡点子部门");//判断 是否售卡点
//dump($isskd);
if ($isskd === false) {
} else {
    $operatorid=$GLOBALS['CUSERLOGIN']->getUserId();
    $depid=GetEmpDepTopIdByUserId($CUSERLOGIN->getUserId(), 100);

    // $whereSQL .= " AND (depid='" . $GLOBALS['CUSERLOGIN']->getUserId() . "' OR operatorid=$operatorid)";
    $role_sql .= " AND (   depid IN ($depid) OR  operatorid='$operatorid')";
}

$query = "SELECT #@__order.id,#@__order.ordertype,#@__order.ordernum FROM `#@__order`
          LEFT JOIN `#@__client_depinfos` ON #@__client_depinfos.clientid=#@__order.clientid WHERE (#@__order.sta=1) AND ($whereSQL) $role_sql ORDER  BY #@__order.id DESC limit 0,1";
//dump($query);
$row = $dsql->GetOne($query);
$orderid = $row["id"];
$ordertype = strtoupper(str_replace("order", "", $row["ordertype"]));
$ordernum = $row["ordernum"];
$aa = array(
    "orderid" => $orderid,
    "ordertype" => $ordertype,
    "ordernum" => $ordernum
);


if ($isCW) {
    /*$whereSql .= "  AND #@__order.desc='直通车线路' ";  //只直通车线路 */
    $sql = "
       
          SELECT #@__order.ordernum,#@__order.id,#@__order.ordertype       FROM #@__device_automobile_uselog 
           LEFT JOIN #@__order_addon_car ON #@__order_addon_car.id=#@__device_automobile_uselog.orderAddonId
           LEFT JOIN #@__order ON #@__order_addon_car.orderid=#@__order.id
        WHERE (#@__order.sta=1) AND (#@__order.ordertype='orderCar')   ORDER  BY #@__device_automobile_uselog.id DESC limit 0,1
        ";
    $row = $dsql->GetOne($sql);
    $orderid = $row["id"];
    $ordertype = strtoupper(str_replace("order", "", $row["ordertype"]));
    $ordernum = $row["ordernum"];
    $aa = array(
        "orderid" => $orderid,
        "ordertype" => "CWAP",
        "ordernum" => $ordernum
    );

}

echo json_encode($aa);

