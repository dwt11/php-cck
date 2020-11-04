<?php
/**
 * @version        $Id: index.php 1 8:24 2010年7月9日
 * @package        DWTCMS.Member
 * @license        http://help.DWTcms.com/usersguide/license.html
 * @link           http://www.DWTcms.com
 */
require_once("include/config.php");
if (empty($action)) $action = '';
//dump($cfg_ml);

CheckRank();



//require_once("member/updateWeixinInfo.php");//如果照片和呢称为空  则更新会员的微信信息



//dump($cfg_ml);
//定义用户名显示
$displayClientName = "";
$isphone = false;
/*if ($cfg_ml->M_UserName != "") {
    $displayClientName = $cfg_ml->M_UserName;
} else if (!empty($cfg_ml->fields["mobilephone"])) {*/
$mobilephone = GetPhoneCode($cfg_ml->fields["mobilephone"]);
if ($mobilephone != "") $mobilephone = "<i class=\"fa fa-mobile-phone\"></i> " . $mobilephone;
/*}*/
//$nickName=$cfg_ml->fields["nickname"];
$nickName = $cfg_ml->fields["realname"];
if ($nickName == "") $nickName = $cfg_ml->fields["nickname"];//如果用户未填写姓名,则使用他的微信名称

$photo = $cfg_ml->fields["photo"];
if ($photo == "") $photo = "../images/zw.jpg";


if (!empty($cfg_ml->fields["mobilephone_check"]) && $cfg_ml->fields["mobilephone_check"] == "1") {
    $isphone = true;
}


//---------------成长值
$scroeInfo = GetClientType("score", $CLIENTID);
$scroeInfo_array = explode(",", $scroeInfo);
$scoreName = $scroeInfo_array[1];
//$scoreNumb=$scroeInfo_array[0];

//会员类型
$rankInfo = GetClientType("rank", $CLIENTID);
if ($rankInfo != "") {
    $rankInfo_array = explode(",", $rankInfo);
    $number = count($rankInfo_array);
    $plus = "";
    if ($number > 1) $plus = "($number)";
    $rankName = $rankInfo_array[0] . "会员" . $plus;
//$scoreNumb=$scroeInfo_array[0];
    //dump($rankInfo);
} else {
    $rankName = "注册会员";
}

$jbnum = GetClientJBJFnumb('jb', $CLIENTID);
$jfnum = GetClientJBJFnumb('jf', $CLIENTID);

//--------------------下级返回的金币数量
$jbnum_tg = 0;
$query = "SELECT sum(jbnum) as dd FROM #@__clientdata_jblog 
            where clientid='$CLIENTID' and isdel=0 and ( `desc` like '下下级会员购买赠送%' or `desc` like '下级会员购买赠送%')  ";
$row = $dsql->GetOne($query);
if (isset($row['dd'])) $jbnum_tg = (int)$row['dd'] / 100;


//----------------未完成的订单数量提示
$order_not_num = "";
$query = "SELECT count(id) as dd FROM #@__order  WHERE sta='0'  and  isdel='0'  and clientid='$CLIENTID'";
$row = $dsql->getone($query);
if (isset($row["dd"]) && $row["dd"] > 0) {
    $order_not_num = $row["dd"];
}


//---------------未完成的提现extraction数量提示
$extraction_not_num = "";
$query = "SELECT count(id) as dd FROM #@__clientdata_extractionlog  WHERE status='0' and clientid='$CLIENTID'";
$row = $dsql->getone($query);
if (isset($row["dd"]) && $row["dd"] > 0) {
    $extraction_not_num = $row["dd"];
}
//---------------一起游数量提示
$yqy_num = "";
$query = "SELECT count(id) as dd FROM #@__ztc_share  WHERE isdel='0' and clientid_n='$CLIENTID'";
$row = $dsql->getone($query);
if (isset($row["dd"]) && $row["dd"] > 0) {
    $yqy_num = $row["dd"];
}
//---------------未使用优惠券数量提示
$yhq_num = "";
$query = "SELECT count(id) as dd FROM #@__clientdata_coupon  WHERE isuse='0' AND clientid='$CLIENTID'";
$row = $dsql->getone($query);
if (isset($row["dd"]) && $row["dd"] > 0) {
    $yhq_num = $row["dd"];
}


//------------------乘车卡数量
$ztc_num = "";
$queryztc = "SELECT count(#@__order.id) as dd 
          FROM  `#@__order`
          LEFT JOIN #@__order_addon_ztc  ON `#@__order`.id=#@__order_addon_ztc.orderid
          WHERE 
              (
                `#@__order`.clientid='$CLIENTID' AND `x_order`.ordertype='orderZtc'  AND `#@__order`.sta=1
              )
          AND `#@__order`.isdel=0 
         ";
$rowztc = $dsql->getone($queryztc);
if (isset($rowztc["dd"]) && $rowztc["dd"] > 0) {
    $ztc_num = $rowztc["dd"];
}


//-----------------未出行的预约数量提示
require_once("service/service.function.php");

$app_not_num = "";
$nowtime=time();
$whereSqlappt = " AND x_order_addon_lycp.appttime>={$nowtime}";
$query = getApptSQL($CLIENTID, $whereSqlappt, $orderby = "",$sta="2");
$row = $dsql->getone($query);
$dsql->SetQuery($query);
$dsql->Execute();

$app_not_num_tttttt = $dsql->GetTotalRow();
if ($app_not_num_tttttt > 0) {
    $app_not_num = $app_not_num_tttttt;
}


//////是否有过充值卡(有过充值卡,才显示功能)
//,以及可以用的个数
$czknumb = "";
$isczk = false;
$query = "SELECT COUNT(id) AS dd FROM #@__order WHERE clientid='$CLIENTID' AND isdel=0 AND sta=1 AND ordertype='orderCzk' ORDER BY createtime DESC ";
$rowczk = $dsql->GetOne($query);
//没有信息就不显示 161101
if (isset($rowczk["dd"]) && $rowczk["dd"] > 0) {
    $isczk = true;
    $query = "SELECT COUNT(#@__order_addon_czk.id) AS dd
                                FROM #@__order_addon_czk 
                                LEFT JOIN #@__order   order1 ON order1.id=#@__order_addon_czk.orderid
                               WHERE  clientid='$CLIENTID' AND usedate=0     ";
    $rowczklist = $dsql->GetOne($query);
    if (isset($rowczklist["dd"]) && $rowczklist["dd"] > 0) {
        $czknumb = $rowczklist["dd"];
    }
}

//////是否司乘人员

$isscry = false;
$query = "SELECT COUNT(emp_id) AS dd FROM #@__emp_client WHERE clientid='$CLIENTID'   ";
$rowscry = $dsql->GetOne($query);
//没有信息就不显示 161101
if (isset($rowscry["dd"]) && $rowscry["dd"] > 0) {
    $isscry = true;
    $fcnumb = "";
    //查询当前时间以后的发车记录
    $query = "SELECT COUNT(#@__device_automobile_uselog.id) AS dd
                                FROM #@__device_automobile_uselog
                                LEFT JOIN #@__emp_client    ON (#@__emp_client.emp_id=#@__device_automobile_uselog.driverid OR #@__emp_client.emp_id=#@__device_automobile_uselog.guideid )
                               WHERE  #@__emp_client.clientid='$CLIENTID' AND start_date>UNIX_TIMESTAMP(now())     ";
    $rowfcjl = $dsql->GetOne($query);
    if (isset($rowfcjl["dd"]) && $rowfcjl["dd"] > 0) {
        $fcnumb = $rowfcjl["dd"];
    }
}


$dpl = new DWTTemplate();
$tpl = "mycentre.htm";
//dump($tpl);
$dpl->LoadTemplate($tpl);
$dpl->display();




