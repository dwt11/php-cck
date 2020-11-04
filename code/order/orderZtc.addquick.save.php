<?php
/**
 * 直通车订单快速添加保存
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值


//检查身份证\电话\实体卡号是否重复
for ($goodsi = 1; $goodsi < $buynumb; $goodsi++) {
    $realname = 'realname_' . $goodsi;
    $mobilephone = 'mobilephone_' . $goodsi;
    $idcard = 'idcard_' . $goodsi;
    $cardcode = 'cardcode_' . $goodsi;
    if (!empty(${$mobilephone})) {
        $mobilephone_t = ${$mobilephone};
        $phoenISuse = ValidatePhoneISon($mobilephone_t);//新的手机号 是否已经使用
        if ($phoenISuse != "手机号可用") {
            $aa = array(
                "info" => $phoenISuse,
                "jsApiParameters" => "",
                "orderid" => ""
            );
            echo json_encode($aa);
            exit();
        }
    }
    if (!empty(${$idcard})) {
        $idcard_t = ${$idcard};
        $isidcard = Get_ztc_list_idcard_search($idcard_t);
        if ($isidcard === "0") {
            $info = '所选身份证已经购买过';
            $aa = array(
                "info" => $info,
                "jsApiParameters" => "",
                "orderid" => ""
            );
            echo json_encode($aa);
            exit();
        }
    }
    if (!empty(${$cardcode})) {
        $cardcode_t = ${$cardcode};
        $checkCardCode = ValidateZtcCardCodeISon($cardcode_t);
        if ($checkCardCode != "可以使用") {
            $aa = array(
                "info" => $checkCardCode,
                "jsApiParameters" => "",
                "orderid" => ""
            );
            echo json_encode($aa);
            exit();
        }
    }
}
//------------------------------返回值初始化
//"info" => "",   提示信息
//"jsApiParameters" => "",支付字符串
//"orderid" => ""  订单ID
$aa = array(
    "info" => "",
    "jsApiParameters" => "",
    "orderid" => ""
);


$ordertype = "orderZtc";
$jfnum100 = $dk_jf * 100 / $buynumb;   //得到单价
$jbnum100 = $dk_jb * 100 / $buynumb;
$total100 = $totalMoney * 100 / $buynumb;
$paynum100 = $payMoney * 100 / $buynumb;
$fh_ejjb100 = $fh_ejjb * 100 ;
$fh_ejjf100 = $fh_ejjf * 100 ;
$fh_sjjb100 = $fh_sjjb * 100 ;
$fh_sjjf100 = $fh_sjjf * 100 ;
$operatorid = $CUSERLOGIN->userID;


$depid = $DEP_TOP_ID;

$usertypename = $GLOBALS['CUSERLOGIN']->getUserTypeName();
//dump($usertypename);
$isskd = strpos($usertypename, "售卡点子部门");//判断 是否售卡点
// dump($isskd);
if ($isskd === false) {
    //非售卡点的登录用户默认添加的会员部门ID是17
} else {
    //售卡点 是当前的部门

    $depid=GetEmpDepTopIdByUserId($CUSERLOGIN->getUserId(), 100);


}

//---------------------------------------创建订单附加

//循环$buynumb个商品,如果有商品编号,则添加到销售商品表这里的
//同时添加到销售记录表

//DUMP($buynumb);
$error_str="";
for ($goodsi = 1; $goodsi <= $buynumb; $goodsi++) {

    $realname_ename = 'realname_' . $goodsi;
    $mobilephone_ename = 'mobilephone_' . $goodsi;
    $idcard_ename = 'idcard_' . $goodsi;
    $cardcode_ename = 'cardcode_' . $goodsi;

    $realname = $$realname_ename;
    $mobilephone = $$mobilephone_ename;
    $idcard = $$idcard_ename;
    $cardcode = $$cardcode_ename;

    //先创建用户
    $clientid = RegClient(
        $realname, $mobilephone, $mobilephone_check = "", $address="", $tag="", $description="", $from = "手工添加",
        $idcard, $operatorid, $sponsorid,
        $pwd = "",
        $depid, $openid = "", $AppId = "",
        $nickname = "", $sex_temp = "", $city = "", $province = "", $country = "", $headimgurl = ""
    );

    if(!($clientid>0)){
        $error_str.=$realname;
        continue;
    }

    //做到这里,要检查是否创建用户成功
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
        $error_str.=" ".$realname;
        continue;
        /* $aa = array(
               "info" => $orderInfo,
               "jsApiParameters" => "",
               "orderid" => ""
           );
           echo json_encode($aa);
           exit();*/
    }


        //dump($icpic_t);
        $sqladdordergoods = "
                    INSERT INTO `#@__order_addon_ztc` ( `orderid`,`goodsid`,`name`, `tel`, `idcard`, `cardcode`)
                    VALUES ( '$orderid','$goodsid','{$realname}', '{$mobilephone}', '{$idcard}', '{$cardcode}');";
        $dsql->ExecuteNoneQuery($sqladdordergoods);


//---------------------------------------订单支付过程
    if ($paytype != "") {
        //模拟支付过程
        $total_fee = $paynum100;
        $json = "{
            \"appid\":\"\",
            \"attach\":[],
            \"bank_type\":\"\",
            \"cash_fee\":\"\",
            \"fee_type\":\"\",
            \"is_subscribe\":\"\",
            \"mch_id\":\"\",
            \"nonce_str\":\"\",
            \"openid\":\"\",
            \"out_trade_no\":\"$orderCode-144850\",
            \"result_code\":\"SUCCESS\",
            \"return_code\":\"SUCCESS\",
            \"return_msg\":\"OK\",
            \"sign\":\"EAE6E5AC280E341BD9D2E7C202D0F96A\",
            \"time_end\":\"\",
            \"total_fee\":\"$total_fee\",
            \"trade_state\":\"SUCCESS\",
            \"trade_type\":\"JSAPI\",
            \"transaction_id\":\"\"
            }";
        $result = json_decode($json, true);//dump($result);
        saveTruePayOrder($result, $paytype);
    }

//---------------------------------------订单支付过程



}
//---------------------------------------创建订单附加

$info="添加成功";
if($error_str!=""){
    $info=$error_str." 订单未添加成功,请检查";
}
$aa = array(
    "info" => $info
);

echo json_encode($aa);
exit();


