<?php
/**
 * 编辑系统管理员
 *
 * @version        $Id: sys_user_edit.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

require_once("../sys/sysGroup.class.php");

$dopost = isset($dopost) ? $dopost : "";



if ($dopost == 'save') {
    $searchrx = isset($searchrx) ? trim($searchrx) : $searchrx = "1";
    $menurx = isset($menurx) ? trim($menurx) : $menurx = "1";
    $updateDate = time();
    $guanzhu_return=preg_replace("/\r/",'',$guanzhu_return);//清除掉/R  否则微信显示的消息 会多一个空行
    $inQuery = "UPDATE `#@__interface_weixin` SET
                `msg`='$msg',
                `searchrx`='$searchrx',
                `menurx`='$menurx',
                `updateDate`='$updateDate',
                guanzhu_return ='$guanzhu_return'
                WHERE (`id`='$id')";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("更新数据时出错，请检查原因！", "-1");
        exit();
    }




    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("修改信息成功！", $$ENV_GOBACK_URL);
    exit();
}
//读取归档信息
$arcQuery = "SELECT *  FROM #@__interface_weixin  WHERE id='$id' ";
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
    <title><?php echo $sysFunTitle ?></title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">


</head>
<body class="gray-bg" style="min-width: 500px">


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" value="<?php echo $id ?>"/>

        <div class="form-group">
            <label class="col-sm-2 control-label">
                智能回复数量：
            </label>
            <div class="col-sm-2">
                <input type="number" max="10" class="form-control" name="searchrx" readonly value="<?php echo $arcRow['searchrx'] ?>">
            </div>
            <div class="col-sm-4 form-control-static">
                最多10条,用户在微信发送内容时,系统自动从商品或文章中（模糊匹配）找出相关内容，并以图文方式回复给用户
            </div>
        </div>




        <div class="form-group">
            <label class="col-sm-2 control-label">关注时自动回复内容:</label>

            <div class="col-sm-4">
               <!-- <script type='text/javascript'>
                    var dirname_plus='';   //150901添加 fun_name  emp/archives从页面中传递过来的功能文件夹名称  用于区分 附件的保存地址
                </script>
                <script id="desc" name="desc" type="text/plain"  ></script>
                <script type='text/javascript' src='/include/ueditor/ueditor.config_simple.js'></script>
                <script type='text/javascript' src='/include/ueditor/ueditor.all.js'></script>
                <script type='text/javascript' src='/include/ueditor/lang/zh-cn/zh-cn.js'></script>
                <script type='text/javascript'>  ue=UE.getEditor('desc');</script>-->
                <textarea class="form-control" name="guanzhu_return" cols="30" rows="5" id="guanzhu_return"><?php echo $arcRow['guanzhu_return'] ?></textarea>


            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">无匹配时的回复:</label>

            <div class="col-sm-4">
                <input type="text" class="form-control" name="msg" value="<?php echo $arcRow['msg'] ?>">
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


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/iCheck/icheck.min.js"></script>

<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script language='javascript'>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $().ready(function () {
        $("#form1").validate({
            rules: {
                userName: {required: !0, minlength: 4},
                pwd: {minlength: 6},
                pwd1: {minlength: 6, equalTo: "#pwd"},
                safecode: {required: !0}
            },
            messages: {
                userName: {required: "请填写用户登录名", minlength: "用户登录名必须大于3个字符"},
                pwd: {minlength: "密码必须5个字符以上"},
                pwd1: {minlength: "密码必须5个字符以上", equalTo: "两次填写的密码不一致"},
                safecode: {required: "请填写安全验证串"}
            }
        })
    });


</script>
</body>
</html>

