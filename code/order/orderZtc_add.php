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

    if (empty($isxf)) $isxf = '';

    //如果不是续费过程,才检查身份证重复
    if ($isxf != 1) {
        //检查身份证是否重复
        for ($goodsi = 1; $goodsi <= $buynumb; $goodsi++) {
            $realname = 'realname_' . $goodsi;
            $mobilephone = 'mobilephone_' . $goodsi;
            $idcard = 'idcard_' . $goodsi;
            $cardcode = 'cardcode_' . $goodsi;
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
                $checkCardCode = ValidateZtcCardCodeISon($cardcode_t, "", "#@__order_addon_axk");
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


//------------变量预处理
    $clientid = $clientid;
    $ordertype = "orderZtc";
    $jfnum100 = $dk_jf * 100;
    $jbnum100 = $dk_jb * 100;
    $total100 = $totalMoney * 100;
    $paynum100 = $payMoney * 100;
    $fh_ejjb100 = $fh_ejjb * 100;
    $fh_ejjf100 = $fh_ejjf * 100;
    $fh_sjjb100 = $fh_sjjb * 100;
    $fh_sjjf100 = $fh_sjjf * 100;
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

//循环15个商品,如果有商品编号,则添加到销售商品表这里的
//同时添加到销售记录表
    //dump($buynumb);
    for ($goodsi = 1; $goodsi <= $buynumb; $goodsi++) {
        $realname_ename = 'realname_' . $goodsi;
        $mobilephone_ename = 'mobilephone_' . $goodsi;
        $idcard_ename = 'idcard_' . $goodsi;
        $cardcode_ename = 'cardcode_' . $goodsi;

        $realname = $$realname_ename;
        $mobilephone = $$mobilephone_ename;
        $idcard = $$idcard_ename;
        $cardcode = $$cardcode_ename;
        // dump($realname);

        if (!empty($realname) || !empty($mobilephone) || !empty($idcard)) {
            $sqladdordergoods = "
                    INSERT INTO `#@__order_addon_ztc` ( `orderid`,`goodsid`,`name`, `tel`, `idcard`, `cardcode`)
                    VALUES ( '$orderid','$goodsid','{$realname}', '{$mobilephone}', '{$idcard}', '{$cardcode}');";
            //dump($sqladdordergoods);
            $dsql->ExecuteNoneQuery($sqladdordergoods);
            if ($goodsi == 1) {
                //dump(3333);
                //如果第一个乘车卡的用户
                //如果clint表中未保存过用户的姓名，则更新用户的姓名
                //更新基本信息
                $questr = "SELECT realname  FROM `#@__client`  where  id='$clientid'";
                $row = $dsql->GetOne($questr);
                // dump($row);
                if ($row["realname"] == "") {
                    $createtime = time();
                    $query = "UPDATE #@__client SET   realname='{$realname}' ,pubdate='$createtime'     WHERE id='$clientid'; ";
                    $dsql->ExecuteNoneQuery($query);
                }


                //获取客户信息
                $questr = "SELECT idcard  FROM `#@__client_addon`  where  clientid='$clientid'";
                $row = $dsql->GetOne($questr);
                if ($row["idcard"] == "") {
                    $query = "UPDATE #@__client_addon SET   idcard='{$idcard}'     WHERE clientid='$clientid'; ";
                    $dsql->ExecuteNoneQuery($query);
                }
            }
        }
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


    if ($isxf == 1) {

        //如果是续费的,要把照片\照片审核\订单时间更新一下


        //更新卡的订购时间(启用时间)\备注
        $sql = "UPDATE `#@__order`    SET createtime='$orderCreateTime_new',`desc`=concat('续费 ',`desc`) WHERE id='$orderid';    ";
        $dsql->ExecuteNoneQuery($sql);

        //更新卡的子记录中的值:照片和照片审核情况
        $sql = "UPDATE `#@__order_addon_ztc`    SET idpic='$idpic',`idpic_desc`='审核通过' WHERE orderid='$orderid';    ";
        $dsql->ExecuteNoneQuery($sql);

        //这里将此订单的会员类型时间修改为正确的
        $addRowAddtable = $dsql->GetOne("SELECT rankLenth FROM `#@__goods_addon_ztc` WHERE goodsid='$goodsid'");
        $rankLenth = $addRowAddtable["rankLenth"];
        $ranktime = $orderCreateTime_new;
        $rankcutofftime = strtotime("+{$rankLenth} month", $ranktime);
        $sql = "UPDATE `#@__clientdata_ranklog`    SET ranktime='$ranktime',rankcutofftime='$rankcutofftime'  WHERE   orderid='$orderid';    ";
        $dsql->ExecuteNoneQuery($sql);



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
                    <form id="form" action="orderZtc.add.php" class="form-horizontal">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择商品:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectGoods()">选择商品</button>
                                <input type="hidden" name="goodsid" id="goodsid" value=""/>
                                <span id="goodsid_str"><span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">商品名称:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="goodstitle"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">单价:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="price"></span>
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
                            <label class="col-sm-2 control-label">介绍人:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="sponsorname"></span>
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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>

<script src="../ui/js/validate.js"></script>

<script>
    $(function () {
        var goodsid = "";
        intervalName = setInterval(handle, 1000);//定时器句柄
        function handle() {
            //如果值不一样,则代表了改变
            if ($("#goodsid").val() != goodsid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                goodsid = $("#goodsid").val();//保存改变后的值
                $("#goodsid_str").html("编号" + goodsid);//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "../goods/goods.do.php",
                    data: {
                        goodsid: goodsid,
                        dopost: "GetOneGoodsInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        $("#price").html(result.price);
                        $("#goodstitle").html(result.goodsname);
                    }
                });
            }
        }
    });

    function selectGoods() {
        layer.open({type: 2, title: '选择商品', content: '../goods/goods.select.php?typeid=1'});
    }


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
                        $("#sponsorname").html(result.sponsorname);
                    }
                });
            }
        }
    });


    $().ready(function () {
        $("#form").validate({
            rules: {
                goodsid: {required: true},
                clientid: {required: true}
            },
            messages: {
                goodsid: {required: "请选择商品"},
                clientid: {required: "请选择用户"}
            }
        });

    });
</script>

</body>
</html>