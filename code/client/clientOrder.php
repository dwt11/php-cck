<?php
/**
 * 客户列表
 *
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');


$t1 = ExecTime();


if (!isset($keyword)) $keyword = '';
if (!isset($orderby)) $orderby = '';
if (!isset($dopost)) $dopost = '';

$depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
//dump($depid);
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$whereSql = " where #@__client_depinfos.isdel=0 ";






$query = "SELECT  cl.id,cl.realname,cl.mobilephone,cl.mobilephone_check,cl.mobilephone_checkDate,cl.senddate,cl.description,cl.`from`,
          x_client_depinfos.depid,  x_client_depinfos.openid,x_client_depinfos.clientid,
          cladd.jfnum,cladd.jbnum,cladd.scoresnum,cladd.scorescutofftime,cladd.sponsorid,
          clw.nickname,clw.photo,
          x_order.paytime,x_order_addon_ztc.idcard,x_order_addon_ztc.`name`,x_order_addon_ztc.`tel`
          FROM  x_order_addon_ztc  
             INNER JOIN x_order ON x_order.id=x_order_addon_ztc.orderid 
    INNER JOIN x_client cl  ON x_order.clientid=cl.id 
    INNER JOIN x_client_depinfos  ON x_client_depinfos.clientid=cl.id 
          INNER JOIN x_client_addon cladd ON cl.id=cladd.clientid 
          INNER JOIN x_client_weixin clw ON cl.id=clw.clientid 
          WHERE  x_client_depinfos.isdel=0  AND x_order.ordertype='orderZtc'  AND  x_order.sta=1  AND x_order.isdel=0 
          ORDER BY    x_order.paytime ASC ";

 //dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10000;


//模板
if (empty($s_tmplets)) $s_tmplets = 'clientOrder.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


/**
 * 160516手机是否验证
 *
 * @param $ischeck
 *
 * @return string
 */
function getPhoneIsCheck($ischeck, $mobilephone_checkDate)
{
    $str = "";
    if ($ischeck == 1) $str = "  已验证";
    //$datetime=GetDateMk($mobilephone_checkDate);
    //if($datetime!="")$str.=" <br>验证时间：$datetime";
    return $str;
}







