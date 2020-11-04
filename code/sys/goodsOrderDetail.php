<?php
/**
 * 内容列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once('goods.functions.php');
require_once(DWTINC.'/datalistcp.class.php');
require_once (DWTINC.'/enums.func.php');  //获取数据字典对应的值
// var_dump($roleCheck);exit;


$totalMoney_all=$payMoney_all=0;   //每页最后一行合计清零

$t1 = ExecTime();


$depid = isset($depid) ? $depid : "0";


setcookie('ENV_GOBACK_URL', $dwtNowUrl, time()+3600, '/');


$whereSql=" WHERE 1=1 ";

$title="订单明细管理";

if($depid!="0")
{
    $whereSql .= " and goodsOrderDetail.depid='$depid'";
}


//4日期
$startdate = isset($startdate) ? $startdate : "";
$enddate = isset($enddate) ? $enddate : "";
if ($startdate!="" && $enddate!="")
{
  $title.="日期:从".$startdate."到".$enddate;
  $startdate1=GetMkTime($startdate);      //(时间戳)获得选定开始日期的开始时间 格式  2014-11-04 00:00:00
  $enddate1=GetMkTime($enddate)+86399;    //(时间戳)获得选定结束日期的结束时间格式2014-11-04 23:59:59   86399代表23小时59分59秒
  //dump(GetDateTimeMk($startdate1).GetDateTimeMk($enddate1));
  $whereSql  .= " And (goodsOrderDetail.senddate>= '$startdate1' and goodsOrderDetail.senddate<= '$enddate1')"; //qq
}











$query = "SELECT  goodsOrderDetail.* ,dep.dep_name
 FROM #@__sys_goods_orderdetails goodsOrderDetail
 LEFT JOIN #@__emp_dep dep on goodsOrderDetail.depid=dep.dep_id
 $whereSql
   ORDER BY   goodsOrderDetail.id desc";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 25;

//GET参数
$dlist->SetParameter('depid', $depid);
$dlist->SetParameter('startdate', $startdate);
$dlist->SetParameter('enddate', $enddate);

//模板
$s_tmplets = 'goodsOrderDetail.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();

// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

