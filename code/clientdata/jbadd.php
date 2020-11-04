<?php
/**
 * 金币充值卡充值
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


if ($dopost == 'getje') {
    //$_SESSION["getczkjetime"] = time();//将ID号存入数据库待查
    //dump($_SESSION["getczkjetime"]);
    //这里验证时间 安全性考虑 未做完,因为只在后台操作,暂时不做了
    if ($czk_password == "") {
        echo "请正确输入充值卡密码";
        exit();
    }
    if (strlen($czk_password) != 16) {
        echo "请正确输入充值卡密码";
        exit();
    }

    $nquery = " SELECT je FROM #@__order_addon_czk  WHERE usedate>0 AND czk_password='{$czk_password}'";
    $chRow = $dsql->GetOne($nquery);
    if (is_array($chRow)) {
        echo "此卡已经使用过";
        exit();
    }

    $nquery = " SELECT je FROM #@__order_addon_czk  WHERE czk_password='{$czk_password}'";
    $chRow = $dsql->GetOne($nquery);
    //dump($chRow);
    if (is_array($chRow)) {
        echo $chRow["je"] / 100;
        exit();
    } else {
        echo "充值卡读取失败";
        exit();
    }

}
if ($dopost == 'save') {
    if (empty($clientid)) $clientid = '';
    if ($clientid == '') {
        echo "请选择会员！";
        exit;
    } else {

        $je = 0;
        $nquery = " SELECT je FROM #@__order_addon_czk  WHERE usedate>0 AND czk_password='{$czk_password}'";
        $chRow = $dsql->GetOne($nquery);
        if (is_array($chRow)) {
            echo "此卡已经使用过";
            exit();
        }

        $nquery = " SELECT je FROM #@__order_addon_czk  WHERE czk_password='{$czk_password}'";
        $chRow = $dsql->GetOne($nquery);
        //dump($chRow);
        if (is_array($chRow)) {
            $jbnum100 = $chRow["je"];
        } else {
            echo "充值卡读取失败";
            exit();
        }

        $usedate = time();
        $querySQL = "UPDATE `#@__order_addon_czk` SET `usedate`='$usedate' WHERE czk_password='{$czk_password}' AND usedate=0";
        // dump($querySQL);
        $istrue = $dsql->ExecuteNoneQuery2($querySQL);

        // dump($istrue);
        if ($jbnum100 > 0 && $istrue) {
            $arcQuery = "SELECT clientid FROM #@__client_addon  WHERE clientid='$clientid' ";
            $arcRow = $dsql->GetOne($arcQuery);
            $istrue = Update_jb($clientid, $jbnum100, "管理员充值卡充值 充值卡密码:{$czk_password}", 0, $CUSERLOGIN->userID, "$info");
            if ($istrue) {
                //如果是有效的乘车卡会员 ，则充值多少金币，送多少积分
                $rankInfo = GetClientType("rank", $clientid);
                $rankInfo_array = explode(",", $rankInfo);
                //dump($rankInfo_array);
                if (
                    $rankInfo != ""/*除了刚注册的会员,只要有会员身份的所有的会员卡都返一半的积分171103修改*/
                ) {
                    $desc = "金币充值赠送(充值卡) 充值卡密码:{$czk_password}";

                    $mynumjf100 = intval($jbnum100 / 2);
                    Update_jf($clientid, $mynumjf100, $desc);//                    //171031修改为购买后赠送现金一半的积分


                }
                echo "操作成功";
            } else {
                echo "操作失败，请检查会员的金币数量";
            }
        } else {
            echo "充值失败";

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
    <title>充值卡充值</title>
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="../ui/css/animate.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
</head>

<body>


<!--标题栏和 添加按钮            开始-->
<div class="ibox-title">
    <h5>充值卡充值</h5>
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
            <label class="col-sm-2 control-label">充值卡密码</label>
            <div class="col-sm-2 ">
                <input class="form-control" id="czk_password" name="czk_password" type="number">
            </div>
            <div class="col-sm-2 form-control-static">
                <span id="pwd_str" class=" text-success  font-bold "></span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">金额</label>
            <div class="col-sm-2 form-control-static ">
                <span id="je"></span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">备注:</label>

            <div class="col-sm-2">
                <textarea class="form-control" name="info" cols="30" rows="5" id="info"></textarea>
            </div>
        </div>


        <div class="clearfix" style="margin-bottom: 50px"></div>
        <div class="bodyButtomTab">
            <div class="col-xs-4 col-xs-offset-2 ">
                <button class="btn btn-primary" type="submit">保存</button>

            </div>
        </div>

    </form>
    <!--表格数据区------------结束-->
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
                czk_password: {number: true, required: true},
            },
            messages: {
                clientid: {required: "选择会员"},
                czk_password: {number: "请输入数字", required: "必填"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "jbadd.php",
                    data: {
                        dopost: "save",
                        clientid: $("#clientid").val(),
                        czk_password: $("#czk_password").val(),
                        info: $("#info").val()
                    },
                    dataType: 'html',
                    success: function (result) {
                        if (result == "操作成功") {
                            layer.msg('操作成功', {
                                shade: 0.5, //开启遮罩
                                time: 2000 //20s后自动关闭
                            }, function () {
                                location.reload();
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
        var czk_password = "";
        intervalName11 = setInterval(handle11, 1000);//定时器句柄
        function handle11() {
            //如果值不一样,则代表了改变
            if ($("#czk_password").val() != czk_password) {
                //console.log($("#goodsid").val()+"----"+goodsid);
                czk_password = $("#czk_password").val();//保存改变后的值
                $("#je").html("");


                var inputlength = czk_password.length;
                console.log(inputlength);
                var pwd_str = czk_password.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");    //格式化16位密码,每四位,加一个空格
                $("#pwd_str").html(pwd_str);
                if (inputlength == 16) {
                    $.ajax({
                        type: "get",
                        url: "jbadd.php",
                        data: {
                            czk_password: czk_password,
                            dopost: "getje"
                        },
                        dataType: 'html',
                        success: function (result) {
                            console.log(result);
                            $("#je").html(result);
                        }
                    });
                }
            }
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