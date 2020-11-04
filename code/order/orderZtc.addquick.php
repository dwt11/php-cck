<?php
/**
 * 订单快速添加
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
//$goodsid = 1;
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
$price = $price100 / 100;



?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>直通车订单快速添加</title>
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
                    <h5>直通车订单快速添加 </h5> 本页只可以给系统中<b>不存在会员信息</b>的客户添加乘车卡
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form id="form" class="form-horizontal">
                        <input type="hidden" name="goodsid" id="goodsid" value="<?php echo $goodsid ?>"/>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">商品名称:</label>
                            <div class="col-sm-6 form-control-static">
                                <?php echo "【{$goodscode}】 $goodsname" ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">单价:</label>
                            <div class="col-sm-2 form-control-static">
                                ￥<span id="price"><?php echo $price; ?></span>
                            </div>
                        </div>
                        <?php

                        $usertypename = $GLOBALS['CUSERLOGIN']->getUserTypeName();
                        //dump($usertypename);
                        $isskd = strpos($usertypename, "售卡点子部门");//判断 是否售卡点
                        //dump($isskd);
                        if ($isskd === false) {
                            //售卡点不显示介绍人
                            //22售卡点管理人员
                            //24零售卡点
                            ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">介绍人:</label>
                                <div class="col-sm-2">
                                    <button type="button" id="select" class="btn btn-primary" onclick="selectClient()">选择</button>
                                    <input type="hidden" name="clientid" id="clientid" value=""/>
                                    <input type="hidden" name="sponsorid" id="sponsorid" value=""/>
                                    <span id="sponsoridid_str"></span>
                                    <span id="sponsoridname"></span>
                                </div>
                                <button type="button" id="clear" class="btn btn-primary" onclick="clearsponsorid()">清空介绍人</button>

                            </div>
                        <? } else {
                            echo "                <input type=\"hidden\" name=\"sponsorid\" id=\"sponsorid\" value=\"0\"/>";
                        } ?>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">优惠信息:</label>
                            <div class="col-sm-4 form-control-static">

                                <?php
                                $gd = new goodsOrder(0, $goodsid, $price);
                                $info = $gd->GetBenefitInfoHtmlToWeb();
                                if ($info == "") {
                                    echo "无";
                                } else {
                                    echo $info;
                                }
                                ?>


                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">购买数量:</label>
                            <div class="col-sm-10 form-control-static">
                                <span id="buyNumb">1</span> &nbsp;
                                <a onclick="AddGoodsTr();"> <i class='glyphicon glyphicon-plus' aria-hidden='true'></i>
                                </a>
                                                            批量添加后每个乘车人,都会保存一个会员信息
                            </div>
                        </div>

                        <div class="form-group" id="goodslist">
                            <div class="form-group" id="tr_1">
                                <label for="" class="col-sm-2 control-label">卡信息:</label>
                                <div class="col-sm-10 form-inline">
                                    <div class="form-group">
                                        <input type="text" value="" autoComplete='off' placeholder="姓名" id="realname_1" name="realname_1" class="form-control" style="max-width: 80px">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" value="" autoComplete='off' placeholder="手机号码" id="mobilephone_1" name="mobilephone_1" class="form-control" style="max-width:120px">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" value="" autoComplete='off' placeholder="身份证号码" id="idcard_1" name="idcard_1" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" value="" autoComplete='off' placeholder="实体卡号" id="cardcode_1" name="cardcode_1" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div style="display: none">
                            <input type='text' name='benefitCreatetime' id='benefitCreatetime' value="<?php echo $gd->Get_benefit_createtime() ?>">
                            <br>价格<input type='text' name='dk_jg' id='dk_jg'>
                            <br>金币<input type='text' name='dk_jb' id='dk_jb'>
                            <br>积分<input type='text' name='dk_jf' id='dk_jf'>
                            <br>二级金币<input type='text' name='fh_ejjb' id='fh_ejjb'>
                            <br>二级积分<input type='text' name='fh_ejjf' id='fh_ejjf'>
                            <br>三级金币<input type='text' name='fh_sjjb' id='fh_sjjb'>
                            <br>三级积分<input type='text' name='fh_sjjf' id='fh_sjjf'>
                            <br>余额金币<input type='text' name='ye_jb' id='ye_jb' value="<?php echo GetClientJBJFnumb('jb', $clientid) ?>">
                            <br>余额积分<input type='text' name='ye_jf' id='ye_jf' value="<?php echo GetClientJBJFnumb('jf', $clientid) ?>">
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
                        <?php
                        $operatorid = $CUSERLOGIN->userID;
                        $empName = GetEmpNameByUserId($operatorid);
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">操作员:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="price"><?php echo $empName; ?></span>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>


                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class='btn btn-primary' id='add_test' type='button' onclick='gopay("orderZtc.addquick.save.php?dopost=save","orderZtc.php",true)'>保存添加</button>
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
<script src="../ui/js/validate.js"></script>
<script src="../ui/js/public.js"></script>
<script>
    var mobilephoneUseONclient=1;//验证手机在客户表 是否使用  这里不验证
</script>
<script src="orderZtc_add.js"></script>
<script src="orderNew.js"></script>

<script>
    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: '../client/client.select.php'});
    }
    function clearsponsorid() {
        $("#clientid").val("");
        $("#sponsorid").val("");
        $("#sponsoridname").html("");
        $("#sponsoridid_str").html("");
    }
    $(function () {
        var clientid = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#clientid").val() != clientid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                clientid = $("#clientid").val();//保存改变后的值
                if (clientid != "") {
                    $("#sponsoridid_str").html("编号" + clientid);//保存改变后的值
                    $("#sponsorid").val(clientid);//保存改变后的值
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
                            $("#sponsoridname").html(result.realname + " " + result.mobilephone);
                        }
                    });
                }
            }
        }
    });
</script>
</body>
</html>