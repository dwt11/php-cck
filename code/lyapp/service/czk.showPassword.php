<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
if (empty($orderAddonId)) $orderAddonId = '';
if ($orderAddonId == '') {
    ShowMsg("非法参数!", "-1");
    exit();
}
if (empty($dopost)) $dopost = '';
if ($dopost == 'getpassword') {
    $pwdistrue=GetClientPayPwdIsTrue($CLIENTID,$pwd);
    if (!$pwdistrue) {
        echo "支付密码错误,操作失败！";
        exit();
    }

    $nquery = " SELECT czk_password FROM #@__order_addon_czk LEFT JOIN #@__order ON #@__order_addon_czk.orderid=#@__order.id WHERE clientid='$CLIENTID' AND #@__order_addon_czk.id='{$orderAddonId}'";
    $chRow = $dsql->GetOne($nquery);
    //dump($chRow);
    if (is_array($chRow)) {
        echo $chRow["czk_password"];
        exit();
    } else {
        echo "读取失败";
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
</head>
<body>
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
        <input type="hidden" name="orderAddonId" id="orderAddonId" value="<?php echo $orderAddonId; ?>"/>
        <div class="text-center">
            此充值卡,可在充值金币时使用
            <br>
            请勿泄露充值卡密码
            <br>
            此充值密码只可使用一次
            <br>
            此卡领出后,不可退换
        </div>

        <div class="form-group">
            <div class="col-sm-12">
                <input type="text" class="form-control pword m-b" placeholder="请先验证支付密码" onfocus="this.type='password'" autocomplete="off" name="pwd" id="pwd"/>
            </div>
            <div class="col-sm-12">
                <input type="text" class="form-control m-b" autocomplete="off" name="czkPassword" id="czkPassword" readonly/>
            </div>
            <div class="col-sm-12 text-center">
                <button class="btn btn-primary" type="submit" id="submit">显示充值卡密码</button>
            </div>
        </div>


    </form>
</div>
<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/ui/js/content.min.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>
<script src="/ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {
        //格式化16位密码,每四位,加一个空格
        $('input[name^=czk_password_]').each(function () {
            // orderlistids.push($(this).val());
            this.value = this.value.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
        });


        $("#form1").validate({
            rules: {
                pwd: {required: !0, minlength: 6}
            },
            messages: {
                pwd: {required: "请填写密码", minlength: "密码必须6个字符以上"}
            }, submitHandler: function (form) {
                //$("#submit").attr({"disabled": "disabled"});
                $.ajax({
                    type: "post",
                    url: "czk.showPassword.php",
                    data: {
                        dopost: "getpassword",
                        orderAddonId: $("#orderAddonId").val(),
                        pwd: $("#pwd").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        result = result.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
                        $("#czkPassword").val(result);
                    }
                });
            }
        })
    });
</script>
</body>
</html>
