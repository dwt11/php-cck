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
if (!isset($dopost)) $dopost = '';

if ($GLOBAMOREDEP) {
    if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($depid)) $depid = "0";
}

setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
$admin_usertype = $CUSERLOGIN->getUserType();

$whereSql = " where isdel='1' ";
$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   cl.realname LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.mobilephone LIKE '%$keyword%'"; //qq
    $whereSql .= "  or cl.address LIKE '%$keyword%'"; //qq
    $whereSql .= " or clw.nickname LIKE '%$keyword%'";  //
    $whereSql .= " or cl.tag LIKE '%$keyword%'";  //
    $whereSql .= " or cl.description LIKE '%$keyword%' )";//备注

}
//按会员类型搜索
$rank = isset($rank) ? $rank : "";
if ($rank != "") {
    $whereSql .= "And cl.rank = '$rank'";
}
if ($depid != "0") {
    $whereSql .= " AND depid='$depid' ";
}


$query = "SELECT   cl.realname,cl.mobilephone,cl.mobilephone_check,cl.mobilephone_checkDate,cl.from,cl.description,
          clw.sex,clw.nickname,clw.photo,#@__client_depinfos.*,
          cladd.jfnum,cladd.jbnum,cladd.scoresnum,cladd.scorescutofftime,cladd.sponsorid
          FROM #@__client_depinfos
             LEFT JOIN #@__client cl on cl.id=#@__client_depinfos.clientid
             LEFT JOIN #@__client_addon cladd on cl.id=cladd.clientid
             LEFT JOIN #@__client_weixin clw on cl.id=clw.clientid
 $whereSql
   ORDER BY   cl.senddate DESC ";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('rank', $rank);

//模板
if (empty($s_tmplets)) $s_tmplets = 'recycling.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


function getClientDepNames($depids)
{
    global $dsql;
    $depname = "";
    $depid_array = explode(",", $depids);
    foreach ($depid_array as $depid) {
        $depname .= ($depname == "" ? "" : ",");
        $depname .= GetDepsNameByDepId($depid);
    }

    return $depname;
}


/**
 * 160516手机是否验证
 *
 * @param $ischeck
 *
 * @return string
 */
function getPhoneIsCheck($ischeck,$mobilephone_checkDate)
{
    $str="";
    //if ($ischeck == 1) $str=" 已验证";
    $datetime=GetDateTimeMk($mobilephone_checkDate);
    if($datetime!="")$str.=" <br>验证时间:$datetime";
    return $str;
}


