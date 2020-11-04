<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title>红包</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/lyapp/css/hongbao.css">
</head>
<body>
<div class="container" >
    <div class="RedBox">
        <div class="topcontent">
            <h2 class="bounceInDown">恭喜您获得会员卡<br>优惠券</h2>
            <img src="/images/logo.jpg" alt="" width="80" height="80" class="zoomIn">
            <div class="description1 flipInX" id='agree' >点击查看优惠券</div>
        </div>
    </div>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $('#agree').click(function () {
        window.parent.location.href = '/lyapp/member/coupon.php';
        //parent.$("body").unbind("touchmove");//恢复父页面滚动
        //parent.layer.closeAll('iframe');
    })

</script>

</body>
</html>
<?php
require_once("include/config.php");
?>

<?php
$query = "UPDATE #@__clientdata_coupon SET isview=1 WHERE  clientid='$CLIENTID'; ";
$dsql->ExecuteNoneQuery($query);
//dump(GetCookie('DWTis_coupon_view'));
DropCookie('DWTis_coupon_view');

?>