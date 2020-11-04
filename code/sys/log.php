<?php
/**
 * 日志列表
 *
 * @version        $Id: log.php 1 8:48 13日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

require_once(DWTINC . "/datalistcp.class.php");
require_once(DWTINC . "/common.func.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
$sql = $where = "";

if (empty($userName)) $userName = "";
if (empty($logfilename)) $logfilename = "";
if (empty($cip)) $cip = "";
if (empty($dtime)) $dtime = 0;
if ($userName != "") $where .= " AND #@__sys_admin.userName like '%$userName%' ";
if ($cip != "") $where .= " AND #@__sys_log.cip LIKE '%$cip%' ";
if ($logfilename != "") $where .= " AND #@__sys_log.filename LIKE '%$logfilename%' ";

if ($dtime > 0) {
    $nowtime = time();
    $starttime = $nowtime - ($dtime * 24 * 3600);
    $where .= " AND #@__sys_log.dtime>'$starttime' ";
}
$sql = "SELECT #@__sys_log.*,#@__sys_admin.userName FROM #@__sys_log
     LEFT JOIN #@__sys_admin ON #@__sys_admin.id=#@__sys_log.adminid
     WHERE 1=1 $where   ORDER BY   #@__sys_log.lid DESC";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
//$dlist->SetParameter("adminid",$adminid);
$dlist->SetParameter("userName", $userName);
$dlist->SetParameter("cip", $cip);
$dlist->SetParameter("logfilename", $logfilename);
$dlist->SetParameter("dtime", $dtime);
$dlist->SetTemplate("log.htm");
$dlist->SetSource($sql);
$dlist->Display();


function getFileTitle($fileName = "")
{
    //获取功能标题
    require_once("../include/role.class.php");
    $roleCheck = new roleClass();
    $roleCheck->RoleCheckToBool($fileName);
    $sysFunTitle = $roleCheck->funName;
    return $sysFunTitle;


}