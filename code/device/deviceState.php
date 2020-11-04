<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("device.functions.php");
require_once(DWTINC . "/fields.func.php");

$t1 = ExecTime();
$typeid = isset($typeid) ? intval($typeid) : 0;
if (empty($keyword)) $keyword = '';
if (empty($state)) $state = '';
$tl = new DeviceTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetDeviceTypeOptionS();  //搜索表单的分类值//GetOptionArray

$whereSql = " WHERE 1=1 ";

if ($state != "") {
    $whereSql .= "AND state='$state' ";
}
if ($keyword != "") {
    $whereSql .= "AND (
                        #@__device.`devicename` LIKE '%$keyword%' 
                         OR #@__device.`devicecode` LIKE '%$keyword%' 
                        )";
}
if($typeid > 0)
{
    $whereSql .= " AND `typeid` IN (" . $tl->GetDeviceSonIds() . ")";    //搜索用的
}

//获得数据表名
$sql= "SELECT  * FROM #@__device 
       LEFT JOIN #@__device_addon_automobile ON #@__device_addon_automobile.deviceid=#@__device.id
 $whereSql   ORDER BY   id ASC ";

//初始化
$dlist = new DataListCP();
$dlist->pageSize = 10;
//GET参数
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('typeid', $typeid);
$dlist->SetParameter('state', $state);

//模板
$s_tmplets = 'deviceState.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
