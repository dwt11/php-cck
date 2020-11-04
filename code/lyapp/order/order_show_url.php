<?php
/**用户在会员中心打开的订单详情**/
require_once(dirname(__FILE__) . "/../include/config.php");
require_once DWTINC . '/enums.func.php';  //获取联动枚举表单

CheckRank();
if (empty($orderid)) $orderid = '';
if ($orderid == '') {
    ShowMsg("非法参数!", "-1");
    exit();
}


$query = "SELECT o1.*,o2.realname,o2.mobilephone FROM
          #@__order o1
          LEFT JOIN #@__client o2 ON o1.clientid=o2.id
          where o1.clientid='$CLIENTID' AND o1.id='$orderid'  AND (o1.isdel=0 OR o1.isdel=4 ) ORDER BY o1.createtime DESC ";
$rowOrder = $dsql->GetOne($query);
//没有信息就不显示 161101
if (!isset($rowOrder) || $rowOrder == "") {
    ShowMsg("非法参数!", "-1");
    exit();
}
$orderSta = $rowOrder["sta"];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>订单详情</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
    <link href="../../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext">
                订单详情
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>
    <div class="alert alert-info">
        <h2 class="text-center">
            <?php
            if ($orderSta == 0) {
                echo "<span class='text-danger' style='font-weight: bold'>未支付</span>";
                //如果未支付,则自动退款
                CancelOrderNoPay($rowOrder["id"], $CLIENTID, $orerid=0);
            } elseif ($orderSta == 1) {
                echo "已支付";
                if($rowOrder["isdel"]==4)echo " 部分退款";
            } else {
                echo "异常支付";
            }
            ?>
        </h2>

        <div class="agile-detail">
            订单总价：<?php
            $totalnum = $rowOrder["total"] / 100;
            echo $totalnum;
            ?>
        </div>
        <div class="agile-detail">
            商品数量：<?php echo GetOrderListNum($rowOrder["id"]); ?>
        </div>
        <?php

        $paynum = (int)$rowOrder["paynum"] / 100;
        $jfnum = (int)$rowOrder["jfnum"] / 100;
        $jbnum = (int)$rowOrder["jbnum"] / 100;


        $tips = "现金";
        if ($orderSta == 1) {
            $tips = "实付款";
        }

        echo "<div class=\"agile-detail\">
                    {$tips}：￥{$paynum}
                    <div class=\"text-warning pull-right\" style=\"font-weight: bold\">";
        if ($jbnum > 0) echo "金币:{$jbnum}&nbsp;";
        if ($jfnum > 0) echo "积分:{$jfnum}";
        echo "    </div>
                    </div>";
        ?>


        <div class="agile-detail">


            <?php
            echo '订购时间：' . GetDateTimeMk($rowOrder['createtime']);
            ?>
        </div>
        <?php
        if ($orderSta == 1) {
            echo '<div class="agile-detail">支付时间：' . GetDateTimeMk($rowOrder['paytime']) . "</div>";

        }
        ?>
    </div>


    <?php
    if ($rowOrder["ordertype"] == "orderZtc") {
        //直通车订单
        $query = "SELECT #@__order_addon_ztc.*,order1.ordernum
                                FROM #@__order_addon_ztc 
                                LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_ztc.orderid
                               WHERE #@__order_addon_ztc.orderid='{$orderid}' and clientid='$CLIENTID'      ";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {

            ?>
            <ul class="list-group list-group-plus list-font-color-black">
                <li class="list-group-item1 list-group-item-border">
                    <?php echo getOrderGoodsList($orderid); ?>
                    <div class="clearfix"></div>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    姓名
                    <span class="pull-right  "><?php echo $row["name"] ?> </span>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    电话
                    <span class="pull-right  "><?php echo $row["tel"] ?> </span>
                </li>
                <li class="list-group-item1 ">
                    身份证
                    <span class="pull-right  "><?php echo $row["idcard"] ?> </span>
                </li>
            </ul>

            <?php

        }
    }

    if ($rowOrder["ordertype"] == "orderLycp") {
        //旅游产品订单订单
        $query = "SELECT #@__order_addon_lycp.*,order1.ordernum
                                FROM #@__order_addon_lycp 
                                LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_lycp.orderid
                               WHERE #@__order_addon_lycp.orderid='{$orderid}' and clientid='$CLIENTID'      ";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            $del_tip="";
            if ($row['isdel'] == "1") $del_tip = "<b>已从出行列表中删除</b>";

            ?>
            <ul class="list-group list-group-plus list-font-color-black">
                <li class="list-group-item1 list-group-item-border">
                    <?php echo getOrderGoodsList($orderid);

                    echo "<br>".$del_tip;?>
                    <div class="clearfix"></div>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    姓名
                    <span class="pull-right  "><?php echo $row["realname"] ?> </span>
                </li>

                <li class="list-group-item1 list-group-item-border">
                    发车时间
                    <span class="pull-right  "><?php echo GetDateNoYearMk($row["appttime"]) ?> </span>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    电话
                    <span class="pull-right  "><?php echo $row["tel"] ?> </span>
                </li>
                <li class="list-group-item1 ">
                    身份证
                    <span class="pull-right  "><?php echo $row["idcard"] ?> </span>
                </li>
            </ul>
            <?php
        }
    }
    if ($rowOrder["ordertype"] == "orderCar") {
        //车辆租赁订单
        $query = "SELECT #@__order_addon_car.*,order1.sta
                                FROM #@__order_addon_car 
                                LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_car.orderid
                               WHERE #@__order_addon_car.orderid='{$orderid}' and clientid='$CLIENTID'      ";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            ?>
            <ul class="list-group list-group-plus list-font-color-black">
                <li class="list-group-item1 list-group-item-border">
                    <?php echo getOrderGoodsList($orderid); ?>
                    <div class="clearfix"></div>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    姓名
                    <span class="pull-right  "><?php echo $row["realname"] ?> </span>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    电话
                    <span class="pull-right  "><?php echo $row["tel"] ?> </span>
                </li>

                <li class="list-group-item1 list-group-item-border">
                    取车时间
                    <span class="pull-right  "><?php echo GetDateNoYearMk($row["start_date"]) ?> </span>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    还车时间
                    <span class="pull-right  "><?php echo GetDateNoYearMk($row["end_date"]) ?> </span>
                </li>
                <li class="list-group-item1 list-group-item-border">
                    台数
                    <span class="pull-right  "><?php echo $row["carNumb"] ?> </span>
                </li>
                <?php if ($row["sta"] == 1) { ?>
                    <li class="list-group-item1 list-group-item-border">
                        车辆信息
                        <span class="pull-right  "><?php echo GetOrderUseDeviceLog($row['id']); ?> </span>
                        <div class="clearfix"></div>
                    </li>
                    <li class="list-group-item1 ">
                        车辆状态
                        <span class="pull-right  "><?php
                            if ($row["state"] == 0) echo "未取车";
                            if ($row["state"] == 1) echo "已提车";
                            if ($row["state"] == 2) echo "已还车";

                            ?> </span>
                    </li>
                <?php } ?>
            </ul>
            <?php
        }
    }
    if ($rowOrder["ordertype"] == "orderCzk") {
        //车辆租赁订单

        $goodsinfo = getOrderGoodsList($orderid);
        echo "<ul class=\"list-group list-group-plus list-font-color-black\">
                <li class=\"list-group-item1 list-group-item-border\">
                   {$goodsinfo  }
                    <div class=\"clearfix\"></div>
                </li>";
        $query = "SELECT #@__order_addon_czk.*
                                FROM #@__order_addon_czk 
                                LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_czk.orderid
                               WHERE #@__order_addon_czk.orderid='{$orderid}' and clientid='$CLIENTID'      ";
        $dsql->SetQuery($query);
        $dsql->Execute();
        $czk_i = 0;
        while ($row = $dsql->GetArray()) {
            $czk_i++;
            $je = (int)$row["je"] / 100;

            /*$usedate = $row["usedate"];
            if ($usedate > 0) {
                $usedate = " 时间:" . GetDateTimeMk($usedate);
                $usedate .= " 使用人:[]";
            } else {
                $id=$row["id"];
                $usedate="密码:<a onclick=\"layer.open({type: 2,title: '查看充值卡密码', content: 'czk.showPassword.php?orderAddonId=$id'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='查看充值卡密码' > 查看充值卡密码 </a> ";
            }*/
            echo "        <li class=\"list-group-item1 list-group-item-border\">
                    卡{$czk_i} 
                    <span class=\"pull-right  \">￥{$je} </span>
                </li>
                ";

        }
        echo "</ul>";
    }
    ?>


    <?php include("../index_foot.php"); ?>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/lyapp/js/main.js"></script>
<script src="/ui/js/jquery.lazyload.js" type=text/javascript></script>
<script src="/ui/js/jquery.lazyload.plus.js" type=text/javascript></script>
<script src="/lyapp/js/quickButton.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>


</body>
</html>
