<?php
/**
 * 短信参数编辑
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


    //检测公众号类型是否改变,如果改变则判断是否存在重复的内容

    $questr = "SELECT * FROM `#@__interface_phoneMsg` WHERE isdel=0 and depid ='$depid' ";
    $rowarc = $dsql->GetOne($questr);
    if (is_array($rowarc)) {
        ShowMsg("当前公司已经有短信接口的配置记录,发生了重复,请检查！", "-1");
        exit();
    }


    $sendDate = $updateDate = time();

    $inQuery = "INSERT INTO `#@__interface_phoneMsg` (`depid`, `Appkey`,  `AppSecret`, `sendDate`, `updateDate`, `isdel`)
                                            VALUES ('$depid', '$Appkey', '$AppSecret', '$sendDate', '$updateDate','0')";
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
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
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


                        <div class="form-group">
                            <label class="col-sm-2 control-label">公司名称:</label>

                            <div class="col-sm-2">
                                <?php
                                if ($GLOBALS['CUSERLOGIN']->getUserType() != 10 && $GLOBAMOREDEP) {
                                    echo "<input type='hidden' value='" . $GLOBALS['NOWLOGINUSERTOPDEPID'] . "' id='depid' name='depid'> ";
                                    echo "<div class='form-control-static'>".GetDepsNameByDepId($GLOBALS['NOWLOGINUSERTOPDEPID'])."</div>";
                                } else {
                                    $depOptions = GetDepOnlyTopOptionList();
                                    echo "<select name='depid' id='depid'  class='form-control m-b'  >\r\n";
                                    echo "<option value='0'>请选择公司...</option>\r\n";
                                    echo $depOptions;
                                    echo "</select>";
                                }
                                ?>
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="col-sm-2 control-label">Appkey:</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="Appkey" id="Appkey" value="">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">AppSecret(密钥):</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="AppSecret" id="AppSecret" value="">
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
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>


<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->


<script>
    $(document).ready(function () {
        $(".i-checks").iCheck({checkboxClass: "icheckbox_square-green", radioClass: "iradio_square-green",})
    });
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