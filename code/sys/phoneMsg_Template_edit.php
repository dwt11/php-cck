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
if (empty($dopost)) $dopost = '';


if (!isset($id)) {
    ShowMsg("参数出错!", "-1");
    exit();
}


if ($dopost == 'save') {

    if ($signName  == "") {
        ShowMsg("请填写短信签名", "-1");
        exit();
    }

    if ($templateCode == "") {
        ShowMsg("请填写短信模板编号", "-1");
        exit();
    }

    if ($templateBody  == "") {
        ShowMsg("请填写短信模板", "-1");
        exit();
    }


    $inQuery = "UPDATE `#@__interface_phonemsg_template` SET    `signName`='$signName' , `templateCode`='$templateCode' , `templateBody`='$templateBody'  WHERE (`id`='$id')";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}

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

<body class="gray-bg" >
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name='form2' id='form2' action='' method='post' class="form-horizontal" target="_parent">
        <input type='hidden' name='dopost' value='save'>
        <input name="id" type="hidden" id="id" value="<?php echo $arcRow['id'] ?>">


        <div class="form-group">
            <label class="col-sm-2 control-label">模板名称:</label>

            <div class="col-sm-4 form-control-static">
               <?php echo $arcRow['name'] ?>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信签名:</label>

            <div class="col-sm-4">
                <input type="text" class="form-control" name="signName" id="signName" value="<?php echo $arcRow['signName'] ?>">
            </div>
        </div>

  

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信模板编号:</label>

            <div class="col-sm-4">
                <input type="text" class="form-control" name="templateCode"  id="templateCode" value="<?php echo $arcRow['templateCode'] ?>">
            </div>
        </div>
        <?php
        require_once("phoneMsg_Template_smsParam.do.php");
        $json_sms=(json_encode($smsParam));
        ?>
        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板必含参数:</label>

            <div class="col-sm-4">
                <span id="smsParam"><?php echo $smsParam[$arcRow['name']]?></span>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信模板:</label>

            <div class="col-sm-4">
                              <textarea name="templateBody" id="templateBody" class="form-control" placeholder="发送模板内容和参数示例，与《阿里大于》申请的一致" rows="5"><?php echo $arcRow['templateBody'] ?></textarea>
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
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);


    $().ready(function () {
        $("#form2").validate({
            rules: {
                signName: {required: !0},
                templateCode: {required: !0},
                templateBody: {required: !0}
            },
            messages: {
                signName: {required: "请填写短信签名"},
                templateCode: {required: "请填写短信模板编号"},
                templateBody: {required: "请填写短信模板"}
            }
        })
    });
</script>

</body>
</html>