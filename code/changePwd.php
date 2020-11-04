<?php
/**
 * 修改密码
 *
 * @version        $Id: changPwd.php 151008
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('config.php');
if (empty($dopost)) $dopost = '';
$id = preg_replace("#[^0-9]#", '', $GLOBALS['CUSERLOGIN']->getUserId());
if ($dopost == 'check') {
    $oldpwd = substr(md5($oldpwd), 5, 20);
    $chRow = $dsql->GetOne("SELECT id FROM `#@__sys_admin`  WHERE  id='$id' and pwd='$oldpwd' ");
//dump($chRow);
    if(is_array($chRow)){
        echo "true";
    }else {
        echo "false";
    }
    exit;
}
if ($dopost == 'saveedit') {
    $newpwd = trim($newpwd);
    if ($newpwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $newpwd)) {
        ShowMsg('密码不合法，请使用[0-9a-zA-Z_@!.-]内的字符！', '-1', 0, 3000);
        exit();
    }
    $pwdm = '';
    if ($newpwd != '') {
        //$pwdm = " pwd='".md5($newpwd)."'";
        $newpwd = " pwd='" . substr(md5($newpwd), 5, 20) . "'";
    }
    $query = "UPDATE `#@__sys_admin` SET $newpwd WHERE id='$id'";
    // dump($query);
    $dsql->ExecuteNoneQuery($query);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui/css/style.min.css" rel="stylesheet">
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight"  style="background-color: #ffffff">
    <form class="form-horizontal m-t" id="form1">
        <input type="hidden" name="dopost" value="saveedit"/>
        <div class="form-group">
            <div class="col-sm-2">
                <input type="text" class="form-control" value="<?php echo $CUSERLOGIN->getUserName() ?>" disabled>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                <input type="text" name="oldpwd" onfocus="this.type='password'" id="oldpwd" autocomplete="off" class="form-control" placeholder="请填写旧密码">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                <input type="text" name="newpwd" onfocus="this.type='password'" id="newpwd" autocomplete="off" class="form-control" placeholder="请填写新密码">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                <input type="text" name="newpwd1" onfocus="this.type='password'" id="newpwd1" autocomplete="off" class="form-control" placeholder="请再次确认新密码">
            </div>
        </div>
        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>

</div>

<script src="ui/js/jquery.min.js"></script>
<script src="ui/js/bootstrap.min.js"></script>
<script type="text/javascript" src="ui/js/content.min.js"></script>
<script src="ui/js/plugins/layer/layer.min.js"></script>
<!--右下角自动隐藏提示框 显示提示-->
<script src="ui/js/plugins/toastr/toastr.min.js"></script>
<link href="ui/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <!--验证用-->
<script src="ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {
        $("#form1").validate({
            rules: {
                oldpwd: {
                    required: !0,
                    remote: {//校验旧密码是否正确
                        type: "post",
                        url: "changePwd.php?dopost=check",
                        data: {
                            oldpwd: function () {
                                return $("#oldpwd").val();
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
                },
                newpwd: {required: !0, minlength: 6, notEqual: "#oldpwd"},
                newpwd1: {required: !0, minlength: 6, equalTo: "#newpwd"}
            },
            messages: {
                oldpwd: {required: "请填写旧密码", remote: "旧密码错误"},
                newpwd: {required: "请填写新密码", minlength: "密码必须6个字符以上", notEqual: "不能与旧密码相同"},
                newpwd1: {required: "请再次确认新密码", minlength: "密码必须6个字符以上", equalTo: "两次填写的密码不一致"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "changePwd.php",
                    data: "dopost=saveedit&newpwd=" + $("#newpwd").val(),
                    dataType: 'html',
                    success: function (result) {
                        parent.display_tips("操作成功.");
                        parent.layer.closeAll('iframe');
                    }
                });
            }
        })
    });
</script>


</body>
</html>


