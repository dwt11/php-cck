<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
$sql = $where = "";

if (empty($realname)) $realname = "";
if ($realname != "") $where .= " AND #@__client.realname like '%$realname%' ";

$query = "SELECT #@__scores_log.*,#@__client.realname FROM #@__scores_log LEFT JOIN #@__client ON #@__client.id=#@__scores_log.clientid WHERE 1=1 $where  ORDER BY updatedate DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 10;

//GET参数
$dlist->pageSize = 20;
//$dlist->SetParameter("userid",$userid);
$dlist->SetParameter("realname", $realname);

//模板
if (empty($s_tmplets)) $s_tmplets = 'clientScoresLog.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);
$dlist->Display();


