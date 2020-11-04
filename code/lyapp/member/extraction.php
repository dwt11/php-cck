<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
CheckRank();


$query = "SELECT *
            FROM #@__clientdata_extractionlog  
             WHERE isdel=0  AND clientid='$CLIENTID'   ORDER BY   createtime DESC ";

$dlist = new DataListCP();
$dlist->pageSize = 5;
$dlist->SetTemplate("extraction.htm");
$dlist->SetSource($query);
$dlist->Display();


