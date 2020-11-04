<?php
/**
 * 客户列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日Z tianya $
 * @package        DwtX.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

$whereSQL = "   ";
if (!isset($day_s) || $day_s == "") $day_s = date("Y-m-d", time() - 604800);
if (!isset($day_d) || $day_d == "") $day_d = date("Y-m-d", time());
if (!isset($ordertype)) $ordertype = "";
if($ordertype==""){
    ShowMsg("参数无数");
    exit;
}

if ($day_s != "") {
    $day_s_int = GetMkTime($day_s . " 00:00:00");
    $whereSQL .= " AND  createtime>='$day_s_int'";
}
if ($day_d != "") {
    $day_d_int = GetMkTime($day_d . " 23:59:59");
    $whereSQL .= " AND  createtime<='$day_d_int'";
}


$report_array = array();
$report_goodsname_array = array();

//获取订单类型对应的 扩展表名称
$sqlrowTrue = "SELECT addtable FROM `#@__sys_channeltype` WHERE nid='$ordertype' ";
$rowTrue = $dsql->GetOne($sqlrowTrue);

$addtable_name = $rowTrue["addtable"];

$query = "SELECT goodsname,goodsid,FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,COUNT($addtable_name.goodsid) AS goodscount,sum(total) AS totalmoney FROM  x_order  
INNER JOIN $addtable_name ON $addtable_name.orderid=x_order.id
INNER JOIN x_goods ON $addtable_name.goodsid=x_goods.id
INNER JOIN x_client_depinfos ON x_order.clientid=x_client_depinfos.clientid
WHERE x_order.sta=1 AND x_order.isdel=0 AND ordertype='$ordertype' AND  x_client_depinfos.isdel=0 
     $whereSQL
GROUP BY $addtable_name.goodsid , FROM_UNIXTIME(createtime,'%Y-%m-%d')
        
        ORDER BY createtime DESC  
";
if ($ordertype == "orderCzk") {
    $query = "SELECT '充值卡' as goodsname,goodsid,FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowday,COUNT($addtable_name.goodsid) AS goodscount,sum(total) AS totalmoney FROM  x_order  
                INNER JOIN $addtable_name ON $addtable_name.orderid=x_order.id
                INNER JOIN x_client_depinfos ON x_order.clientid=x_client_depinfos.clientid
            WHERE x_order.sta=1 AND x_order.isdel=0 AND ordertype='$ordertype'  AND  x_client_depinfos.isdel=0 
                     $whereSQL
                GROUP BY $addtable_name.goodsid , FROM_UNIXTIME(createtime,'%Y-%m-%d')
                        
                        ORDER BY createtime DESC  
                ";

}
//$query="";
//dump($query);
$dsql->SetQuery($query);
$dsql->Execute("order123233425");
while ($row1 = $dsql->GetArray("order123233425")) {


    $nowday = $row1["nowday"];
    $goodsname = $row1["goodsname"];
    $goodsid = $row1["goodsid"];
    $bs = (int)$row1["goodscount"];
    $money = $row1["totalmoney"] / 100;
//    dump($row1);
    $report_array[$goodsid][$nowday]["bs"] = $bs;
    $report_array[$goodsid][$nowday]["money"] = $money;
    $report_goodsname_array[$goodsid] = $goodsname;
}/**/

//预处理数据 ,如果商品名称太多,则按每4列一个表分隔
$i = 1;
$key_i = 0;
foreach ($report_array as $key => $value) {
    if ($i % 5 == 0) {
        $report_array_temp[$key_i][$key] = $value;
        $key_i++;
    } else {
        $report_array_temp[$key_i][$key] = $value;
    }
    $i++;
}

//$report_array_temp;

//dump($report_array);
include DwtInclude('count/BgoodsByDay.do.htm');




