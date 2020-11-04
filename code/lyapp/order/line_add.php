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
$query = "SELECT litpic,goodscode,goodsname,price,#@__goods_addon_lycp.jfnum FROM #@__goods
 LEFT JOIN #@__goods_addon_lycp ON #@__goods_addon_lycp.goodsid=#@__goods.id
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
$benprice_ztc = GetGoodBenefitInfoPrice($goodsid, $CLIENTID, "直通车", $appttime);//直通车优惠


//默认的会员价格为商品里的JB和JF
$default_jb_jf_str = "   
   金币<span id='jbnum_basic' class='font-num' >$jbnum </span> 
   积分<span id='jfnum_basic' class='font-num' >$jfnum </span>
   ";

//优惠价格里的非会员价格   不为空 则使用这个
$benprice_0 = GetGoodBenefitInfoPrice($goodsid, $CLIENTID, "0", $appttime);//非会员
if ($benprice_0 != "") {
    if ($benprice_0 == "免费") {
        $default_jb_jf_str = "   
               金币<span id='jbnum_basic' class='font-num' >0</span> 
               积分<span id='jfnum_basic' class='font-num' >0</span>
               ";
    } else {
        $benprice_0_array = explode(" ", $benprice_0);
        //dump($benprice_0_array);
        $jb_temp = trim(str_replace("金币", "", $benprice_0_array[0]));
        $jf_temp = trim(str_replace("积分", "", $benprice_0_array[2]));
        $default_jb_jf_str = "   
               金币<span id='jbnum_basic' class='font-num' >$jb_temp</span> 
               积分<span id='jfnum_basic' class='font-num' >$jf_temp</span>
               ";
    }
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
    <form id="form" class="form-horizontal">
        <input id="goodsid" name="goodsid" value="<?php echo $goodsid; ?>" type="hidden">
        <ul class="list-group list-group-plus-nomargintop list-font-color-black">

            <li class="list-group-item1">
                <span class="h3 font-bold"><?php echo "$goodsname" ?></span>
                <div class="clearfix"></div>
            </li>
            <li class="list-group-item1">
                <span class="">
                    非会员:
                    <?php
                    echo $default_jb_jf_str;


                    ?>
                </span>
                <span class="pull-right">
                    <span class="text-danger font-bold ">
                        直通车卡:
                        <?php
                        echo $benprice_ztc;
                        ?>


                    </span>
                </span>
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


        //获取所有卡类型
        $ZTCclientType_array = GetGoodsZTCclientTYPE();
        $gd = new goodsOrder($CLIENTID, $goodsid, $price, $no_benefit_type = "", $only_benefit_type = "", $appttime);
        //$info=$gd->GetBenefitInfoHtmlToWeb_MORE_CARD();
        //dump($info);
        //dump($ZTCclientType_array);
        foreach ($ZTCclientType_array as $ZTCclientType) {
            //dump($ZTCclientType);
            $enname_global = GetPinyin($ZTCclientType, $ishead = 1);//在HTML页面中的标识名称
            //获取乘车卡
            //$isjihuo_not_panduan为1的话,关闭卡激活判断
            //$ztcCard_array = getZtcCard($CLIENTID, $appttime, $only_client_type = $ZTCclientType, "QT",$isshareCLIENTID=0,$goodsid,$isjihuo_not_panduan=1);

            //171102修改为不用激活判断
            $ztcCard_array = getZtcCard($CLIENTID, $appttime, $only_client_type = $ZTCclientType, "QT",$isshareCLIENTID=0,$goodsid);
            //dump($ztcCard_array);
            if (isset($ztcCard_array["ztcinfo"]) && is_array($ztcCard_array["ztcinfo"])) {
                echo "<ul class=\"list-group list-group-plus list-font-color-black\">";

                echo "
                        <li class=\"list-group-item1 list-group-item-border\">
                            [<span id='ztcclienttype_$enname_global'>{$ZTCclientType}</span>]已选择数量
                            <span class=\"pull-right  \">
                                <span id=\"buynumb_{$enname_global}\" >0</span>
                            </span>
                        </li>
                        ";
                foreach ($ztcCard_array["ztcinfo"] as $ztcinfo) {
                    echo $ztcinfo;
                }
                echo "<li class=\"list-group-item1   text-muted small\">
                            优惠信息
                            <span class=\"pull-right  \">
                               ";
                 //dump($ZTCclientType);
                //$info = $gd->GetBenefitInfoHtmlToWeb_ZTC_CARD($ZTCclientType);
				//170823添加   因为爱心卡会员登录时,获取不到共享直通车卡的价格,导致多付款
				$info=GetGoodBenefitInfoPrice_111111($goodsid, $ZTCclientType,$appttime);
                if ($info == "") {
                    echo "无";
                } else {
                    echo $info;
                }
                echo " <span id=\"buynumb_ztc\" ></span>
                            </span>
                        </li>
                        </ul>";
            }
        }


        echo "<ul class=\"list-group list-group-plus list-font-color-black\" id=\"goodslist\">";
        $name = $cfg_ml->fields['realname'];
        $tel = $cfg_ml->fields['mobilephone'];
        $idcard = $cfg_ml->fields['idcard'];

        $str = "<li class=\"list-group-item1 list-group-item-border\">
                            其他乘车人
                            <span class=\"pull-right  \">
                                <a onclick=\"AddGoodsTrQT();\"><i class='glyphicon glyphicon-plus' aria-hidden='true'></i> </a>
                            </span>
                            <span id=\"buyNumb\" class=\"pull-right\">0</span>
                        </li>

            ";
        echo $str;
        echo "</ul>";
        ?>


        <!--暂时不要备注，随后 再开170306
        <ul class="list-group list-group-plus list-font-color-black">
            <li class="list-group-item1">
                <textarea name="desc" id="desc" class="form-control" placeholder="订单备注"></textarea>
            </li>
        </ul>-->

        <div style="display: none">


            优惠规则<input type='text' name='benefitInfo_text' id='benefitInfo_text' value="" style="width: 600px">

            <br>余额金币<input type='text' name='ye_jb' id='ye_jb' value="<?php echo GetClientJBJFnumb('jb', $CLIENTID) ?>">
            <br>余额积分<input type='text' name='ye_jf' id='ye_jf' value="<?php echo GetClientJBJFnumb('jf', $CLIENTID) ?>">
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

        <div style="display: none">
            <input type="text" name="lineid" id="lineid" value="<?php echo $lineid ?>"><br>
            <input type="text" name="appttime" id="appttime" value="<?php echo $appttime ?>"><br>
            <input type="text" name="tjsite" id="tjsite" value="<?php echo $tjsite ?>"><br>
            <input type="text" name="tmpType" id="tmpType" value="<?php echo $tmpType ?>"><br>
        </div>
        <div class="clearfix" style="margin-bottom: 120px"></div>
        <div class="bodyButtomTab">
            <div class="form-group" id="paydiv" style="display: none">
                <input type="password" name="paypwd" id="paypwd" value="" class="form-control-plus-pay" placeholder="请在此填写支付密码(在会员中心设置)">
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
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add' type='button' onclick='gopay(\"line_save.php?paytype=weixin\",1)'>提交订单</button>";
                } else {
                    echo "请从微信登录";
                }
                if (DEBUG_LEVEL) {
                    echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"line_save.php?paytype=none112101\",0)'>模拟支付</button>";
                    //echo "<button class='btn btn-plus btn-lg  btn-primary' id='add_test' type='button'  onclick='gopay(\"line_save.php?1=1\",0)'>模拟支付不成功</button>";

                }
                ?>
            </div>
            <div class="text-danger pull-left" style="padding-left: 2px">实付款<br>￥<span id="payMoney"></span>
                <input id="totalMoney" name="totalMoney" type="hidden" value="" readonly>
            </div>
        </div>
    </form>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/lyapp/js/main.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script src="/ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/ui/js/plugins/iCheck/icheck.min.js"></script>
<script src="/ui/js/validate.js"></script>
<script src="/ui/js/public.js"></script>
<script src="/lyapp/js/weixinPay.js"></script>
<script src="line2.js?v=1.0002"></script>
<script src="../js/weixinHideOptionMenu.js"></script>
</body>
</html>

