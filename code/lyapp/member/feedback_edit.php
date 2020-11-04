<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
if (empty($dopost)) $dopost = '';
if (empty($id)) {
    showMsg("非法参数", "feedback.php");
    exit;
}

/*---------------------
 function action_save(){ }
 ---------------------*/
if ($dopost == "save") {
    $s_userip = GetIP();
    $dtime = time();
    $in_query = "UPDATE  `#@__feedback` SET   `body`='$body', `cip`='$s_userip', `dtime`='$dtime'  WHERE (`id`='$id' and clientid='$CLIENTID');";
   // dump($in_query);
    $dsql->ExecuteNoneQuery($in_query);
    //showMsg("编辑成功", "feedback.php");
    exit;
}//End dopost==save
$query = "SELECT * FROM `#@__feedback`  WHERE  id='$id' and clientid='$CLIENTID'";
$row = $dsql->GetOne($query);
if (!is_array($row)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title>编辑反馈</title>
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
                修改建议
            </div>
            <div class="col-xs-6 text-right">

            </div>
        </div>
    </div>
    <div class="ibox float-e-margins">
    <div class="ibox-content icons-box">
        <form id="form1" class="form-horizontal m-t">
            <input type="hidden" name="dopost" value="save">
            <input type="hidden" name="id"  id="id" value="<?echo $id;?>">
            <div class="form-group">
                <div class="text-center">
                    <textarea name="body" id="body" class="form-control"  rows="5"><?php echo $row["body"]?></textarea>
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
                    url: "feedback_edit.php",
                    //data: "dopost=save&body="+$("#body").val(),
                    data: {dopost:"save",body:$("#body").val(),id:$("#id").val()} ,
                    dataType: 'html',
                    success: function(result)
                    {
                        layer.msg('保存成功', {
                            time: 1000, //20s后自动关闭
                        }, function () {
                                window.location.href = 'feedback.php';
                            });                      
			    
                    }
                });
            }
        })
    });
</script>
</body>
</html>
