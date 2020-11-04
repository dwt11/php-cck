<?php
require_once(dirname(__FILE__) . "/../include/config.php");

CheckRank();


//------------变量预处理
$clientid = $CLIENTID;
$ordertype = "orderLycp";


//判断是否可以预订
//判断固定线路 时间是否合适
$query11 = "SELECT beforHours,gotime FROM #@__line where id='$lineid' and tmp='$tmpType'";
$rowarc = $dsql->GetOne($query11);
//dump($query11);
//显示没有预约过的订单详细列表
if (isset($rowarc['beforHours']) && $rowarc['beforHours'] > 0) {
    $app_time = date('Y-m-d',$appttime). date(' H:i:00', $rowarc['gotime']);//预约的日期+发车的时间
    //dump($app_time);
    $sy_int = ((int)(strtotime($app_time)) - (time()));
    $sy_hours = $sy_int / 3600;  //当前日期距发车的小时数
    if ($sy_hours < $rowarc['beforHours']) {
        //剩余小时数  小于 提前发车小时
        $info = '您选择的日期不能预约<br>需要提前 ' . (int)($rowarc['beforHours']) . ' 小时预约';
        $aa = array(            "info" => $info        );
        echo json_encode($aa);
        exit();
    }
}


//判断  是否超员
$query11 = "SELECT seats FROM #@__line where id='$lineid' and seats>0";//临时线路  判断子订单的卡 是否使用过
$rowarc = $dsql->GetOne($query11);
//显示没有预约过的订单详细列表
if (isset($rowarc['seats'])) {
    $dsql->SetQuery("SELECT id FROM #@__order_addon_lycp where  lineid='$lineid' and appttime='$appttime'");
    $dsql->Execute();
    $s_seats = $dsql->GetTotalRow();
    if ($s_seats > 0 && $s_seats >= $rowarc['seats']) {

        $info = '此线路超员';
        $aa = array(            "info" => $info        );
        echo json_encode($aa);
        exit();
    }
}


//---------------------------------------创建通用主

$orderReturnStr = CreateOrderGWC(
    $goodsid,
    $clientid,
    $ordertype,
    $desc
);
//dump($orderReturnStr);
$orderGWCReturnStr_array = explode(",", $orderReturnStr);
$orderGWCInfo = "";      //订单操作成功与否信息
$orderGWCId = "";//订单Id
if (count($orderGWCReturnStr_array)>0) {
    $orderGWCInfo = $orderGWCReturnStr_array[0];      //订单操作成功与否信息
    $orderGWCid = $orderGWCReturnStr_array[1];//订单Id
}
//dump($orderReturnStr);
if ($orderGWCInfo != "加入购物车成功") {
    $aa = array(
        "info" => $orderGWCInfo,
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}
//---------------------------------------创建通用主


//---------------------------------------创建订单附加


$addon_array = array();
if ($cardType == "qtr") {
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
}

if ($cardType == "ztc") {
    //从直通车卡获取 乘车人信息
    $cckid_array = explode(",", $cckids);
    if (count($cckid_array) > 0) {
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
    }
}
//dump($addon_array);
if (count($addon_array) > 0) {
    foreach ($addon_array as $info_array) {
        $orderlistztcid = $info_array["orderlistztcid"];
        $realname = $info_array["realname"];
        $tel = $info_array["tel"];
        $idcard = $info_array["idcard"];


        $sql = "INSERT INTO `#@__ordergwc_addon_lycp` ( `GWCid`,  `lineid`, `orderlistztcid`, `appttime`,  `tjsite`, `realname`, `tel`, `idcard`)
           VALUES ('$orderGWCid',  '$lineid', '$orderlistztcid', '$appttime' ,'$tjsite', '$realname', '$tel', '$idcard');";
        //dump($sql);
        $dsql->ExecuteNoneQuery($sql);
        $aa = array(
            "info" => "加入购物车成功",
            "orderid" => $orderGWCid
        );

    }
} else {
    $aa = array(
        "info" => "购物车子记录创建失败",
        "orderid" => ""
    );
    echo json_encode($aa);
    exit();
}

//---------------------------------------创建附加



//---------------------------------------订单支付过程

echo json_encode($aa);
exit();
