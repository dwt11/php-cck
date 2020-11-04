<?php
require_once(dirname(__FILE__) . "/../include/config.php");
$orderid = trim(preg_replace("#[^0-9]#", '', $orderid));
if (empty($orderid)) $orderid = '';
if ($orderid == '') {
    echo("非法参数!");
    exit();
}

$return_str = ReturnOrder($orderid, $CLIENTID, $orerid=0);



echo($return_str);
exit();







