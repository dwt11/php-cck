<?php
require_once("../config.php");

if (empty($orderid)) $orderid = '';
if ($orderid == '') {
    ShowMsg("非法参数!", "-1");
    exit();
}


$query = "SELECT #@__order.*,o2.realname,o2.mobilephone from          #@__order
          LEFT JOIN #@__client o2 ON #@__order.clientid=o2.id
          WHERE  #@__order.id='$orderid'    ORDER BY #@__order.createtime DESC ";
//这里不进行订单状态判断 ,因为订单取消记录也要调用 这个进行显示
//dump($query);
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
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg" style="min-width: 350px">


<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5 style="margin-left: 5px">
            订单号:<?php echo $rowOrder["ordernum"]; ?>
        </h5>

        <div class="pull-right " style="margin-right:5px;font-size: 14pxs ">
            <?php
            if ($orderSta == 0) {
                echo "<span class='text-danger' style='font-weight: bold'>未支付</span>";
            } elseif ($orderSta == 1) {
                echo "已支付";
                if($rowOrder["isdel"]==4)echo " 部分退款";
            } else {
                echo "异常支付";
            }
            ?>
        </div>


    </div>
    <div class="ibox-content">


        <div class="alert alert-info">
            <?php


            $totalnum = (int)$rowOrder["total"] / 100;
            $paynum = (int)$rowOrder["paynum"] / 100;
            $jfnum = (int)$rowOrder["jfnum"] / 100;
            $jbnum = (int)$rowOrder["jbnum"] / 100;


            ?>
            <div class="agile-detail">
                订单总价：<?php echo $totalnum; ?>
            </div>
            <div class="agile-detail">
                商品数量：<?php echo GetOrderListNum($rowOrder["id"]); ?>
            </div>
            <?php


            $tips = "现金";
            if ($orderSta == 1) {
                $tips = "实付款";
            }

            echo "<div class=\"agile-detail\">
                    {$tips}：￥{$paynum}";

            echo "        <div class=\"text-warning pull-right\" style=\"font-weight: bold\">";
            if ($jbnum > 0) echo "金币:{$jbnum}&nbsp;";
            if ($jfnum > 0) echo "积分:{$jfnum}";
            echo "    </div>
                    </div>";

            if ($paynum > 0) {
                echo "<div class=\"agile-detail\">微信支付ID:" . $rowOrder["pay_transaction_id"] . "   </div>";
            }


            ?>


            <div class="agile-detail">
                会员信息：<?php echo $rowOrder["realname"]; ?> &nbsp;
                <div class="text-warning pull-right" style="font-weight: bold">
                    <?php echo $rowOrder["mobilephone"]; ?>
                </div>
            </div>

            <div class="agile-detail">
                <?php
                echo '订购时间：' . GetDateTimeMk($rowOrder['createtime']);
                ?>
            </div>
            <?php
            if ($rowOrder['sta'] == 1) {
                echo '<div class="agile-detail">支付时间：' . GetDateTimeMk($rowOrder['paytime']) . "</div>";
                $createtime = $rowOrder['createtime'];

            }
            ?>
        </div>


        <?php
        if ($rowOrder["ordertype"] == "orderZtc") {
            //直通车订单
            $query = "SELECT #@__order_addon_ztc.*,order1.ordernum
                                FROM #@__order_addon_ztc
                                 LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_ztc.orderid
                                WHERE #@__order_addon_ztc.orderid='{$orderid}'     ";
            //dump($query);
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
                               WHERE #@__order_addon_lycp.orderid='{$orderid}'       ";
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
            $query = "SELECT #@__order_addon_car.*,order1.ordernum
                                FROM #@__order_addon_car 
                                LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_car.orderid
                               WHERE #@__order_addon_car.orderid='{$orderid}'       ";
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
                    <li class="list-group-item1 ">
                        台数
                        <span class="pull-right  "><?php echo $row["carNumb"] ?> </span>
                    </li>
                </ul>
                <?php
            }
        }
        ?>


    </div>


    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <script src="../ui/js/content.min.js"></script>
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <!--表格-->
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
    <script src="../ui/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
    <script src="../ui/js/bootstrap-table.js"></script>
    <!--表格-->

    <SCRIPT src="../ui/js/jquery.lazyload.js" type=text/javascript></SCRIPT>
    <SCRIPT src="../ui/js/jquery.lazyload.plus.js" type=text/javascript></SCRIPT>
    <script type="text/javascript" charset="utf-8">


        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);
    </script>
</body>
</html>
