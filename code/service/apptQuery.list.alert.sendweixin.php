<?php

//紧急变更 通知
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
        #@__order_addon_lycp.id AS addonlycpid,
        #@__order_addon_lycp.appttime,
        #@__order_addon_lycp.tel,
             #@__order.clientid,
           #@__order_addon_lycp.realname,
        #@__order_addon_lycp.tel,
        #@__order_addon_lycp.lineid
        FROM #@__order_addon_lycp  
        LEFT JOIN #@__order  ON #@__order.id = #@__order_addon_lycp.orderid
        WHERE #@__order_addon_lycp.id IN($ids)
        ORDER BY     seatNumber ASC";

    //dump($query);
    $dsql->SetQuery($query);
    $dsql->Execute();
//dump($dsql->GetTotalRow());
    $info = array();
    $lineid = "";
    while ($row_weixin170417 = $dsql->GetArray()) {

        //如果线路不同，才获取新的值
        if ($lineid != $row_weixin170417["lineid"]) {
            $appttime = $row_weixin170417["appttime"];
            $lineid = $row_weixin170417["lineid"];
            //获取线路名称和发车日期
            $xl_dest = $xl_tmp = $xl_gotime = "";
            $arcQuery = "SELECT #@__line.gotime,#@__line.tmp,goods.goodsname  FROM #@__line
              LEFT JOIN #@__goods as goods ON goods.id=#@__line.goodsid
              WHERE  #@__line.id='$lineid' ";
            $arcRow = $dsql->GetOne($arcQuery);
            if ($arcRow) {
                $goodsname = $arcRow["goodsname"];
                //if(strlen($goodsname)>25)$goodsname=cn_substr_utf8($goodsname,25)."...";
                $xl_tmp = $arcRow["tmp"];
                $xl_gotime = $arcRow["gotime"];
            }
            $godate = "";
            $appttime_str = MyDate("Y年m月d日", $appttime);
            if ($xl_tmp == '每日') {
                //固定线路 取用户输入的发车日期
                $godate = $appttime_str . " " . date(' H时i分', $xl_gotime);
            } elseif ($xl_tmp == '临时') {
                //临时线路取线路的发车日期
                $godate = date('Y年m月d日 H时i分', $xl_gotime);
            } else {
                $godate = $appttime_str;
            }

        }


        //$clientid="1090";测试使用
        $clientid = $row_weixin170417["clientid"];
        $info[] = array(
            "realname" => $row_weixin170417["realname"],
            "clientid" => $clientid,
            "goodsname" => $goodsname,
            "godate" => $godate
        );


        $name = "行程变更通知";
        $weixinMsgDataArray = array();
        foreach ($info as $row_info) {
            $weixinMsgDataArray["frist"] = "您的旅游行程有重要变更。";
            $weixinMsgDataArray["goodsname"] = $row_info["goodsname"];
            $weixinMsgDataArray["godate"] = $row_info["godate"];
            $remark = $row_info["realname"] . " $message";
            $weixinMsgDataArray["remark"] = $remark;
        }
        //dump($clientid);
        if ($clientid > 0) {
            $return_info = SendTemplateMessage($name, $clientid, "17", $weixinMsgDataArray);
            //dump($return_info);
            $addonlycpid = $row_weixin170417["addonlycpid"];
            if ($return_info == "发送成功") {

                //如果有发送记录就加1  没有就添加
                $sql11111 = "INSERT INTO x_order_addon_lycp_weixin (addonlycpid,weixinSendNumb) VALUES ($addonlycpid,1) ON DUPLICATE KEY UPDATE weixinSendNumb=weixinSendNumb+1; ";
                //dump($sql11111);
                $dsql->ExecuteNoneQuery($sql11111);


                $truesendnum++;
            } elseif ($return_info == "未获取到会员OPENID") {

                $phoneMsgDataArray = array();
                foreach ($info as $row_info) {
                    $phoneMsgDataArray["godate"] = $row_info["godate"];
                    $phoneMsgDataArray["goodsname"] = $row_info["goodsname"];
                    $remark = $row_info["realname"] . " $message";
                    $phoneMsgDataArray["remark"] = $remark;


                }

                $mobilephone = $row_weixin170417["tel"];
                $return = SendPhoneMSG($mobilephone, $name = "行程变更通知", $clientid, $DEPID = 17, $data = $phoneMsgDataArray);
                //dump($return);
                //这里没有判断$return是否成功
                //如果有发送记录就加1  没有就添加
                $sql11111 = "INSERT INTO x_order_addon_lycp_weixin (addonlycpid,weixinSendNumb) VALUES ($addonlycpid,1) ON DUPLICATE KEY UPDATE weixinSendNumb=weixinSendNumb+1; ";
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

        优先微信,没有微信才发送短信.

        <div class="form-group">
            <label class="col-sm-2 control-label">变更原因:</label>

            <div class="col-sm-2">
                <textarea name="message" id="message" class="form-control" placeholder="请填写内容20字以内" rows="5"></textarea>
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
            您的旅游行程有重要变更，请及时查看，以免影响您的旅行。<br>
            线路名称：{北京3日游}<br>
            行程日期：{2016年6月1日08:00}<br>
            {刘德华 因暴雨取消行程}
        </div>
        <div class="form-group">
            短信内容示例：<br>
            行程变更通知,线路名称{北京3日游} 出行日期{2016年6月1日08:00},变更原因: {刘德华 因暴雨取消行程}
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




