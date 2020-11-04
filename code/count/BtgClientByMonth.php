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

global $day_data_array;
//$day_data_array = array();//按天的数据,初始化,如果获取过就不再获取
if (!isset($keyword)) $keyword = '';
if (!isset($nowmonth)) $nowmonth = date('Y-m', time());


setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");

$keyword = isset($keyword) ? $keyword : "";
$whereSql = "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   cl.realname LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.mobilephone LIKE '%$keyword%') "; //qq

}
/*
$whereSqlaa = "";
if ($keyword != "") {
    $whereSqlaa .= "And ( ";
    $whereSqlaa .= "   claa.realname LIKE '%$keyword%'"; //qq
    $whereSqlaa .= "  or claa.mobilephone LIKE '%$keyword%') "; //qq

}*/


$day_s = ($nowmonth . "-01 00:00:00");

$day_d = ($nowmonth . "-31 23:59:59");



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
$dlist->pageSize = 15;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('nowmonth', $nowmonth);


//模板
if (empty($s_tmplets)) $s_tmplets = 'BtgClientByMonth.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


function getNumb($clientid, $js, $nowmonth = "", $nowday = "")
{

    //171009修改,这里的一级 LIKE '订单删除同时删除赠送的金币%' AND jb.jbnum='-5000' 已经不起作用,因为一级的赚送金币改为了30
    global $dsql;
    $wheresql = "";
    if ($nowmonth != "") $wheresql = "  AND FROM_UNIXTIME(jb.createtime,'%Y-%m')='$nowmonth' ";
    if ($nowday != "") $wheresql = "  AND FROM_UNIXTIME(jb.createtime,'%Y-%m-%d')='$nowday' ";
    if ($js == 1) $wheresql .= " AND  jb.desc LIKE '下级会员购买赠送%'   ";
    if ($js == 2) $wheresql .= " AND  jb.desc LIKE '下下级会员购买赠送%'   ";
    /*$questr = "select sum(jb.jbnum) as dd  FROM #@__clientdata_jblog jb  WHERE
                      (jb.desc LIKE '下级会员购买赠送%' OR  (jb.`desc`  LIKE '订单删除同时删除赠送的金币%'  AND jb.jbnum='-5000' )) AND jb.clientid='$clientid'   AND jb.isdel=0
                    /*筛选出撤消了的金币，如果当前ID，在期中，则不计算为返利金币
                    如果未找到撤消 则赋值为空，如果不赋值 则会出错
                    *
                    $wheresql
                    AND
                                IFNULL(
                                        (
                                          SELECT CONCAT(GROUP_CONCAT(jbaa.info),',') FROM #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0                   $wheresql AND jbaa.desc LIke '操作错误金币撤消' AND jbaa.clientid='$clientid'
                                        )
                                        ,''
                                       )

                        NOT LIKE CONCAT('%原编号',jb.id,',%')

                   GROUP BY jb.clientid  ";
      if ($js == 2) $questr = "SELECT count(jb.jbnum) as dd  FROM #@__clientdata_jblog jb  WHERE
                            (jb.desc LIKE '下下级会员购买赠送%' OR  (jb.`desc`  LIKE '订单删除同时删除赠送的金币%' AND jb.jbnum='-3000'))  AND jb.clientid='$clientid'   AND jb.isdel=0
                                          $wheresql
                           AND
                            IFNULL(
                                    (
                                      SELECT CONCAT(GROUP_CONCAT(jbaa.info),',') FROM #@__clientdata_jblog jbaa  WHERE    jbaa.isdel=0     $wheresql AND jbaa.desc LIke '操作错误金币撤消' AND jbaa.clientid='$clientid'
                                    )
                                    ,''
                                   )
                                                not like CONCAT('%原编号',jb.id,',%')
                          group by jb.clientid  "; */


    $questr = "SELECT count(jb.jbnum) as dd  FROM #@__clientdata_jblog jb
                LEFT JOIN   #@__order ON #@__order.id=jb.orderid
                INNER JOIN   #@__order_addon_ztc ON #@__order.id=#@__order_addon_ztc.orderid
                WHERE
                    x_order.sta=1 AND (x_order.isdel=0 OR x_order.isdel=2 OR x_order.isdel=3)/*挂失了的卡*/
                     AND jb.clientid='$clientid'  
                      AND                     jb.isdel=0
                     $wheresql
                    GROUP BY jb.clientid  
                 ";

    $rowarc = $dsql->GetOne($questr);
    // dump($questr);
    if (is_array($rowarc)) {
        $str = $rowarc['dd'];
        //$jbnum100_total = $rowarc['dd'];
        //$jbnum_total = $jbnum100_total / 100;
        //$str = "金额：$jbnum_total 人数：" . $jbnum_total / 50;
        //$str = $jbnum_total / 50;
        // if ($js == 2) $str = "金额：" . $rowarc["dd"] . " 人数：" . $rowarc['dd'] / 30;
        // if ($js == 2) $str = $jbnum_total / 30;
        return $str;
    }
    return "0";
}
