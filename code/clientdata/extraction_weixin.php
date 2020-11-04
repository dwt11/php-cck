<?php
require_once('../config.php');
if (empty($id)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = trim(preg_replace("#[^0-9]#", '', $id));
if (empty($dopost)) $dopost = '';


//订单信息
$query = "SELECT e.*,o2.realname,o2.mobilephone,cw.openid from
          #@__clientdata_extractionlog e
          LEFT JOIN #@__client o2 ON e.clientid=o2.id
          LEFT JOIN #@__client_depinfos cw ON cw.clientid=o2.id
        where e.id='$id' ";
$rowOrder = $dsql->GetOne($query);
//dump($query);

$realname = $rowOrder['realname'];//提现金额
$paydesc_value = $rowOrder['paydesc'];//
$mobilephone = $rowOrder['mobilephone'];//提现金额
$paynum100 = $rowOrder['jbnum'];//提现金额
$paynum_str = $paynum100 / 100;//提现金额
$openId = $rowOrder["openid"];//①、获取用户openid
//$ordercodetime = ($rowOrder['ordernum'] . "-" . date("His"));
$date = GetDateMk($rowOrder["createtime"]);


if ($dopost == 'save') {


    $userid = preg_replace("#[^0-9]#", '', $GLOBALS['CUSERLOGIN']->getUserId());
    $pwd = substr(md5($pwd), 5, 20);
    $chRow = $dsql->GetOne("SELECT id FROM `#@__sys_admin`  WHERE  id='$userid' and pwd='$pwd' ");
    if (!is_array($chRow)) {
        $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
        echo "密码错误,操作失败！";
        exit();
    }

//大于100分 也就是一元
    if ($openId != "" && $paynum100 > 100) {
        //$paynum = $paynum * 100;//提现金额

        $appid = GetWeixinAppId($DEP_TOP_ID);
        $secret = GetWeixinAppSecret($DEP_TOP_ID);

        $wxPayData_array = GetWeixinPayDataArray($DEP_TOP_ID);
        $key = $wxPayData_array["wxPay_key"];

        //=======【证书路径设置】== ===================================
        /**
         * TODO：设置商户证书路径
         * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
         */
        //$SSLCERT_PATH = 'E:\website\jl\code\include\weixin\pay\cert160918\apiclient_cert.pem';
        //$SSLKEY_PATH = 'E:\website\jl\code\include\weixin\pay\cert160918\apiclient_key.pem';
        // $SSLCERT_PATH = 'E:\website\xz\code\include\weixin\pay\cert161208\apiclient_cert.pem';
        //$SSLKEY_PATH = 'E:\website\xz\code\include\weixin\pay\cert161208\apiclient_key.pem';
        $SSLCERT_PATH = $cfg_basedir . $wxPayData_array["wxPay_ssl_path"] . '/apiclient_cert.pem';
        $SSLKEY_PATH = $cfg_basedir . $wxPayData_array["wxPay_ssl_path"] . '/apiclient_key.pem';


        $mch_appid = $appid;

        $mchid = $wxPayData_array["wxPay_mchid"];//商户号
        $nonce_str = time() . rand(100, 999);//随机数 不能超过32位
        $partner_trade_no = $id;//商户订单号(170224修改,这个不能太长,否则 提示参数错误:单号必须为33位以下的数字或字母)
        $openid = $openId;//用户唯一标识
        $check_name = 'NO_CHECK';//校验用户姓名选项，NO_CHECK：不校验真实姓名 FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
        $re_user_name = $realname;//用户姓名
        $amount = $paynum100;//金额（以分为单位，必须大于100）
        //$amount = 100;//金额（以分为单位，必须大于100）
        $desc = $realname . $date;//描述(李军 13155554444 2015-9-6 提现申请)
        $spbill_create_ip = $_SERVER["REMOTE_ADDR"];//请求ip
        //封装成数据
        $dataArr = array();
        $dataArr['amount'] = $amount;
        $dataArr['check_name'] = $check_name;
        $dataArr['desc'] = $desc;
        $dataArr['mch_appid'] = $mch_appid;
        $dataArr['mchid'] = $mchid;
        $dataArr['nonce_str'] = $nonce_str;
        $dataArr['openid'] = $openid;
        $dataArr['partner_trade_no'] = $partner_trade_no;
        $dataArr['re_user_name'] = $re_user_name;
        $dataArr['spbill_create_ip'] = $spbill_create_ip;

        $sign = getSign($dataArr, $key);


        //echo "-----<br/>签名：" . $sign . "<br/>*****";//die;
        $data = "<xml>
        <mch_appid>" . $mch_appid . "</mch_appid>
        <mchid>" . $mchid . "</mchid>
        <nonce_str>" . $nonce_str . "</nonce_str>
        <partner_trade_no>" . $partner_trade_no . "</partner_trade_no>
        <openid>" . $openid . "</openid>
        <check_name>" . $check_name . "</check_name>
        <re_user_name>" . $re_user_name . "</re_user_name>
        <amount>" . $amount . "</amount>
        <desc>" . $desc . "</desc>
        <spbill_create_ip>" . $spbill_create_ip . "</spbill_create_ip>
        <sign>" . $sign . "</sign>
        </xml>";
        //dump($data);


        require_once DWTINC . '/weixin/pay/log.php';


        $logHandler = new CLogFileHandler($cfg_basedir . $wxPayData_array["wxPay_debug_path"]."/tx_" . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);
        Log::DEBUG("call back:" . $data);//这里用来判断   微信是不是在一直回调访问这个页面，用于验证  支付后是不是通知了微信


        // exit;
        $ch = curl_init();
        $MENU_URL = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        curl_setopt($ch, CURLOPT_URL, $MENU_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $zs1 = $SSLCERT_PATH;
        $zs2 = $SSLKEY_PATH;
        curl_setopt($ch, CURLOPT_SSLCERT, $zs1);
        curl_setopt($ch, CURLOPT_SSLKEY, $zs2);
// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01;
// Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $info = curl_exec($ch);


        $info_array = FromXml($info);//XML转为数组
        $err_str = "";
        if (isset($info_array["result_code"]) && $info_array["result_code"] == "SUCCESS") {


            $payment_no = $info_array["partner_trade_no"] . "-" . $info_array["payment_no"];//订单号==商户订单号+微信订单号
            $payment_time = GetMkTime($info_array["payment_time"]);//付款时间
            $payoperatorid = $CUSERLOGIN->userID;//操作人
            //付款成功
            $query = "UPDATE `#@__clientdata_extractionlog` SET status='3' ,payment_no='$payment_no',payment_time='$payment_time',payoperatorid='$payoperatorid',paydesc='$paydesc' WHERE id='$id'";
            $dsql->ExecuteNoneQuery($query);
            $return_str = "付款成功";


            //发送提现成功微信通知
            $weixinMsgDataArray = array();
            $weixinMsgDataArray["frist"] = "提现成功通知";
            $weixinMsgDataArray["keyword1"] = "{$paynum_str}金币";
            $weixinMsgDataArray["keyword3"] = $info_array["payment_time"];
            $return_info = SendTemplateMessage("提现成功通知", $clientid, "{$DEP_TOP_ID}", $weixinMsgDataArray);


        } else if (isset($info_array["result_code"]) && $info_array["result_code"] == "FAIL") {
            //付款失败

            /*
            string(360) "<xml>
            <return_code><![CDATA[SUCCESS]]></return_code>
            <return_msg><![CDATA[帐号余额不足，请用户充值或更换支付卡后再支付.]]></return_msg>
            <result_code><![CDATA[FAIL]]></result_code>
            <err_code><![CDATA[NOTENOUGH]]></err_code>
            <err_code_des><![CDATA[帐号余额不足，请用户充值或更换支付卡后再支付.]]></err_code_des>
            </xml>"*/
            $payment_no = $info_array["return_msg"];//失败的原因
            $payment_time = time();//失败时间
            $payoperatorid = $CUSERLOGIN->userID;//操作人
            $query = "UPDATE `#@__clientdata_extractionlog` SET status='4' ,payment_no='$payment_no',payment_time='$payment_time',payoperatorid='$payoperatorid',paydesc='$paydesc' WHERE id='$id'";
            $dsql->ExecuteNoneQuery($query);
            $return_str = "付款失败";
        } else if (curl_errno($ch)) {
            //有失败信息=====证书类的失败原因
            //echo '<br>Errno' . curl_error($ch) . "<br>";
            $err_str = curl_error($ch);
            $payment_no = $err_str;//失败的原因
            $payment_time = time();//失败时间
            $payoperatorid = $CUSERLOGIN->userID;//操作人
            $query = "UPDATE `#@__clientdata_extractionlog` SET status='4' ,payment_no='$payment_no',payment_time='$payment_time',payoperatorid='$payoperatorid',paydesc='$paydesc' WHERE id='$id'";
            //dump($query);
            $dsql->ExecuteNoneQuery($query);
            $return_str = "付款失败";
        } else {
            //其他的失败原因
            $payment_no = "付款失败，请联系管理员查找原因";//失败的原因
            $payment_time = time();//失败时间
            $payoperatorid = $CUSERLOGIN->userID;//操作人
            $query = "UPDATE `#@__clientdata_extractionlog` SET status='4' ,payment_no='$payment_no',payment_time='$payment_time',payoperatorid='$payoperatorid',paydesc='$paydesc' WHERE id='$id'";
            $dsql->ExecuteNoneQuery($query);
            $return_str = "付款失败";
        }

        curl_close($ch);
        //echo "-----<br/>请求返回值：";
        //dump($info);
        //echo "<br/>*****";
        //die;

    } else {
        $return_str = "付款失败";
    }

    echo $return_str;
    exit();

}

?>

    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href="../ui/css/bootstrap.min.css" rel="stylesheet">
        <link href="../ui/css/plugins/iCheck/custom.css" rel="stylesheet">
        <link href="../ui/css/animate.min.css" rel="stylesheet">
        <link href="../ui/css/style.min.css" rel="stylesheet">
    </head>

    <body class="gray-bg">

    <div class="wrapper wrapper-content animated fadeInRight" style="background-color: #ffffff">
        <form name="form1" id="form1" action="" method="post" class="form-horizontal" target="_parent">
            <input type="hidden" name="dopost" value="save"/>
            <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>

            <div class="form-group">
                <div class="col-sm-2">
                    会员信息：<?php echo $realname . " " . $mobilephone; ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                    提现金额：<?php echo $paynum_str; ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                    <input type="text" class="form-control pword m-b" placeholder="登录密码" onfocus="this.type='password'" autocomplete="off" name="pwd" id="pwd"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                    <textarea name="paydesc" id="paydesc" class="form-control" placeholder="备注" rows="5"><?php echo $paydesc_value; ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="text-center">
                    <button class="btn btn-primary" type="submit" id="submit">确认支付</button>
                </div>
            </div>

        </form>
    </div>
    <script src="../ui/js/jquery.min.js"></script>
    <script src="../ui/js/bootstrap.min.js"></script>
    <script src="../ui/js/content.min.js"></script>
    <script src="../ui/js/plugins/layer/layer.min.js"></script>
    <script src="../ui/js/plugins/validate/jquery.validate.min.js"></script>
    <!--验证用-->
    <script>
        //让这个弹出层iframe自适应高度150109
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        parent.layer.iframeAuto(index);
        $().ready(function () {
            $("#form1").validate({
                rules: {
                    pwd: {required: !0, minlength: 6}
                },
                messages: {
                    pwd: {required: "请填写密码", minlength: "密码必须6个字符以上"}
                }, submitHandler: function (form) {
                    $("#submit").attr({"disabled": "disabled"});
                    $.ajax({
                        type: "post",
                        url: "extraction_weixin.php",
                        data: {
                            dopost: "save",
                            id: $("#id").val(),
                            pwd: $("#pwd").val(),
                            paydesc: $("#paydesc").val()
                        },
                        dataType: 'html',
                        success: function (result) {
                            if (result == "付款成功") {
                                window.parent.layer.msg(result, {
                                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                                }, function () {
                                    window.parent.location.href = 'extraction.php?sta=1';
                                });
                            } else {
                                window.parent.layer.msg(result, {
                                    time: 2000 //2秒关闭（如果不配置，默认是3秒）
                                }, function () {
                                    window.parent.location.href = 'extraction.php';
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


<?php

/**
 *    作用：格式化参数，签名过程需要使用
 */
function formatBizQueryParaMap($paraMap, $urlencode)
{
    //dump($paraMap);//die;
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v) {
        if ($urlencode) {
            $v = urlencode($v);
        }
        //$buff .= strtolower($k) . "=" . $v . "&";
        $buff .= $k . "=" . $v . "&";
    }
    $reqPar = "";
    if (strlen($buff) > 0) {
        $reqPar = substr($buff, 0, strlen($buff) - 1);
    }
    //dump($reqPar);//die;
    return $reqPar;
}

/**
 *    作用：生成签名
 */
function getSign($Obj, $key)
{
    //dump($Obj);//die;
    foreach ($Obj as $k => $v) {
        $Parameters[$k] = $v;
    }
    //签名步骤一：按字典序排序参数
    ksort($Parameters);
    $String = formatBizQueryParaMap($Parameters, false);
    //echo '【string1】'.$String.'</br>';
    //签名步骤二：在string后加入KEY
    $String = $String . "&key=" . $key;
    //echo "【string2】".$String."</br>";
    //签名步骤三：MD5加密
    $String = md5($String);
    //echo "【string3】 ".$String."</br>";
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    //echo "【result】 ".$result_."</br>";
    return $result_;
}

/**
 * 将xml转为array
 *
 * @param string $xml
 *
 * @throws WxPayException
 */
function FromXml($xml)
{
    if (!$xml) {
        return "";
    }
    //将XML转为array
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}


