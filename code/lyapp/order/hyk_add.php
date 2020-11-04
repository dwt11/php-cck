<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
require_once DWTINC . '/order.class.php';


CheckRank();


if (empty($dopost)) $dopost = '';
if (empty($goodsid)) $goodsid = '';
if (empty($goodsid)) {
    showMsg("非法参数", "index.php");
    exit;
}


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
 

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>确认订单</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
    <link href="/ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/ui/css/plugins/spinner/jquery.spinner.css" rel="stylesheet">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <!--产品信息-->
    <form id="form" class="form-horizontal">
        <input id="goodsid" name="goodsid" value="<?php echo $goodsid; ?>" type="hidden">
        <ul class="list-group list-group-plus-nomargintop list-font-color-black">
            <li class="list-group-item1 list-group-item-border">
                <img src="<?php echo $photo; ?>" width="60" height="60" style='float:left; margin-right: 5px'/>

                <b><?php echo "【{$goodscode}】 $goodsname" ?></b>
                <br><span class="text-danger"> ￥<span id="price"><?php echo $price; ?></span></span>
                <div class="clearfix"></div>
            </li>
            <li class="list-group-item1 ">
                数量
                <span class="pull-right  ">
                   <input type="text" id="buyNumb_t"/>
                    <span id="buyNumb" style="display: none">1</span>
                </span>
            </li>
        </ul>
       

        <?php
        $gd = new goodsOrder($CLIENTID, $goodsid, $price);
        echo $gd->GetBenefitInfoHtmlToWeb();
        //echo $gd->GetJbjfdkHtmlToWeb();
        //echo $gd->GetJejsHtmlToWeb();
        ?>
        <div  style="display: none">
            <input type='text' name='benefitCreatetime' id='benefitCreatetime' value="<?php echo $gd->Get_benefit_createtime() ?>">
            <br>价格<input type='text' name='dk_jg' id='dk_jg'>
            <br>金币<input type='text' name='dk_jb' id='dk_jb'>
            <br>积分<input type='text' name='dk_jf' id='dk_jf'>
            <br>二级金币<input type='text' name='fh_ejjb' id='fh_ejjb'>
            <br>二级积分<input type='text' name='fh_ejjf' id='fh_ejjf'>
            <br>三级金币<input type='text' name='fh_sjjb' id='fh_sjjb'>
            <br>三级积分<input type='text' name='fh_sjjf' id='fh_sjjf'>
            <br>余额金币<input type='text' name='ye_jb' id='ye_jb' value="<?php echo GetClientJBJFnumb('jb', $CLIENTID) ?>">
            <br>余额积分<input type='text' name='ye_jf' id='ye_jf' value="<?php echo GetClientJBJFnumb('jf', $CLIENTID) ?>">
        </div>
        <!--可以用的支付方式-->




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


        <div class="clearfix" style="margin-bottom: 120px"></div>

        <div class="bodyButtomTab">
            <div class="form-group" id="paydiv" style="display: none">
                <input type="password" name="paypwd"   id="paypwd"   value=""    class="form-control-plus-pay" placeholder="请在此填写支付密码(在会员中心设置)">
            </div>
            <div class="form-group" id="error" style="display: none">
                <span id="error_str" class="text-danger"></span>
            </div>
            <div class="pull-right" >
                <?php
                ///微信支付调试
                //如果是在微信浏览器中

                if (IsWeixinBrowser()) {
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add' type='button' onclick='gopay(\"hyk_save.php?paytype=weixin\",0)'>微信支付</button>";
                } else {
                    echo "请从微信登录";
                }
                    //echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"hyk_save.php?paytype=none112101\",0)'>模拟支付</button>";
                ?>
            </div>
            <div class="text-danger pull-left" style="padding-left: 2px">实付款<br>
                                                                          ￥<span id="payMoney"></span>
                <input id="totalMoney" name="totalMoney" type="hidden" value="" readonly style="max-width: 20px">
               </div>
        </div>
    </form>
</div>


<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/iCheck/icheck.min.js"></script>

<script src="/lyapp/js/weixinPay.js"></script>
<script src="hyk_add.js"></script>
<script src="order.js"></script>
<script src="../js/weixinHideOptionMenu.js"></script>
<script src="/ui/js/jquery.spinner.js"></script>
</body>
</html>
