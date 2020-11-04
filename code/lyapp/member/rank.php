<?php
 require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP. "/datalistcp.class.php");

CheckRank();
$scroeInfo = GetClientType("score", $CLIENTID);
//dump($scroeInfo);
$scroeInfo_array = explode(",", $scroeInfo);
$scoreName = $scroeInfo_array[1];
$scoresnum=$scroeInfo_array[0];


$query = "SELECT * FROM #@__clientdata_ranklog WHERE clientid='$CLIENTID'    ORDER BY   ranktime DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 5;
$dlist->SetTemplate("rank.htm");
$dlist->SetSource($query);
$dlist->Display();



