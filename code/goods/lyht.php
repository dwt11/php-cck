<?php
require_once("../config.php");
require_once(DWTINC.'/datalistcp.class.php');
$t1 = ExecTime();



setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");


$query = "SELECT  * FROM `x_lyht`   ORDER BY   id DESC ";

//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;

//GET参数
/*$dlist->SetParameter('typeid', $typeid);*/

//模板
$s_tmplets = 'lyht.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;
