<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
if (empty($dopost)) $dopost = '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>充值</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext">
                金币充值
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">
            <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-4 control-label">输入充值金额:</div>
                    <div class="col-xs-8">
                        <input type="number" class="form-control" name="jbnum" id="jbnum" value="100">
                    </div>
                </div>

                <div class="form-group">
                    <div class="text-center">

                        <?php
                        //如果是在微信浏览器中

                        if (IsWeixinBrowser()) {
                            echo "<button  id=\"submit\" class='btn btn-primary' type='submit'>微信支付</button>";
                        } else {
                            echo "请从微信登录";
                            //echo "<button class='btn btn-primary' type='submit'>微信支付</button>";
                        }
                        ?>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-12"  >
                        微信充值赠送积分规则：
                        <br>&nbsp;&nbsp;&nbsp;&nbsp;充值数额为100的整数倍时赠送50%，如充值数额不是100的整数倍，则按照充值数额的前一个整数倍赠送积分;
                        <br>例如：
                        <br>&nbsp;&nbsp;&nbsp;&nbsp;充值100元，可获得100金币+赠送50积分;
                        <br>&nbsp;&nbsp;&nbsp;&nbsp;充值199元，可获得199金币+赠送50积分（按照100的基数赠送积分）
                        <br>&nbsp;&nbsp;&nbsp;&nbsp;充值201元，可获得201金币+赠送100积分

                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script src="/lyapp/js/weixinPay.js"></script>
<script>
    $().ready(function () {
        $("#form1").validate({
            rules: {
                jbnum: {number: true, required: true}
            },
            messages: {
                jbnum: {number: "必须填写数字", required: "不能为空"}
            },
            submitHandler: function (form) {
                $("#submit").attr({"disabled": "disabled"});
                //进度条
                var index = layer.load(2, {
                    shade: [0.1, '#fff'] //0.1透明度的白色背景
                });
                $.ajax({
                    type: "post",
                    url: "jb_pay.php",
                    data: {jbnum: $("#jbnum").val()},
                    dataType: 'json',
                    success: function (result) {
                        layer.closeAll('loading'); //关闭加载层
                       // console.log(result);
                        var url_href= "jb.php?keyword=获得";
                        var jsApiParameters =result;
                        start_wx_pay(jsApiParameters,url_href);//去微信中跳转
                    }
                });
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg("系统错误,请重试", {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    window.location.href = 'jb_ing.php';
                });
            }
        })
    });
</script>
</body>
</html>
