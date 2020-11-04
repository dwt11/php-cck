<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
require_once DWTINC . '/order.class.php';


/*---------------------------------------------------
 *  乘车卡信息,只做为预约时的联系人信息使用,每个卡算做一个商品
 *
 * 优惠信息 根据会员类型来计算,每个商品都按优惠规则来
 *
 *
 *
 *
 *
 *
 *
 * */
CheckRank();
if (empty($dopost)) $dopost = '';
if (empty($goodsid)) $goodsid = '';
if (empty($goodsid)) {
    showMsg("非法参数", "index.php");
    exit;
}


//------------------------商品信息，
$query = "SELECT litpic,goodscode,goodsname,price FROM #@__goods WHERE   #@__goods.id=$goodsid";
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
$price = $price100 / 100;


//------------------------发车日期
$appttime = "";
$appttime_str = "";
if ($tmpType == "临时") {
    $query1 = "SELECT gotime FROM #@__line WHERE   id=$lineid";
    $arcRow1 = $dsql->GetOne($query1);
    if (isset($arcRow1["gotime"])) {
        $appttime_str = date('Y年m月d日  H时i分', $arcRow1["gotime"]);

        $appttime = $arcRow1["gotime"];
    }

} else if ($tmpType == "每日") {
    $appttime_str = $xz_time;
    $appttime = GetMkTime($xz_time);
}

//dump($appttime);
//if (empty($appttime)) $appttime = '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>预约线路</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
    <link href="/ui/css/plugins/iCheck/custom.css" rel="stylesheet">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <!--产品信息-->
    <?php
    ?>
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
                预约时间
                <span class="pull-right  ">
                        <?php echo $appttime_str; ?>
                    </span>
            </li>


        </ul>

        <?php if ($tjsite != "") {
            echo "            <ul class=\"list-group list-group-plus list-font-color-black\">
                                <li class=\"list-group-item1  \">
                                    上车点
                                    <span class=\"pull-right  \">
                                        $tjsite
                                    </span>
                                </li>
                           </ul>
               ";
        }
        ?>


        <?php
        //获取直通车乘车卡
        $ztcCard_array = getZtcCard($CLIENTID, $appttime);
        if (isset($ztcCard_array["ztcinfo"]) && is_array($ztcCard_array["ztcinfo"])) {
            echo "<ul class=\"list-group list-group-plus list-font-color-black\">
                        <li class=\"list-group-item1 list-group-item-border\">
                            直通车卡
                            <span class=\"pull-right  \">
                                <span id=\"buyNumb\" ></span>
                            </span>
                        </li>
                        ";
            foreach ($ztcCard_array["ztcinfo"] as $ztcinfo) {
                echo $ztcinfo;
            }
            echo "</ul>";
        }
        ?>


        <?php
        /*echo "<ul class=\"list-group list-group-plus list-font-color-black\" id=\"goodslist\">";
        $name = $cfg_ml->fields['realname'];
        $tel = $cfg_ml->fields['mobilephone'];
        $idcard = $cfg_ml->fields['idcard'];

        $str = "<li class=\"list-group-item1 list-group-item-border\">
                            其他乘车人
                            <span class=\"pull-right  \">
                                <a onclick=\"AddGoodsTr();\"><i class='glyphicon glyphicon-plus' aria-hidden='true'></i> </a>
                            </span>
                            <span id=\"buyNumb_plus\" class=\"pull-right\">0</span>
                        </li>
        
            ";
        echo $str;
        echo "</ul>";*/
        ?>


        <ul class="list-group list-group-plus list-font-color-black">
            <li class="list-group-item1">
                <textarea name="desc" id="desc" class="form-control" placeholder="订单备注"></textarea>
            </li>
        </ul>
        <?php
        //$no_benefit_type = "";
        //if ($cardType == "qtr") $no_benefit_type = "直通车";
        $gd = new goodsOrder($CLIENTID, $goodsid, $price, $no_benefit_type = "", $only_benefit_type = "直通车",$appttime);
        echo $gd->GetBenefitInfoHtmlToWeb();
        //echo $gd->GetJbjfdkHtmlToWeb();
        //echo $gd->GetJejsHtmlToWeb();
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

        <div style="display: none">
            <input type="text" name="lineid" id="lineid" value="<?php echo $lineid ?>"><br>
            <input type="text" name="appttime" id="appttime" value="<?php echo $appttime ?>"><br>
            <input type="text" name="tjsite" id="tjsite" value="<?php echo $tjsite ?>"><br>
            <input type="text" name="tmpType" id="tmpType" value="<?php echo $tmpType ?>"><br>
        </div>
        <div class="clearfix" style="margin-bottom: 120px"></div>

        <div class="bodyButtomTab">
            <div class="form-group" id="paydiv" style="display: none">
                <input type="text" name="paypwd" onfocus="this.type='password'" id="paypwd" autocomplete="off" value=""
                       class="form-control" placeholder="请填写支付密码">
            </div>
            <div class="form-group" id="error" style="display: none">
                <span id="error_str" class="text-danger"></span>
            </div>
            <div class="pull-right">
                <button class="btn btn-plus btn-lg btn-warning" id="lyht_button" type="button" onclick='lyht("<?php echo $goodsid ?>")'>合同</button>
                <?php
                ///微信支付调试
                //如果是在微信浏览器中
                if (IsWeixinBrowser()) {
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add' type='button' onclick='gopay(\"line_save.php?paytype=weixin\",1)'>微信支付</button>";
                } else {
                    echo "请从微信登录";
                }
                //echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"line_save.php?paytype=none112101\",1)'>模拟支付</button>";
                ?>
            </div>
            <div class="text-danger pull-left" style="padding-left: 2px">实付款<br>
                                                                         ￥<span id="payMoney"></span>
                <span id="payMoney_plus" style="display: none"></span>
                <input id="totalMoney" name="totalMoney" type="hidden" value="" readonly style="max-width: 20px">
                <input id="totalMoney_plus" name="totalMoney_plus" type="hidden" value="" readonly style="max-width: 20px">
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
<script src="line_add.js"></script>
<script src="order.js"></script>
<script src="../js/weixinHideOptionMenu.js"></script>
</body>
</html>

