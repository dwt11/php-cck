<?php
require_once(dirname(__FILE__) . "/../include/config.php");

CheckRank();

//------------------------------返回值初始化
//"info" => "",   提示信息
//"jsApiParameters" => "",支付字符串
//"orderid" => ""  订单ID
$aa = array(
    "info" => "",
    "jsApiParameters" => "",
    "orderid" => ""
);

//------------变量预处理
$clientid = $CLIENTID;
$ordertype = "orderLycp";
$jfnum100 = $dk_jf * 100;
$jbnum100 = $dk_jb * 100;
$total100 = $totalMoney * 100;
$paynum100 = $payMoney * 100;
$fh_ejjb100 = 0;
$fh_ejjf100 = 0;
$fh_sjjb100 = 0;
$fh_sjjf100 = 0;
$operatorid = 0;


//判断是否可以预订


//---------------------------------------得到预订 的卡信息
$addon_array = array();
//其他人的数据获取
for ($goodsi = 1; $goodsi < 16; $goodsi++) {
    $realname = 'realname_' . $goodsi;
    $mobilephone = 'mobilephone_' . $goodsi;
    $idcard = 'idcard_' . $goodsi;
    if (!empty(${$realname}) || !empty(${$mobilephone}) || !empty(${$idcard})) {
        $addon_array[] = array(
            "orderlistztcid" => 0,
            "realname" => ${$realname},
            "tel" => ${$mobilephone},
            "idcard" => ${$idcard}
        );
    }
}

//从直通车卡获取 乘车人信息
$cckid_array = explode("|", $cckids);
foreach ($cckid_array as $cckid) {
    if ($cckid != "") {
        $query = "SELECT `name`,tel,idcard FROM #@__order_addon_ztc    WHERE   id='$cckid' ";
        $arcRow = $dsql->GetOne($query);
        if (is_array($arcRow)) {
            $addon_array[] = array(
                "orderlistztcid" => $cckid,
                "realname" => $arcRow["name"],
                "tel" => $arcRow["tel"],
                "idcard" => $arcRow["idcard"]
            );
        }
    }
}


//这个过程,在显示选择身份证界面已经检查过,这里再检查一下
//检查当日是否预约过
$isappt = false;//没有预约过
if (count($addon_array) > 0) {
    foreach ($addon_array as $info_array) {
        $idcard = $info_array["idcard"];
        $isAppt = GetIdcardIStrueAppt($idcard, $appttime);

        if ($isAppt) {
            $info = '所选身份证已经预约过当日线路,请重新选择';
            $aa = array(
                "info" => $info,
                "jsApiParameters" => "",
                "orderid" => ""
            );
            echo json_encode($aa);
            exit();
        }

    }
} else {
    $info = '没有有效的乘车人员,请重新选择';
    $aa = array(
        "info" => $info,
        "jsApiParameters" => "",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();

}


//判断 时间是否合适
if (!GetLineBeforHoursIStrue($lineid, $appttime)) {
    $info = '已超过预约截止时间,不能预约';
    $aa = array(
        "info" => $info,
        "jsApiParameters" => "",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}


//判断  是否超员

$seatsnumb = GetLineSeatsNumb($lineid, $appttime, $buynumb);
//dump($seatsnumb);
if ($seatsnumb === 0) {
    $info = '此线路满员';
    $aa = array(
        "info" => $info,
        "jsApiParameters" => "",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}
if ($seatsnumb > 0) {
    $info = "剩下{$seatsnumb}座位可以预约,请修改人数";
    $aa = array(
        "info" => $info,
        "jsApiParameters" => "",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}


//---------------------------------------创建通用主订单
$desc = urldecode($desc);
$orderReturnStr = CreateOrder(
    $clientid,
    $ordertype,
    $desc,
    $jfnum100,
    $jbnum100,
    $operatorid,
    $total100,
    $paynum100,
    $benefitInfo,
    $fh_ejjb100,
    $fh_ejjf100,
    $fh_sjjb100,
    $fh_sjjf100,
    $buynumb
);
$orderReturnStr_array = explode(",", $orderReturnStr);
$orderInfo = "";      //订单操作成功与否信息
$orderCode = "";//订单编号
$orderid = "";//订单Id
if (count($orderReturnStr_array) > 0) {
    $orderInfo = $orderReturnStr_array[0];      //订单操作成功与否信息
    $orderCode = $orderReturnStr_array[1];//订单编号
    $orderid = $orderReturnStr_array[2];//订单Id
}
//dump($orderReturnStr_array);
if ($orderInfo != "订单创建成功") {
    $aa = array(
        "info" => $orderInfo,
        "jsApiParameters" => "",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}
//---------------------------------------创建通用主订单


//---------------------------------------创建订单附加

 $orderCAOYUAN = false;//是否超卖
if (count($addon_array) > 0 ) {
    foreach ($addon_array as $info_array) {
        $orderlistztcid = $info_array["orderlistztcid"];
        $realname = $info_array["realname"];
        $tel = $info_array["tel"];
        $idcard = $info_array["idcard"];

        $orderCAOYUAN = GetLineCAOYUAN($lineid, $appttime, 1);//180325增加超卖判断
        //dump($orderCAOYUAN);
        //获取座位号   当前线路 预约时间 不重复的线路号
        $seatNumber = GetLineAppttimeMaxSeatsNumb($lineid, $appttime, $deviceid = "");
        $sql = "INSERT INTO `#@__order_addon_lycp` ( `orderid`, `goodsid`, `lineid`, `orderlistztcid`, `appttime`,  `tjsite`, `seatNumber`, `realname`, `tel`, `idcard`)
                    VALUES ('$orderid', '$goodsid', '$lineid', '$orderlistztcid', '$appttime' ,'$tjsite', '$seatNumber', '$realname', '$tel', '$idcard');";
        $dsql->ExecuteNoneQuery($sql);

    }
} else {
    //这里本来准备做 删除过程,但考虑到要用户知晓情况,还是让用户手工来操作吧170224
    $aa = array(
        "info" => "订单错误",
        "jsApiParameters" => "",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}
if ($orderCAOMAI) {
    $aa = array(
        "info" => "座位数不够",
        "jsApiParameters" => "",
        "orderid" => ""
    );
    //如果失败,则自动退款
    CancelOrderNoPay($orderid, $clientid, $orerid = 0);
    echo json_encode($aa);
    exit();
}
//---------------------------------------创建订单附加


//---------------------------------------订单支付过程

if (empty($paytype)) $paytype = "";
//dump($paytype);
if ($paytype == "weixin") {
    if ($paynum100 == 0) {
        $total_fee = $paynum100;
        $json = "{
            \"appid\":\"wx111111\",
            \"attach\":[],
            \"bank_type\":\"CFT\",
            \"cash_fee\":\"1\",
            \"fee_type\":\"CNY\",
            \"is_subscribe\":\"Y\",
            \"mch_id\":\"222222222\",
            \"nonce_str\":\"CUeBVaLSEMBYxgsm\",
            \"openid\":\"11111111111\",
            \"out_trade_no\":\"$orderCode-144850\",
            \"result_code\":\"SUCCESS\",
            \"return_code\":\"SUCCESS\",
            \"return_msg\":\"OK\",
            \"sign\":\"EAE6E5AC280E341BD9D2E7C202D0F96A\",
            \"time_end\":\"20160922144903\",
            \"total_fee\":\"$total_fee\",
            \"trade_state\":\"SUCCESS\",
            \"trade_type\":\"JSAPI\",
            \"transaction_id\":\"55555555555555555555555\"
            }";
        $result = json_decode($json, true);//dump($result);
        saveTruePayOrder($result, "0元");
        $aa = array(
            "info" => "订单创建成功",
            "jsApiParameters" => "",
            "orderid" => $orderid
        );

    } else {
        $openId = GetClientOpenID($CLIENTID);//获取 客户信息 openid
        $ordercodetime = ($orderCode . "-" . date("His"));

        $jsApiParameters = GetJsApiParameters("商品购买", $ordercodetime, $paynum100, $openId, $DEP_WEBSITE_NAME . "/lyapp/pay/wx_notify.php");

        //获取微信支付参数,返回给order.js
        //在微信 的回调文件里,再写入成功支付信息
        if ($jsApiParameters != "") {
            $aa = array(
                "info" => "支付",
                "jsApiParameters" => $jsApiParameters,
                "orderid" => $orderid
            );
        }
    }
} else if ($paytype == "none112101") {
    //模拟支付过程
    $total_fee = $paynum100;
    $json = "{
            \"appid\":\"wx111111\",
            \"attach\":[],
            \"bank_type\":\"CFT\",
            \"cash_fee\":\"1\",
            \"fee_type\":\"CNY\",
            \"is_subscribe\":\"Y\",
            \"mch_id\":\"222222222\",
            \"nonce_str\":\"CUeBVaLSEMBYxgsm\",
            \"openid\":\"11111111111\",
            \"out_trade_no\":\"$orderCode-144850\",
            \"result_code\":\"SUCCESS\",
            \"return_code\":\"SUCCESS\",
            \"return_msg\":\"OK\",
            \"sign\":\"EAE6E5AC280E341BD9D2E7C202D0F96A\",
            \"time_end\":\"20160922144903\",
            \"total_fee\":\"$total_fee\",
            \"trade_state\":\"SUCCESS\",
            \"trade_type\":\"JSAPI\",
            \"transaction_id\":\"55555555555555555555555\"
            }";
    $result = json_decode($json, true);//dump($result);
    saveTruePayOrder($result, "模拟支付");
    $aa = array(
        "info" => "订单创建成功",
        "jsApiParameters" => "",
        "orderid" => $orderid
    );
} else {
    $aa = array(
        "info" => "订单创建成功未支付",
        "jsApiParameters" => "",
        "orderid" => $orderid
    );
}
//---------------------------------------订单支付过程

echo json_encode($aa);
exit();
