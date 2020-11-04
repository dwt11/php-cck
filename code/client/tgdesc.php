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
$whereSql = "";


if (!isset($keyword)) $keyword = '';
if (!isset($orderby)) $orderby = '';
if (!isset($dopost)) $dopost = '';
if (!isset($day_s) || $day_s == "") $day_s = date("Y-m-d", time() - 604800);
if (!isset($day_d) || $day_d == "") $day_d = date("Y-m-d", time());


//点击后递归查询所以的下级总数
if (empty($dopost)) $dopost = '';
if ($dopost == "getALLNumb") {
    global $dsql, $clientid_all_numb;
    $clientid_all_numb = "";
    if ($day_s != "") {
        $day_s_int = GetMkTime($day_s . " 00:00:00");
        //$whereSql .= " AND  x_order.createtime>='$day_s_int' ";
    }
    if ($day_d != "") {
        $day_d_int = GetMkTime($day_d . " 23:59:59");
        //$whereSql .= " AND  x_order.createtime<='$day_d_int' ";
    }
//dump($day_s);

//时间查询
    $whereSql11 = " AND  x_order.createtime>='$day_s_int' ";
    $whereSql11 .= " AND  x_order.createtime<='$day_d_int' ";

    //dump( $whereSql11);
    giveme2($clientid, 1, $whereSql11);
    echo $clientid_all_numb;
    exit();
}


if ($GLOBAMOREDEP) {
    if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($depid)) $depid = "0";
}

setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
$admin_usertype = $CUSERLOGIN->getUserType();

$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "    realname LIKE '%$keyword%'"; //qq
    $whereSql .= "  or  mobilephone LIKE '%$keyword%') "; //qq

}
$sponsorid_s=0;
$clientTgData_arrty = GetClientTgData($day_s, $day_d);
if(count($clientTgData_arrty)>0) {
//dump($clientTgData_arrty);
//exit;
    $sponsorid_s = implode(",", array_keys($clientTgData_arrty["0"]));//获取ID,供下面的获取姓名使用  array_keys检索出以id为内容的key
}
//exit;
//然后再在子过程中取他的上级的下级购买数量
$query = "
        SELECT realname,id as clientid,mobilephone 
        FROM x_client 
        WHERE id IN ({$sponsorid_s})
        $whereSql
         ORDER BY  field(id,$sponsorid_s)
         ";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 500;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('day_s', $day_s);
$dlist->SetParameter('day_d', $day_d);


//模板
if (empty($s_tmplets)) $s_tmplets = 'tgdesc.htm';
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
 * @param $clientid   会员ID
 * @param $js         级数
 * @param $day_s_int  开始时间
 * @param $day_d_int  结束时间
 *
 * @return mixed|string

function getNumb($clientid, $js, $day_s_int, $day_d_int)
 * {
 * global $dsql;
 * $whereSql = " AND  x_order.createtime>='$day_s_int' ";
 * $whereSql .= " AND  x_order.createtime<='$day_d_int' ";
 *
 * if ($js == 1) $whereSql .= " AND  (
 * (    jb.isdel=0 AND jb.`desc` LIKE '下级会员购买赠送%'  )
 * OR
 * (    x_clientdata_jflog.isdel=0 AND x_clientdata_jflog.`desc` LIKE '下级会员购买赠送%'  )
 * )      ";
 * if ($js == 2)  $whereSql .= " AND  (
 * (    jb.isdel=0 AND jb.`desc` LIKE '下下级会员购买赠送%'  )
 * OR
 * (    x_clientdata_jflog.isdel=0 AND x_clientdata_jflog.`desc` LIKE '下下级会员购买赠送%'  )
 * )      ";
 *
 * //因为返还金币是按直通车子订单数量返还的,
 * //所以这里统计数量,是按子订单数量来的
 * //如果这里也要金额 的话,不能用以下的语句,要重新做一个
 * $questr = "
 *
 * SELECT COUNT(x_order_addon_ztc.id) as dd  FROM x_order
 * LEFT JOIN   x_clientdata_jblog jb ON x_order.id=jb.orderid
 * INNER JOIN   x_order_addon_ztc ON x_order.id=x_order_addon_ztc.orderid
 * LEFT JOIN   x_clientdata_jflog ON x_clientdata_jflog.orderid=x_order.id
 * WHERE
 * x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡
 * AND x_order.clientid='$clientid'
 * $whereSql
 * GROUP BY x_order.clientid
 *
 * ";
 *
 * //DUMP($questr);
 * $rowarc = $dsql->GetOne($questr);
 * if (is_array($rowarc)) {
 * //$str = $rowarc['dd'];
 * $str = $rowarc['dd'];
 *
 * /*        $jbnum100_total=$rowarc['dd'];
 * $jbnum_total=$jbnum100_total/100;
 * $str= "金额：$jbnum_total 人数：".$jbnum_total/50;
 * $str= $jbnum_total/50;
 * if($js==2)$str= "金额：".$rowarc["dd"]." 人数：".$rowarc['dd']/30;
 * if($js==2)$str=$jbnum_total/30;
 * return $str;
 * }
 * return "";
 * }
 */
/**
 * @param $clientid   会员ID
 * @param $js         级数
 * @param $wheresql   开始时间 结束的查询语句
 *                    $onlyJS  只获取几级
 *
 * @return mixed|string
 */


//递归下级
/**
 * @param $clientid   会员ID
 * @param $js         级数
 * @param $wheresql   开始时间 结束时间 查询SQL
 *                    $onlyJS  只获取几级
 *
 * @return mixed|string
 */
function giveme2($id, $gi, $wheresql)
{

    global $dsql, $clientid_all_numb;//所有循环的总数


    $sqlstr = "SELECT clientid FROM `#@__client_addon` WHERE sponsorid='$id' ";
    //  dump($wheresql);
    $dsql->SetQuery($sqlstr);
    $dsql->Execute($gi . $id);
    while ($row = $dsql->GetArray($gi . $id)) {

        $clientid = $row["clientid"];
        $query = "SELECT COUNT(#@__order_addon_ztc.id) as dd  FROM #@__order
                     INNER JOIN   #@__order_addon_ztc ON #@__order.id=#@__order_addon_ztc.orderid
                        WHERE 
                        x_order.sta=1 
                        AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/  
                        AND  #@__order.ordertype='orderZtc'  
                        AND #@__order.clientid='{$clientid}'
                        $wheresql
                        ";

        //dump($query);
        $rowarc = $dsql->GetOne($query);
        if (isset($rowarc) && $rowarc["dd"] > 0) {

            //下级买过直通车才计数
            // dump($query);

            // dump($rowarc["dd"]."-----");
            $clientid_all_numb += $rowarc["dd"];

        }
        //$client_yxj_Array[$gi][$id][] = $row['clientid']; //
        giveme2($row['clientid'], ($gi + 1), $wheresql);
    }

}