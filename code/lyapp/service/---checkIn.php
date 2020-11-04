<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP. "/datalistcp.class.php");

if(empty($keyword))$keyword="";
if($keyword!="")$wheresql="";
/*---------------------
 function action_save(){ }
 ---------------------*/
CheckRank();
//lyapp/order/line_add.PHP中也使用,更新时一块更新
$nowdate=GetDateMk(time());
$whereSql = " WHERE  
                FROM_UNIXTIME(#@__order_addon_lycp.appttime,'%Y-%m-%d')='$nowdate' 
                AND #@__order.clientid='$CLIENTID'";

$query = "
        SELECT 
        #@__order_addon_lycp.id,#@__order_addon_lycp.appttime,#@__order_addon_lycp.tjsite,#@__order_addon_lycp.seatNumber,
        #@__order_addon_lycp.deviceid,
        #@__order_addon_lycp.realname,#@__order_addon_lycp.tel,#@__order_addon_lycp.idcard,
        #@__order_addon_lycp.info,#@__order_addon_lycp.infodate,#@__order_addon_lycp.infooperatorid
        FROM #@__order_addon_lycp #@__order_addon_lycp
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
        $whereSql
        ORDER BY   #@__order_addon_lycp.deviceid ASC, seatNumber ASC";

//dump($query);
$dsql->SetQuery($query);
$dsql->Execute();
//dump($dsql->GetTotalRow());
$info = array();
while ($row = $dsql->GetArray()) {
    $info[$row["deviceid"]][] = array(
        "id" => $row["id"],
        "seatNumber" => $row["seatNumber"],
        "realname" => $row["realname"],
        "tel" => $row["tel"],
        "idcard" => $row["idcard"],
    );
}


dump($info);

/*
$dpl = new DWTTemplate();
$tpl = "device.htm";
//dump($tpl);
$dpl->LoadTemplate($tpl);
$dpl->display();*/
