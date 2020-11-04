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


if ($dopost == 'save') {

    if ($Appkey == "") {
        ShowMsg("请填写Appkey", "-1");
        exit();
    }
    if ($AppSecret == "") {
        ShowMsg("请填写AppSecret(密钥)", "-1");
        exit();
    }


    $updateDate = time();

    $inQuery = "UPDATE `#@__interface_phoneMsg` SET
                `Appkey`='$Appkey',
                `AppSecret`='$AppSecret',
                `updateDate`='$updateDate'
                WHERE (`depid`='$depid')";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }

    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}

if ($dopost == '') {
    //读取归档信息
    $arcQuery = "SELECT *  FROM #@__interface_phoneMsg  WHERE depid='$depid' ";
    //dump($arcQuery);
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        ShowMsg("读取信息出错!", "-1");
        exit();
    }

}
?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">


</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?>
                        <small></small>
                    </h5>
                </div>
                <div class="ibox-content">


                    <form name='form2' id='form2' action='' method='post' class="form-horizontal">
                        <input type='hidden' name='dopost' value='save'>
                        <input name="id" type="hidden" id="depid" value="<?php echo $depid ?>">


                        <?php
                        if ($GLOBAMOREDEP) {
                            echo "<div class='form-group'>
                                            <label class='col-sm-2 control-label'>公司名称:</label>
                                            <div class='col-sm-2'>
                                                <p  class='form-control-static'>" . GetDepsNameByDepId($arcRow['depid']) . "</p>
                                            </div>
                                    </div>";
                        } ?>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">Appkey:</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="Appkey" id="Appkey" value="<?php echo $arcRow['Appkey'] ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">AppSecret(密钥):</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="AppSecret" id="AppSecret" value="<?php echo $arcRow['AppSecret'] ?>">
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary" type="submit">保存内容</button>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>


<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->

<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
</script>
<script>
    $().ready(function () {
        $("#form2").validate({
            rules: {
                depid: {isIntGtZero: !0},

                Appkey: {required: !0},
                AppSecret: {required: !0}
            },
            messages: {
                depid: {isIntGtZero: "请选择公司"},
                Appkey: {required: "请填写Appkey"},
                AppSecret: {required: "请填写AppSecret(密钥)"}
            }
        })
    });
</script>

</body>
</html>