<?php

//续费 通知
require_once("../config.php");
if (empty($dopost)) $dopost = '';
if (empty($id)) $id = '';
if (empty($ids)) $ids = '';

//表单过来的乘车人
if ($ids == "") {
    echo "未选择人员";
    exit();
}

if ($dopost == "send") {
    $ids = str_replace('`', ',', $ids);

    $truesendnum = 0;
    $falsesendnum = 0;
    //dump($ids);

    $query = "
        SELECT 
        #@__order_addon_ztc.`name`,#@__order_addon_ztc.id,#@__order_addon_ztc.tel
        ,#@__order.ordernum,
             #@__order.clientid,#@__order.createtime,
             #@__goods_addon_ztc.rankLenth
        FROM #@__order_addon_ztc  
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_ztc.orderid
        LEFT JOIN #@__goods_addon_ztc  ON #@__order_addon_ztc.goodsid=#@__goods_addon_ztc.goodsid
        WHERE #@__order_addon_ztc.id IN($ids)
        ORDER BY     #@__order_addon_ztc.id ASC";
    $dsql->SetQuery($query);
    $dsql->Execute();
    //dump($dsql->GetTotalRow());
    $info = array();
    $lineid = "";
    while ($row_weixin170417 = $dsql->GetArray()) {

        //$clientid="1090";测试使用
        $addonztcid=$row_weixin170417["id"];
        $clientid = $row_weixin170417["clientid"];
        $createtime = $row_weixin170417["createtime"];
        $rankcutofftime_str = "";
        if ($createtime < 1483199999) {
            $rankcutofftime_str = "2017-12-31";//如果是2016-12-31前的订单 则订单到期日是2017-12-31
        } else {
            $rankcutofftime_str = GetDateMk(strtotime("+{$row_weixin170417["rankLenth"]} month", $createtime));

        }
        $realname = $row_weixin170417["name"];
        $tel = $row_weixin170417["tel"];
        $ordernum = $row_weixin170417["ordernum"];

        $updatetime=time();

        $weixinMsgDataArray = array();
        $name = "乘车卡续费提醒";
        $weixinMsgDataArray["frist"] = $realname;
        $weixinMsgDataArray["name"] = "乘车卡".$ordernum;
        $weixinMsgDataArray["expDate"] = $rankcutofftime_str;
        $weixinMsgDataArray["remark"] = $message;

        //dump($weixinMsgDataArray);
        //dump($clientid);
        if ($clientid > 0) {
            //dump($weixinMsgDataArray);
            //dump($name);
            //dump($clientid);
            //exit;
            //$return_info = "未获取到会员OPENID";
            $return_info = SendTemplateMessage($name, $clientid, "17", $weixinMsgDataArray);
            //dump($return_info);

            if ($return_info == "发送成功") {

                //如果有发送记录就加1  没有就添加
                $sql11111 = "INSERT INTO x_order_addon_ztc__dqinfo (addonztcid,weixinSendNumb) VALUES ($addonztcid,1) ON DUPLICATE KEY UPDATE weixinSendNumb=weixinSendNumb+1,weixinSendDate='{$updatetime}'; ";
                //dump($sql11111);
                $dsql->ExecuteNoneQuery($sql11111);


                $truesendnum++;
            } elseif ($return_info == "未获取到会员OPENID") {

                $phoneMsgDataArray = array();
                $phoneMsgDataArray["ZTCDATE"] = $rankcutofftime_str;
                $phoneMsgDataArray["name"] = $realname;
                $phoneMsgDataArray["cardcode"] = $ordernum;
                $phoneMsgDataArray["remark"] = $message;

                $mobilephone = $row_weixin170417["tel"];
                //dump($mobilephone);
                //dump($phoneMsgDataArray);
               // exit();

                $return = SendPhoneMSG($mobilephone, $name = "乘车卡续费通知", $clientid, $DEPID = 17, $data = $phoneMsgDataArray);
                //dump($return);
                //这里没有判断$return是否成功
                //如果有发送记录就加1  没有就添加
                $sql11111 = "INSERT INTO x_order_addon_ztc__dqinfo (addonztcid,weixinSendNumb) VALUES ($addonztcid,1) ON DUPLICATE KEY UPDATE weixinSendNumb=weixinSendNumb+1,weixinSendDate='{$updatetime}'; ";
                //dump($sql11111);
                $dsql->ExecuteNoneQuery($sql11111);


                $truesendnum++;

            } else {
                $falsesendnum++;
            }
        }
        // sleep(5);//170403删除不用延时了
    }

    $return_info = "";
    if ($truesendnum > 0) $return_info = "发送成功{$truesendnum}条";
    if ($falsesendnum > 0) $return_info .= "发送失败{$falsesendnum}条";
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    //dump($return_info);
    ShowMsg($return_info, $$ENV_GOBACK_URL);
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
    <link href="../ui/css/style.min.css" rel="stylesheet">

</head>

<body class="gray-bg">

<div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
    <form id="form1" name="form1" action="" method="post" class="form-horizontal" target="_parent">
        <input type="hidden" name="ids" value="<?php echo $ids; ?>">
        <input type="hidden" name="dopost" value="send">

        优先发给购买人的微信。如果购买人没有微信则发送短信到乘车卡实际的手机号中（不是购买人的手机）.

        <div class="form-group">
            <label class="col-sm-2 control-label">备注信息:</label>

            <div class="col-sm-2">
                <textarea name="message" id="message" class="form-control" placeholder="请填写内容20字以内" rows="5">请及时续费</textarea>
                <span id="chars">最多二十个字符</span>
            </div>
        </div>


        <div class="form-group">
            <div class="text-center">
                <button class="btn btn-primary" type="submit">发送</button>
            </div>
        </div>
        <div class="form-group">
            微信内容示例：<br>


            {刘德华}<br>

            您的{乘车卡2017030304}有效期至{201X-XX-XX}<br>
            {请及时续费}

        </div>
        <div class="form-group">
            短信内容示例：<br>
            续费通知:您的乘车卡在201X-XX-XX到期,姓名{刘德华},卡号{123456},说明:{请及时续费}
        </div>
    </form>
</div>


<script src="../ui/js/jquery.min.js"></script>
<script src="../ui/js/plugins/layer/layer.min.js"></script>
<script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    //让这个弹出层iframe自适应高度150109
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
    parent.layer.iframeAuto(index);
    $().ready(function () {
        $("#form1").validate({
            rules: {
                message: {required: !0}
            },
            messages: {
                message: {required: "请填写内容"}
            }
        })

        $('#message').maxLength(20);

    });


    jQuery.fn.maxLength = function (max) {
        this.each(function () {
            var type = this.tagName.toLowerCase();
            var inputType = this.type ? this.type.toLowerCase() : null;
            if (type == "input" && inputType == "text" || inputType == "password") {
                //Apply the standard maxLength
                this.maxLength = max;
            }
            else if (type == "textarea") {
                this.onkeypress = function (e) {
                    var ob = e || event;
                    var keyCode = ob.keyCode;
                    var hasSelection = document.selection ? document.selection.createRange().text.length > 0 : this.selectionStart != this.selectionEnd;
                    return !(this.value.length >= max && (keyCode > 50 || keyCode == 32 || keyCode == 0 || keyCode == 13) && !ob.ctrlKey && !ob.altKey && !hasSelection);
                };
                this.onkeyup = function () {
                    if (this.value.length > max) {
                        this.value = this.value.substring(0, max);
                    }
                };
            }
        });
    };
</script>

</body>
</html>




