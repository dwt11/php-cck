<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
if (empty($dopost)) $dopost = '';

/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == "save") {
    $s_userip = GetIP();
    $dtime = time();
    $in_query = "INSERT INTO `#@__feedback` (`clientid`, `filename`, `body`, `cip`, `dtime`, `completeTime`, `completeBody`)
                  VALUES ('$CLIENTID', '', '$body', '$s_userip', '$dtime', '0', '')";
    $dsql->ExecuteNoneQuery($in_query);
    //showMsg("添加成功", "feedback.php");
    exit;

}//End dopost==save
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>添加反馈</title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="main">
    <?php include("../index_heard.php"); ?>
    <div class="widget1   text-center">
        <div class="row">
            <div class="col-xs-6 text-left lefttext">
                添加建议
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
        <div class="ibox-content icons-box">
            <form id="form1" class="form-horizontal m-t">
                <input type="hidden" name="dopost" value="save">
                <div class="form-group">
                    <div class="text-center">
                        <textarea name="body" id="body" class="form-control" placeholder="请填写您在使用中遇到的问题,我们将为您不断改进" rows="5"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="text-center">
                        <button class="btn btn-primary" type="submit">保存内容</button>
                        <br>
                        <span class=" text-warning">
                        请勿发布不相关内容。
                        <br>三次发布不相关内容，账户将被封禁！
                    </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../../ui/js/jquery.min.js"></script>
<script src="../../ui/js/bootstrap.min.js"></script>
<!--验证用-->
<script src="../../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../../ui/js/plugins/layer/layer.min.js"></script>
<script>
    $().ready(function () {
        $("#form1").validate({
            rules: {
                body: {required: !0}
            },
            messages: {
                body: {required: "请填写建议内容"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "feedback_add.php",
                    //data: "dopost=save&body="+$("#body").val(),
                    data: {dopost: "save", body: $("#body").val()},
                    dataType: 'html',
                    success: function (result) {
                        layer.msg('保存成功', {
                            shade: 0.5, //开启遮罩
                            time: 1000 //20s后自动关闭
                        }, function () {
                            window.location.href = 'feedback.php';
                        });

                    }
                });
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg("系统错误,请重试", {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    window.location.href = 'feedback_add.php';
                });
            }
        })
    });
</script>
</body>
</html>
