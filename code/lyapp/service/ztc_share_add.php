<?php

require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();

if (empty($dopost)) $dopost = '';
/*---------------------
 function action_save(){ }
 ---------------------*/
//检测验证码是否正确
/*if(!isset($_SESSION))session_start();
if (empty($_SESSION[$mobilephone])) {
    ShowMsg("未获取手机验证码,请重新获取", "ztc_share_check.php");
    exit;
}
$phoneMsgId = $_SESSION[$mobilephone];// 在短信类中生成
$query = "SELECT body FROM `#@__interface_phonemsg_log`    WHERE id='$phoneMsgId'";
$row = $dsql->GetOne($query);
if ($row["body"] != $checkCode) {
    ShowMsg("手机验证码填写错误,请核对", "ztc_share_check.php");
    exit;
}
*/

if ($dopost == 'save') {
    if ($orderlistids != "") {
        foreach ($orderlistids as $orderlistid) {
            $query11 = "SELECT o2.clientid FROM #@__order_addon_ztc o1
                        LEFT JOIN  #@__order o2  on o1.orderid=o2.id
                          where o1.id='$orderlistid'";//临时线路  判断子订单的卡 是否使用过
            // dump($query11);
            $rowarc = $dsql->GetOne($query11);

            $clientid_o = $rowarc['clientid'];

            $createtime = time();
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__ztc_share` ( `orderListId`, `clientid_n`, `clientid_o`, `createtime`)
            VALUES ( '$orderlistid', '$CLIENTID', '$clientid_o','$createtime' );");
        }
        echo "添加成功";
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
    <link href="/ui/css/plugins/iCheck/custom.css" rel="stylesheet">
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
                    选择好友乘车卡
                </div>
            </div>

    </div>


        <?php



        //$chRow = $dsql->GetOne("SELECT id FROM `#@__client`  WHERE  mobilephone_check=1 AND mobilephone='$mobilephone' AND id!='$CLIENTID' ");

        $query = "SELECT #@__client.id FROM `#@__client`
                    LEFT JOIN #@__client_depinfos ON #@__client_depinfos.clientid=#@__client.id
                      WHERE  mobilephone='$mobilephone' AND x_client.id!='$CLIENTID' AND isdel=0";
        //dump($query);
        $chRow = $dsql->GetOne($query);
        if (is_array($chRow)) {
            $clientid_uuuu = $chRow["id"];//选择的目标用户的ID
        }
        $ztcCard_array = getZtcCard($clientid_uuuu, 0, $only_client_type = "","QT",$CLIENTID);
        //dump($ztcCard_array);
        if (isset($ztcCard_array["ztcinfo"]) && is_array($ztcCard_array["ztcinfo"])) {
            $all_ka = count($ztcCard_array["ztcinfo"]); //总子订单记数,如果是普通 会员   ，判断是否有可以使用的乘车卡
            echo "<ul class=\"list-group list-group-plus list-font-color-black\" style=\"margin-bottom: 60px;\">";
            foreach ($ztcCard_array["ztcinfo"] as $ztcinfo) {
                echo $ztcinfo;
            }

            echo "</ul>";
        }
        ?>


    <div class="bodyButtomTab">

        <?php
        // 有乘车卡
        $strtip = "";
        if ($all_ka > 0) {
            echo "
		            <div class=\"pull-right\" style=\"margin-right:10px\">

                    <button type=\"submit\" class=\"btn btn-primary \" onclick='submit();'>保存</button>
                    </div>
                    ";
        } else {
            $strtip = "没有可用的乘车卡";
        }
        ?>
        <div class="text-danger pull-left" style="margin-left: 10px;margin-top:5px">
            <?php echo $strtip; ?>
            <span id="error" class="text-danger"></span>
        </div>
    </div>

</div>


<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<script src="../../ui/js/plugins/iCheck/icheck.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script src="../../ui/js/plugins/iCheck/icheck.min.js"></script>
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });


    function submit() {

        $("#error").text("");


        var orderlistids = [];
        $('input[name=cck_]').each(function () {
            if (this.checked) {
                orderlistids.push($(this).val());
            }
        });
        if (orderlistids.length == 0) {
            $("#error").text("请选择乘车卡");
            return false;
        }
        datas = {
            orderlistids: orderlistids,
            mobilephone: <?php echo $mobilephone?>,
            dopost: "save"
        };
        $.post('ztc_share_add.php', datas, function (data, textStatus) {
            if (data == '添加成功') {
                layer.msg('添加成功', {
                    shade: 0.5, //开启遮罩
                    time: 1000 //20s后自动关闭
                }, function () {
                    location.href = "ztc_share.php";
                });

            } else {
                layer.msg(data, {
                    shade: 0.5, //开启遮罩
                    time: 1000 //20s后自动关闭
                });
            }
        }, 'html');

        return false;
    }
</script>
</body>
</html>
