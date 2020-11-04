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

if (!isset($startdate)) $startdate = '';
if($startdate!="")$startdate = date('Y-m-d', strtotime($startdate));//date('Y年m月',strtotime('- 2 month',$t)), 原函数
if($startdate=="")$startdate = date('Y-m-d', time());//date('Y年m月',strtotime('- 2 month',$t)), 原函数

//dump($startdate);
$nowmonth=date('Y-m', strtotime($startdate));
//dump($startdate);
/*
$m = date('m', strtotime($nowmonth . "-01"));
$month_1 = "";//上一月
$month_2 = "";

if ($m == 01) {
    $month_1 = date('Y', strtotime($nowmonth . "-01")) . "-12";
} else {
    $month_1 = date('Y', strtotime($nowmonth . "-01")) . "-" . GetIntAddZero($m - 1, 2);
}

if ($m == 12) {
    $month_2 = date('Y', strtotime($nowmonth . "-01")) . "-01";
} else {
    $month_2 = date('Y', strtotime($nowmonth . "-01")) . "-" . GetIntAddZero($m + 1, 2);
}*/

//dump($nowmonth);

$total_total = $total_addjb = $total_subjb = 0;
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$query = "SELECT  FROM_UNIXTIME(createtime,'%Y-%m-%d') AS nowmonth,sum(IF(jbnum>0,jbnum,0)) AS addjb,
						 sum(IF(jbnum<0,jbnum,0)) AS subjb,
						 sum(jbnum) AS 				 total
						 
						FROM #@__clientdata_jblog
					    INNER JOIN x_client_depinfos ON x_clientdata_jblog.clientid=x_client_depinfos.clientid
						  WHERE  x_client_depinfos.isdel=0 AND  FROM_UNIXTIME(#@__clientdata_jblog.createtime,'%Y-%m')='$nowmonth'
						  GROUP BY FROM_UNIXTIME(#@__clientdata_jblog.createtime,'%Y-%m-%d')";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;


//模板
if (empty($s_tmplets)) $s_tmplets = 'jbCountByDay.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


