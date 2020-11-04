<?php
/**
 *
 * @version        $Id: order_add.php 1 8:26 2010年7月12日
 * @package
 * @license
 * @link
 */
require_once("../config.php");
if (empty($dopost)) $dopost = '';

/*--------------------------------
function __save(){   }
-------------------------------*/
if ($dopost == 'save') {

    if (empty($clientid)) $clientid = '';

    if($jbnum>=0){
        echo "扣除失败,数量必须是负数！";
        exit;
    }
    if ($clientid == '') {
        echo "扣除失败,请选择会员！";
        exit;
    } else {
        $arcQuery = "SELECT clientid FROM #@__client_addon  WHERE clientid='$clientid' ";

        $arcRow = $dsql->GetOne($arcQuery);
        $jbnum100 = $jbnum * 100;
        $istrue = Update_jb($clientid, "{$jbnum100}", "管理员手工充值", 0, $CUSERLOGIN->userID, "$info");
        if ($istrue) {
            echo "操作成功";
        } else {
            echo "操作失败，请检查会员的金币数量";
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>金币扣除</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">

                <!--标题栏和 添加按钮            开始-->
                <div class="ibox-title">
                    <h5><?php echo $sysFunTitle ?> </h5>
                </div>
                <!--标题栏和 添加按钮   结束-->


                <div class="ibox-content">
                    <!--表格数据区------------开始-->
                    <form name="form1" id="form1" action="" method="post" class="form-horizontal">
                        <input type="hidden" name="dopost" value="save"/>
                        <input type="hidden" name="clientid" id="clientid" value=""/>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">选择会员:</label>
                            <div class="col-sm-10">
                                <button type="button" class="btn btn-primary" onclick="selectClient()">选择会员</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">会员名称</label>
                            <div class="col-sm-2 form-control-static">
                                <span id="realname"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">金币数量</label>
                            <div class="col-sm-2">
                                <input class="form-control" id="jb" name="jb" type="number">
                            </div>
                            <div class="col-sm-2 form-control-static">
                                必须是负数,小于0
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">备注:</label>

                            <div class="col-sm-2">
                                <textarea class="form-control" name="info" cols="30" rows="5" id="info"></textarea>
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary">保存</button>
                            </div>
                        </div>

                    </form>
                    <!--表格数据区------------结束-->
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/bootstrap.min.js"></script>
<script src="../ui/js/content.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    $(document).ready(function () {

        $("#form1").validate({
            rules: {
                clientid: {required: true},
                jb: {number: true, required: true, max: 0},
            },
            messages: {
                clientid: {required: "选择会员"},
                jb: {number: "请输入数字", required: "必填", max: "不能大于0,必须是负数"},

            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "jb_sub.php",
                    data: {
                        dopost: "save",
                        clientid: $("#clientid").val(),
                        jbnum: $("#jb").val(),
                        info: $("#info").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "操作成功") {
                            layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                window.location.href = "jb.php";
                            });
                        } else {
                            layer.msg(result, {
                                time: 2000 //20s后自动关闭
                            });
                        }
                    }
                });
            }
        });
    });

    function selectClient() {
        layer.open({type: 2, title: '选择会员', content: '../client/client.select.php'});
    }
    $(function () {
        var clientid = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#clientid").val() != clientid) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                clientid = $("#clientid").val();//保存改变后的值
                $("#clientid_str").html("编号" + clientid);//保存改变后的值
                $.ajax({
                    type: "get",
                    url: "../client/client.do.php",
                    data: {
                        clientid: clientid,
                        dopost: "GetOneClientInfo"
                    },
                    dataType: 'json',
                    success: function (result) {
                        console.log(result);
                        $("#realname").html(result.realname + " " + result.mobilephone);
                    }
                });
            }
        }
    });

</script>
</body>
</html>