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
    $ordertype = "orderCzk";
    $jfnum100 = 0;
    $jbnum100 = 0;
    $total100 = $totalMoney * 100;
    $paynum100 = $payMoney * 100;
    $fh_ejjb100 = 0;
    $fh_ejjf100 = 0;
    $fh_sjjb100 = 0;
    $fh_sjjf100 = 0;
    $operatorid = $CUSERLOGIN->userID;

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
        $benefitCreatetime=0,
        $fh_ejjb100=0,
        $fh_ejjf100=0,
        $fh_sjjb100=0,
        $fh_sjjf100=0,
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

    for($buynumb_i=1;$buynumb_i<=$buynumb;$buynumb_i++) {
        $czk_password=GetOrderCZKpassword();
        $je100=$je*100;
        $sqladdordergoods = "
                    INSERT INTO `#@__order_addon_czk` ( `orderid`,`czk_password`,`je`)
                    VALUES ( '$orderid','$czk_password','$je100');";
        $dsql->ExecuteNoneQuery($sqladdordergoods);
    }
//---------------------------------------创建订单附加


//---------------------------------------订单支付过程
    if ($paytype != "") {
        //模拟支付过程
        $total_fee = $paynum100 ;
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
                    <form id="form" action="orderCzk.add.php" class="form-horizontal">


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

                        <div class="form-group" id="goodslist">
                            <div class="form-group" id="tr_1">
                                <label for="" class="col-sm-2 control-label">充值卡金额:</label>
                                <div class="col-sm-10 form-inline">
                                    <div class="form-group">
                                        <select id="price" name="price"  class="form-control">
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="200">200</option>
                                            <option value="300">300</option>
                                            <option value="1000">1000</option>
                                            <option value="2000">2000</option>
                                            <option value="3000">3000</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="submit">下一步</button>
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

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>


<script>
    //==============================选择客户
    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: '../client/client.select.php'});
    }
    $(function () {
        var clientid = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#clientid").val() != clientid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                clientid = $("#clientid").val();//保存改变后的值
                $("#clientid_str").html("编号" + clientid);//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "../client/client.do.php",
                    data: {
                        clientid: clientid,
                        dopost: "GetOneClientInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        console.log(result);
                        $("#realname").html(result.realname + " " + result.mobilephone);
                    }
                });
            }
        }
    });


    $().ready(function () {
        $("#form").validate({
            rules: {
                clientid: {required: true}
            },
            messages: {
                clientid: {required: "请选择用户"}
            }
        });
    });
</script>

</body>
</html>