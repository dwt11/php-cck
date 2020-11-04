<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();

if (empty($keyword)) $keyword = '';
if(empty($dopost)) $dopost = '';
if(empty($paynum_all)) $paynum_all = 0;
if(empty($jbnum_all)) $jbnum_all = 0;
if(empty($jfnum_all)) $jfnum_all = 0;
if(empty($total_all)) $total_all = 0;
if(empty($number_all)) $number_all = 0;


$whereSql = " WHERE 1=1 AND #@__order.isdel>0 ";


$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    $whereSql .= "AND ( 
                        c1.`realname` LIKE '%$keyword%' 
                        OR c1.`mobilephone` LIKE '%$keyword%' 
                        OR #@__order.`ordernum` LIKE '%$keyword%'
                        ) ";
}
if ($startdate != "") {
    $startdate1=$startdate." 00:00:00";
    $whereSql .= " AND #@__order.`createtime` >= UNIX_TIMESTAMP('$startdate1') ";
}

if ($enddate != "") {
    $enddate1=$enddate." 23:59:59";
    $whereSql .= " AND #@__order.`createtime` <= UNIX_TIMESTAMP('$enddate1') ";
}

//是否交易过
$sta = isset($sta) ? $sta : "";
if ($sta != "") {
    if($sta==="0")$whereSql .= "And #@__order.sta=0 ";//未交易
    if($sta==1)$whereSql .= "And #@__order.sta=1 ";
    if($sta==2)$whereSql .= "And (#@__order.sta!=0 and #@__order.sta!=1) ";
/*    if ($sta == "bftk") $whereSql .= "And (#@__order.isdel=4 ) ";*/
}
//支付方式
$paytype = isset($paytype) ? $paytype : "";
if ($paytype != "") {
   $whereSql .= "And #@__order.paytype='$paytype' ";
 }
//订单类型
$ordertype = isset($ordertype) ? $ordertype : "";
if ($ordertype != "") {
   $whereSql .= "And #@__order.ordertype='$ordertype' ";
 }
$query = "SELECT #@__order.*,c1.realname,c1.mobilephone  FROM #@__order
             LEFT JOIN #@__client AS c1 ON c1.id=#@__order.clientid
            $whereSql
              ORDER BY   /*#@__order.returntime DESC,*/#@__order.createtime DESC
";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);
$dlist->SetParameter('sta', $sta);
$dlist->SetParameter('paytype', $paytype);
$dlist->SetParameter('ordertype', $ordertype);

//模板
if (empty($s_tmplets)) $s_tmplets = 'orderCancel.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


