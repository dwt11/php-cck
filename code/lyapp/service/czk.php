<?php
require_once(dirname(__FILE__) . "/../include/config.php");

CheckRank();


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>充值卡</title>
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
                充值卡
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>


    <?php
    $query = "SELECT id,ordernum FROM #@__order WHERE clientid='$CLIENTID' AND isdel=0 AND sta=1 AND ordertype='orderCzk' ORDER BY createtime DESC ";
    $dsql->SetQuery($query);
    $dsql->Execute(1702231551);
    while ($row11 = $dsql->GetArray(1702231551)) {
        $ordercode = $row11["ordernum"];
        $orderid = $row11["id"];
        echo "<ul class=\"list-group list-group-plus list-font-color-black\">
                <li class=\"list-group-item1 list-group-item-border\">
                   订单号:CZK{$ordercode}
                    <div class=\"clearfix\"></div>
                </li>";
        $query = "SELECT #@__order_addon_czk.*
                                FROM #@__order_addon_czk 
                                LEFT JOIN #@__order   order1 on order1.id=#@__order_addon_czk.orderid
                               WHERE #@__order_addon_czk.orderid='{$orderid}' and clientid='$CLIENTID'      ";
        $dsql->SetQuery($query);
        $dsql->Execute(1702231552);
        $czk_i = 0;
        while ($row = $dsql->GetArray(1702231552)) {
            $czk_i++;

            $usedate = $row["usedate"];
            if ($usedate > 0) {
                $czk_password = $row["czk_password"];

                $clientid = $operatorid = "";
                $query156 = "SELECT clientid,operatorid FROM `#@__clientdata_jblog` WHERE isdel=0 AND `desc` LIKE '%{$czk_password}' ";
                //dump($query);
                $row156 = $dsql->GetOne($query156);
                if (isset($row156["clientid"]) && $row156["clientid"] != "") {
                    $clientid = getOneCLientRealName($row156["clientid"]);
                    $operatorid = GetEmpNameByUserId($row156["operatorid"]);
                }

                $usedate = "时间:" . GetDateTimeMk($usedate);
                $usedate .= "<br>使用人:[$clientid]  操作人:[$operatorid]";
            } else {
                $id = $row["id"];
                $usedate = "密码:<a onclick=\"layer.open({type: 2,title: '查看充值卡密码', content: 'czk.showPassword.php?orderAddonId=$id'});\"  href='javascript:' data-toggle='tooltip' data-placement='top' title='查看充值卡密码' > 查看充值卡密码 </a> ";
            }

            $je=$row["je"]/100;
            echo "        <li class=\"list-group-item1 list-group-item-border\">
                    卡{$czk_i} {$usedate}
                    <span class=\"pull-right  \">￥{$je} </span>
                </li>
                ";

        }
        echo "</ul><br>";

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
