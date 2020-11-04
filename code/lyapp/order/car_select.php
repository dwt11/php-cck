<?php

require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单


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
$price100 = $arcRow["price"];
$jfnum100 = $arcRow["jfnum"];
$price = $price100 / 100;
$jfnum = $jfnum100 / 100;
$jbnum = $price - $jfnum;
if ($jfnum <= 0) $jfnum = 0;
if ($jbnum <= 0) $jbnum = 0;

if (empty($now_rankinfo)) $now_rankinfo = '';//如果有多个会员身份 当前要显示的身份

//获取用户类型
$rankInfo = GetClientType("rank", $CLIENTID);
$rankInfo_array = explode(",", $rankInfo);
//dump($rankInfo_array);
$ishhr = false;//默认不是合伙人
if (in_array("合伙人", $rankInfo_array)) {
    $ishhr = true;
    //$now_rankinfo="合伙人";
}

$isztc = false;//默认不是直通车
if (in_array("直通车", $rankInfo_array)) {
    $isztc = true;
    //$now_rankinfo="直通车";
}
$clickRankInfo="";
if ($ishhr && $isztc && $now_rankinfo == "") {
    $now_rankinfo = "合伙人";//如果有直能车和合伙人两个身份,并且没有指定人员类型，则默认未选择的时候 只显示合伙人的
    $clickRankInfo = "直通车";
    } elseif ($ishhr && $isztc && $now_rankinfo == "直通车") {
    //如果用户点击了会员类型
    $now_rankinfo = "直通车";
    $clickRankInfo = "合伙人";
} elseif ($ishhr && $isztc && $now_rankinfo == "合伙人") {
    //如果用户点击了会员类型
        $now_rankinfo = "合伙人";
        $clickRankInfo = "直通车";
}elseif($ishhr && !$isztc ){
//如果只有一个会员类型
   $now_rankinfo="合伙人";
}elseif(!$ishhr && $isztc ){
//如果只有一个会员类型
   $now_rankinfo="直通车";
}

//$benprice = GetGoodBenefitInfoPrice($goodsid, $CLIENTID);

//dump($appttime);
//if (empty($appttime)) $appttime = '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>租赁车辆选择使用日期</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="../../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
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
                    echo "金币<span id='jbnum_basic' class='font-num' >$jbnum </span>";
                    echo "积分<span id='jfnum_basic' class='font-num' >$jfnum </span>";
                    ?>
                </span>
                <span class="pull-right">
                    <span class="text-danger font-bold ">

<?php
if ($now_rankinfo != "") echo "当前页面显示为:[{$now_rankinfo}]价格";
//这里因为每个价格不同，则不能直接显示价格，以下面的列表价格为准
?>

                    </span>
                </span>
                <div class="clearfix"></div>
            </li>


                <?php

            $data_option = "";
            for ($daynumbiii = 1; $daynumbiii <= 30; $daynumbiii++) {
                $nowday_int = time() + $daynumbiii * 86400;

                $date_str = date('m月d日', $nowday_int);
                $date_str_all = date("Y-m-d", $nowday_int);
                //dump($date_str_all);
                $benprice = "";


                $ky = true;//车辆所在日期是否可用  默认可用
                $ky_str = $disabled = "";
                $query444 = "SELECT id FROM #@__car_stop WHERE   goodsid=$goodsid AND FROM_UNIXTIME(stoptime,'%Y-%m-%d')=FROM_UNIXTIME($nowday_int,'%Y-%m-%d')   ";
                //dump($query444);
                $arcRow444 = $dsql->GetOne($query444);
                if (is_array($arcRow444)) {
                    $ky = false;
                    $ky_str = "[不可用]";
                    $disabled = " disabled ";
                }

                if ($now_rankinfo != "") $benprice = GetGoodBenefitInfoPrice($goodsid, $CLIENTID, $clientTypeValue = $now_rankinfo, $appttime = $nowday_int);
                if ($benprice == "") $benprice = "金币$jbnum 积分$jfnum";
                $data_option .= " <li class=\"list-group-item1  h4\">
                                                   <label class=\"i-checks\"  >
                                                         <input type='checkbox' name='sydateint_$daynumbiii'  id='sydateint_$daynumbiii' value='$nowday_int' {$disabled} > 
                                                         <span id='datestr_$daynumbiii'>$date_str</span>  $ky_str
                                                         <span id='datestrall_$daynumbiii' style='display: none'>$date_str_all</span> 
                                                         </label>
                                                         
                                                    <span class=\"pull-right  \" id='money_$daynumbiii'>
                                                      $benprice        
                                                    </span>
                        </li>
                                ";
            }

            $date_int_1 = time() + 1 * 86400;
            $date_int_2 = time() + 2 * 86400;
            $date_int_3 = time() + 3 * 86400;
            $date_str_1 = date('m月d日', $date_int_1);
            $date_str_2 = date('m月d日', $date_int_2);
            $date_str_3 = date('m月d日', $date_int_3);
            $date_str_all_1 = date('Y-m-d', $date_int_1);
            $date_str_all_2 = date('Y-m-d', $date_int_2);
            $date_str_all_3 = date('Y-m-d', $date_int_3);

                echo "<li class=\"list-group-item1 list-group-item-border\">
                                               请选择连续的日期; 
                                                 <span class=\"pull-right  \">";
                if ($ishhr && $isztc) {
                    echo "<span class=\"text-danger font-bold \">

                        <a href=\"car_select.php?goodsid={$goodsid}&did=17&now_rankinfo={$clickRankInfo}\">查看[{$clickRankInfo}]价格</a>

                    </span>";
                }
                echo "</span>
                     </li>
                                     
                                    ";
                echo "<li class=\"list-group-item1 list-group-item-border\">
                                               开始日期必须是 <b>{$date_str_1}</b> 或 <b>{$date_str_2}</b> 或   <b>{$date_str_3}<b>  
                                <span class=\"pull-right  \">";

                echo "</span>
                     </li>
                                     $data_option
                                    ";
            ?>
        </ul>
        <div class="clearfix" style="margin-bottom: 100px"></div>
        <?php
        $disabled = "";

        echo "
            <input name='goodsid' id='goodsid' value='$goodsid' type='hidden'>
            <div class=\"bodyButtomTab\">
                <div class=\"pull-left\"> 
                                <span id=\"error_str\" class=\"text-danger\" ></span>
                </div>
                <div class=\"pull-right\">
                    <button onclick='carSelectSubimt(\"$date_str_all_1,$date_str_all_2,$date_str_all_3\")' class='btn btn-plus btn-lg btn-primary' id='add_test' type='button' $disabled>确认信息</button>
                </div>
            </div>";
        ?>
    </form>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/lyapp/js/main.js"></script>
<script src="/ui/js/jquery.lazyload.js" type=text/javascript></script>
<script src="/ui/js/jquery.lazyload.plus.js" type=text/javascript></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/iCheck/icheck.min.js"></script>
<script src="car_select.js"></script>
<script src="../js/weixinHideOptionMenu.js"></script>

</body>
</html>


