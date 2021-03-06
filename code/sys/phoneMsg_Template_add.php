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

/*--------------------------------
 function __save(){  }
 -------------------------------*/
if (!isset($depid)) {
    ShowMsg("参数出错!", "-1");
    exit();
}
if ($dopost == 'save') {
    if ($name == "") {
        ShowMsg("请填写模板名称", "-1");
        exit();
    }

    if ($signName == "") {
        ShowMsg("请填写短信签名", "-1");
        exit();
    }

    if ($templateCode == "") {
        ShowMsg("请填写短信模板编号", "-1");
        exit();
    }
    if ($templateBody == "") {
        ShowMsg("请填写短信模板", "-1");
        exit();
    }


    $questr = "SELECT id FROM `#@__interface_phonemsg_template` WHERE `name`='$name' and depid ='$depid' ";
    $rowarc = $dsql->GetOne($questr);
    //dump ($rowarc);
    if (is_array($rowarc)) {
        ShowMsg("已经有此模板,发生了重复,请检查！", "-1");
        exit();
    }


    $inQuery = "INSERT INTO `#@__interface_phonemsg_template` (`depid`, `name`, `signName`,  `templateCode`, `templateBody`)
                                                    VALUES ('$depid', '$name', '$signName',  '$templateCode', '$templateBody')";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("添加数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("添加信息成功！", $$ENV_GOBACK_URL);
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
        <input type='hidden' name='depid' value='<?php echo $depid ?>'>


        <div class="form-group">
            <label class="col-sm-2 control-label">模板名称:</label>

            <div class="col-sm-4">
                <select name="name" id="name" class="form-control">
                    <option value="">请选择</option>
                    <option value="注册验证码">注册验证码</option>
                    <option value="购买成功">购买成功</option>
                    <option value="朋友购买成功">朋友购买成功</option>
                    <option value="业务验证码">业务验证码</option>
                    <option value="旅游订单预订成功通知">旅游订单预订成功通知</option>
                </select>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信签名:</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="signName" id="signName" placeholder="必须同<阿里大于>中申请的一致">
            </div>
        </div>


        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信模板编号:</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="templateCode"  id="templateCode" placeholder="必须同<阿里大于>中申请的一致">
            </div>
        </div>
        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板必含参数:</label>

            <div class="col-sm-4">
                <span id="smsParam"></span>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">短信模板:</label>

            <div class="col-sm-4">
                <textarea name="templateBody" id="templateBody" class="form-control" placeholder="发送模板内容和参数示例，与《阿里大于》申请的一致" rows="5"></textarea>
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



<?php require_once("phoneMsg_Template_smsParam.do.php");
$json_sms=(json_encode($smsParam));
?>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $().ready(function () {
        //短信模板参数
        var smsParam = new Array();
        smsParam = eval(<?php echo $json_sms?>);
        //console.log(smsParam);
        $("#name").change(function () {
            var name = $("#name").val();
            if (name != '') {
                var info = smsParam[name];
                $("#smsParam").html(info);
            }
        });

        $("#form2").validate({
            rules: {
                name: {required: !0},
                signName: {required: !0},
                templateCode: {required: !0},
                templateBody: {required: !0}
            },
            messages: {
                name: {required: "请填写模板名称"},
                signName: {required: "请填写短信签名"},
                templateCode: {required: "请填写短信模板编号"},
                templateBody: {required: "请填写短信模板"}
            }
        });
    });
</script>

</body>
</html>