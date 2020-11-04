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

    if ($template_id == "") {
        ShowMsg("请填写模板ID", "-1");
        exit();
    }

    if ($templateBody == "") {
        ShowMsg("请填写模板内容", "-1");
        exit();
    }

    if ($url == "") {
        ShowMsg("请填写连接地址", "-1");
        exit();
    }

    $topcolor="#000000";

    $inQuery = "UPDATE `#@__interface_weixinmsg_template` SET    `template_id`='$template_id' , `url`='$url' , `topcolor`='$topcolor' , `templateBody`='$templateBody'  WHERE (`id`='$id')";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}

//读取归档信息
$arcQuery = "SELECT *  FROM #@__interface_weixinmsg_template  WHERE id='$id' ";
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


        <div class="form-group">
            <label class="col-sm-2 control-label">模板名称:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $arcRow['name'] ?>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板ID:</label>

            <div class="col-sm-4">
                <input type="text" class="form-control" name="template_id" id="template_id" value="<?php echo $arcRow['template_id'] ?>">
            </div>
        </div>


        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">连接:</label>

            <div class="col-sm-4">
                <input type="text" class="form-control" name="url" id="url" value="<?php echo $arcRow['url'] ?>">
                只可以是站内连接,如/lyapp/order_show_url.php?orderid={orderid}
            </div>
        </div>
        <?php
        require_once("weixin_Msg_Template_param.do.php");
        $json_sms = (json_encode($smsParam));
        ?>
        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板必含参数:</label>

            <div class="col-sm-4">
                <span id="smsParam"><?php echo $smsParam[$arcRow['name']] ?></span>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板内容:</label>

            <div class="col-sm-4">
                <textarea
                        name="templateBody"
                        id="templateBody"
                        class="form-control"
                        placeholder="发送模板内容和参数示例，与《公众平台》申请的一致"
                        rows="8"><?php echo $arcRow['templateBody'] ?></textarea>
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
                template_id: {required: !0},
                url: {required: !0},
                templateBody: {required: !0}
            },
            messages: {
                template_id: {required: "请填写模板ID"},
                url: {required: "请填写连接地址"},
                templateBody: {required: "请填写模板内容"}
            }
        })
    });
</script>

</body>
</html>