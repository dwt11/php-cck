<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

$whereSql = " WHERE 1=1 and order1.isdel=0 and ztc.cardcode!='' and order1.ordertype='orderZtc' ";

$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   #@__order_addon_ztc.name LIKE '%$keyword%'"; //qq
    $whereSql .= " or   #@__order_addon_ztc.tel LIKE '%$keyword%'"; //qq
    $whereSql .= "  or order1.ordernum LIKE '%$keyword%'"; //qq
    $whereSql .= "  or ztc.cardcode LIKE '%$keyword%'"; //qq
    $whereSql .= "  )";//备注

}


$query = "SELECT #@__order_addon_ztc.name,#@__order_addon_ztc.tel,#@__order_addon_ztc.id, 
  ztc.cardcode,order1.createtime,order1.ordernum,c1.mobilephone,order1.clientid FROM #@__order_addon_ztc
 LEFT JOIN #@__order AS order1 ON order1.id=#@__order_addon_ztc.orderid
  LEFT JOIN #@__order_addon_ztc  ztc on ztc.orderid=order1.id
LEFT JOIN #@__client AS c1 ON c1.id=order1.clientid
$whereSql
  ORDER BY   ztc.cardcode asc
";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 20;

//GET参数
$dlist->SetParameter('keyword', $keyword);


//模板
if (empty($s_tmplets)) $s_tmplets = 'orderZtcCardCodeQr.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


