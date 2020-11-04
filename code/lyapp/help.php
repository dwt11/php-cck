<?php
require_once("include/config.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>功能说明</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext">
                功能说明
            </div>

        </div>

    </div>
    <div class="ibox-content " style="line-height: 26px;">
        <!-- <p>
             <b>1、会员体系：</b>

         <div style=" text-indent: 2em;">会员体系分为三级。</div>
         <div style=" text-indent: 2em;">第一级：<b>当前会员</b>;</div>
         <div style=" text-indent: 2em;">第二级：推广后的<b>直推会员</b>;</div>
         <div style=" text-indent: 2em;">第三级：第二级推广后的<b>转推会员</b>。</div>
         </p>-->
        <p>
            <b>1、积分规则：</b>

        <div style=" text-indent: 2em;">购买成功即送200消费积分。</div>
        <div style=" text-indent: 2em;">A推广新会员B成功购买本卡，随即奖励A 50金币+50消费积分。</div>
        <div style=" text-indent: 2em;">当B推广新会员C成功购买时，奖励A30金币+30消费积分；奖励B50金币+50消费积分。</div>
        </p>

        <p>
            <b>3、提现规则：</b>

        <div style=" text-indent: 2em;">金币500以上（包含500）可以提现。</div>
        </p>
        <p>
            <b>4、转股规则：</b>

        <div style=" text-indent: 2em;">金币2000以上，可以手动转变为一股债券股份，资金锁定三年，三年内可以免费乘坐景区直通车，并且参与公司分红,数额总数达到股份本金数额，三年到期退还本金。</div>
        </p>
        <p class="text-center">
            活动最终解释权归 旅游汽车有限公司
        </p>
    </div>
    <?php include("index_foot.php"); ?>

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
