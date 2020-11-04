<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
require_once DWTINC . '/order.class.php';
if (!IsWeixinBrowser()&&!DEBUG_LEVEL) {

    echo " 请在微信中打开";
    exit();//安全检测
}
$startdateint = isset($startdateint) ? intval($startdateint) : 0;
$enddateint = isset($enddateint) ? intval($enddateint) : 0;
$jbnum = isset($jbnum) ? intval($jbnum) : "";
$jfnum = isset($jfnum) ? intval($jfnum) : "";
if(!$startdateint>0 || !$enddateint>0 || $jbnum == "" && $jfnum == "")
{
    echo "参数错误";
}
//天数
$daynumb=($enddateint-$startdateint)/86400+1;

    /*---------------------------------------------------
     *  乘车卡信息,只做为预约时的联系人信息使用,每个卡算做一个商品
     *
     * 优惠信息 根据会员类型来计算,每个商品都按优惠规则来
     *
     *
     *优惠价格来自上一个页面
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
/*$price100 = $arcRow["price"];
$jfnum100 = $arcRow["jfnum"];
$price = $price100 / 100;
$jfnum = $jfnum100 / 100;
$jbnum = $price - $jfnum;
if ($jfnum <= 0) $jfnum = 0;
if ($jbnum <= 0) $jbnum = 0;

$benprice = GetGoodBenefitInfoPrice($goodsid, $CLIENTID);*/

//dump($appttime);
//if (empty($appttime)) $appttime = '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>租赁车辆</title>
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
            <li class="list-group-item1">
                <span class="h3 font-bold"><?php echo "$goodsname" ?></span>
                <div class="clearfix"></div>
            </li>
            <li class="list-group-item1">
                <span class="">
                    价格:
                    <?php
                    echo "金币<span id='jbnum_basic' class='font-num' >$jbnum </span>";
                    echo "积分<span id='jfnum_basic' class='font-num' >$jfnum </span>";
                    ?>
                </span>
                <span class="pull-right">
                    <span class="text-danger font-bold ">
                        <?php
                       // if($benprice!="") echo "   会员:$benprice";
                        ?>


                    </span>
                </span>
                <div class="clearfix"></div>
            </li>
            <li class="list-group-item1 ">
                数量(台)
                <span class="pull-right  ">
                   <input type="text" id="carNumb"/>
                </span>
            </li>
        </ul>
        <ul class="list-group list-group-plus list-font-color-black">

            <li class="list-group-item1 ">
                取车日期
                <span class="pull-right  ">
                        <?php
                        $default_date = date('m月d日', $startdateint);
                       // /$max_date = date('Y-m-d', strtotime("+4 day"));//最大日期
                       // $min_date = date('Y-m-d', strtotime("+1 day"));//最小日期

                        echo $default_date;

                        $default_date = date('Y-m-d', $startdateint);
                        echo "<input    value='$default_date' type=\"hidden\" name=\"start_date\"  id=\"start_date\" />";
                        echo "<input    value='$benefitInfo_text' type=\"hidden\" name=\"benefitInfo_text\"  id=\"benefitInfo_text\" />";
                        ?>
                    </span>
            </li>
            <li class="list-group-item1 ">
                还车日期
                <span class="pull-right  ">
                        <?php
                        $default_date = date('m月d日', $enddateint);
                       // $max_date = date('Y-m-d', strtotime("+4 day"));//最大日期
                       // $min_date = date('Y-m-d', strtotime("+1 day"));//最小日期

                        echo $default_date;
                        $default_date = date('Y-m-d', $enddateint);
                        echo "<input    value='$default_date' type=\"hidden\" name=\"end_date\"  id=\"end_date\" />";
                        ?>
                    </span>
            </li>
            <li class="list-group-item1 ">
                合计
                <span class="pull-right  ">
                    <span id="dayNumb"><?php echo $daynumb?></span>天,
                    共<span id="buyNumb"></span>件
                    </span>
            </li>
        </ul>

        <ul class="list-group list-group-plus list-font-color-black">
            <li class="list-group-item1">
                联系人信息
                <div class="pull-right  ">
                    <div style="max-width: 250px">
                        <div>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" name="realname" id="realname"
                                       value="<?php echo $cfg_ml->fields['realname']; ?>"
                                       placeholder="姓名必填">
                            </div>
                            <div class="col-xs-7">
                                <input type="number" class="form-control" name="mobilephone" id="mobilephone"
                                       value="<?php echo $cfg_ml->fields['mobilephone']; ?>"
                                       placeholder="手机号">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </li>
        </ul>

        <ul class="list-group list-group-plus list-font-color-black">
            <li class="list-group-item1">
                <textarea name="desc" id="desc" class="form-control" placeholder="订单备注"></textarea>
            </li>
        </ul>
        <?php
        //$no_benefit_type = "";
        //if ($cardType == "qtr") $no_benefit_type = "直通车";
       // $gd = new goodsOrder($CLIENTID, $goodsid, $price, $no_benefit_type = "", $only_benefit_type = "");
        //echo $gd->GetBenefitInfoHtmlToWeb();
        //echo $gd->GetJbjfdkHtmlToWeb();
        //echo $gd->GetJejsHtmlToWeb();
        ?>
        <div style="display: none">

            <!--优惠规则<input type='text' name='benefitInfo_text' id='benefitInfo_text' value="" style="width: 600px">
            <br>使用金币<input type='text' name='jbnum' id='jbnum' readonly>
            <br>使用积分<input type='text' name='jfnum' id='jfnum' readonly>
            --><br>余额金币<input type='text' name='ye_jb' id='ye_jb' readonly value="<?php echo GetClientJBJFnumb('jb', $CLIENTID); ?>">
            <br>余额积分<input type='text' name='ye_jf' id='ye_jf' readonly value="<?php echo GetClientJBJFnumb('jf', $CLIENTID);?>">
        </div>
        <!--可以用的支付方式-->


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


        <div class="clearfix" style="margin-bottom: 120px"></div>

        <div class="bodyButtomTab">
            <div class="form-group" id="paydiv" style="display: none">
                <input type="password" name="paypwd" id="paypwd" value="" class="form-control-plus-pay" placeholder="请在此填写支付密码(在会员中心设置)">
            </div>
            <div class="form-group" id="error" style="display: none">
                <span id="error_str" class="text-danger"></span>
            </div>
            <div class="pull-right">
                <?php
                ///微信支付调试
                //如果是在微信浏览器中
                if (IsWeixinBrowser()) {
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add' type='button' onclick='gopay(\"weixin\")'>微信支付</button>";
                } else {
                    echo "请从微信登录";
                }
                if(DEBUG_LEVEL){
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"none112101\")'>模拟支付</button>";
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"\")'>模拟支付不成功</button>";
                 }
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
<script src="car_add.js"></script>
<script src="../js/weixinHideOptionMenu.js"></script>
<script src="/ui/js/jquery.spinner.js"></script>
</body>
</html>

