<?php
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once("device.functions.php");
require_once(DWTINC . "/fields.func.php");
/*170129
 * 这里如果以后权限判断 ,则单独判断 当前登录用户所属公司的商品,
 * 同一个公司下的所有商品都可以选择,
 * 不参与后台系统的权限判断
 * */
// requir
ExecTime();

$typeid = isset($typeid) ? intval($typeid) : 0;
$appttime = isset($appttime) ? $appttime : "";
if (empty($keyword)) $keyword = '';
if (empty($targetname)) $targetname = '';//父页目标名称
$tl = new DeviceTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetDeviceTypeOptionS();  //搜索表单的分类值//GetOptionArray




$whereSql = " WHERE 1=1 ";

$appttime_str="";
if($appttime!=""){
    $appttime_str=GetDateNoYearMk($appttime);
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
$dlist->pageSize = 20;
//GET参数
$dlist->SetParameter('appttime', $appttime);
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('typeid', $typeid);
$dlist->SetParameter('targetname', $targetname);

//模板
$s_tmplets = 'device.select.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($sql);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;


/**获取 车辆状态
 *
 * 时间判断
 *
 * @param $deviceid
 * @param $appttime
 *
 * @param $state
 *
 * @return bool
 */
function getState($deviceid, $appttime,$state){
    global  $dsql;
    if($state!="正常")return "车辆状态为停用";


    $sql5555 = ("SELECT  orderid
                    FROM x_device_automobile_uselog 
                    LEFT JOIN x_order_addon_car ON x_order_addon_car.id = x_device_automobile_uselog.orderAddonId
                    LEFT JOIN x_order ON x_order_addon_car.orderid = x_order.id
                    WHERE state=1
                    AND x_order.isdel=0
                    AND '$appttime' BETWEEN x_order_addon_car.start_date AND x_order_addon_car.end_date 
                    AND deviceid='$deviceid'"

                );//order by yynumb desc取最大的数 这个可能出现多个
    $row = $dsql->GetOne($sql5555);
    if (isset($row["orderid"])&&isset($row["orderid"])>0) {
       $ordercode= GetOrderOneInfo($row["orderid"],"ordernum");
        return "<span style='font-size:10px '>订单号{$ordercode}</span>";

    }
    //dump($appttime);
    return "正常";

}