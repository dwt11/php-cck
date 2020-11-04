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
//商品信息
$query = "SELECT litpic,goodscode,goodsname,price,#@__goods_addon_car.jfnum FROM #@__goods
 LEFT JOIN #@__goods_addon_car ON #@__goods_addon_car.goodsid=#@__goods.id
 WHERE   #@__goods.id=$goodsid";
$arcRow = $dsql->GetOne($query);
if (!is_array($arcRow)) {
    ShowMsg("读取档案基本信息出错!", "-1");
    exit();
}
$photo = $arcRow["litpic"];
if ($photo == "") $photo = "/images/arcNoPic.jpg";
$goodscode = $arcRow["goodscode"];
$goodsname = $arcRow["goodsname"];
$price100 = $arcRow["price"];
$jfnum100 = $arcRow["jfnum"];
$price = $price100 / 100;
$jfnum = $jfnum100 / 100;
$jbnum = $price - $jfnum;
if ($jfnum <= 0) $jfnum = 0;
if ($jbnum <= 0) $jbnum = 0;

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
    <link href="/ui/css/plugins/spinner/jquery.spinner.css" rel="stylesheet">
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
                                <?php
                                 echo " 非会员:金币<span id='jbnum_basic' class='font-num' >$jbnum </span>";
                                 echo "积分<span id='jfnum_basic' class='font-num' >$jfnum </span>";

                                $benprice = GetGoodBenefitInfoPrice($goodsid);
                                if ($benprice != "") echo "   <br>直通车卡:$benprice";
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">会员姓名:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo getOneCLientRealName($clientid); ?>
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="col-sm-2 control-label">数量(台):</label>

                            <div class="col-sm-2 form-control-static">
                                <input type="text" id="carNumb"/>&nbsp;
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">请选择取车日期:</label>
                            <div class="col-sm-2 form-control-static">

                                <?php
                                $default_date = date('Y-m-d');
                                ?>
                                <input
                                        value='<?php echo $default_date ?>'
                                        type="text" name="start_date" id='start_date'
                                        class="form-control  Wdate " size="14" placeholder="请选择取车日期"
                                        onfocus="WdatePicker({onpicked:function(){end_date.focus();},skin:'whyGreen',dateFmt:'yyyy-MM-dd',minDate:'%y-%M-%d',maxDate:'%y-%M-#{%d+30}'})"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">请选择还车日期:</label>
                            <div class="col-sm-2 form-control-static">
                                <input value='<?php echo $default_date ?>' type="text" name="end_date" id='end_date' class="form-control  Wdate " size="14" placeholder="请选择还车日期"
                                       onfocus="WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd',minDate:'%y-%M-%d}',maxDate:'%y-%M-#{%d+30}'})"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">合计:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="dayNumb"></span>天,
                                                          共<span id="buyNumb"></span>件
                                </span>

                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">联系人信息:</label>
                            <div class="col-sm-10 form-inline">
                                <div class="form-group">
                                    <input value="<?php echo $realname?>" type="text" placeholder="姓名" id="realname" name="realname" class="form-control">
                                </div>
                                <div class="form-group">
                                    <input value="<?php echo $mobilephone?>" type="text"  placeholder="手机号码" id="mobilephone" name="mobilephone" class="form-control">
                                </div>
                            </div>
                        </div>





                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单备注:</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" rows="3" name="desc" id="desc"></textarea>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">优惠信息:</label>
                            <div class="col-sm-4 form-control-static">

                                <?php
                                $gd = new goodsOrder($clientid, $goodsid, $price);
                                $info = $gd->GetBenefitInfoHtmlToWeb();
                                if ($info == "") {
                                    echo "无";
                                } else {
                                    echo $info;
                                }
                                ?>


                            </div>
                        </div>



                        <div style="display: none">
            优惠规则<input type='text' name='benefitInfo_text' id='benefitInfo_text' value="" style="width: 600px">
            <br>使用金币<input type='text' name='jbnum' id='jbnum' readonly>
            <br>使用积分<input type='text' name='jfnum' id='jfnum' readonly>
            <br>余额金币<input type='text' name='ye_jb' id='ye_jb' readonly value="<?php echo GetClientJBJFnumb('jb', $clientid) ?>">
            <br>余额积分<input type='text' name='ye_jf' id='ye_jf' readonly value="<?php echo GetClientJBJFnumb('jf', $clientid) ?>">
                        </div>
                        <!--可以用的支付方式-->
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
                                        总额(<span id="buynumb_all"></span>件)
                                        <span class="pull-right   text-danger" style="margin-right: 5px">
                                            金币<span id="t_total_jb"></span>
                                            积分<span id="t_total_jf"></span>
                                        </span>

                                        <br>
                                        使用金币(余额<span id="ye_jb_d"></span>)
                                        <span class="pull-right   text-danger" style="margin-right: 5px">
                                            -<span id="t_dk_jb"></span>
                                        </span>
                                        <br>
                                        使用积分(余额<span id="ye_jf_d"></span>)
                                        <span class="pull-right   text-danger" style="margin-right: 5px">
                                            -<span id="t_dk_jf"></span>
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
                                <button class='btn btn-primary' id='add_test' type='button' onclick='gopay()'>保存添加</button>
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
<script src="orderCar_add.js"></script>
<script src="/ui/js/jquery.spinner.js"></script>


</body>
</html>