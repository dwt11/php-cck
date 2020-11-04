<?php
require_once(dirname(__FILE__) . "/../include/config.php");

if (empty($dopost)) $dopost = '';
CheckRank();

//dump($_SESSION["13191151310"]);

/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == 'save') {

    $checkCode_str = ValidatePhoneCode($mobilephone, $checkCode);
    //检测验证码是否正确
    if ($checkCode_str != "验证成功") {
        echo $checkCode_str;
        exit;
    }

        //更新验证时间和客户ID
        $newpwd = substr(md5($newpwd), 5, 20);
        $sql = "UPDATE  `#@__client_pw` SET   paypwd='$newpwd'    WHERE (`clientid`='$CLIENTID');";
        $dsql->ExecuteNoneQuery($sql);
        echo "修改成功";
        exit;
}


//获取客户信息
$questr = "SELECT *  FROM `#@__client`  where  id='$CLIENTID'";
$row = $dsql->GetOne($questr);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>支付密码设定</title>
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
            <div class="col-xs-12 text-left lefttext">
                支付密码修改
            </div>

        </div>

    </div>

    <div class="ibox-content icons-box">
        <form id="formphone" class="m-t" role="form" action="">
            <input type="hidden" name="dopost" value="save"/>

            <div class="form-group">
                <input name="mobilephone" id="mobilephone" type="number" class="form-control"
                       value="<?php echo $row["mobilephone"] ?>" placeholder="手机号码" readonly>
            </div>
            <div class="form-group">
                <button class="btn  btn-primary" style="float: right" type="button"
                        onclick="settime(this,<?php echo $CLIENTID; ?>,'<?php echo urlencode("业务验证码")?>')">获取验证码
                </button>
                <input type="number" class="form-control" style="width: 45%" id="checkCode" name="checkCode"
                       placeholder="点击右侧">
            </div>


            <div class="form-group">
                <input type="text" name="newpwd" onfocus="this.type='password'" id="newpwd" autocomplete="off"
                       class="form-control" placeholder="请填写支付密码">
            </div>
            <div class="form-group">
                <input type="text" name="newpwd1" onfocus="this.type='password'" id="newpwd1" autocomplete="off"
                       class="form-control" placeholder="请确认支付密码">
            </div>


            <button type="submit" class="btn btn-primary block full-width">保存</button>
        </form>
    </div>
    <?php include("../index_foot.php"); ?>

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
                checkCode: {required: !0, minlength: 4, maxlength: 4},
                newpwd: {required: !0, minlength: 6, notEqual: "#oldpwd"},
                newpwd1: {required: !0, minlength: 6, equalTo: "#newpwd"}
            },
            messages: {
                mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "手机号应以13/14/15/17/18开头"},
                checkCode: {required: "请填写短信中的四位数字", minlength: "验证码应为4个数字", maxlength: "验证码应为4个数字"},
                newpwd: {required: "请填写新密码", minlength: "密码必须6个字符以上", notEqual: "不能与旧密码相同"},
                newpwd1: {required: "请再次确认新密码", minlength: "密码必须6个字符以上", equalTo: "两次填写的密码不一致"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "paypwd.php",
                    data: {
                        dopost: "save",
                        mobilephone: $("#mobilephone").val(),
                        checkCode: $("#checkCode").val(),
                        newpwd: $("#newpwd").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "修改成功") {
                            layer.msg('修改成功', {
                                shade: 0.5, //开启遮罩
                                time: 1000 //20s后自动关闭
                            }, function () {
                                location.href = "../index.php";
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
