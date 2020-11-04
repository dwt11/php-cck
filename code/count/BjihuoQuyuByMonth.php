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
SELECT COUNT(#@__ztc_jihuo.orderListId) AS jihuocount, #@__ztc_jihuo.dep_id,DEP2.dep_name,x_emp_dep.dep_reid
FROM  #@__ztc_jihuo 
INNER JOIN #@__order_addon_ztc  ON #@__order_addon_ztc.id=#@__ztc_jihuo.orderListId
INNER JOIN #@__order  ON #@__order.id=#@__order_addon_ztc.orderid
INNER JOIN x_emp_dep ON x_emp_dep.dep_id= #@__ztc_jihuo.dep_id
INNER JOIN x_emp_dep AS DEP2 ON DEP2.dep_id= x_emp_dep.dep_reid
WHERE x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/
  AND FROM_UNIXTIME( #@__ztc_jihuo.createtime,'%Y-%m')='$nowmonth'
  AND ordertype='orderZtc'
  $whereSql
GROUP BY x_emp_dep.dep_reid 
ORDER BY  jihuocount  DESC ";
/* ORDER BY  CONVERT(  realname USING gbk )  asc  ";*/

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 15;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('nowmonth', $nowmonth);


//模板
if (empty($s_tmplets)) $s_tmplets = 'BjihuoQuyuByMonth.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


function getNumb_DD($dep_reid, $nowday = "")
{
    global $dsql;

    $dsql->SetQuery("SELECT GROUP_CONCAT(dep_id) as depids FROM `#@__emp_dep`  WHERE dep_reid='$dep_reid'");
////dump("SELECT * FROM `#@__em_dep`  WHERE dep_id=$dep_id");
    $myrow = $dsql->GetOne();

    $dep_ids=$myrow["depids"];

    $query = "
SELECT COUNT(#@__ztc_jihuo.orderListId) AS jihuocount
FROM  #@__ztc_jihuo 
INNER JOIN #@__order_addon_ztc  ON #@__order_addon_ztc.id=#@__ztc_jihuo.orderListId
INNER JOIN #@__order  ON #@__order.id=#@__order_addon_ztc.orderid

WHERE x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/
  AND FROM_UNIXTIME(#@__ztc_jihuo.createtime,'%Y-%m-%d')='$nowday'
  AND ordertype='orderZtc'
  AND #@__ztc_jihuo.dep_id IN ($dep_ids)
ORDER BY  jihuocount  DESC ";
    //dump($query);

    $rowarc = $dsql->GetOne($query);
    //   dump($query);
    if (is_array($rowarc)) {
        $str = $rowarc['jihuocount'];
        if ($str == 0) $str = "";
        return $str;
    }
    return "";
}
