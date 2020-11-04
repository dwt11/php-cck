<?php
/*170129
 * 这里如果以后权限判断 ,则单独判断 当前登录用户所属公司的商品,
 * 同一个公司下的所有商品都可以选择,
 * 不参与后台系统的权限判断
 * */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');

// requir
ExecTime();
if (!isset($keyword)) $keyword = '';
if (!isset($dopost)) $dopost = '';


require_once( "../include/role.class.php");
$roleCheck = new roleClass();
$roleCheck->RoleCheckToBool("client/client.php");//用于权限判断

$whereSql = " where #@__client_depinfos.isdel=0 ";//不展示锁定会员

if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   cl.realname LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.mobilephone LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.address LIKE '%$keyword%'"; //qq
    $whereSql .= " or cl.tag LIKE '%$keyword%'";  //
    //$whereSql .= " or cl.description LIKE '%$keyword%' )";//备注  删除这个搜索不然垃圾信息太多
    $whereSql .= "  )";//备注

}

//客户编辑时,选择介绍人,原来已经有了介绍人,则不显示原来的人名
if (!isset($clientid)) $clientid = '';
if ($clientid != "") {
    $whereSql .= "AND cl.`id` <> $clientid ";
}
//
if (!isset($depid)) $depid = '';
if ($depid != "") {
    $whereSql .= " AND #@__client_depinfos.depid='$depid' ";
}
if ($dopost == "jbtx") $whereSql .= " and jbnum>0";


//按会员类型搜索
$rank = isset($rank) ? $rank : "";
$rankleftjoin="";
if ($rank != "") {
    $whereSql .= "And rankinfostr like '%$rank%'";
    $rankleftjoin="              /*获取会员类型*/
            LEFT JOIN (
                        SELECT clientid,GROUP_CONCAT(x_clientdata_ranklog.rank,'|',x_clientdata_ranklog.rankcutofftime) AS rankinfostr 
                        FROM `x_clientdata_ranklog`
                        
                         GROUP BY `x_clientdata_ranklog`.clientid
                    ) ranklogtable ON cl.id=ranklogtable.clientid
";
}
//获得数据表名
$sql = "SELECT  cl.realname,cl.mobilephone,cl.id,
          #@__client_depinfos.depid, #@__client_depinfos.clientid,
          cladd.jfnum,cladd.jbnum,cladd.sponsorid,cladd.idcard
          FROM #@__client_depinfos
             LEFT JOIN #@__client cl on cl.id=#@__client_depinfos.clientid
             LEFT JOIN #@__client_addon cladd on cl.id=cladd.clientid
             $rankleftjoin
            $whereSql
               ORDER BY   #@__client_depinfos.clientid desc";

//初始化
//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('rank', $rank);
$dlist->SetParameter('dopost', $dopost);
$dlist->SetParameter('clientid', $clientid);
$dlist->SetParameter('depid', $depid);

//模板
$s_tmplets = 'client.select.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
