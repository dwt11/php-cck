<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();

if (empty($orderlistid)) $orderlistid = '';
if (empty($dopost)) $dopost = '';

if ($orderlistid == '') {
    ShowMsg("获取参数出错!", "-1");
    exit();
}
$query = "SELECT #@__order_addon_ztc.* FROM #@__order_addon_ztc
          LEFT JOIN #@__order ON #@__order.id=#@__order_addon_ztc.orderid
          LEFT JOIN #@__ztc_zhuanyi ON #@__ztc_zhuanyi.orderListId=#@__order_addon_ztc.id
          WHERE   #@__order_addon_ztc.id='$orderlistid'  AND 
          (#@__order.clientid=$CLIENTID OR  #@__ztc_zhuanyi.clientid_n=$CLIENTID)";
$row = $dsql->GetOne($query);
if (!is_array($row)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}

if ($dopost == 'save') {
    $chRow = $dsql->GetOne("SELECT id FROM `#@__client`  WHERE  mobilephone_check=1 AND mobilephone='$mobilephone' AND id!='$CLIENTID' ");
    if(is_array($chRow)){
        $clientid_n=$chRow["id"];//新账户的客户ID
        $createtime = time();
        $dsql->ExecuteNoneQuery("INSERT INTO `#@__ztc_zhuanyi` ( `orderListId`, `clientid_n`, `clientid_o`, `createtime`)
            VALUES ( '$orderlistid', '$clientid_n', '$CLIENTID','$createtime' );");
        $str= "转移成功";
    }else {
        $str= "目标账户不能接收此卡,请重新选择";
    }
    echo $str;
    exit;
}



?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>转移乘车卡</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-12 text-left" style="font-size: 24px">
                转移乘车卡到其他的账户
            </div>
        </div>
    </div>
    <form id="form1" class="form-horizontal" method="post">

        <div class="alert alert-info">
            <div class="text-danger font-bold text-center" style="font-size: 28px;">请慎重操作<br>只有一次转移机会</div>
            <div class="text-danger font-bold text-center" style="font-size: 28px;">转移后,不可以被共享</div>
            <div class="font-bold ">
                <?php echo "<B>" . GetZtcCardCode($row['orderid'], $row['id']) . "</B> "; ?>
            </div>

            <div>
                联系方式:<?php echo $row['name']; ?>
                <?php echo $row['tel']; ?>
            </div>

            <div>
                身份证号:<?php echo $row['idcard'] ?>
            </div>


            <input type="hidden" name="dopost" value="save"/>
            <input id="orderlistid" name="orderlistid" value="<?php echo $orderlistid; ?>" type="hidden">
            <br>
            <div class='hr-line-dashed' style="margin: 0; padding: 0;margin-top: 5px;margin-bottom:  5px"></div>
            <br>
            <div class="form-group">
                <div class="col-xs-4    control-label">接收账户:</div>
                <div class="col-xs-5">
                    <input type="number" class="form-control" name="mobilephone"
                           value="" id="mobilephone"
                           placeholder="手机号">
                </div>
                <div class="col-xs-3 form-control-static" id="realname"></div>
            </div>
            <div class="form-group">
                <div class="col-xs-4    control-label">支付密码:</div>
                <div class="col-xs-5">
                    <input type="text" name="paypwd" onfocus="this.type='password'" id="paypwd" autocomplete="off" value=""
                           class="form-control" placeholder="请填写支付密码">
                </div>
            </div>
             <div class="text-center">
                <button type="submit" class="btn btn-primary">确认将乘车卡转移到此账户</button>
                <div class="text-center"><span class="error"></span></div>
            </div>
        </div>
    </form>


</div>


<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>


<script>
    $(document).ready(function () {
        $('#mobilephone').bind('input propertychange', function () {
            var mobilephone_t=$("#mobilephone").val();
            var lenth=mobilephone_t.length;
            console.log(lenth);
            if(lenth==11) {
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
                mobilephone: {required: !0,minlength: 11,    isMobile: !0                },
                paypwd: {
                    required: !0, remote: {//校验密码是否正确
                        type: "post",
                        url: "../member/paypwd_check.php?paypwd=" + $("#paypwd").val(),
                        dataType: "html",
                        dataFilter: function (data, type) {
                            if (data == "支付密码正确") {
                                return true;
                            } else {
                                return false;
                            }

                        }
                    }
                }
            },
            messages: {
                mobilephone: {required: "请填写手机号", minlength: "应为11个数字", isMobile: "手机号码错误"},
                paypwd: {required: "请填写支付密码", remote: "支付密码错误"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "ztcCard_zhuanyi.php",
                    data: {
                        dopost: "save",
                        mobilephone: $("#mobilephone").val(),
                        orderlistid: $("#orderlistid").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "转移成功") {
                            layer.msg('转移成功', {
                                time: 1000 //20s后自动关闭
                            }, function () {
                                window.location.href = 'ztcCard.php';
                            });
                        } else {
                            layer.msg(result, {
                                time: 3000 //20s后自动关闭
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
