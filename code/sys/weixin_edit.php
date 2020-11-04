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


if ($dopost == 'getACCESS_TOKEN') {
    //dump($AppId);
    //dump($AppSecret);
    $ACCESS_TOKEN = Get_access_token($AppId, $AppSecret);
    if ($ACCESS_TOKEN != "" && $ACCESS_TOKEN != "false") {
        echo "true";
    } else {
        echo "false";
    }
    exit();
}
if ($dopost == 'save') {
    if ($TOKEN == "") {
        ShowMsg("请填写Token(令牌)", "-1");
        exit();
    }
    if ($url == "") {
        ShowMsg("请填写URL(服务器地址)", "-1");
        exit();
    }
    if ($AppId == "") {
        ShowMsg("请填写AppID(应用ID)", "-1");
        exit();
    }
    if ($AppSecret == "") {
        ShowMsg("请填写AppSecret(应用密钥)", "-1");
        exit();
    }

    //检测是否可以获取到$ACCESS_TOKEN  用于判断微信的参数是否正确
    $ACCESS_TOKEN = Get_access_token($AppId, $AppSecret);
    if ($ACCESS_TOKEN == "" || $ACCESS_TOKEN == "false") {
        ShowMsg("AppId与AppSecret不正确,或与微信通信出现错误", "-1");
        exit();
    }

    //检测公众号类型是否改变,如果改变则判断是否存在重复的内容
    if ($old_weixin_type != $weixin_type) {
        $questr = "SELECT weixin_type FROM `#@__interface_weixin` WHERE isdel=0 and id ='$id' and weixin_type ='$weixin_type'";
        $rowarc = $dsql->GetOne($questr);
        if (is_array($rowarc)) {
            ShowMsg("当前公司已经有\"$weixin_type\"的配置记录,发生了重复,请检查！", "-1");
            exit();
        }
    }


    $updateDate = time();

    $inQuery = "UPDATE `#@__interface_weixin` SET
                `url`='$url',
                `TOKEN`='$TOKEN',
                `AppId`='$AppId',
                `AppSecret`='$AppSecret',
                `ACCESS_TOKEN`='$ACCESS_TOKEN',
                `updateDate`='$updateDate',
                weixin_type ='$weixin_type'
                WHERE (`id`='$id')";
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
    $arcQuery = "SELECT *  FROM #@__interface_weixin  WHERE id='$id' ";
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


                    <form name='form2' id='form2' action='weixin_edit.php' method='post' class="form-horizontal">
                        <input type='hidden' name='dopost' value='save'>
                        <input name="id" type="hidden" id="id" value="<?php echo $arcRow['id'] ?>">


                        <?php
                        if ($GLOBAMOREDEP) {
                            echo "<div class='form-group'>
                        <label class='col-sm-2 control-label'>所属顶级部门:</label>
                        <div class='col-sm-2'>
                            <p  class='form-control-static'>" . GetDepsNameByDepId($arcRow['depid']) . "</p>
                        </div>
                </div>";
                        } ?>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">公众号类型:</label>

                            <div class="col-sm-10">
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" value="服务号" id="RadioGroup1_0" name="weixin_type" <?php if ($arcRow['weixin_type'] == "服务号") echo "checked" ?> >服务号</label>
                                <label class="checkbox-inline i-checks">
                                    <input type="radio" value="订阅号" id="RadioGroup1_1" name="weixin_type" <?php if ($arcRow['weixin_type'] == "订阅号") echo "checked" ?>>订阅号</label>
                                <input type="hidden" name="old_weixin_type" value="<?php echo $arcRow['weixin_type'] ?>">

                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">Token(令牌):</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="TOKEN" value="<?php echo $arcRow['TOKEN'] ?>">
                            </div>


                            <div class="col-sm-6 form-control-static">
                                <a href="http://mp.weixin.qq.com" target="_blank" class="set"> 登录微信公众平台</a> 在"开发-基本配置"中将Token(令牌)与此处的值设置一致。
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">URL(服务器地址):</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="url" value="<?php echo $arcRow['url'] ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">AppID(应用ID):</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="AppId" id="AppId" value="<?php echo $arcRow['AppId'] ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-2 control-label">AppSecret(应用密钥):</label>

                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="AppSecret" id="AppSecret" value="<?php echo $arcRow['AppSecret'] ?>">
                            </div>
                        </div>






                        <div class="form-group">
                            <label class="col-sm-2 control-label"><i class='glyphicon glyphicon-question-sign' aria-hidden='true' data-toggle='tooltip' data-placement='top' title='ACCESS_TOKEN值的有效期为2小时,必须在获取到ACCESS_TOKEN后在2小时内生成微信菜单,超过2小时需重新获取(当前页面保存时,会自动更新此值)'></i>
                                ACCESS_TOKEN:</label>

                            <div class="col-sm-4">
                                <?php
                                $ACCESS_TOKEN = $arcRow['ACCESS_TOKEN'];
                                if ($arcRow['ACCESS_TOKEN'] == "") $ACCESS_TOKEN = "111";
                                echo $ACCESS_TOKEN;
                                ?>
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
</script>
<script>
    $().ready(function () {
        $("#form2").validate({
            rules: {
                TOKEN: {required: !0},
                url: {required: !0, url: !0},
                AppId: {required: !0},
                AppSecret: {
                    required: !0,
                    remote: {//通过与微信通信,获取ACCESS_TOKEN,如果获取到ACCESS_TOKEN则表示参数正确
                        type: "post",
                        url: "weixin_edit.php?dopost=getACCESS_TOKEN",
                        data: {
                            AppId: function () {
                                return $("#AppId").val();
                            }, AppSecret: function () {
                                return $("#AppSecret").val();
                            }
                        },
                        dataType: "html",
                        dataFilter: function (data, type) {
                            if (data == "true")
                                return true;
                            else
                                return false;
                        }
                    }
                }
            },
            messages: {
                TOKEN: {required: "请填写Token(令牌)"},
                url: {required: "请填写URL(服务器地址)", url: "网址格式不正确,应为:http://www.163.com"},
                AppId: {required: "AppID(应用ID)"},
                AppSecret: {required: "请填写AppSecret(应用密钥)",remote: "AppId与AppSecret不正确,或与微信通信出现错误"},
            }
        })
    });
</script>

</body>
</html>