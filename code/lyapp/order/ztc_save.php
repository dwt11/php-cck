<?php
require_once(dirname(__FILE__) . "/../include/config.php");

CheckRank();
DropCookie('gourl');//清空跳转页面

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
$ordertype = "orderZtc";
$operatorid = 0;
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
    $operatorid,
    $total100,
    $paynum100,
    $benefitCreatetime,
    $fh_ejjb100,
    $fh_ejjf100,
    $fh_sjjb100,
    $fh_sjjf100,
    $buynumb,
    $couponid
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

//循环15个商品,如果有商品编号,则添加到销售商品表这里的
//同时添加到销售记录表
for ($goodsi = 1; $goodsi < 6; $goodsi++) {
    $realname = 'realname_' . $goodsi;
    $mobilephone = 'mobilephone_' . $goodsi;
    $idcard = 'idcard_' . $goodsi;
    if (!empty(${$realname}) || !empty(${$mobilephone}) || !empty(${$idcard})) {
        //dump($icpic_t);
        $sqladdordergoods = "
                    INSERT INTO `#@__order_addon_ztc` ( `orderid`,`goodsid`,`name`, `tel`, `idcard`)
                    VALUES ( '$orderid','$goodsid','{${$realname}}', '{${$mobilephone}}', '{${$idcard}}');";
        $dsql->ExecuteNoneQuery($sqladdordergoods);
        if ($goodsi == 1) {
            //dump(3333);
            //如果第一个乘车卡的用户
            //如果clint表中未保存过用户的姓名，则更新用户的姓名
            //更新基本信息
            $questr = "SELECT realname  FROM `#@__client`  where  id='$CLIENTID'";
            $row = $dsql->GetOne($questr);
            // dump($row);
            if ($row["realname"] == "") {
                $query = "UPDATE #@__client SET   realname='{${$realname}}' ,pubdate='$createtime'     WHERE id='$CLIENTID'; ";
                $dsql->ExecuteNoneQuery($query);
            }


            //获取客户信息
            $questr = "SELECT idcard  FROM `#@__client_addon`  where  clientid='$CLIENTID'";
            $row = $dsql->GetOne($questr);
            if ($row["idcard"] == "") {
                $query = "UPDATE #@__client_addon SET   idcard='{${$idcard}}'     WHERE clientid='$CLIENTID'; ";
                $dsql->ExecuteNoneQuery($query);
            }
        }
    }
}
//---------------------------------------创建订单附加


//---------------------------------------订单支付过程

if (empty($paytype)) $paytype = "";
//dump($paytype);
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
    saveTruePayOrder($result, "模拟支付");
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

if (empty($isxf)) $isxf = '';
if ($isxf == 1) {

    //不管订单是否支付成功 都把以下信息更新 ,因为微信支付的信息是从另外的过程执行的

    //如果是续费的,要把照片\照片审核\订单时间更新一下


    //更新卡的订购时间(启用时间)\备注
    $sql = "UPDATE `#@__order`    SET createtime='$orderCreateTime_new',`desc`=concat('续费 ',`desc`) WHERE id='$orderid';    ";
    //dump($sql);
    $dsql->ExecuteNoneQuery($sql);

    //更新卡的子记录中的值:照片和照片审核情况
    $sql = "UPDATE `#@__order_addon_ztc`    SET idpic='$idpic',`idpic_desc`='审核通过' WHERE orderid='$orderid';    ";
    //dump($sql);
    $dsql->ExecuteNoneQuery($sql);

    //这里将此订单的会员类型时间修改为正确的
    $addRowAddtable = $dsql->GetOne("SELECT rankLenth FROM `#@__goods_addon_ztc` WHERE goodsid='$goodsid'");
    $rankLenth = $addRowAddtable["rankLenth"];
    $ranktime = $orderCreateTime_new;
    $rankcutofftime = strtotime("+{$rankLenth} month", $ranktime);
    //如果用户没有支付,则__clientdata_ranklog会没有记录,更新不成功
    $sql = "UPDATE `#@__clientdata_ranklog`    SET ranktime='$ranktime',rankcutofftime='$rankcutofftime'  WHERE   orderid='$orderid';    ";
    //dump($sql);
    $dsql->ExecuteNoneQuery($sql);



}


echo json_encode($aa);
exit();
