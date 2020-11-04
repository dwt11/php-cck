<?php
/**
 * 参数 列表
 *
 * @version        $Id:  2016年4月29日 14:46
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/datalistcp.class.php");

setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
$sql = $where = "";
if ($GLOBAMOREDEP) {
    if (empty($depid)) $depid = $GLOBALS['NOWLOGINUSERTOPDEPID'];
} else {
    if (empty($depid)) $depid = "";
}

if (empty($phone)) $phone = "";
if (empty($dtime)) $dtime = 0;
$whereSql = "where 1=1 ";
if ($depid >0) $whereSql .= " and depid='$depid' ";
if ($phone != "") $whereSql .= " AND mobilephone LIKE '%$phone%' ";
if ($dtime > 0) {
    $nowtime = time();
    $starttime = $nowtime - ($dtime * 24 * 3600);
    $whereSql .= " AND senddate>'$starttime' ";
}

$sql = "SELECT * FROM #@__interface_phoneMsg_log $whereSql   ORDER BY   id deSC";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetParameter("depid", $depid);
$dlist->SetTemplate("phoneMsgLog.htm");
$dlist->SetSource($sql);
$dlist->Display();

