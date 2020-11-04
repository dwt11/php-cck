<?php

require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();

if (empty($orderlistid)) $orderlistid = '';
if (empty($dopost)) $dopost = '';

if ($orderlistid == '') {
    ShowMsg("获取参数出错!", "-1");
    exit();
}
//先判断 是否有权限,再保存
$query = "SELECT #@__order_addon_ztc.* FROM #@__order_addon_ztc
          LEFT JOIN #@__order ON #@__order.id=#@__order_addon_ztc.orderid
         WHERE   #@__order_addon_ztc.id='$orderlistid'  AND ISNULL(editdate) AND 
          (#@__order.clientid=$CLIENTID )";
$row = $dsql->GetOne($query);
if (!is_array($row)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}

if ($dopost == 'save') {
    if ($realname == "" || $mobilephone == "" || $idcard == "") echo "保存失败，姓名、电话、身份证不可以为空";
    $query = "UPDATE #@__order_addon_ztc SET `name`='$realname',`tel`='$mobilephone', idcard='$idcard',editdate='" . time() . "' WHERE id='$orderlistid'; ";
    $dsql->ExecuteNoneQuery($query);
    echo "修改成功";
    exit;
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>编辑乘车卡信息</title>
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
                编辑乘车卡信息
            </div>
        </div>
       </div>
    <form id="form1" class="form-horizontal" method="post">

        <div class="alert alert-info">
            <div class="text-danger font-bold text-center" style="font-size: 28px;">请慎重操作<br>只有一次修改机会</div>
            <div class="form-group">
                <div class="col-xs-3    control-label">联系方式:</div>
                <div class="col-xs-3">
                    <input type="text" class="form-control" name="realname"
                           value="<?php echo $row['name']; ?>" id="realname"
                           placeholder="姓名必填">
                </div>
                <div class="col-xs-5">
                    <input type="number" class="form-control" name="mobilephone"
                           value="<?php echo $row['tel']; ?>" id="mobilephone"
                           placeholder="手机号">
                </div>
            </div>

            <div class="form-group">
                <div class="col-xs-3 control-label">身份证号:</div>
                <div class="col-xs-8">
                    <input type="text" name="idcard" id="idcard"
                           class="form-control" value="<?php echo $row['idcard'] ?>" placeholder="身份证号">
                </div>
            </div>


            <input type="hidden" name="dopost" value="save"/>
            <input id="orderlistid" name="orderlistid" value="<?php echo $orderlistid; ?>" type="hidden">


            <div class="text-center">
                <button type="submit" class="btn btn-primary">保存信息</button>
                <div class="text-center"><span id="error" class="text-danger"></span></div>
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
        $("#form1").validate({
            rules: {
                realname: {required: !0},
                mobilephone: {required: !0, minlength: 11, isMobile: !0},
                idcard: {required: !0, isIdCardNo: !0}
            },
            messages: {
                realname: {required: "请填写姓名"},
                mobilephone: {required: "请填写手机号", minlength: "手机号应为11个数字", isMobile: "请正确填写您的手机号码"},
                idcard: {required: "请填写身份证号", isIdCardNo: "身份证号格式不正确"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "ztcCard_edit.php",
                    data: {
                        dopost: "save",
                        orderlistid: $("#orderlistid").val(),
                        mobilephone: $("#mobilephone").val(),
                        realname: $("#realname").val(),
                        idcard: $("#idcard").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "修改成功") {
                            layer.msg('修改成功', {
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
