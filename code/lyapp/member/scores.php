<?php
 require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP. "/datalistcp.class.php");

CheckRank();
$scroeInfo = GetClientType("score", $CLIENTID);
//dump($scroeInfo);
$scroeInfo_array = explode(",", $scroeInfo);
$scoreName = $scroeInfo_array[1];
$scoresnum=$scroeInfo_array[0];


$query = "SELECT * FROM #@__clientdata_scoreslog WHERE clientid='$CLIENTID' AND isdel=0   ORDER BY   createtime DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 5;
$dlist->SetTemplate("scores.htm");
$dlist->SetSource($query);
$dlist->Display();



