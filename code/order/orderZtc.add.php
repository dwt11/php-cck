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

$arcQuery = "
  SELECT litpic,goodscode,goodsname,price,rankLenth FROM #@__goods 
  LEFT JOIN #@__goods_addon_ztc  ON #@__goods.id=#@__goods_addon_ztc.goodsid
  WHERE id='$goodsid'";
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
$rankLenth = $arcRow["rankLenth"];
$price = $price100 / 100;

$sponsorid = $realname = $mobilephone = $idcard = $cardcode = $idpic = $orderCreateTime = $orderCreateTime_str = "";
$questr = "SELECT mobilephone,realname  FROM `#@__client`
           LEFT JOIN  #@__client_depinfos ON #@__client_depinfos.clientid=#@__client.id
           WHERE  #@__client.id='$clientid' AND isdel=0";
$row_1 = $dsql->GetOne($questr);
if (is_array($row_1)) {
    if ($row_1["realname"] != "" || $row_1["mobilephone"] != "") {
        $realname = $row_1["realname"];
        $mobilephone = $row_1["mobilephone"];
    }
} else {
    ShowMsg("此会员账户已经删除,请重新选择!", "-1");
    exit();
}


$questr = "SELECT sponsorid,idcard  FROM `#@__client_addon` WHERE  clientid='$clientid'";
$row_1 = $dsql->GetOne($questr);
if ($row_1["sponsorid"] != "") {

    $sponsorid = $row_1["sponsorid"];
    $idcard = $row_1["idcard"];
}

//171004如果是续费的则从连接上直接传递过来 卡的信息,这里重新赋值
if (empty($idcard_o)) $idcard_o = '';
if (empty($cardcode_o)) $cardcode_o = '';
if (empty($name_o)) $name_o = '';
if (empty($tel_o)) $tel_o = '';
if (empty($idpic_o)) $idpic_o = '';
if (empty($orderCreateTime_o)) $orderCreateTime_o = '';
if (empty($isxf)) $isxf = '';

if ($idcard_o != "") $idcard = $idcard_o;//身份证号
if ($cardcode_o != "") $cardcode = $cardcode_o;//实体卡号
if ($name_o != "") $realname = $name_o;//姓名
if ($tel_o != "") $mobilephone = $tel_o;//年龄
if ($idpic_o != "") $idpic = $idpic_o;//身份证

if ($isxf == 1) {
    if ($orderCreateTime_o != "") {
        //未超期的卡,使用旧的结束时间做为起始时间
        $orderCreateTime = $orderCreateTime_o;
        $orderCreateTime_str = GetDateMk($orderCreateTime);
        //$orderCreateTime_new = strtotime("+1 day", $orderCreateTime);//先加一天
        $orderCreateTime_new = strtotime("+{$rankLenth} months", $orderCreateTime);//再加有效期
        $orderCreateTime_new_str = GetDateMk($orderCreateTime_new);
    } else {
        //超期的卡,使用当前日期为起始时间
        $orderCreateTime_new = time();//再加有效期
        $orderCreateTime_new_str = GetDateMk($orderCreateTime_new);
    }
}//卡的开始时间

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

<body>


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
                        <div class="form-group">
                            <label class="col-sm-2 control-label">会员姓名:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo getOneCLientRealName($clientid); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">介绍人:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo getOneCLientRealName($sponsorid); ?>
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

                        <div class="form-group">
                            <label class="col-sm-2 control-label">购买数量:</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="buyNumb">1</span> &nbsp;
                                                            <!--<a onclick="AddGoodsTr();"> <i class='glyphicon glyphicon-plus' aria-hidden='true'></i>
                                                            </a>-->
                            </div>
                        </div>

                        <div class="form-group" id="goodslist">
                            <div class="form-group" id="tr_1">
                                <label for="" class="col-sm-2 control-label">卡信息:</label>
                                <div class="col-sm-10 form-inline">
                                    <div class="form-group">
                                        <input value="<?php echo $realname ?>" <?php if ($isxf == 1) echo "disabled" ?> type="text" placeholder="姓名" id="realname_1" name="realname_1" class="form-control" style="max-width: 80px">
                                    </div>
                                    <div class="form-group">
                                        <input value="<?php echo $mobilephone; ?>" <?php if ($isxf == 1) echo "disabled" ?> type="text" placeholder="手机号码" id="mobilephone_1" name="mobilephone_1" class="form-control" style="max-width: 120px">
                                    </div>
                                    <div class="form-group">
                                        <input value="<?php echo $idcard; ?>" <?php if ($isxf == 1) echo "disabled" ?> type="text" placeholder="身份证号码" id="idcard_1" name="idcard_1" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" value="<?php echo $cardcode; ?>" <?php if ($isxf == 1) echo "disabled" ?> autoComplete='off' placeholder="实体卡号" id="cardcode_1" name="cardcode_1" class="form-control">
                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php
                        if ($isxf == 1) {
                            //续费

                            echo "                        <div class=\"form-group\">
                                                                <label class=\"col-sm-2 control-label\">新卡起始日期:</label>
                                                                <div class=\"col-sm-2 form-control-static\">
                                                                    {$orderCreateTime_new_str}
                                                                </div>
                                                            </div>
                                                            ";
                        }
                        ?>


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
        <div class="clearfix" style="margin-bottom: 50px"></div>
        <div class="bodyButtomTab">
            <div class="col-xs-4 col-xs-offset-2 ">
                                <?php
                                if ($isxf == 1) {
                                    //ztcDaoqiList.htm页面传递过来的续费订单
                                    echo " 
                                         <script>
                                            var isxf = 1;//如果是续费不验证  手机 身份证和 实体卡
                                        </script>
                                        
                                         <button class='btn btn-primary' id='add_test' type='button' onclick='gopay(\"orderZtc_add.php?dopost=save&isxf=1&idpic={$idpic}&orderCreateTime_new={$orderCreateTime_new}\",\"/service/ztcDaoqiList.php\")'>续费添加</button>";
                                } else {
                                    echo "
                                         <script>
                                            var isxf = 0;//如果是续费不验证  手机 身份证和 实体卡
                                        </script>

                                        <button class='btn btn-primary' id='add_test' type='button' onclick='gopay(\"orderZtc_add.php?dopost=save\",\"orderZtc.php\")'>保存添加</button>";
                                }

                                ?>

                                <span id="error" class="text-danger"></span>
                            </div>

                        </div>





    </form>
    <!--表格数据区------------结束-->
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
    var mobilephoneUseONclient = 0;//验证手机在客户表 是否使用/这里验证
</script>
<script src="orderZtc_add.js?v=1.0"></script>
<script src="orderNew.js"></script>
</body>
</html>