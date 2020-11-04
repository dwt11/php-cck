<?php
require_once(dirname(__FILE__) . "/../include/config.php");

if (empty($dopost)) $dopost = '';
CheckRank();


//获取客户信息
$questr = "SELECT jbnum  FROM `#@__client_addon` WHERE   clientid='$CLIENTID'";
$row = $dsql->GetOne($questr);
$jbnum100_old = $row['jbnum'];
$jbnum_old = $jbnum100_old / 100;
$jbnum_str = $jbnum_old;
if ($dopost == 'save') {
    //对保存的内容进行处理
    $pwdistrue = GetClientPayPwdIsTrue($CLIENTID, $pwd);
    if (!$pwdistrue) {
        echo "支付密码错误,操作失败！";
        exit();
    }
    $jbnum100 = $jbnum * 100;

    if ($jbnum100 > $jbnum100_old) {
        echo "金币输入有误";
        exit();
    } elseif ($jbnum100 > 0) {
        $chRow = $dsql->GetOne("SELECT #@__client.id,realname FROM `#@__client`
                                LEFT JOIN `#@__client_depinfos` ON #@__client_depinfos.clientid=#@__client.id
                                WHERE  #@__client_depinfos.isdel=0 AND mobilephone_check=1 AND mobilephone='$mobilephone' AND #@__client.id!='$CLIENTID'
                                 ");
        if (is_array($chRow)) {
            $clientid_n = $chRow["id"];//新账户的客户ID
            Update_jb($CLIENTID, -$jbnum100, "转账给他人  " . $chRow["realname"], "", $chRow["id"]);
            Update_jb($clientid_n, $jbnum100, "收到他人转账 " . $cfg_ml->fields["realname"], "", $CLIENTID);
            echo "转账成功";
            exit();
        } else {
            echo "目标账户不可以接收";
            exit();
        }
    } else {
        echo "金额错误";
        exit();
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>转账</title>
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
                金币转账
            </div>
        </div>
    </div>
    <form id="form1" class="form-horizontal" method="post">

        <div class="alert alert-info">
            <div class="text-danger font-bold text-center" style="font-size: 28px;">请慎重操作<br>实时到账,不可撤回</div>


            <input type="hidden" name="dopost" value="save"/>
            <div class="form-group">
                <div class="col-xs-4    control-label">接收账户:</div>
                <div class="col-xs-5">
                    <input type="number" class="form-control" name="mobilephone"
                           value="" id="mobilephone"
                           placeholder="手机号">
                </div>

            </div>
            <div class="form-group">
                <div class="col-xs-4 control-label">输入金币个数:</div>
                <div class="col-xs-5">
                    <input type="number" class="form-control" name="jbnum" id="jbnum" value="">

                </div>
                <div class="col-xs-3 form-control-static" >余额:<?php echo $jbnum_str ?></div>

            </div>
            <div class="form-group">
                <div class="col-xs-4    control-label">支付密码:</div>
                <div class="col-xs-5">
                    <input type="password" name="paypwd"  id="paypwd" value=""
                           class="form-control" placeholder="请填写支付密码">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-4    control-label">对方信息:</div>
                <div class="col-xs-8 form-control-static glyphicon-red" id="realname"></div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">确转账到此账户</button>
            </div>
        </div>
    </form>


</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>

<script>
    $(document).ready(function () {
        $('#mobilephone').bind('input propertychange', function () {
            var mobilephone_t = $("#mobilephone").val();
            var lenth = mobilephone_t.length;
            console.log(lenth);
            if (lenth == 11) {
                $.ajax({
                    type: "post",
                    url: "../member/account_check.php",
                    data: {
                        mobilephone: mobilephone_t
                    },
                    dataType: 'html',
                    success: function (result) {
                        $("#realname").html(result);
                    }
                });
            }
        });
        $("#form1").validate({
            rules: {
                jbnum: {number: true, required: true, min: 0, max: <?php echo $jbnum_str ?>},
                mobilephone: {required: !0, minlength: 11, isMobile: !0},
                paypwd: {required: !0}
            },
            messages: {
                jbnum: {number: "必须填写数字", required: "不能为空", min: "金币数量不正确", max: "超出金币余额"},
                mobilephone: {required: "请填写手机号", minlength: "应为11个数字", isMobile: "手机号码错误"},
                paypwd: {required: "请填写支付密码"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "jb_zhuanzhang.php",
                    data: {
                        dopost: "save",
                        jbnum: $("#jbnum").val(),
                        pwd: $("#paypwd").val(),
                        mobilephone: $("#mobilephone").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "转账成功") {
                            layer.msg('转账成功', {
                                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                                time: 1000 //20s后自动关闭
                            }, function () {
                                window.location.href = 'jb.php?keyword=使用';
                            });
                        } else {
                            layer.msg(result, {
                                shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                                time: 1000, //20s后自动关闭
                            });
                        }
                    }
                });
            }

        });
    });


</script>


</body>
</html>
