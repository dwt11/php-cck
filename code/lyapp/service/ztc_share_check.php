<?php
require_once(dirname(__FILE__) . "/../include/config.php");

if (empty($dopost)) $dopost = '';
CheckRank();
/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == 'check') {
//检测 是否正确

    $query = "SELECT idcard FROM `#@__client` INNER JOIN #@__client_addon ON  #@__client_addon.clientid=#@__client.id  WHERE SUBSTRING(idcard, -6)='$idcard_hz' AND mobilephone='$mobilephone'";
    $row = $dsql->GetOne($query);
    if (isset($row["idcard"])) {
         echo "验证成功";
        exit;
    } else {

        echo "信息验证错误,请核对";
        exit;

    }

}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>添加一起游</title>
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
                一起游
            </div>
            <div class="col-xs-6 text-right">
                验证好友手机号码
            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">
            <form id="formphone" class="m-t" role="form" action="">
                <input type="hidden" name="dopost" value="save"/>
                <div class="form-group">
                    <input name="mobilephone" id="mobilephone" type="number" class="form-control" value="" placeholder="好友的手机号码">
                </div>
                <div class="form-group">
                    <input name="idcard_hz" id="idcard_hz" type="text" class="form-control" value="" placeholder="好友的身份证号后六位">
                </div>
                <!--           <div class="form-group">
                    <button class="btn  btn-primary" style="float: right" type="button" onclick="settime(this,<?php echo $CLIENTID; ?>,'<?php echo urlencode("业务验证码") ?>')">获取验证码</button>
                    <input type="number" class="form-control" style="width: 45%" id="checkCode" name="checkCode" placeholder="点击右侧">
                </div>-->


                <button type="submit" class="btn btn-primary block full-width">下一步</button>
            </form>
        </div>
    </div>
</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script src="../js/sendPhoneMSG.js"></script>
<script>
    $().ready(function () {
        $("#formphone").validate({
            rules: {
                mobilephone: {required: !0, minlength: 11, isMobile: !0},
                idcard_hz: {required: !0, minlength: 6, maxlength: 6},
            },
            messages: {
                mobilephone: {required: "请填写好友的手机号", minlength: "手机号应为11个数字", isMobile: "手机号应以13/14/15/17/18开头"},
                idcard_hz: {required: "请填写好友身份证的后六位", minlength: "应为六位", maxlength: "应为六位"},
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "ztc_share_check.php",
                    data: {dopost: "check", mobilephone: $("#mobilephone").val(), idcard_hz: $("#idcard_hz").val()},
                    dataType: 'html',
                    success: function (result) {
                        if (result == "验证成功") {
                            layer.msg('验证成功', {
                                shade: 0.5, //开启遮罩
                                time: 1000, //20s后自动关闭
                            }, function () {
                                location.href = "ztc_share_add.php?idcard_hz=" + $("#idcard_hz").val() + "&mobilephone=" + $("#mobilephone").val();
                            });
                        } else {
                            layer.msg(result, {
                                shade: 0.5, //开启遮罩
                                time: 1000 //20s后自动关闭
                            });
                        }
                    }
                });
            }
        })
    });
</script>
</body>
</html>
