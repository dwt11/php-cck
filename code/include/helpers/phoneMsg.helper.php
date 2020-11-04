<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 短信相关的公用功能
 *
 * @version        20160518 15:53
 * @package
 * @copyright
 * @license
 * @link
 */


require_once(DWTINC . '/phoneMsg/TopSdk.php');

if (!function_exists('SendPhoneMSG')) {

    /**
     * 发送手机验证码
     *
     * @param          $phone    手机号
     * @param          $name     模板标题
     * @param int|会员   $clientid 会员 ID，如果用户没有注册过，则此值为0  ，如果用户是注册后，再验证手机的，此值为用户ID
     * @param          $depid    所属公司 用于从短信参数表获取APPid和appseret；  用于保存发送日志到LOG记录
     *
     * @param array    $data     参数 data传值必须urlencode编码
     *
     * @return string
     */
    function SendPhoneMSG($phone, $name, $clientid = 0, $depid, $data = array())
    {

        $name = urldecode($name);

        //判断手机号是否正确
        if (!CheckMobilePhone($phone)) return "手机号码不正确";
        global $dsql;
        //获取 短信发送的接口参数
        $query = "SELECT Appkey,AppSecret FROM `#@__interface_phonemsg`    WHERE depid='$depid' ";
        $row = $dsql->GetOne($query);
        $Appkey = $row['Appkey'];
        $AppSecret = $row['AppSecret'];

        //获取短信发送的模板和签名
        $query = "SELECT signName,templateCode FROM `#@__interface_phonemsg_template`    WHERE depid='$depid' and  `name`='$name' ";
        //dump($query);
        $row = $dsql->GetOne($query);
        $signName = $row['signName'];
        $templateCode = $row['templateCode'];

        //编译数据
        $smsParam = $roundCode = "";
        if ($name == "注册验证码" || $name == "业务验证码") {
            //生成随机码
            $roundCode = dotrand(1000, 9999);
            $smsParam = array(
                'code' => urlencode($roundCode),
                'product' => urlencode($signName)
            );
        }
        if ($name == "购买成功" || $name == "朋友购买成功") {
            //dump(isset($data));
            //dump($data);
            if (!count($data) > 0) {
                $data = array(
                    'body' => urlencode("")
                );

            }
            $smsParam = $data;
        }
        if ($name == "旅游订单预订成功通知") {
            $smsParam = array(
                'TakeOffDate' => urlencode($data["godate"]),
                'devicename' => urlencode($data["devicename"]),
                'seatNumber' => urlencode($data["seatNumber"])
            );
        }
        if ($name == "行程变更通知") {
            $smsParam = array(
                'GoodsName' => urlencode($data["goodsname"]),
                'GoDate' => urlencode($data["godate"]),
                'remark' => urlencode($data["remark"])
            );
        }
        if ($name == "乘车卡续费通知") {
            $smsParam = array(
                'ZTCDATE' => urlencode($data["ZTCDATE"]),
                'name' => urlencode($data["name"]),
                'cardcode' => urlencode($data["cardcode"]),
                'remark' => urlencode($data["remark"])
            );
        }
        $smsParam = urldecode(json_encode($smsParam));
        // dump($smsParam);
        //dump($data);

        if (!DEBUG_LEVEL_ISSENDMSG) {
            $senddate = time();//当前时间
            $body = $smsParam;//要保存到数据库的内容
            if ($roundCode != "") $body = $roundCode;//如果发送的是验证码,则只保存验证码,因为用户验证时,只取验证码
            $sql = "INSERT INTO `#@__interface_phonemsg_log` (`depid`,`clientid`, `mobilephone`, `body`, `senddate`, `type`) VALUES ('$depid', '$clientid', '$phone', '$body', '$senddate', '$name');";
            $dsql->ExecuteNoneQuery($sql);
            $phonemsgId = $dsql->GetLastID();
            if (!isset($_SESSION)) session_start();
            $_SESSION[$phone] = $phonemsgId;//将ID号存入数据库待查

            return "{$smsParam}调试模式不发送";
        }

        date_default_timezone_set('Asia/Shanghai');
        $c = new TopClient;
        $c->appkey = $Appkey;// 你的appkey
        $c->secretKey = $AppSecret;// 你的secret
        $reg = new AlibabaAliqinFcSmsNumSendRequest;
        $reg->setExtend("123456");//回传参数 公共回传参数，在“消息返回”中会透传回该参数；举例：用户可以传入自己下级的会员ID，在消息返回时，该会员ID会包含在内，用户可以根据该会员ID识别是哪位会员使用了你的应用
        $reg->setSmsType("normal");//短信类型，传入值请填写normal
        $reg->setSmsFreeSignName($signName);//短信签名，传入的短信签名必须是在阿里大鱼“管理中心-短信签名管理”中的可用签名。
        $reg->setSmsParam($smsParam);//短信模板变量，传参规则{"key":"value"}，key的名字须和申请模板中的变量名一致，多个变量之间以逗号隔开。示例：针对模板“验证码${code}，您正在进行${product}身份验证，打死不要告诉别人哦！”，传参时需传入{"code":"1234","product":"alidayu"}
        $reg->setRecNum($phone);//   短信接收号码。支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。群发短信需传入多个号码，以英文逗号分隔，一次调用最多传入200个号码。
        $reg->setSmsTemplateCode($templateCode);//短信模板ID，传入的模板必须是在阿里大鱼“管理中心-短信模板管理”中的可用模板。
        $resp = $c->execute($reg);//返回的是object JSON类型

        //返回值
        //object(SimpleXMLElement)#4 (2) { ["result"]=> object(SimpleXMLElement)#3 (3) { ["err_code"]=> string(1) "0" ["model"]=> string(26) "101632263193^1102175011440" ["success"]=> string(4) "true" } ["request_id"]=> string(13) "10fatj5xdoyri" }
        //是否发送成功
        if (isset($resp->result->err_code) && $resp->result->err_code == 0) {
            //插入发送记录,并获得最后的ID
            $senddate = time();//当前时间
            $body = $smsParam;//要保存到数据库的内容
            if ($roundCode != "") $body = $roundCode;//如果发送的是验证码,则只保存验证码,因为用户验证时,只取验证码

            $sql = "INSERT INTO `#@__interface_phonemsg_log` (`depid`,`clientid`, `mobilephone`, `body`, `senddate`, `type`) VALUES ('$depid', '$clientid', '$phone', '$body', '$senddate', '$name');";
            $dsql->ExecuteNoneQuery($sql);
            $phonemsgId = $dsql->GetLastID();
            if (!isset($_SESSION)) session_start();
            $_SESSION[$phone] = $phonemsgId;//将ID号存入数据库待查
            return $phonemsgId;
        } else if ($resp->code == 15) {
            //短信验证码，使用同一个签名，    对同一个手机号码发送短信验证码，允许每分钟1条，累计每小时7条。 短信通知，使用同一签名、同一模板，对同一手机号发送短信通知，允许每天50条（自然日）。
            require_once DWTINC . '/weixin/pay/log.php';
            //初始化日志
            $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_phoneSend.log');
            $log = Log::Init($logHandler, 15);
            Log::DEBUG("query:" . json_encode($resp) . $resp->sub_msg . $resp->code);
            // dump($resp);
            return $resp->sub_msg;
        } else {
            require_once DWTINC . '/weixin/pay/log.php';
            //初始化日志
            $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_phoneSend.log');
            $log = Log::Init($logHandler, 15);
            Log::DEBUG("query:" . json_encode($resp) . $resp->sub_msg . $resp->code);
            //s dump($resp);

            return "发送失败";
        }
    }

}


if (!function_exists('ValidatePhoneCode')) {

    /**
     * 验证-验证码是否正确
     *
     * @param          $mobilephone    手机号
     *
     * @param          $fromCheckCode  用户输入的验证码
     *
     * @return string
     */
    function ValidatePhoneCode($mobilephone, $fromCheckCode)
    {
        $return_str = "";
        global $dsql;

        //检测验证码是否正确
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION[$mobilephone])) {
            return "未获取手机验证码,请重新获取";
        }

        if ($fromCheckCode == "") {
            return "请输入验证码";
        }

        $phoneMsgId = $_SESSION[$mobilephone];// 在短信类中生成
        $query = "SELECT body FROM `#@__interface_phonemsg_log`    WHERE id='$phoneMsgId'";
        $row = $dsql->GetOne($query);
        if ($row["body"] != $fromCheckCode) {
            return "验证码输入错误,请核对";
        } else {
            return "验证成功";
        }


    }

}





