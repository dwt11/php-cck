<?php
/**
 * 获取购物车数量
 */
require_once("../include/config.php");
if (empty($action)) $action = '';
//dump($cfg_ml);

if (empty($CLIENTID)) $CLIENTID = '0';

$GWCnumb = 0;
$query = "SELECT count(id) as dd FROM #@__ordergwc             where clientid='$CLIENTID'  ";
$row = $dsql->GetOne($query);
if (isset($row['dd'])) $GWCnumb = (int)$row['dd'];

echo $GWCnumb;
