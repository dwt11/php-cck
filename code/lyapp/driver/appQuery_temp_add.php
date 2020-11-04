<?php
/**
 * 添加系统管理员
 *
 * @version        $Id: sys_user_add.php 1 16:22 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once(dirname(__FILE__) . "/../include/config.php");

if (!isset($goodsid)||!isset($lineid)||!isset($appttime)||!isset($deviceid)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}


if (empty($dopost)) $dopost = '';

if ($dopost == 'save') {

    $isscry=false;//是否司乘人员
    $query = "SELECT COUNT(emp_id)AS dd,emp_id  FROM #@__emp_client WHERE clientid='$CLIENTID'   ";
    $rowscry = $dsql->GetOne($query);
//没有信息就不显示 161101
    if (isset($rowscry["dd"]) && $rowscry["dd"]>0) {
        $isscry = true;
        $emp_id = $rowscry["emp_id"];
    }

    if(!DEBUG_LEVEL&&!$isscry){
        echo("<font size='32px'>无权检票</font>");
        exit;

    }


    $createtime=time();

    $money100=$money*100;
    $inquery = "INSERT INTO `#@__lycp_temp_money`( `goodsid`, `lineid`, `appttime`, `info`, `emp_id`, `deviceid`, `seatNumber`, `realname`, `tel`, `idcard`, `money`,isdel, `createtime`)
                                                    VALUES('$goodsid', '$lineid', '$appttime', '$info', '$emp_id', '$deviceid', '', '$realname', '$tel', '', '$money100', '0','$createtime'); ";
//dump($inquery);
    $rs = $dsql->ExecuteNoneQuery($inquery);

    echo "操作成功";
    exit();
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $sysFunTitle ?></title>
    <link href="/ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="/ui/css/font-awesome.min.css" rel="stylesheet">
    <link href="/ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/ui/css/animate.min.css" rel="stylesheet">
    <link href="/ui/css/style.min.css" rel="stylesheet">


</head>
<body class="gray-bg" >


<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="dopost" value="save">

        <div class="form-group">
            <label class="col-sm-2 control-label">姓名:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="realname" id="realname">
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">电话:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="tel"  id="tel"  >
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">费用:</label>

            <div class="col-sm-2">
                <input type="number" step="0.1" class="form-control" name="money"  id="money"  value="0" >
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <input type="text" class="form-control" name="info"  id="info" >
            </div>
        </div>


        <div class="hr-line-dashed"></div>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 text-center">
                <button class="btn btn-primary" type="submit">保存内容</button>
            </div>
        </div>
    </form>
</div>


<script src="/ui/js/jquery.min.js"></script>
<script src="/ui/js/bootstrap.min.js"></script>
<script src="/ui/js/content.min.js"></script>
<script src="/ui/js/plugins/layer/layer.min.js"></script>

<!--验证用-->
<script src="/ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script language='javascript'>
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);

    $().ready(function () {
        $("#form1").validate({
            rules: {
                realname: {required: !0}
            },
            messages: {
                realname: {required: "请填写姓名"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "appQuery_temp_add.php",
                    data: {
                        dopost: "save",
                        realname: $("#realname").val(),
                        tel: $("#tel").val(),
                        goodsid: <?echo $goodsid?>,
                        lineid: <?echo $lineid?>,
                        appttime: <?echo $appttime?>,
                        deviceid: <?echo $deviceid?>,
                        money: $("#money").val(),
                        info: $("#info").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "操作成功") {
                            parent.location.reload();
                           /* layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                parent.location.reload();
                            });*/
                        } else {
                            layer.msg(result, {
                                time: 2000 //20s后自动关闭
                            });
                        }
                    }
                });
            }
        })
    });


</script>
</body>
</html>





