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
if (empty($realname)) $realname = "";
if (empty($mobilephone)) $mobilephone = "";
if (empty($logfilename)) $logfilename = "";
if (empty($cip)) $cip = "";
if (empty($dtime)) $dtime = 0;
if ($realname != "") $where .= " AND (clw.nickname like '%$realname%' or #@__feedback.realname like '%$realname%' )";
if ($mobilephone != "") $where .= " AND #@__client.mobilephone like '%$mobilephone%' ";
if ($cip != "") $where .= " AND #@__feedback.cip LIKE '%$cip%' ";
if ($logfilename != "") $where .= " AND #@__feedback.filename LIKE '%$logfilename%' ";
if ($complete == 1) $where .= " AND #@__feedback.completetime>0 ";
if ($complete == 2) $where .= " AND #@__feedback.completetime=0 ";

if ($dtime > 0) {
    $nowtime = time();
    $starttime = $nowtime - ($dtime * 24 * 3600);
    $where .= " AND #@__feedback.dtime>'$starttime' ";
}
$sql = "SELECT #@__feedback.*,#@__client.mobilephone,#@__client.realname,clw.sex,clw.nickname,clw.photo
      FROM #@__feedback
      LEFT JOIN #@__client ON #@__client.id=#@__feedback.clientid
      LEFT JOIN #@__client_weixin clw ON #@__client.id=clw.clientid
      WHERE 1=1 $where   ORDER BY   #@__feedback.id DESC";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
//$dlist->SetParameter("userid",$userid);
$dlist->SetParameter("realname", $realname);
$dlist->SetParameter("mobilephone", $mobilephone);
$dlist->SetParameter("cip", $cip);
$dlist->SetParameter("logfilename", $logfilename);
$dlist->SetParameter("complete", $complete);
$dlist->SetParameter("dtime", $dtime);
$dlist->SetTemplate("feedback.htm");
$dlist->SetSource($sql);
$dlist->Display();


