<?php
/**
 * 订单添加 第一步,选择商品和客户
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/
if ($dopost == 'save') {
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
    $clientid = $clientid;
    $ordertype = "orderLycp";
    $jfnum100 = $dk_jf * 100;
    $jbnum100 = $dk_jb * 100;
    $total100 = $totalMoney * 100;
    $paynum100 = $payMoney * 100;
    $fh_ejjb100 = 0;
    $fh_ejjf100 = 0;
    $fh_sjjb100 = 0;
    $fh_sjjf100 = 0;
    $operatorid = $CUSERLOGIN->userID;

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

    //后台发车时间之前都可以预约,不和截止时间比较170421
//判断固定线路 时间是否合适
    /*    if (!GetLineBeforHoursIStrue($lineid, $appttime)) {
            $info = '已超过预约截止时间,不能预约';
            $aa = array(
                "info" => $info,
                "jsApiParameters" => "",
                "orderid" => ""
            );
            echo json_encode($aa);
            exit();
        }*/


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

//dump($addon_array);
    $orderCAOYUAN = false;//是否超卖
    if (count($addon_array) > 0  ) {
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
        $aa = array(
            "info" => "订单错误",
            "jsApiParameters" => "",
            "orderid" => ""
        );
        echo json_encode($aa);
        exit();
    }
    if ($orderCAOYUAN) {
        $aa = array(
            "info" => "座位数不够",
            "jsApiParameters" => "",
            "orderid" => ""
        );
        echo json_encode($aa);
        exit();
    }
//---------------------------------------创建订单附加


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
        $aa = array(
            "info" => "添加成功",
            "jsApiParameters" => "",
            "orderid" => $orderid
        );
    }

//---------------------------------------订单支付过程

    echo json_encode($aa);
    exit();


}

if ($dopost == 'getztccardnumb') {
    $ztcCard_numb = 0;
    $query4 = "SELECT count(*) AS dd FROM  `#@__order`
          LEFT JOIN #@__order_addon_ztc  ON `#@__order`.id=#@__order_addon_ztc.orderid
          WHERE 
                  (
                    `#@__order`.clientid='$clientid'
                  )
            AND  (
                    from_unixtime(`#@__order`.createtime)<now() 
                    AND now()<DATE_add(from_unixtime(`#@__order`.createtime), INTERVAL 1 YEAR)
                )/*在有效期内的乘车卡*/
                  AND `x_order`.ordertype='orderZtc'   AND `#@__order`.isdel=0 AND `#@__order`.sta=1
                 ";

    //dump($query4);
    $arcRow4 = $dsql->GetOne($query4);
    if (isset($arcRow4["dd"])) {
        $ztcCard_numb = $arcRow4["dd"];
    }
//获取共享 的直通车卡
    $query5 = "SELECT count(*) AS dd
            FROM    #@__ztc_share  os
           LEFT JOIN #@__order_addon_ztc olist on olist.id=os.orderListId
           LEFT JOIN #@__order o1 on o1.id=olist.orderid
          WHERE  os.isdel='0' and o1.isdel='0' and o1.sta=1 and os.clientid_n='$clientid'
           AND  (from_unixtime(o1.createtime)<now() AND now()<DATE_add(from_unixtime(o1.createtime), INTERVAL 1 YEAR))/*在有效期内的乘车卡*/
            ";
    //dump($query5);
    $arcRow5 = $dsql->GetOne($query5);
    if (isset($arcRow5["dd"])) {
        $ztcCard_numb += $arcRow5["dd"];
    }

    echo $ztcCard_numb;
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?> </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form id="form" class="form-horizontal">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择线路:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectLine()">选择线路</button>
                                <span id="lineid_str"></span>
                                <input type="hidden" name="lineid" id="lineid" value=""/>
                                <input type="hidden" name="goodsid" id="goodsid" value=""/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">商品名称:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="goodstitle"></span>
                            </div>
                        </div>


                        <!--<div class="form-group">
                            <label class="col-sm-2 control-label">单价:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="price"></span>
                            </div>
                        </div>-->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">线路类型:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="tmpType"></span>
                            </div>
                        </div>
                        <!--显示选择的临时线路的出行日期和线路类型-->
                        <div id="lstime" class="form-group" style="display: none">
                            <label class="col-sm-2 control-label">出发日期:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="apptime_str"></span>
                            </div>
                        </div>


                        <!--固定线路,用户自行输入日期-->
                        <div id="gdtime" class="form-group" style="display: none">
                            <label class="col-sm-2 control-label">请选择出行日期:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php
                                $default_date = date('Y-m-d', strtotime("+1 day"));
                                ?>
                                <input value='<?php echo $default_date ?>' type="text" name="xz_time" id='xz_time' class="form-control  Wdate " size="14" placeholder="请选择出行日期" onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd',minDate:'%y-%M-#{%d+1}',maxDate:'%y-%M-#{%d+30}'})"/>
                            </div>
                        </div>
                        <!--上车点-->
                        <div id="scd" class="form-group" style="display: none">
                            <label class="col-sm-2 control-label">上车点:</label>
                            <div class="col-sm-10 form-control-static">
                                <span id='tjsite'></span>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择会员:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectClient()">选择会员</button>
                                <input type="hidden" name="clientid" id="clientid" value=""/>
                                <span id="clientid_str"><span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">会员姓名:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="realname"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">直通车乘车卡信息:</label>
                            <div class="col-sm-6 form-control-static">
                                <span id="cardTypeHtml">
                                    <!--<label class="checkbox-inline   i-checks" style='min-width: 40px;max-width: 120px;line-height: 20px'>
                                        <input name='cardType' id='cardType' type='radio' value='qtr' checked/> 其他乘车人
                                    </label>-->
                                </span>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="button" onclick='lineSelectSubimt()'>下一步</button>
                                <span id="error" class="text-danger"></span>
                            </div>
                        </div>


                    </form>
                    <!--表格数据区------------结束-->
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script type="text/javascript" src="../include/My97DatePicker/WdatePicker.js"></script>
<script src="orderLine_add1.js"></script>

<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green"})
    });
</script>


</body>
</html>