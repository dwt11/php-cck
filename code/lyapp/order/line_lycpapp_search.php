<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();




$isAppt=GetIdcardIStrueAppt($idcard, $appttime);

if ($isAppt) {
    echo "0";
} else {
    echo "1";
}
exit;