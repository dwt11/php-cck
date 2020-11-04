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


if (!isset($nowyear)) $nowyear = '';
if($nowyear==""){
    $nowyear=date("Y",time());
}
$total_total=$total_bs=0;
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$query = "SELECT  FROM_UNIXTIME(createtime,'%Y-%m') AS nowmonth,SUM(paynum) AS  total,COUNT(x_order.id) AS  bs
						 
						FROM #@__order
	         INNER JOIN x_client_depinfos ON x_order.clientid=x_client_depinfos.clientid
        WHERE x_order.sta=1 AND x_order.isdel=0 AND  x_client_depinfos.isdel=0 AND 
					     FROM_UNIXTIME(createtime,'%Y')='$nowyear'   AND paytype='现金'
						  GROUP BY FROM_UNIXTIME(createtime,'%Y-%m')";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;



//模板
if (empty($s_tmplets)) $s_tmplets = 'xsxjCountByMonth.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;

