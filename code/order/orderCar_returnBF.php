<?php
/*部分退款*/
require_once('../config.php');

$orderid = trim(preg_replace("#[^0-9]#", '', $orderid));

if (empty($dopost)) $dopost = '';

$nquery = " SELECT paynum,jbnum,jfnum FROM `#@__order`  where id='$orderid' AND isdel=0 and sta='1' ";
$arcRow11 = $dsql->GetOne($nquery);
if (is_array($arcRow11)) {
    $paynum = $arcRow11["paynum"] / 100;
    $jbnum = $arcRow11["jbnum"] / 100;
    $jfnum = $arcRow11["jfnum"] / 100;

    $maxjbnum = $paynum + $jbnum;
}
if($maxjbnum==0&&$jfnum==0){
    echo("无可退款的金币和积分数量！");
    exit();
}
if ($dopost == 'save') {

    if ($orderid == '') {
        echo("参数无效！");
        exit();
    }

    if($jbnum_str<=0&&$jfnum_str<=0){
        echo "请输入退款的金币或积分";
        exit();
    }

    if ($jbnum_str > $maxjbnum) {
        echo("退款金币数量不能大于(订单中现金+金币的数量)！");
        exit();
    }
    if ($jfnum_str > $jfnum) {
        echo("退款积分数量不能大于订单中积分的数量！");
        exit();
    }

    $orerid = $CUSERLOGIN->userID;
    $return_str = ReturnOrderBF($orderid, $jbnum_str,$jfnum_str,$info, $orerid);

    echo $return_str;
    exit();


}


?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg" style="min-width: 330px">


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal"  >
        <input type="hidden" name="dopost" value="save">

        <input type="hidden" name="orderid" id="orderid" value="<?php echo $orderid ?>"/>

        <div class="form-group">
            <label for="" class="col-sm-2 control-label">金币数量</label>
            <div class="col-sm-2">
                <?php

                $JB_disd=" disabled ";//默认不可以用,当金币大于0才可以使用
                if($maxjbnum>0)$JB_disd="";
                ?>
                <input class="form-control" id="jbnum_str" name="jbnum_str" type="number" value="0" <?php echo $JB_disd?>>
            </div>
            <div class="col-sm-2 form-control-static">
                <?php

                echo "可退金币数量:" . $maxjbnum;
                echo "=金币({$jbnum})+现金({$paynum})"

                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">积分数量</label>
            <div class="col-sm-2">
                <?php

                $JF_disd=" disabled ";//默认不可以用,当金币大于0才可以使用
                if($jfnum>0)$JF_disd="";
                ?>
                <input class="form-control" id="jfnum_str" name="jfnum_str" type="number" value="0" <?php echo $JF_disd;?>>
            </div>
            <div class="col-sm-2 form-control-static">
                订单金币数量:<?php echo $jbnum ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea class="form-control" name="info" cols="30" rows="5" id="info"></textarea>
            </div>
        </div>


        <div class="hr-line-dashed"></div>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 pull-left">
                退款说明：<span class="text-danger font-bold">(谨慎操作不可撤消)</span>
                <br>
                此退款,只涉及当前订单,不涉及返利金币和积分
                <br>
                金币数量(最多)=订单现金数量+订单金币数量
                <br>
                积分数量(最多)=订单的金币数量
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>



<?php

$jbjs_str=$jbjs_tip_str="";
if($maxjbnum>0){
    $jbjs_str=",max:$maxjbnum";
    $jbjs_tip_str=",max:\"不能大于$maxjbnum\"";
}
$jfjs_str=$jfjs_tip_str="";
if($jfnum>0){
    $jfjs_str=",max:$jfnum";
    $jfjs_tip_str=",max:\"不能大于$jfnum\"";
}
?>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);


    $(document).ready(function () {

        $("#form1").validate({
            rules: {
                jbnum_str: {number: true,  min: 0<?php echo $jbjs_str?>},
                jfnum_str: {number: true,  min: 0<?php echo $jfjs_str?>},
            },
            messages: {
                jbnum_str: {number: "请输入数字",  min: "不能小于0"<?php echo $jbjs_tip_str?>},
                jfnum_str: {number: "请输入数字",  min: "不能小于0"<?php echo $jfjs_tip_str?>},

            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "orderZtc_returnBF.php",
                    data: {
                        dopost: "save",
                        orderid: $("#orderid").val(),
                        jbnum_str: $("#jbnum_str").val(),
                        jfnum_str: $("#jfnum_str").val(),
                        info: $("#info").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "操作成功") {
                            layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                parent.location.reload();
                            });
                        } else {
                            layer.msg(result, {
                                time: 2000 //20s后自动关闭
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
