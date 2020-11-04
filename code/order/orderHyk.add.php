<?php
/**
 * 订单添加第二部,填写订单信息
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值
require_once DWTINC . '/order.class.php';
if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/

$arcQuery = "SELECT litpic,goodscode,goodsname,price FROM #@__goods WHERE id='$goodsid'";
$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    ShowMsg("读取档案基本信息出错!", "-1");
    exit();
}
$photo = $arcRow["litpic"];
if ($photo == "") $photo = "/images/arcNoPic.jpg";
$goodscode = $arcRow["goodscode"];
$goodsname = $arcRow["goodsname"];
//if (strlen($goodsname) > 20) $goodsname = cn_substr_utf8($goodsname, 35) . "...";
$price100 = $arcRow["price"];
$price = $price100/100;


$sponsorid =$realname=$mobilephone=$idcard= "";
$questr = "SELECT sponsorid,idcard  FROM `#@__client_addon` where  clientid='$clientid'";
$row_1 = $dsql->GetOne($questr);
if ($row_1["sponsorid"] != "") {

    $sponsorid = $row_1["sponsorid"];
    $idcard = $row_1["idcard"];
}

$questr = "SELECT mobilephone,realname  FROM `#@__client` where  id='$clientid'";
$row_1 = $dsql->GetOne($questr);
if ($row_1["realname"] != ""||$row_1["mobilephone"] != ""){
    $realname = $row_1["realname"];
    $mobilephone = $row_1["mobilephone"];
}




?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单添加</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <style>
        .list-group-plus {
            margin: 0;
            background-color: #FFFFFF;
            line-height: 25px;
            color: #888;
        }

        .list-group-plus li a {
            color: #000000;
        }


        .list-font-color-black {
            color: #000000;
        }


        .list-group-item1 {
            clear: both;
            background-color: inherit;
            display: block;
            padding-bottom: 10px;
            position: relative;
            margin-left: 10px;
            padding-right: 10px;
            font-size: 14px;

        }

        .list-group-item-border {
            border-bottom: 1px solid #e7eaec;
        }
    </style>
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5>订单添加 </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form id="form" class="form-horizontal">
                        <input type="hidden" name="clientid" id="clientid" value="<?php echo $clientid ?>"/>
                        <input type="hidden" name="goodsid" id="goodsid" value="<?php echo $goodsid ?>"/>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">商品名称:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo "【{$goodscode}】 $goodsname" ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">单价:</label>
                            <div class="col-sm-2 form-control-static">
                                ￥<span id="price"><?php echo $price; ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">会员姓名:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo getOneCLientRealName($clientid); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">购买数量:</label>
                            <div class="col-sm-2 form-control-static">
                                <a onclick="removeGoodsTr();"><i class='glyphicon glyphicon-minus' aria-hidden='true'></i></a> <span id="buyNumb">1</span> <a onclick="AddGoodsTr();"><i class='glyphicon glyphicon-plus' aria-hidden='true'></i></a>
                            </div>
                        </div>



                        <!--可以用的支付方式-->
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单备注:</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" rows="3" name="desc" id="desc"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">支付方式:</label>

                            <div class="col-sm-4">
                                <?php echo GetEnumsForm("paytype", '1', '', '', 'radio'); ?>
                            </div>
                            <div class="col-sm-6"></div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">结算信息:</label>
                            <div class="col-sm-4 form-control-static">

                                <ul class="list-group list-group-plus list-font-color-black">
                                    <li class="list-group-item1   text-muted small">
                                        商品金额
                                        <span class="pull-right   text-danger" style="margin-right: 5px">
                                            ￥<span id="t_total"></span>
                                        </span>


                                        <br>
                                        实付款
                                        <span class="pull-right   text-danger" style="margin-right: 5px">
                                            ￥<span id="t_pay"></span>
                                        </span>
                                    </li>
                                </ul>

                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class='btn btn-primary' id='add_test' type='button'  onclick='gopay("orderHyk_add.php?dopost=save","orderHyk.php")'>保存添加</button>
                                <span id="error" class="text-danger" ></span>
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
<script src="orderHyk_add.js"></script>
<script src="order.js"></script>



</body>
</html>