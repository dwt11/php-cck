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
$ordertype = "orderHyk";
$jfnum100 = $dk_jf * 100;
$jbnum100 = $dk_jb * 100;
$total100 = $totalMoney * 100;
$paynum100 = $payMoney * 100;
$fh_ejjb100 = $fh_ejjb * 100;
$fh_ejjf100 = $fh_ejjf * 100;
$fh_sjjb100 = $fh_sjjb * 100;
$fh_sjjf100 = $fh_sjjf * 100;

//---------------------------------------创建通用主订单

$orderReturnStr = CreateOrder(
    $clientid,
    $ordertype,
    $desc,
    $jfnum100,
    $jbnum100,
    $operatorid=0,
    $total100,
    $paynum100,
    $benefitCreatetime,
    $fh_ejjb100,
    $fh_ejjf100,
    $fh_sjjb100,
    $fh_sjjf100,
    $buynumb
);
$orderReturnStr_array = explode(",", $orderReturnStr);
$orderInfo = "";      //订单操作成功与否信息
$orderCode = "";//订单编号
$orderId = "";//订单Id
if (is_array($orderReturnStr_array)) {
    $orderInfo = $orderReturnStr_array[0];      //订单操作成功与否信息
    $orderCode = $orderReturnStr_array[1];//订单编号
    $orderid = $orderReturnStr_array[2];//订单Id
}
//dump($orderReturnStr);
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

$sqladdordergoods = "
                    INSERT INTO `#@__order_addon_hyk` ( `orderid`,`goodsid`,`buynumb`)
                    VALUES ( '$orderid','$goodsid','$buynumb');";
$dsql->ExecuteNoneQuery($sqladdordergoods);
//---------------------------------------创建订单附加


//---------------------------------------订单支付过程

if (empty($paytype)) $paytype = "";
//dump($paytype);
if ($paytype == "weixin") {

    $openId = GetClientOpenID($CLIENTID);//获取 客户信息 openid
    $ordercodetime = ( $orderCode . "-" . date("His"));

    $jsApiParameters = GetJsApiParameters("商品购买", $ordercodetime, $paynum100, $openId, $DEP_WEBSITE_NAME."/lyapp/pay/wx_notify.php");

    //获取微信支付参数,返回给order.js
    //在微信 的回调文件里,再写入成功支付信息
    if ($jsApiParameters != "") {
        $aa = array(
            "info" => "支付",
            "jsApiParameters" => $jsApiParameters,
            "orderid" => $orderid
        );
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
    saveTruePayOrder($result, "微信");
    $aa = array(
        "info" => "支付成功",
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
