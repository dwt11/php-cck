<?php
/**
 * 微信参数编辑
 *
 * @version        $Id: sysGroup_edit.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */

require_once("../config.php");
//读取归档信息
$arcQuery = "SELECT *  FROM #@__interface_phonemsg_template  WHERE id='$id' ";
//dump($arcQuery);
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
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name='form2' id='form2' action='' method='post' class="form-horizontal" target="_parent">
        <input type='hidden' name='dopost' value='save'>
        <input name="id" type="hidden" id="id" value="<?php echo $arcRow['id'] ?>">
        只测试短信通道和模板，不发送参数内容
        <div class="form-group">
            <label class="col-sm-2 control-label">模板名称:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $arcRow['name'] ?>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信签名:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $arcRow['signName'] ?>
            </div>
        </div>


        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信模板编号:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $arcRow['templateCode'] ?>
            </div>
        </div>
        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信模板:</label>

            <div class="col-sm-4">
                <?php echo $arcRow['templateBody'] ?>
            </div>
        </div>


        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">手机号码:</label>

            <div class="col-sm-4">
                <input type="text" class="form-control" name="mobilephone" id="mobilephone">
            </div>
        </div>


        <div class="form-group">
            <div class="text-center">
                <button class="btn  btn-primary" type="button" onclick="settime(this,0,'<?php echo urlencode($arcRow['name']) ?>',<?php echo urlencode($arcRow['depid']) ?>)">发送</button>
            </div>
        </div>


    </form>

</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
</script>
<script src="phoneMSG_test.js"></script>
</body>
</html>