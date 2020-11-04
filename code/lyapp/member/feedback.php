<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");

if (empty($dopost)) $dopost = '';

/*---------------------
 function action_save(){ }
 ---------------------*/
CheckRank();

$query = "SELECT * FROM #@__feedback where clientid='$CLIENTID' ORDER BY id DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize = 5;
$dlist->SetTemplate("feedback.htm");
$dlist->SetSource($query);
$dlist->Display();


