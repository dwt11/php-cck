<?php
/**
扫描车辆二维码后检票
用户扫描车牌后,判断是哪类型的用户,然后跳转
普通 会员跳转到  旅游线路下判断是否有线路 然后检票
 *
 * 司乘人员  跳转到driver下  检票
 */
require_once("include/config.php");
CheckRank();

if (empty($deviceid)) $deviceid = '';
if (empty($did)) $did = '';
//dump($cfg_ml);
if($deviceid==""||$did==""){
    ShowMsg("参数无效");
    exit;

}

$isscry=false;//是否司乘人员
$query = "SELECT COUNT(emp_id) AS dd FROM #@__emp_client WHERE clientid='$CLIENTID'   ";
//dump($query);
$rowscry = $dsql->GetOne($query);
//没有信息就不显示 161101
if (isset($rowscry["dd"]) && $rowscry["dd"]>0) {
    $isscry = true;
}
//dump($isscry);
if(!DEBUG_LEVEL&&!$isscry){
    echo("<font size='32px'>无权检票</font>");
    exit;

}

//跳转并传递参数
header("location:driver/appQuery.php?deviceid=$deviceid");
exit;
//先不做其他 人乘车了


/*CheckRank();
//dump($cfg_ml);
$isscry=false;//是否司乘人员
$query = "SELECT COUNT(emp_id) AS dd FROM #@__emp_client WHERE clientid='$CLIENTID'   ";
$rowscry = $dsql->GetOne($query);
//没有信息就不显示 161101
if (isset($rowscry["dd"]) && $rowscry["dd"]>0) {
    $isscry = true;
}


if($isscry){
    $dpl = new DWTTemplate();
    $tpl = "QRdeviceChickin.htm";
//dump($tpl);
    $dpl->LoadTemplate($tpl);
    $dpl->display();

}else{
    //普通会员 检票上车
    header("location:/service/checkIn.php?deviceid=$deviceid");
}*/