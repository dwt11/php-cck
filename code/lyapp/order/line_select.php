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
$query = "SELECT litpic,goodscode,goodsname,price,#@__goods_addon_lycp.tjsite,#@__goods_addon_lycp.jfnum FROM #@__goods       
          LEFT JOIN #@__goods_addon_lycp ON #@__goods_addon_lycp.goodsid=#@__goods.id
          WHERE              #@__goods.id=$goodsid AND `status`='0'
          
          ";
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
$tjsite = $arcRow["tjsite"];


//--------------------------------获取不过期的线路发车信息
$query3 = "SELECT #@__line.*   FROM  #@__line 
          WHERE   #@__line.islock=1
          AND (
                    (
                            #@__line.tmp='临时'
                            and UNIX_TIMESTAMP(now())< (#@__line.gotime-#@__line.beforHours*3600)
                    )   /*临时线路，只获取 截止时间前的*/
                  
              )
          AND goodsid='$goodsid'
          ORDER BY tmp DESC,gotime ASC";
$dsql->SetQuery($query3);
$dsql->Execute("170126");
$is_mr = false;//是否有每日线路
$ls_option = "";//临时线路分组
while ($row1 = $dsql->GetArray("170126")) {
    $lineid = $row1["id"];
    //dump($lineid);
    $tmp = $row1['tmp'];
    $linedaynumb = $row1['linedaynumb'];
    $linedaynumb_str = " 当天返回";
    //dump($linedaynumb);
    if ($linedaynumb >1) $linedaynumb_str = " 行程{$linedaynumb}天";
    $seats = $row1['seats'];//总座位数
    $s_seats = 0;//已经预约的数量
    $sy_seats = 0;//剩余数量
    $seats_number = "";
    $isappnumb = true;//是否超员 true不超员 false超员

    $carinfo = "";//几号车显示
    if ($row1['carinfo_desc'] != "") {
        $carinfo = "[" . $row1['carinfo_desc'] . "]";
    }

    if ($seats > 0) {
        //如果发车线路有座位数,则判断剩余的座位数
        $s_seats = GetLineSeatsNumb_yjyy($row1["id"]);
        //if ($s_seats > 0) {
        $sy_seats = $seats - $s_seats;
        $seats_number = " [剩{$sy_seats}件]";

        //是否还有剩余的座位
        if ($s_seats >= $seats) {
            $isappnumb = false;
        }
        //}
    }

    /*if ($tmp == '每日') {
        $is_mr = true;
        $lineid_mr = $lineid;
    }*/
    if ($tmp == '临时') {
        $gotime = date('m月d日 H时i分', $row1['gotime']);
        // $ls_option .= "<option value='$lineid' >{$gotime} {$seats_number} {$carinfo}</option>";
        $disabled = "";
        $disabled_str = "";
        $disabled_css = "h4";//默认是H2
        $ename = "name='lineid'  id='lineid'";//默认的元素名称,如果无座则没有元素名称
        if (!$isappnumb) {
            $disabled = "disabled";
            $disabled_str = "[满员]";
            $disabled_css = " text-muted small ";
            $ename = "";
        }

        $ls_option .= "
                        <li class=\"list-group-item1  $disabled_css\">
                                                   <label class=\"i-checks   \"  >
                                                         <input type='radio' $ename value='$lineid'  $disabled> <span>{$disabled_str}{$gotime} $linedaynumb_str</span> 
                                                         </label>
                                                    <span class=\"pull-right  \">
                                                         
                                                             {$seats_number} {$carinfo}
                                                         
                                                    </span>
                        </li>
                        ";
    }
}

$benprice = GetGoodBenefitInfoPrice($goodsid, $CLIENTID);


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>选择出行日期</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../../ui/css/animate.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
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
                        直通车卡:
                        <?php
                        echo $benprice;
                        ?>


                    </span>
                </span>
                <div class="clearfix"></div>
            </li>


            <?php

            if ($tjsite != "") {
                $str_ssss = "";
                $tj_array = explode(",", $tjsite);
                foreach ($tj_array as $tj) {
                    $str_ssss .= "<label class=\"checkbox-inline   i-checks\" style='min-width: 80px;line-height: 20px'>
                                <input name='tjsite' id='tjsite' type='radio'  value='$tj'  /> $tj
                            </label>\r\n";
                }

                echo "            <ul class=\"list-group list-group-plus list-font-color-black\">
                                <li class=\"list-group-item1 list-group-item-border\">
                                    上车点
                                </li>
                                <li class=\"list-group-item1   text-muted small\">
                                    
                                    $str_ssss
                                    
                                    <div class='clearfix'></div>
                                </li>
                           </ul>
               ";
            }

            //$tips = "";
            //if ($ls_option!= "" && $is_mr != "") $tips = "<span class='small'>(两个日期任选其一)</span>";
            if ($ls_option != "") {
                echo "<li class=\"list-group-item1 list-group-item-border\">
                                出发日期 
                                
                                <span class=\"pull-right  \">
                                 请选择
                            </span>
                     </li>

                                
                                     $ls_option
                                    
                                    ";

            } else {
                echo "<li class=\"list-group-item1 list-group-item-border\">
                                出发日期
                                <span class=\"pull-right    text-muted small\">
                                    暂无可选的出发日期
                                </span>
                            </li>";
            }


            /* if ($is_mr != "") echo "<label class=\"checkbox-inline   i-checks\">
                                           <input name='tmpType' id='tmpType' type='radio' value='临时' checked/>
                                       </label>";
                echo "               </span>
                            </li>";*/
            /*if ($is_mr != "") {
                $default_date = date('Y-m-d', strtotime("+1 day"));
                $max_date = date('Y-m-d', strtotime("+30 day"));//最大日期
                $min_date = date('Y-m-d', strtotime("+1 day"));//最小日期
                echo "<li class=\"list-group-item1 list-group-item-border\">
                                自选出发日期
                                <span class=\"pull-right    text-muted small\">
                                    <input   value='$lineid_mr' type=\"hidden\" name=\"lineid_mr\"  id=\"lineid_mr\" />
                                    <input min='$min_date' max='$max_date'  value='$default_date' type=\"date\" name=\"xz_time\"  id=\"xz_time\" />
                                ";

                if ($ls_option != "") echo "<label class=\"checkbox-inline   i-checks\">
                                               <input name='tmpType' id='tmpType' type='radio' value='每日' />
                                           </label>";

                echo "         </span>
                        </li>";
            }
            if ($ls_option == "" && $is_mr == "") {

            }*/
            ?>


        </ul>

        <?php
        ?>
        <div class="clearfix" style="margin-bottom: 100px"></div>


        <?php
        $disabled = "";
        $str = "";
        if ($ls_option == "" && $is_mr == "") {
            $str = "暂无发车信息";
            $disabled = ' disabled ';
        }
        echo "
            <input name='goodsid' id='goodsid' value='$goodsid' type='hidden'>
            <div class=\"bodyButtomTab\">
                <div class=\"pull-left\"> 
                                $str 
                                <span id=\"error_str\" class=\"text-danger\" ></span>
                </div>
                <div class=\"pull-right\">
                    <button onclick='lineSelectSubimt()' class='btn btn-plus btn-lg btn-primary' id='add_test' type='button' $disabled>确认信息</button>
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

<script src="line1.js"></script>
<script src="../js/weixinHideOptionMenu.js"></script>

</body>
</html>


