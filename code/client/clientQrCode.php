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

set_time_limit(0);

$t1 = ExecTime();


if (!isset($keyword)) $keyword = '';
if (!isset($orderby)) $orderby = '';
if (!isset($dopost)) $dopost = '';

if ($GLOBAMOREDEP) {
    if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($depid)) $depid = "0";
}

setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
$admin_usertype = $CUSERLOGIN->getUserType();

$whereSql = " where #@__client_depinfos.isdel=0  ";
$keyword = isset($keyword) ? $keyword : "";
if ($keyword != "") {
    $whereSql .= "And ( ";
    $whereSql .= "   cl.realname LIKE '%$keyword%'"; //qq
    $whereSql .= "  OR cl.mobilephone LIKE '%$keyword%'"; //qq
    $whereSql .= "  OR order1.ordernum LIKE '%$keyword%'"; //qq
    $whereSql .= "  OR orderztc.cardcode LIKE '%$keyword%'"; //qq
    $whereSql .= "  )";//备注

}



$query = "SELECT  cl.id,cl.realname,cl.mobilephone,cl.senddate,order1.ordernum,orderztc.cardcode
 FROM #@__client_depinfos
 LEFT JOIN #@__client cl on cl.id=#@__client_depinfos.clientid
LEFT JOIN #@__order order1 on order1.clientid=cl.id
LEFT JOIN #@__order_addon_ztc orderztc on order1.id=orderztc.orderid
 $whereSql
 And
 order1.id is not null and  order1.sta=1  and order1.isdel=0 and order1.ordertype='orderZtc'
   ORDER BY    cl.senddate DESC";

// dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 20;

//GET参数
$dlist->SetParameter('keyword', $keyword);

//模板
if (empty($s_tmplets)) $s_tmplets = 'clientQrCodeWeixin.htm';
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
 * 160516手机是否验证
 *
 * @param $ischeck
 *
 * @return string
 */
function getPhoneIsCheck($ischeck, $mobilephone_checkDate)
{
    $str = "";
    if ($ischeck == 1) $str = "  已验证";
    //$datetime=GetDateMk($mobilephone_checkDate);
    //if($datetime!="")$str.=" <br>验证时间：$datetime";
    return $str;
}


/**
 * 160516是否有微信 的记录
 *
 * @param $ischeck
 *
 * @return string
 */
function getIsWeixin($id)
{
    global $dsql;
    $questr = "SELECT clientid FROM `#@__client_depinfos` where  `clientid` ='$id' ";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        return true;
    }
    return false;
}

/**
 * 160516是否有登录相关的记录
 *
 * @param $ischeck
 *
 * @return string
 */
function getIsUser($id)
{
    global $dsql;
    $questr = "SELECT clientid FROM `#@__client_pw` where  `clientid` ='$id' ";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        return true;
    }
    return false;
}

