<?php
require_once(dirname(__FILE__) . "/../include/config.php");
require_once(DEDEINC_APP . "/datalistcp.class.php");
CheckRank();
if(!isset($isuse)) $isuse = '0';
if(!isset($dopost)) $dopost = '';

$whereSql = " WHERE clientid='$CLIENTID' ";
if ($isuse != "") $whereSql .= " AND (#@__clientdata_coupon.isuse='$isuse')";

$query = "SELECT * FROM #@__clientdata_coupon $whereSql   ORDER BY   id DESC ";
//dump($query);
$dlist = new DataListCP();
$dlist->pageSize =10;
//GET参数
$dlist->SetParameter('isuse', $isuse);
if (empty($listtemp)) $listtemp = 'coupon.htm';

if($dopost=="ajax"){
//如果是下拉的,则使用以下的模板
    $listtemp =  "coupon_ajax.htm" ;
}

$dlist->SetTemplate($listtemp);
$dlist->SetSource($query);
$dlist->Display();



