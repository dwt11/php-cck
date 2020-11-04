<?php
require_once("../config.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();
if (empty($keyword)) $keyword = '';


if(!isset($isuse)) $isuse = '';
if(empty($dopost)) $dopost = '';
//dump($isuse);

$whereSql = " WHERE 1=1  ";


//$keyword = isset($keyword) ? $keyword : "";
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($keyword != "") {
    //$whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%' OR c2.`idcard` LIKE '%$keyword%' or jb.desc LIKE '%$keyword%' ) ";
    $whereSql .= "AND ( c1.`realname` LIKE '%$keyword%' OR c1.`mobilephone` LIKE '%$keyword%'  OR cw.nickname LIKE '%$keyword%'   ) ";
}
if ($startdate != "") {
    $stardate_t=$startdate." 00:00:00";
    $whereSql .= "AND #@__clientdata_jblog.`createtime` >= UNIX_TIMESTAMP('$stardate_t') ";
}

if ($enddate != "") {
    $enddate_t=$enddate." 23:59:59";
    $whereSql .= "AND #@__clientdata_jblog.`createtime` <= UNIX_TIMESTAMP('$enddate_t') ";
}
if ($isuse != "") $whereSql .= " AND (#@__clientdata_coupon.isuse='$isuse')";






$query = "SELECT #@__clientdata_coupon.*,c1.realname,c1.mobilephone ,cw.nickname FROM #@__clientdata_coupon 
 INNER JOIN #@__client c1 ON #@__clientdata_coupon.clientid=c1.id
 INNER JOIN `#@__client_weixin` cw ON cw.clientid=c1.id
$whereSql
  ORDER BY   #@__clientdata_coupon.id desc
";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 20;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('isuse', $isuse);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);

//模板
if (empty($s_tmplets)) $s_tmplets = 'coupon.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


//获取 系统的积分额度
function getJBall(){
    global $dsql;
    $str = "";
    $mx_jbnum=0.00;
    //明细记录中的
    $query = "SELECT SUM(jbnum) as dd FROM `#@__clientdata_coupon` WHERE isuse=0";
    $row = $dsql->GetOne($query);
    if (isset($row["dd"])&&$row["dd"]!="") {
        $mx_jbnum100 = $row["dd"];
        $mx_jbnum = $mx_jbnum100/100;
    }
    $mx_jbnum=number_format($mx_jbnum,2);







    return $mx_jbnum;

}
