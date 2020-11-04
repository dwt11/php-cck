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
if (!isset($day_s) || $day_s=="")    $day_s=date("Y-m-d", time()-604800);
if (!isset($day_d) || $day_d=="")    $day_d=date("Y-m-d", time());




if($day_s!=""){
    $day_s_int=GetMkTime($day_s." 00:00:00");
    $whereSQL .= " AND  x_order.createtime>='$day_s_int'";
}
if($day_d!=""){
    $day_d_int=GetMkTime($day_d." 23:59:59");
    $whereSQL .= " AND  x_order.createtime<='$day_d_int'";
}




$report_array=array();


$query="
SELECT x_order.ordertype,FROM_UNIXTIME(x_order.createtime,'%Y-%m-%d') AS nowday,COUNT(x_order.id) AS ordercount,sum(x_order.total) AS totalmoney FROM  x_order  
INNER JOIN x_client_depinfos ON x_order.clientid=x_client_depinfos.clientid
WHERE x_order.sta=1 AND x_order.isdel=0 AND  x_client_depinfos.isdel=0 

$whereSQL
GROUP BY ordertype , FROM_UNIXTIME(createtime,'%Y-%m-%d')
         
               
        ORDER BY x_order.ordertype DESC 
";
// dump($query);
$dsql->SetQuery($query);
$dsql->Execute("order");
while ($row1 = $dsql->GetArray("order")) {

    $nowday = $row1["nowday"];
    $ordertype = $row1["ordertype"];

    $bs = $row1["ordercount"];
    $money = $row1["totalmoney"]/100;
    $report_array[$ordertype][$nowday]["bs"]= $bs ;
    $report_array[$ordertype][$nowday]["money"]= $money ;
}


include DwtInclude('count/BorderByDay.htm');




