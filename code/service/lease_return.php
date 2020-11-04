<?php
require_once("../config.php");
if (empty($dopost)) $dopost = '';
/*--------------------------------
 function __save(){  }
 -------------------------------*/
if ($dopost == 'save') {

    $oper_id = $CUSERLOGIN->userID;

    //更新
    $completetime = time();
    $sql = "UPDATE `#@__order_addon_car` SET `state`=2,`return_infodate`='$completetime', `return_info`='$return_info',return_infooperatorid='$oper_id' WHERE id='$id' ";
    if (!$dsql->ExecuteNoneQuery($sql)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

    ShowMsg("操作成功！", $$ENV_GOBACK_URL);
    exit();
}


if ($dopost == '') {



}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form id="form1" name="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="dopost" value="save">


        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea name="return_info" id="return_info" class="form-control" placeholder="请填写内容" rows="5"></textarea>
            </div>
        </div>


        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {
        $("#form1").validate({
            rules: {
                return_info: {required: !0}
            },
            messages: {
                return_info: {required: "请填写内容"}
            }
        })
    });
</script>

</body>
</html>



