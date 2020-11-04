<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


if (empty($keyword)) $keyword = '';

$whereSql = " WHERE jb.isdel=0 ";


$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    $whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%' ) ";
}
if ($startdate != "") {
    $whereSql .= "AND jb.`createtime` >= UNIX_TIMESTAMP('$startdate') ";
}

if ($enddate != "") {
    $whereSql .= "AND jb.`createtime` <= UNIX_TIMESTAMP('$enddate') ";
}

//是否交易过
$sta = isset($sta) ? $sta : "2";
if ($sta != "") {
    if ($sta == 1) $whereSql .= "And (jb.status=3 || jb.status=5) ";
    if ($sta ==2) $whereSql .= "And (jb.status!=3 and jb.status!=5 and jb.status!=2) ";
}

//支付方式
$paytype = isset($paytype) ? $paytype : "";
if ($paytype != "") {
    if($paytype=='微信') {
        $whereSql .= "And jb.payment_no IS NOT NULL ";
    } elseif ($paytype=='现金') {
        $whereSql .= "And jb.payment_no IS NULL ";
    }

}

//申请时间范围
$daybefore=isset($daybefore) ? $daybefore : "";
if ($daybefore < 0) {
    if ($daybefore >-6) {
        $whereSql .= "And FROM_UNIXTIME(jb.createtime,'%Y-%m-%d')= '".date("Y-m-d",strtotime($daybefore." day"))."'";
    } else {
        $whereSql .= "And FROM_UNIXTIME(jb.createtime,'%Y-%m-%d')<= '".date("Y-m-d",strtotime($daybefore." day"))."'";
    }
}

$query = "SELECT jb.* ,c1.realname,c1.mobilephone ,c2.openid
FROM #@__clientdata_extractionlog jb
LEFT JOIN #@__client c1 ON jb.clientid=c1.id
LEFT JOIN #@__client_depinfos c2 on c2.clientid=c1.id
$whereSql
  ORDER BY   jb.id desc
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
$dlist->SetParameter('daybefore', $daybefore);


//模板
if (empty($s_tmplets)) $s_tmplets = 'extraction.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

