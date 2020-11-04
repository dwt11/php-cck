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

$query1 = "SELECT gotime FROM #@__line WHERE   id=$lineid";
$arcRow1 = $dsql->GetOne($query1);
if (isset($arcRow1["gotime"])) {
    $appttime_str = date('Y年m月d日  H时i分', $arcRow1["gotime"]);

    $appttime = $arcRow1["gotime"];
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>旅游订单快速添加</title>
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
                    <h5>旅游订单快速添加 </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <div class="alert alert-warning alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                       1  本页只可以给乘车卡添加订单;<br>
                       2  无乘车卡的,请在普通"订单添加"中使用"其他人",创建订单;<br>
                        3 此页会为选择的每个会员,自动创建一个订单,并从会员账户扣款
                    </div>

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
                                <?php
                                //默认的会员价格为商品里的JB和JF
                                $default_jb_jf_str = "   
                                   非会员:金币<span id='jbnum_basic' class='font-num' >$jbnum </span> 
                                   积分<span id='jfnum_basic' class='font-num' >$jfnum </span>
                                   ";

                                //优惠价格里的非会员价格   不为空 则使用这个
                                $benprice_0 = GetGoodBenefitInfoPrice($goodsid, 0, "0", $appttime);//非会员
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
                                $benprice_ztc = GetGoodBenefitInfoPrice($goodsid, 0, "直通车", $appttime);//直通车优惠
                                if ($benprice_ztc != "") echo "   <br>直通车卡:$benprice_ztc";
                                ?>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">预约时间:</label>
                            <div class="col-sm-2 form-control-static">
                                <?php echo $appttime_str; ?>
                            </div>
                        </div>

                        <?php
                        $gd = GetGoodBenefitInfo_clientTypeName_array($goodsid, $appttime);
                        //$info=$gd->GetBenefitInfoHtmlToWeb_MORE_CARD();

                        foreach ($gd as $clientTypeName_array) {

                            $clientTypeName = $clientTypeName_array["clientTypeName"];
                            $jbnum_temp_11 = $clientTypeName_array["jbnum"];
                            $jfnum_temp_11 = $clientTypeName_array["jfnum"];
                            $enname_global = GetPinyin($clientTypeName, $ishead = 1);//在HTML页面中的标识名称
                            echo "<div class=\"form-group alert alert-warning\">
                                        <div class=\"form-group \">
                                                <label class=\"col-sm-2 control-label\">[<span id='ztcclienttype_$enname_global'>{$clientTypeName}</span>] 订单数量:</label>
                                                <div class=\"col-sm-8 form-control-static\">
                                                    <span id=\"buyNumb_{$enname_global}\">0</span> &nbsp;
                                                            <a onclick=\"AddGoodsListTr_quick_forZTCCARD('$enname_global','$clientTypeName','$appttime');\"> <i class='glyphicon glyphicon-plus' aria-hidden='true'></i></a>
                                                     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                     <b>单价:金币<span id='jbnum_$enname_global'>{$jbnum_temp_11}</span> 积分<span id='jfnum_$enname_global'>{$jfnum_temp_11}</span></b>
                                                </div>
                                        </div>
                        
                                    <div class=\"form-group\" id=\"goodslist_$enname_global\">
                                    </div>
                        
                                </div> ";

                        }


                        /* //无乘车卡会员------------------------------------------------
                         echo "<div class=\"form-group alert alert-warning\">
                                                 <div class=\"form-group \">
                                                 <label class=\"col-sm-2 control-label\">无乘车卡 普通会员 数量:</label>
                                                 <div class=\"col-sm-2 form-control-static\">
                                                 <span id=\"buyNumb\">0</span> &nbsp;
                                                 <a onclick=\"AddGoodsTr(1);\"> <i class='glyphicon glyphicon-plus' aria-hidden='true'></i></a>
                                         </div>
                                     </div>

                                     <div class=\"form-group\" id=\"goodslist\">
                                     </div>

                                 </div> ";*/


                        ?>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">订单备注:</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" rows="3" name="desc" id="desc"></textarea>
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

                        <div style="display: none">
                            <input type="text" name="lineid" id="lineid" value="<?php echo $lineid ?>"><br>
                            <input type="text" name="appttime" id="appttime" value="<?php echo $appttime ?>"><br>
                            <input type="text" name="tjsite" id="tjsite" value=""><br>
                            <input type="text" name="tmpType" id="tmpType" value=""><br>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class='btn btn-primary' id='add_test' type='button' onclick='gopay_quick("orderLine.addquick.save.php?dopost=save","orderLine.php",0)'>保存添加</button>
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
<script src="orderLine.addquick.js"></script>
</body>
</html>