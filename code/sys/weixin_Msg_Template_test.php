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

//读取归档信息
$arcQuery = "SELECT *  FROM #@__interface_weixinmsg_template  WHERE id='$id' ";
//dump($arcQuery);
$arcRow = $dsql->GetOne($arcQuery);
if (!is_array($arcRow)) {
    ShowMsg("读取信息出错!", "-1");
    exit();
}
$name = $arcRow['name'];
$template_id = $arcRow['template_id'];
$templateBody = $arcRow['templateBody'];
$url = $arcRow['url'];
$depid = $arcRow['depid'];

if ($dopost == 'save') {
    /*    {{first.DATA}} 订单号：{{keyword1.DATA}} 金额：{{keyword2.DATA}} 商品名称：{{keyword3.DATA}} 购买日期：{{keyword4.DATA}} {{remark.DATA}}*/
    $return_info = "";
    if ($name == "新订单生成通知") {
        $data = array(
            'data' => array(
                'first' => array(
                    'value' => urlencode("商品购买成功通知"),/*加商品类型 直通车会员卡 旅游线路*/
                    'color' => "#000000"
                ),
                'OrderId' => array(
                    'value' => urlencode("2016002350"),
                    'color' => "#000000"
                ),
                'ProductId' => array(
                    'value' => urlencode("199"),
                    'color' => "#000000"
                ),
                'ProductName' => array(
                    'value' => urlencode("商品名称"),
                    'color' => "#000000"
                ),
                'remark' => array(
                    'value' => urlencode("[直通车]感谢您的使用"),
                    'color' => "#000000"
                )
            )
        );
    }
    if($name=="旅游订单预订成功通知"){
        $data = array(
            'data' => array(
                'first' => array(
                    'value' => urlencode("您的线路预约已经确认,请按时乘车出行。"),
                    'color' => "#000000"
                ),
                'OrderID' => array(
                    'value' => urlencode("555555"),
                    'color' => "#000000"
                ),
                'PkgName' => array(
                    'value' => urlencode("旅游线路名称"),
                    'color' => "#000000"
                ),
                'TakeOffDate' => array(
                    'value' => urlencode("2017年11月11j日"),
                    'color' => "#000000"
                ),
                'Remark' => array(
                    'value' => urlencode("座位号：00 车牌号：京A00000"),
                    'color' => "#000000"
                )
            )
        );
    }
    if($name=="会员充值通知"){
        $data = array(
            'data' => array(
                'first' => array(
                    'value' => urlencode("金币充值成功通知"),
                    'color' => "#000000"
                ),
                'accountType' => array(
                    'value' => urlencode("会员姓名"),
                    'color' => "#000000"
                ),
                'account' => array(
                    'value' => urlencode("XXXX"),
                    'color' => "#000000"
                ),
                'amount' => array(
                    'value' => urlencode("50元"),
                    'color' => "#000000"
                ),
                'result' => array(
                    'value' => urlencode("充值成功"),
                    'color' => "#000000"
                ),
                'remark' => array(
                    'value' => urlencode("金币增加数量50"),
                    'color' => "#000000"
                )
            )
        );
    }
    if($name=="返现到账通知"){
        $data = array(
            'data' => array(
                'first' => array(
                    'value' => urlencode("您的朋友XXX购买直通车会员卡成功,返还金币已经到账"),
                    'color' => "#000000"
                ),
                'order' => array(
                    'value' => urlencode("20170202"),
                    'color' => "#000000"
                ),
                'money' => array(
                    'value' => urlencode("50金币"),
                    'color' => "#000000"
                ),
                'remark' => array(
                    'value' => urlencode(""),/*暂时不要内容*/
                    'color' => "#000000"
                )
            )
        );
    }
    if($name=="积分到帐提醒"){
        $data = array(
            'data' => array(
                'first' => array(
                    'value' => urlencode("您的朋友XXX购买直通车会员卡成功,返还积分已经到账"),
                    'color' => "#000000"
                ),
                'keyword1' => array(
                    'value' => urlencode("XXX积分"),
                    'color' => "#000000"
                ),
                'keyword2' => array(
                    'value' => urlencode("50金币"),/*可用积分余额*/
                    'color' => "#000000"
                ),
                'remark' => array(
                    'value' => urlencode("订单号:XXXXX"),/*暂时不要内容*/
                    'color' => "#000000"
                )
            )
        );
    }
    if($name=="提现成功通知"){
        $data = array(
            'data' => array(
                'first' => array(
                    'value' => urlencode("提现成功通知"),
                    'color' => "#000000"
                ),
                'keyword1' => array(
                    'value' => urlencode("XXX金币"),
                    'color' => "#000000"
                ),
                'keyword2' => array(
                    'value' => urlencode("微信钱包"),/*账户*/
                    'color' => "#000000"
                ),
                'keyword3' => array(
                    'value' => urlencode("2017年3月22日"),/*付款时间*/
                    'color' => "#000000"
                ),
                'remark' => array(
                    'value' => urlencode(""),/*暂时没用*/
                    'color' => "#000000"
                )
            )
        );
    }

    $return_info=messageToWeixin($name, $clientid, $depid, $data );
    echo $return_info;
    exit();

}


?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../ui/css/style.min.css" rel="stylesheet">
    <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
</head>

<body class="gray-bg" STYLE="min-width: 800px">
<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form name='form2' id='form2' action='' method='post' class="form-horizontal" target="_parent">
        <input type='hidden' name='dopost' value='save'>
        <input name="id" type="hidden" id="id" value="<?php echo $arcRow['id'] ?>">
        只测试短信短道和模板，不发送参数内容
        <div class="form-group">
            <label class="col-sm-2 control-label">模板名称:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $name ?>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板ID:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $template_id ?>
            </div>
        </div>


        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">连接:</label>

            <div class="col-sm-4 form-control-static">
                <?php echo $url ?>
            </div>
        </div>

        <div class="form-group" id="view">
            <label class="col-sm-2 control-label">模板内容:</label>

            <div class="col-sm-4">
                <?php echo $templateBody ?>
            </div>
        </div>


        <div class="form-group">
            <label class="col-sm-2 control-label">选择会员:</label>
            <div class="col-sm-10">
                <button type="button" class="btn btn-primary" onclick="selectClient()">选择会员</button>
                <input type="hidden" name="clientid" id="clientid" value=""/>
                <span id="clientid_str"><span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">会员姓名:</label>
            <div class="col-sm-2 form-control-static">
                <span id="realname"></span>
            </div>
        </div>


        <div class="form-group">
            <div class="text-center">
                <button class="btn  btn-primary" type="submit">发送</button>
            </div>
        </div>


    </form>

</div>
<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<!--验证用-->
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<!--验证用-->
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    //==============================选择客户
    function selectClient() {
        layer.open({
            type: 2,
            title: '选择会员',
            content: '../client/client.select.php?depid=<?php echo $arcRow['depid']?>'
        });
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
    $().ready(function () {
        $("#form2").validate({
            rules: {
                clientid: {required: !0}
            },
            messages: {
                clientid: {required: "请选择会员"}
            },
            submitHandler: function (form) {
                $.ajax({
                    type: "post",
                    url: "weixin_Msg_Template_test.php",
                    data: {
                        dopost: "save",
                        id: $("#id").val(),
                        clientid: $("#clientid").val()
                    },
                    dataType: 'html',
                    success: function (result) {

                        if (result == "发送成功") {
                            layer.msg('发送成功', {
                                time: 1000 //20s后自动关闭
                            });
                        } else {
                            layer.msg(result, {
                                time: 1000 //20s后自动关闭
                            });
                        }
                    }
                });
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                layer.msg("系统错误,请重试", {
                    shade: 0.5, //开启遮罩 , //0.1透明度的白色背景
                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                }, function () {
                    window.location.href = 'phone.php';
                });
            }
        })
    });

</script>
</body>
</html>