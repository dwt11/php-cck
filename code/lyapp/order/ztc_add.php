<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单
require_once DWTINC . '/order.class.php';


CheckRank();


if (empty($CLIENTID)) {
    PutCookie('gourl', GetCurUrl(), 3600 * 24 * 7);//跳转页面，如果是推荐的用户，首次进入购买页面，则在用户登录 后，直接跳转到这一页
    header("Location:index.php");
    exit;
}
if (empty($dopost)) $dopost = '';
if (empty($goodsid)) $goodsid = '';
if (empty($goodsid)) {
    showMsg("非法参数", "index.php");
    exit;
}


$arcQuery = "
  SELECT litpic,goodscode,goodsname,price,rankLenth FROM #@__goods 
  LEFT JOIN #@__goods_addon_ztc  ON #@__goods.id=#@__goods_addon_ztc.goodsid
  WHERE id='$goodsid'";
$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    ShowMsg("读取档案基本信息出错!", "-1");
    exit();
}
$rankLenth = $arcRow["rankLenth"];

/*
$paypwd = "";
$row_paypwd = "SELECT paypwd FROM #@__client_pw WHERE clientid='$CLIENTID'";
$row_paypwd = $dsql->GetOne($row_paypwd);
if (isset($row_paypwd["paypwd"]) && $row_paypwd["paypwd"] != "") $paypwd = $row_paypwd["paypwd"];*/


$sponsorid = "";
$questr = "SELECT sponsorid  FROM `#@__client_addon` where  clientid='$CLIENTID'";
$row_1 = $dsql->GetOne($questr);
if ($row_1["sponsorid"] != "") $sponsorid = $row_1["sponsorid"];
//dump($sponsorid);

$idcard = $cfg_ml->fields['idcard'];//默认的身份证号
$mobilephone = $cfg_ml->fields['mobilephone'];//默认的
$realname = $cfg_ml->fields['realname'];//默认

//171004如果是续费的则从连接上直接传递过来 卡的信息,这里重新赋值
if (empty($idcard_o)) $idcard_o = '';
if (empty($cardcode_o)) $cardcode_o = '';
if (empty($name_o)) $name_o = '';
if (empty($tel_o)) $tel_o = '';
if (empty($idpic_o)) $idpic_o = '';
if (empty($orderCreateTime_o)) $orderCreateTime_o = '';
if (empty($isxf)) $isxf = '';

if ($idcard_o != "") $idcard = $idcard_o;//身份证号
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
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>确认订单</title>
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
    <?php $photo = $arcRow["litpic"];
    if ($photo == "") $photo = "/images/arcNoPic.jpg";
    $goodscode = $arcRow["goodscode"];
    $goodsname = $arcRow["goodsname"];
    //if (strlen($goodsname) > 20) $goodsname = cn_substr_utf8($goodsname, 35) . "...";
    $price100 = $arcRow["price"];
    $price = $price100 / 100;

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
            <li class="list-group-item1">
                介绍人
                <span class="pull-right  "><?php echo getOneCLientRealName($sponsorid); ?> </span>
            </li>
        </ul>

        <ul class="list-group list-group-plus list-font-color-black" id="goodslist">

            <?php

            //不是续费的才能添加数量
            echo "            <li class=\"list-group-item1 list-group-item-border\">
                                    购买数量";
            if (!($isxf == 1)) {
                echo "                  <span class=\"pull-right\" id=\"ztcaddnumb\">
                                        <a onclick=\"AddGoodsTr();\"> <i class='glyphicon glyphicon-plus' aria-hidden='true'></i> </a>
                                    </span>";
            }
            echo "
                                    &nbsp;<span id=\"buyNumb\" class=\"pull-right  \">1</span>&nbsp;
                                </li>
                    ";


            ?>


            <li class="list-group-item1" id="tr_1">
                乘车人信息
                <div class="pull-right  ">
                    <div style="max-width: 250px">
                        <div>
                            <div class="col-xs-5">
                                <input type="text" class="form-control" name="realname_1"
                                       value="<?php echo $realname; ?>" <?php if ($isxf == 1) echo "disabled" ?> id="realname_1"
                                       placeholder="姓名必填">
                            </div>
                            <div class="col-xs-7">
                                <input type="number" class="form-control" name="mobilephone_1"
                                       value="<?php echo $mobilephone; ?>" <?php if ($isxf == 1) echo "disabled" ?> id="mobilephone_1"
                                       placeholder="手机号">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div style="margin-top: 5px">
                            <div class="col-xs-12">
                                <input type="text" name="idcard_1" id="idcard_1" class="form-control"
                                       value="<?php echo $idcard ?>" <?php if ($isxf == 1) echo "disabled" ?>
                                       placeholder="身份证号">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </li>
        </ul>
        <!--        <ul class="list-group list-group-plus list-font-color-black">
                    <li class="list-group-item1">
                        <textarea name="desc" class="form-control">订单备注</textarea>
                    </li>
                </ul>

        -->
        <?php

        $gd = new goodsOrder($CLIENTID, $goodsid, $price);//如果身份为普通 会员,

        //dump($gd);
        $rankInfo = GetClientType("rank", $CLIENTID);
        $rankInfo_array = explode(",", $rankInfo);
        if (in_array("直通车", $rankInfo_array) && in_array("合伙人", $rankInfo_array)) {
            //如果为合伙人,已经购买过直通车卡,则不再享受合伙人优惠
            $gd = new goodsOrder($CLIENTID, $goodsid, $price, $no_benefit_type = "", $only_benefit_type = "直通车");

        }


        echo $gd->GetBenefitInfoHtmlToWeb();
        //echo $gd->GetJbjfdkHtmlToWeb();
        //echo $gd->GetJejsHtmlToWeb();

        //170306要在goodsOrder中统一生成当前用户当前商品的订单检验,然后购买时校验 ,购买后销毁????????????
        //$_SESSION['session_userid_'.$GLOBALS['CUSERLOGIN'] -> getUserId()]=$userid;
        //global $cfg_cookie_encode,$cfg_domain_cookie;
        //setcookie($key, $value, time()+$kptime, $pa,$cfg_domain_cookie);
        //setcookie($key.'__ckMd5', substr(md5($cfg_cookie_encode.$value),0,16), time()+$kptime, $pa,$cfg_domain_cookie);


        $coupon_jbnum = $couponid = 0;//红包金额
        if ($goodsid == 1) {
            //直通车乘车卡,才有优惠券
            $query = "SELECT isuse FROM #@__goods_coupon  WHERE id=1 ";
            $row = $dsql->GetOne($query);
            if (isset($row["isuse"]) && $row["isuse"] == 1) {
                //开启红包才能用

                $query = "SELECT id,jbnum FROM #@__clientdata_coupon  WHERE clientid='$CLIENTID'   AND (#@__clientdata_coupon.isuse='0')  ORDER BY   id DESC ";
                $row = $dsql->GetOne($query);
                //规则
                if (isset($row["jbnum"]) && $row["jbnum"] > 0) {
                    $coupon_jbnum = $row["jbnum"] / 100;
                    $couponid = $row["id"];
                }
            }
        }

        ?>
        <div style="display: none">
            <input type='text' name='benefitCreatetime' id='benefitCreatetime' value="<?php echo $gd->Get_benefit_createtime() ?>">
            <br>价格<input type='text' name='dk_jg' id='dk_jg'>
            <br>金币<input type='text' name='dk_jb' id='dk_jb'>
            <br>积分<input type='text' name='dk_jf' id='dk_jf'>
            <br>优惠券<input type='text' name='coupon_jbnum' id='coupon_jbnum' value="<?php echo $coupon_jbnum ?>">
            <br>优惠券ID<input type='text' name='couponid' id='couponid' value="<?php echo $couponid ?>">
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
                <?php if ($coupon_jbnum > 0) {
                    echo "<br>优惠券
                            <span class=\"pull-right   text-danger\" style=\"margin-right: 5px\">
                                ￥<span >-{$coupon_jbnum}</span>
                            </span>
                ";
                } ?>
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
            <div class="form-group">
                <span class="text-danger">点击购买,代表您已同意"告知书"内容</span>
            </div>
            <div class="form-group" id="paydiv" style="display: none">
                <input type="password" name="paypwd" id="paypwd" value="" class="form-control-plus-pay" placeholder="请在此填写支付密码(在会员中心设置)">
            </div>
            <div class="form-group" id="error" style="display: none">
                <span id="error_str" class="text-danger"></span>
            </div>
            <div class="pull-right">
                <button class="btn btn-plus btn-lg btn-warning" id="lyht_button" type="button" onclick='lyht(1)'>告知书</button>
                <?php
                if ($isxf == 1) {
                    //ztcDaoqiList.htm页面传递过来的续费订单
                    echo " 
                                         <script>
                                            var isxf = 1;//如果是续费不验证  手机 身份证和 实体卡
                                        </script>
                                        
                                        ";
                    ///微信支付调试
                    //如果是在微信浏览器中
                    if (IsWeixinBrowser()) {
                        echo "<button class='btn btn-plus btn-lg  btn-primary' id='add' type='button' onclick='gopay(\"ztc_save.php?paytype=weixin&isxf=1&idpic={$idpic}&orderCreateTime_new={$orderCreateTime_new}&cardcode={$cardcode_o}\",0)'>微信续费</button>";
                    } else {
                        echo "请从微信登录";
                    }
                    if (DEBUG_LEVEL) {
                        echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"ztc_save.php?paytype=none112101&isxf=1&idpic={$idpic}&orderCreateTime_new={$orderCreateTime_new}&cardcode={$cardcode_o}\",0)'>模拟续费成功</button>";
                        echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"ztc_save.php?paytype=&isxf=1&idpic={$idpic}&orderCreateTime_new={$orderCreateTime_new}&cardcode={$cardcode_o}\",0)'>模拟续费不成功</button>";

                    }
                } else {
                    echo "
                                         <script>
                                            var isxf = 0;//如果是续费不验证  手机 身份证和 实体卡
                                        </script>

                                        ";
                    ///微信支付调试
                    //如果是在微信浏览器中
                    if (IsWeixinBrowser()) {
                        echo "<button class='btn btn-plus btn-lg  btn-primary' id='add' type='button' onclick='gopay(\"ztc_save.php?paytype=weixin\",0)'>微信支付</button>";
                    } else {
                        echo "请从微信登录";
                    }
                    if (DEBUG_LEVEL) echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"ztc_save.php?paytype=none112101\",0)'>模拟支付</button>";

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


<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script src="/ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/ui/js/plugins/iCheck/icheck.min.js"></script>
<script src="/ui/js/validate.js"></script>
<script src="ztc_add.js?v=1.2"></script>
<script src="order.js"></script>
<script src="/lyapp/js/weixinPay.js"></script>
<script src="../js/weixinHideOptionMenu.js"></script>
<script>
    function lyht(lyhtid) {
        layer.open({
            type: 2,
            title: '告知书',
            closeBtn: 0, //不显示关闭按钮
            anim: 2,
            shadeClose: 0, //开启遮罩
            scrollbar: false,//浏览器滚动禁用 手机不起作用,待查
            content: '../goods/ztcgzs.php?lyhtid=' + lyhtid
        });
        //禁止主页面滚动
        $("body").bind("touchmove", function (event) {
            event.preventDefault();//code
        });
    }

</script>
</body>
</html>
