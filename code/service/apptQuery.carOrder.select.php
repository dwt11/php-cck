<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
//--------------------------------获取线路名称 发车时间
$lineid = isset($lineid) ? $lineid : "";
$appttime = isset($appttime) ? $appttime : "";
if ($lineid == "" || $appttime == "") {
    ShowMsg("参数出错!", "-1");
}

$whereSql = " WHERE (#@__order.isdel=0 OR #@__order.isdel=4 )  AND  #@__order_addon_lycp.isdel=0  AND #@__order.sta=1";//显示 未被删除和已经支付的主订单  并且子订单未被业务删除
$whereSql .= " AND #@__order_addon_lycp.lineid=$lineid  ";
$whereSql .= " AND  #@__order_addon_lycp.appttime='$appttime' ";

if($CarOrderid_old!="")$whereSql.=" AND orderCarId!='$CarOrderid_old'";

$query = "
        SELECT 
        #@__order_addon_lycp.orderCarId
        FROM #@__order_addon_lycp #@__order_addon_lycp
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
        $whereSql
        AND orderCarId!=''
       GROUP BY orderCarId";
//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
//GET参数
//$dlist->SetParameter('keyword', $keyword);


//模板
$s_tmplets = 'apptQuery.carOrder.select.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
