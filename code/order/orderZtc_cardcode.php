<?php
/**
 * 添加系统管理员
 *
 * @version        $Id: sys_user_add.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
if (!isset($id)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}


if (empty($dopost)) $dopost = '';

if ($dopost == 'save') {
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    if ($cardcode != $cardcode_old) {


        $checkCardCode=ValidateZtcCardCodeISon($cardcode,$id);
        if($checkCardCode!="可以使用"){
            //判断输入的卡号是否是数字,并且不重复
            ShowMsg($checkCardCode, $$ENV_GOBACK_URL);
            exit();
        }

        $query = "UPDATE #@__order_addon_ztc SET cardcode='$cardcode'    WHERE orderid='$id'; ";
        if (!$dsql->ExecuteNoneQuery($query)) {
            ShowMsg('更新数据表时出错，请检查', $$ENV_GOBACK_URL);
            exit();
        }
    }
    if ($cardcode == "") {
        $query = "UPDATE #@__order_addon_ztc SET cardcode=''    WHERE orderid='$id'; ";
        if (!$dsql->ExecuteNoneQuery($query)) {
            ShowMsg('更新数据表时出错，请检查', $$ENV_GOBACK_URL);
            exit();
        }
    }


     ShowMsg('成功保存！', $$ENV_GOBACK_URL);
    exit();
}
//读取归档信息
$arcQuery = "SELECT ztc.cardcode  FROM #@__order
 LEFT JOIN #@__order_addon_ztc  ztc on ztc.orderid=#@__order.id
 WHERE #@__order.id='$id' and #@__order.isdel=0 ";

$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    ShowMsg("读取信息出错!", "-1");
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
<body class="gray-bg" style="min-width: 300px">


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save">

        <div class="form-group">
            <label class="col-sm-2 control-label">实体卡卡号:</label>

            <div class="col-sm-2">
                <input type="hidden" class="form-control" name="id" value="<?php echo $id; ?>">
                <input type="text" class="form-control" name="cardcode" value="<?php echo $arcRow["cardcode"]; ?>">
                <input type="hidden" class="form-control" name="cardcode_old" value="<?php echo $arcRow["cardcode"]; ?>">
            </div>
        </div>


        <div class="hr-line-dashed"></div>
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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
</script>
<script language='javascript'>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $().ready(function () {
        $("#form1").validate({
            rules: {
                //cardcode: {required: !0}
            },
            messages: {
                //cardcode: {required: "请填写实体卡号"}
            }
        })
    });
</script>
</body>
</html>





