<?php
/**
 * 积分撤消
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';
if (empty($info)) $info = '';


//读取归档信息
$arcQuery = "SELECT clientid,jfnum,id,createtime  FROM #@__clientdata_jflog  WHERE id='$id' ";
$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}

$clientid = $arcRow['clientid'];
$jfnum100 = $arcRow['jfnum'];
$jfnum_str = $jfnum100/100;
$id = $arcRow['id'];
$createtime = GetDateTimeMk($arcRow['createtime']);

$arcQuery1 = "SELECT realname FROM #@__client  WHERE id='$clientid' ";
$arcRow1 = $dsql->GetOne($arcQuery1);
$realname = $arcRow1['realname'];

$arcQuery2 = "SELECT jfnum FROM #@__client_addon  WHERE clientid='$clientid' ";
$arcRow2 = $dsql->GetOne($arcQuery2);
$jf_ye = $arcRow2['jfnum']/100;


$info .= " " . " 原时间: $createtime 原编号$id";
/*--------------------------------
function __save(){   }
-------------------------------*/
if ($dopost == 'save') {

    if ($clientid == '') {
        echo "充值失败,会员信息错误！";
        exit;
    } else {

        $jfnum100 = -$jfnum100;
        $istrue = Update_jf($clientid, "$jfnum100", "操作错误积分撤消", 0, $CUSERLOGIN->userID, "$info");
        if ($istrue) {
            echo "操作成功";
        } else {
            echo "操作失败，请检查会员的积分数量";
        }
        exit;
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
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">

    <form name="form1" id="form1" action="" method="post" class="form-horizontal">

        <input type="hidden" name="dopost" value="save"/>
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>


        <div class="form-group">
            <label for="" class="col-sm-2 control-label">会员名称</label>

            <div class="col-sm-2">
                <span class="realname"><?php echo $realname ?></span>
            </div>
        </div>

        <div class="form-group">
            <label for="" class="col-sm-2 control-label">积分余额</label>

            <div class="col-sm-2">
                <span class="jfnum"><?php echo($jf_ye) ?></span>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">操作积分数量</label>

            <div class="col-sm-2">
                <span class="jfnum"><?php echo -($jfnum_str) ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea class="form-control" name="info" cols="30" rows="5" id="info"></textarea>
            </div>
        </div>


        <div class="form-group">

            <div class="col-sm-4 col-sm-offset-2 text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>

    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script src="../ui/js/plugins/validate/messages_zh.min.js"></script>
<script>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {

        $("#form1").validate({
            rules: {},
            messages: {},
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "jf_rest.php",
                    data: {
                        dopost: "save",
                        id: $("#id").val(),
                        info: $("#info").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "操作成功") {
                            layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                parent.location.href = "jf.php";
                            });
                        } else {
                            layer.msg(result, {
                                time: 2000 //20s后自动关闭
                            });
                        }
                        //parent.layer.closeAll();
                    }
                });
            }
        });
    });
</script>
</body>
</html>