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

//商品信息
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


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
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
                            <div class="col-sm-6 form-control-static">
                                <?php echo "【{$goodscode}】 $goodsname" ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">单价:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php
                                //默认的会员价格为商品里的JB和JF
                                $default_jb_jf_str = "   
                                   非会员:金币<span id='jbnum_basic' class='font-num' >$jbnum </span> 
                                   积分<span id='jfnum_basic' class='font-num' >$jfnum </span>
                                   ";

                                //优惠价格里的非会员价格   不为空 则使用这个
                                $benprice_0 = GetGoodBenefitInfoPrice($goodsid, $clientid, "0", $appttime);//非会员
                                //dump($benprice_0);
                                if ($benprice_0 != "") {
                                    if ($benprice_0 == "免费") {
                                        $default_jb_jf_str = "   
                                           非会员:金币<span id='jbnum_basic' class='font-num' >0</span> 
                                           积分<span id='jfnum_basic' class='font-num' >0</span>
                                           ";
                                    } else {
                                        $benprice_0_array = explode(" ", $benprice_0);
                                        //dump($benprice_0_array);
                                        $jb_temp = trim(str_replace("金币", "", $benprice_0_array[0]));
                                        $jf_temp = trim(str_replace("积分", "", $benprice_0_array[2]));
                                        $default_jb_jf_str = "   
                                          非会员: 金币<span id='jbnum_basic' class='font-num' >$jb_temp</span> 
                                           积分<span id='jfnum_basic' class='font-num' >$jf_temp</span>
                                           ";
                                    }
                                }

                                echo $default_jb_jf_str;
                                $benprice_ztc = GetGoodBenefitInfoPrice($goodsid, $clientid, "直通车", $appttime);//直通车优惠
                                if ($benprice_ztc != "") echo "   <br>直通车卡:$benprice_ztc";
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
                            <label class="col-sm-2 control-label">预约时间:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo $appttime_str; ?>
                            </div>
                        </div>
                        <?php

                        if ($tjsite != "") {
                            echo "<div class=\"form-group\">
                                    <label class=\"col-sm-2 control-label\">上车点:</label>
                                    <div class=\"col-sm-2 form-control-static\">
                                         $tjsite
                                    </div>
                                </div>";
                        }


                        //获取所有卡类型
                        $ZTCclientType_array = GetGoodsZTCclientTYPE();
                        $gd = new goodsOrder($clientid, $goodsid, $price, $no_benefit_type = "", $only_benefit_type = "", $appttime);
                        //$info=$gd->GetBenefitInfoHtmlToWeb_MORE_CARD();
                        //dump($info);
                        foreach ($ZTCclientType_array as $ZTCclientType) {

                            if($ZTCclientType!="") {
                                $enname_global = GetPinyin($ZTCclientType, $ishead = 1);//在HTML页面中的标识名称
                                echo "<div class=\"form-group alert alert-warning\">";
                                //获取乘车卡
                                $ztcCard_array = getZtcCard($clientid, $appttime, $only_client_type = $ZTCclientType, "HT");
                                if (isset($ztcCard_array["ztcinfo"]) && is_array($ztcCard_array["ztcinfo"])) {
                                    echo "<div class=\"form-group\">
                                                <label class=\"col-sm-2 control-label\">
                                                [<span id='ztcclienttype_$enname_global'>{$ZTCclientType}</span>]已选择数量:
                                                </label>
                                                <div class=\"col-sm-2 form-control-static\">
                                                    <span id=\"buynumb_{$enname_global}\">0</span> &nbsp;
                                                </div>
                                            </div>
                                         <div class=\"form-group\">
                                            <label class=\"col-sm-2 control-label\">卡选择:</label>
                                            <div class=\"col-sm-4 form-control-static\">
                                    ";
                                    foreach ($ztcCard_array["ztcinfo"] as $ztcinfo) {
                                        echo $ztcinfo;
                                    }
                                    echo "                            </div>
                                                    </div> ";


                                    echo "  <div class=\"form-group\">
                                                <label class=\"col-sm-2 control-label\">优惠信息:</label>
                                                <div class=\"col-sm-4 form-control-static\">";


                                    $info = GetGoodBenefitInfoPrice_111111($goodsid, $ZTCclientType, $appttime);
                                    if ($info == "") {
                                        echo "无";
                                    } else {
                                        echo $info;
                                    }


                                    echo "      </div>
                                        </div>";
                                } else {
                                    echo "<div class=\"form-group\">
                                            <label class=\"col-sm-2 control-label\">[$ZTCclientType]优惠:</label>
                                            <div class=\"col-sm-2 form-control-static\">
                                                暂无
                                            </div>
                                        </div>";
                                }
                                echo "</div>";

                            }
                        }


                        //其他人优惠------------------------------------------------
                        echo "<div class=\"form-group alert alert-warning\">
                                        <div class=\"form-group \">
                                            <label class=\"col-sm-2 control-label\">其他人数量:</label>
                                            <div class=\"col-sm-2 form-control-static\">
                                                <span id=\"buyNumb\">0</span> &nbsp;
                                                <a onclick=\"AddGoodsTr(1);\"> <i class='glyphicon glyphicon-plus' aria-hidden='true'></i></a>
                                            </div>
                                        </div>
                
                                     <div class=\"form-group\" id=\"goodslist\">
                                     </div>
                                    
                                </div> ";


                        ?>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单备注:</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" rows="3" name="desc" id="desc"></textarea>
                            </div>
                        </div>


                        <div style="display: none">
                             优惠规则<input type='text' name='benefitInfo_text' id='benefitInfo_text' value=""  >

                            <br>余额金币<input type='text' name='ye_jb' id='ye_jb' value="<?php echo GetClientJBJFnumb('jb', $clientid) ?>">
                            <br>余额积分<input type='text' name='ye_jf' id='ye_jf' value="<?php echo GetClientJBJFnumb('jf', $clientid) ?>">
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

                        <div style="display: none">
                            <input type="text" name="lineid" id="lineid" value="<?php echo $lineid ?>"><br>
                            <input type="text" name="appttime" id="appttime" value="<?php echo $appttime ?>"><br>
                            <input type="text" name="tjsite" id="tjsite" value="<?php echo $tjsite ?>"><br>
                            <input type="text" name="tmpType" id="tmpType" value="<?php echo $tmpType ?>"><br>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class='btn btn-primary' id='add_test' type='button' onclick='gopay("orderLine_add.php?dopost=save","orderLine.php")'>保存添加</button>
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
<script src="orderLine_add2.js?v=1.0002"></script>


</body>
</html>