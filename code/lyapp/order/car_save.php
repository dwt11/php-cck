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
$ordertype = "orderCar";
$operatorid = 0;
$jfnum100 = $dk_jf * 100;
$jbnum100 = $dk_jb * 100;
$total100 = $totalMoney * 100;
$paynum100 = $payMoney * 100;
$fh_ejjb100 = 0;
$fh_ejjf100 = 0;
$fh_sjjb100 = 0;
$fh_sjjf100 = 0;


//判断  当日车辆数量 是否购


$start_date = GetMkTime($start_date);
$end_date = GetMkTime($end_date);


$stocknumber = 0;//车辆库存数量为0不限制数量

//获取车辆数量
//判断 是否被别的车辆共享数量或共享了别的车辆的ID
//如果被别人共享  则获取  为当前车辆的库存
//如果共享别人的 则获取 上级ID的车辆库存

//如果参与 了共享  ,则查询已经使用的数量 时的GOODSID,为查询出的所有GOODSid,包含当前ID
//如果没有参与 共享 ,则goodsid为当前车辆的ID
$goodsid_kc = $goodsid;


$sql77777 = ("SELECT  GROUP_CONCAT(goodsid) as goodsid_temp,fatherNumberID  FROM x_goods_addon_car INNER JOIN x_goods ON x_goods_addon_car.goodsid=x_goods.id WHERE `status`=0 AND   fatherNumberID='$goodsid' OR (fatherNumberID>0 AND goodsid='$goodsid' ) ");
$row7777 = $dsql->GetOne($sql77777);
if (isset($row7777["fatherNumberID"]) && $row7777["fatherNumberID"] > 0) {
    //如果存在关联

    if ($row7777["fatherNumberID"] == $goodsid) {
        //如果当前商品是一级商品 被别人共享
        $goodsid_kc = $row7777["goodsid_temp"] . ",{$goodsid}";//加上自己本身
        $sql5555888 = ("SELECT  stocknumber   FROM x_goods_addon_car  WHERE goodsid='$goodsid' ");
        $row66666888 = $dsql->GetOne($sql5555888);
        if ($row66666888) $stocknumber = $row66666888["stocknumber"];
    } else {
        //共享的别人的数量
        $sql999 = ("SELECT  GROUP_CONCAT(goodsid) as goodsid_temp  FROM x_goods_addon_car  INNER JOIN x_goods ON x_goods_addon_car.goodsid=x_goods.id WHERE `status`=0 AND fatherNumberID='{$row7777["fatherNumberID"]}' ");
        $row999 = $dsql->GetOne($sql999);
        if (isset($row999) && $row999["goodsid_temp"] != "") {
            $goodsid_kc = $row999["goodsid_temp"] . ",{$row7777["fatherNumberID"]}";//加上父亲
        }
        $sql5555888 = ("SELECT  stocknumber   FROM x_goods_addon_car  WHERE goodsid='{$row7777["fatherNumberID"]}' ");
        $row66666888 = $dsql->GetOne($sql5555888);
        if ($row66666888) $stocknumber = $row66666888["stocknumber"];
    }
} else {
    $sql5555888 = ("SELECT  stocknumber   FROM x_goods_addon_car  WHERE goodsid='$goodsid' ");
    $row66666888 = $dsql->GetOne($sql5555888);
    if ($row66666888) $stocknumber = $row66666888["stocknumber"];
}

$isyy = true;//是否可约
$daynumb = abs($end_date - $start_date) / 86400;
for ($idddd = 0; $idddd <= $daynumb; $idddd++) {
    $nowday_int = $start_date + $idddd * 86400;
    //获取 所选日期   所选商品  已经预订 的数量
    $sql5555 = ("SELECT  sum(carNumb) AS yynumb
                    FROM x_order_addon_car 
                    LEFT JOIN x_order order1 ON order1.id = x_order_addon_car.orderid
                    WHERE order1.isdel=0 AND  order1.sta=1 
                    AND '$nowday_int' BETWEEN start_date AND end_date 
                    AND goodsid IN ($goodsid_kc)
                    GROUP BY FROM_UNIXTIME(start_date, '%Y%m%d') 
                     order by yynumb desc");//order by yynumb desc取最大的数 这个可能出现多个
    $row66666 = $dsql->GetOne($sql5555);
    //dump($sql5555);
    $yynumb = 0;
    if ($row66666) {
        $yynumb = $row66666["yynumb"];
    }


    //dump("stocknumber" . $stocknumber);
    // dump("yynumb" . $yynumb);
    //dump("carNumb" . $carNumb);
    // dump(" nowday" . GetDateNoYearMk($nowday_int));
    if ($stocknumber > 0) {
        //如果车辆数量 大于0则判断 是否购预约
        $sy_stocknumber = $stocknumber - $yynumb;
        //顾客 预约的车辆是否大于库存
        //  dump(" sy_stocknumber" . $sy_stocknumber);
        if ($carNumb > $sy_stocknumber) {
            //有一个不符合就退出
            $date_str = GetDateNoYearMk($nowday_int);
            $info = "所选日期{$date_str}没有可用车辆";
            // dump($info);
            $aa = array(
                "info" => $info,
                "jsApiParameters" => "",
                "orderid" => ""
            );
            echo json_encode($aa);
            exit();
        }
    }

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
$orderId = "";//订单Id
if (count($orderReturnStr_array) > 0) {
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


$tel = $mobilephone;
$sql = "INSERT INTO `#@__order_addon_car` ( `orderid`, `goodsid`, `carNumb`, `start_date`, `end_date` , `realname`, `tel`, `state`)
           VALUES ('$orderid', '$goodsid', '$carNumb', '$start_date', '$end_date' , '$realname', '$tel', '0');";
$dsql->ExecuteNoneQuery($sql);


//---------------------------------------创建订单附加


//---------------------------------------订单支付过程
if (empty($paytype)) $paytype = "";
//dump($paytype);
if ($paynum100 == 0) {
    $paytime = time();
    $sql = "UPDATE `#@__order`    SET `paytype`='0元',sta=sta+1,paytime='$paytime',pay_transaction_id='' WHERE id='$orderid';    ";
    $dsql->ExecuteNoneQuery($sql);
    $aa = array(
        "info" => "订单创建成功",
        "jsApiParameters" => "",
        "orderid" => $orderid
    );

} else {
    if ($paytype == "weixin") {
        $openId = GetClientOpenID($CLIENTID);//获取 客户信息 openid
        $ordercodetime = ($orderCode . "-" . date("His"));

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
}
//---------------------------------------订单支付过程

echo json_encode($aa);
exit();
