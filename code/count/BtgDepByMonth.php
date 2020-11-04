<?php
/**
 * 客户列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日
 * @package        DwtX.Administrator
 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


$t1 = ExecTime();


if (!isset($keyword)) $keyword = '';
if (!isset($nowmonth)) $nowmonth = date('Y-m', time());



setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

$keyword = isset($keyword) ? $keyword : "";
$whereSql = "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   dep_name LIKE '%$keyword%'"; //qq
    $whereSql .= "  ) "; //qq

}


$query = "
SELECT COUNT( x_order.id) AS ordercount,sum(total) AS totalmoney , x_client_depinfos.depid,x_emp_dep.dep_name
FROM  x_order  
INNER JOIN x_client_depinfos ON x_order.clientid= x_client_depinfos.clientid
INNER JOIN x_emp_dep ON x_emp_dep.dep_id= x_client_depinfos.depid
WHERE x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/
  AND FROM_UNIXTIME( x_order.createtime,'%Y-%m')='$nowmonth'
  AND ordertype='orderZtc'
  $whereSql
GROUP BY depid 
ORDER BY  totalmoney  DESC ";
 /* ORDER BY  CONVERT(  realname USING gbk )  asc  ";*/

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 15;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('nowmonth', $nowmonth);


//模板
if (empty($s_tmplets)) $s_tmplets = 'BtgDepByMonth.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


function getNumb($depid, $nowday="")
{
    global $dsql;

    $query = "
SELECT COUNT( x_order.id) AS ordercount,sum(total) AS totalmoney , x_client_depinfos.depid,x_emp_dep.dep_name
FROM  x_order  
INNER JOIN x_client_depinfos ON x_order.clientid= x_client_depinfos.clientid
INNER JOIN x_emp_dep ON x_emp_dep.dep_id= x_client_depinfos.depid
WHERE x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/
  AND FROM_UNIXTIME(createtime,'%Y-%m-%d')='$nowday'
  AND ordertype='orderZtc'
  AND depid='$depid'
GROUP BY depid 
ORDER BY  totalmoney  DESC ";
    $rowarc = $dsql->GetOne($query);
  //   dump($query);
    if (is_array($rowarc)) {
        $str = $rowarc['totalmoney']/100;
if($str==0)$str="";
        return $str;
    }
    return "";
}
