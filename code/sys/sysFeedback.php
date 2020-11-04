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

if (empty($complete)) $complete = "";
if (empty($userName)) $userName = "";
if (empty($logfilename)) $logfilename = "";
if (empty($cip)) $cip = "";
if (empty($dtime)) $dtime = 0;
if ($userName != "") $where .= " AND #@__sys_admin.userName like '%$userName%' ";
if ($cip != "") $where .= " AND #@__sys_feedback.cip LIKE '%$cip%' ";
if ($logfilename != "") $where .= " AND #@__sys_feedback.filename LIKE '%$logfilename%' ";
if ($complete == 1) $where .= " AND #@__sys_feedback.completetime>0 ";
if ($complete == 2) $where .= " AND #@__sys_feedback.completetime=0 ";

if ($dtime > 0) {
    $nowtime = time();
    $starttime = $nowtime - ($dtime * 24 * 3600);
    $where .= " AND #@__sys_feedback.dtime>'$starttime' ";
}
$sql = "SELECT #@__sys_feedback.*,#@__sys_admin.userName FROM #@__sys_feedback
     LEFT JOIN #@__sys_admin ON #@__sys_admin.id=#@__sys_feedback.userid
     WHERE 1=1 $where   ORDER BY   #@__sys_feedback.id DESC";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
//$dlist->SetParameter("userid",$userid);
$dlist->SetParameter("userName", $userName);
$dlist->SetParameter("cip", $cip);
$dlist->SetParameter("logfilename", $logfilename);
$dlist->SetParameter("complete", $complete);
$dlist->SetParameter("dtime", $dtime);
$dlist->SetTemplate("sysFeedback.htm");
$dlist->SetSource($sql);
$dlist->Display();


