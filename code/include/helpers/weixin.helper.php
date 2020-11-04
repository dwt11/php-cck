<?php if (!defined('DWTINC')) exit('dwtx');
/**
 * 微信 相关的小助手
 * 微信相关的公用功能
 *
 * @version        $Id: archive.helper.php 2 23:00 5日
 * @package        DwtX.Helpers
 * @copyright
 * @license
 * @link
 */


if (!function_exists('IsWeixinBrowser')) {
    /**
     *  是否微信浏览器
     *
     * @return bool  true是 false否
     */
    function IsWeixinBrowser()
    {
        $nowHost = strtolower($_SERVER['HTTP_HOST']);
         $isTrueHost = true;

        $user_agent = $_SERVER['HTTP_USER_AGENT'];//获得浏览器类型
         if (strpos($user_agent, 'MicroMessenger') !== false && $isTrueHost && !DEBUG_LEVEL) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('GetWeixinAppId')) {

    /**根据DEPID获取数据库中的APPid
     *
     * @param $depid
     *
     * @return string
     */
    function GetWeixinAppId($depid)
    {
        global $dsql;
        $return_str = "";
        $row = $dsql->GetOne("SELECT AppId FROM #@__interface_weixin where depid='$depid'");
        if (isset($row["AppId"]) && $row["AppId"] != "") {
            $return_str = $row['AppId'];
        }
        return $return_str;
    }
}

if (!function_exists('GetWeixinAppSecret')) {

    /**根据DEPID获取数据库中的APPid
     *
     * @param $depid
     *
     * @return string
     */
    function GetWeixinAppSecret($depid)
    {
        global $dsql;
        $return_str = "";
        $row = $dsql->GetOne("SELECT AppSecret FROM #@__interface_weixin where depid='$depid'");
        if (isset($row["AppSecret"]) && $row["AppSecret"] != "") {
            $return_str = $row['AppSecret'];
        }
        return $return_str;
    }
}

if (!function_exists('GetWeixinPayDataArray')) {

    /**根据DEPID获取数据库中的APPid
     *
     * @param $depid
     *
     * @return string
     */
    function GetWeixinPayDataArray($depid)
    {
        global $dsql;
        $return_array = array();
        $row = $dsql->GetOne("SELECT wxPay_key,wxPay_ssl_path,wxPay_mchid,wxPay_debug_path FROM #@__interface_weixin where depid='$depid'");
        if (isset($row["wxPay_key"]) && $row["wxPay_key"] != "") {
            $return_array["wxPay_key"] = $row['wxPay_key'];
            $return_array["wxPay_ssl_path"] = $row['wxPay_ssl_path'];
            $return_array["wxPay_mchid"] = $row['wxPay_mchid'];
            $return_array["wxPay_debug_path"] = $row['wxPay_debug_path'];
        }
        return $return_array;
    }
}

if (!function_exists('Get_access_token')) {

    /**获取access_token
     *
     * @param     $appid
     * @param     $secret
     * @param int $force 强制获取
     *
     * @return string
     */
    function Get_access_token($appid, $secret, $force = 0)
    {
        $time = time();


        $weixin_token_file = DEDEDATA . "/weixintoken/weixin_token_file_{$appid}.php";//定义微信 TOKEN的文件地址
        $access_token_str = "access_token_$appid";
        $access_token_oldtime_str = "access_token_oldtime_$appid";
        if (!file_exists($weixin_token_file)) {
            //如果文件不存在则创建 他
            $fp = fopen($weixin_token_file, 'w');


            $str = "
            <?php
            global \${$access_token_str};
            \${$access_token_str}=\"\";
            global \${$access_token_oldtime_str};
             \${$access_token_oldtime_str} =0;
             ";
            fwrite($fp, $str);
        }
        require_once($weixin_token_file);


        //如果没有过期返回旧的
        if (!empty($$access_token_oldtime_str) && $$access_token_oldtime_str + 7000 > $time && $force == 0) {
            return $$access_token_str;
        }


        //如果过期了,则返回新的
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $json = http_request_json($url); //这个地方不能用file_get_contents
        dump($json);
        $data = json_decode($json, true);
        if (isset($data['access_token'])) {
            //如果文件不存在则创建 他
            $fp = fopen($weixin_token_file, 'w');
            $str = "
            <?php
            global \${$access_token_str};
            \${$access_token_str}=\"{$data['access_token']}\";
            global \${$access_token_oldtime_str};
             \${$access_token_oldtime_str} ={$time};
             ";
            fwrite($fp, $str);

            return $data['access_token'];
        } else {
            return "false";
        }
    }
}


if (!function_exists('http_request_json')) {

    /** 因为url是https 所有请求不能用file_get_contents,用curl请求json 数据
     * /*
     *
     * @param $url  访问的地址
     *
     * @return mixed
     */
    function http_request_json($url, $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);//要连接的URL地址，可以在curl_init()中设置
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL验证开启
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//设为0表示不检查证书
        //设为1表示检查证书中是否有CN(common name)字段
        //设为2表示在1的基础上校验当前的域名是否与CN匹配
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);//设置POST方式提交数据，POST格式为application/x-www-form-urlencoded
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//POST格式提交的数据内容
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//返回字符串，而不是调用curl_exec()后直接输出
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('uploadWeixinMenu')) {
    /**发送菜单到微信
     *
     * @param $wxmenu
     * @param $ACCESS_TOKEN
     *
     * @return mixed|string
     */
    function uploadWeixinMenu($wxmenu, $ACCESS_TOKEN)
    {
        $wxmenu = $wxmenu;
        header("Content-type: text/html; charset=utf8");
        define("ACCESS_TOKEN", $ACCESS_TOKEN);
        $ch = curl_init();
        //这里的发布过程，随后要与http_request_json看一下进行整合170208????????
        //$ACCESS_TOKEN获取 ，随后也要移到这里来170208????????
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . ACCESS_TOKEN);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//自定义请求头，使用相对地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//紧随服务器返回的所有重定向信息
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);//当返回的信息头含有转向信息时，自动设置前向连接
        curl_setopt($ch, CURLOPT_POSTFIELDS, $wxmenu);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}


if (!function_exists('GetJsApiParameters')) {

    /**
     * 维信支付 统一下单
     *
     * @param $payname       支付名称 商品名称
     * @param $ordercode     商户订单号
     * @param $paynum100     支付金额 单位分
     * @param $openId        用户openid
     * @param $Notify_url    回调地址
     *
     * @return array|mixed|stdClass
     */
    function GetJsApiParameters($payname, $ordercode, $paynum100, $openId, $Notify_url)
    {

        /*---------------------------------------------微信支付*/
        /*调用微信 支付过程*/
        require_once DWTINC . "/weixin/pay/lib/WxPay.Api.php";
        require_once DWTINC . "/weixin/pay/WxPay.JsApiPay.php";
        require_once DWTINC . "/weixin/pay/log.php";

        //初始化日志
        $logHandler = new CLogFileHandler(DWTINC . "/weixin/pay/logs0919tzy/" . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);

        //打印输出数组信息
        function printf_info($data)
        {
            foreach ($data as $key => $value) {
                echo "<font color='#00ff55;'>$key</font> : $value <br/>";
            }
        }


        $tools = new JsApiPay();
        //$paynum = $paynum * 100;

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($payname);//商品描述
        $input->SetAttach("");//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        $input->SetOut_trade_no($ordercode);//商户订单号，用这个来判断是否支付成功.这个订单号，要系统的订单号，然后+随机数字 ，防止 用户重复提交 造成商户订单号重复
        $input->SetTotal_fee($paynum100);//订单总金额，单位为分，详见支付金额
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");//商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
        $input->SetNotify_url($Notify_url);//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return json_decode($jsApiParameters);
    }
}

/*--------------------------------微信上传图片------------------------------------------------------*/
if (!function_exists('SaveWeixinPicUploadService')) {

    /**
     * 将微信保存的图片，保存到服务器指定地址
     *
     * @param $depid
     * @param $weixinImgUrl 微信服务器的id
     * @param $clientid
     *
     * @return string|微信服务器的id
     */
    function SaveWeixinPicUploadService($depid, $weixinImgUrl, $clientid)
    {
        $imgUrl = $weixinImgUrl;//图片默认地址，如果是普通 浏览器，则下面不会取到值，这个就不变，原样返回
        //保存图片到服务器
        if ($weixinImgUrl != "") {
            $appid = GetWeixinAppId($depid);
            $secret = GetWeixinAppSecret($depid);

            $access_token = Get_access_token($appid, $secret);
            //dump($access_token);
            $weixinImgUrl_array = explode(";", $weixinImgUrl);
            //dump($weixinImgUrl);
            // dump($weixinImgUrl_array);
            foreach ($weixinImgUrl_array as $value) {
                $targetName = getUploadFileAdd($depid) . "idpic/" . $clientid . "_" . $value . ".jpg";//相对地址
                //dump($targetName);
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $targetName;//实际地址
                $dirname = dirname($filePath);//目录名称
                //$weixinImgUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                $weixinImgUrl = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                //dump($weixinImgUrl);
                //打开输出缓冲区并获取远程图片
                ob_start();
                $context = stream_context_create(
                    array('http' => array(
                        'follow_location' => false // don't follow redirects
                    ))
                );
                readfile($weixinImgUrl, false, $context);
                $img = ob_get_contents();//将缓冲区图片 给了IMG
                ob_end_clean();


                //如果获取失败 就重新获取
                $re_array = json_decode($img, true);
                if ($re_array["errcode"] == 40001) {
                    $access_token = Get_access_token($appid, $secret, 1);
                    // $weixinImgUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                    $weixinImgUrl = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                    //dump($weixinImgUrl);
                    //打开输出缓冲区并获取远程图片
                    ob_start();
                    $context = stream_context_create(
                        array('http' => array(
                            'follow_location' => false // don't follow redirects
                        ))
                    );
                    //    dump($weixinImgUrl);
                    readfile($weixinImgUrl, false, $context);
                    $img = ob_get_contents();//将缓冲区图片 给了IMG
                    ob_end_clean();
                } elseif ($re_array["errcode"] == 40007) {
                    return false;
                }

                //if($clientid=='1090') return false;
                //创建目录失败
                if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
                } else if (!is_writeable($dirname)) {
                }

                //移动文件
                if (!(file_put_contents($filePath, $img) && file_exists($filePath))) { //移动失败
                } else { //移动成功
                    $imgUrl = $targetName . ";";
                }

            }
            $imgUrl = rtrim($imgUrl, ";");
        }

        return $imgUrl;
    }
}


/*--------------------------------微信上传图片-压缩保存------------------------------------------------------*/
if (!function_exists('SaveWeixinPicUploadService_NEW')) {

    /**
     * 将微信保存的图片，保存到服务器指定地址
     *
     * @param $depid
     * @param $weixinImgUrl 微信服务器的id
     * @param $clientid
     *
     * @return string|微信服务器的id
     */
    function SaveWeixinPicUploadService_NEW($depid, $weixinImgUrl, $clientid)
    {
        $imgUrl = $weixinImgUrl;//图片默认地址，如果是普通 浏览器，则下面不会取到值，这个就不变，原样返回
        //保存图片到服务器
        if ($weixinImgUrl != "") {
            $appid = GetWeixinAppId($depid);
            $secret = GetWeixinAppSecret($depid);

            $access_token = Get_access_token($appid, $secret);
            //dump($access_token);
            $weixinImgUrl_array = explode(";", $weixinImgUrl);
            //dump($weixinImgUrl);
            // dump($weixinImgUrl_array);
            foreach ($weixinImgUrl_array as $value) {
                $targetName = getUploadFileAdd($depid) . "idpic/" . date('Ym', time()) . "/" . $clientid . "_" . $value . ".jpg";//相对地址
                //dump($targetName);
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $targetName;//实际地址
                $dirname = dirname($filePath);//目录名称
                //$weixinImgUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                $weixinImgUrl = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                //dump($weixinImgUrl);
                //打开输出缓冲区并获取远程图片
                ob_start();
                $context = stream_context_create(
                    array('http' => array(
                        'follow_location' => false // don't follow redirects
                    ))
                );
                readfile($weixinImgUrl, false, $context);
                $img = ob_get_contents();//将缓冲区图片 给了IMG
                ob_end_clean();


                //如果获取失败 就重新获取
                $re_array = json_decode($img, true);
                if ($re_array["errcode"] == 40001) {
                    $access_token = Get_access_token($appid, $secret, 1);
                    // $weixinImgUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                    $weixinImgUrl = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$value}";
                    //dump($weixinImgUrl);
                    //打开输出缓冲区并获取远程图片
                    ob_start();
                    $context = stream_context_create(
                        array('http' => array(
                            'follow_location' => false // don't follow redirects
                        ))
                    );
                    //    dump($weixinImgUrl);
                    readfile($weixinImgUrl, false, $context);
                    $img = ob_get_contents();//将缓冲区图片 给了IMG
                    ob_end_clean();
                } elseif ($re_array["errcode"] == 40007) {
                    return false;
                }

                //if($clientid=='1090') return false;
                //创建目录失败
                if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
                } else if (!is_writeable($dirname)) {
                }

                //移动文件
                if (!(file_put_contents($filePath, $img) && file_exists($filePath))) { //移动失败
                } else { //移动成功
                    $imgUrl = $targetName . ";";
                }

            }
            include_once(DWTINC . '/image.func.php');
            $return_str=ImageResize($filePath,800,800);//对图片进行压缩171105添加

            $imgUrl = rtrim($imgUrl, ";");
        }

        return $imgUrl;
    }
}


//下载微信图片
if (!function_exists('UploadWeixinPicForm')) {

    function downloadImageFromWeiXin($weixinImgUrl)
    {
        $ch = curl_init($weixinImgUrl);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);//只取BODY头
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
//dump($package);
//dump($httpinfo);
        return array_merge(array('body' => $package), array('header' => $httpinfo));

    }
}
if (!function_exists('UploadWeixinPicForm')) {

    /**
     * 微信上传的表单
     *
     * @param $depid
     * @param $formIdName
     *
     *
     * @return string
     */
    function UploadWeixinPicForm($depid, $formIdName)
    {
        $return_str = "";


        //如果是在微信浏览器中
        if (IsWeixinBrowser()) {
            // 微信浏览器上传
            $return_str .= "<div class=\"form-group\" >
                                       <div >
                                                  <button class=\"btn btn-primary\" id=\"chooseImage\" type=\"button\">选择图片</button>
                                                    <br>
                                                    <span id=\"imageInfo\"></span>
                                                    <input id=\"$formIdName\" name=\"$formIdName\" type=\"hidden\">
                                      </div>
                                      <br>
                                       <div >
                                            <button class=\"btn btn-primary\" type=\"submit\" id='submit_btn'   >保存图片</button>
                                        </div>
                           </div>";
        } else {
            //普通 浏览器上传图片
            $return_str .= " 请在微信中打开，然后上传";
        }
        $return_str .= "<script src=\"http://res.wx.qq.com/open/js/jweixin-1.0.0.js\"></script>";
        require_once(DWTINC . '/weixin/wxJSJDK.class.php');
        //获取JSSDK的相关参数
        //dump($DEPID);
        $jssdk = new wxJSSDK($depid);
        $signPackage = $jssdk->GetSignPackage();

        $return_str .= "
                            <script>
                                //var parameters =<?php echo $signPackage?>;
                                // parameters = $.parseJSON(parameters);
                                //console.log(parameters);
                                //console.log(11);
                                wx.config({
                                    debug: false,
                                    appId: '{$signPackage["appId"]}',
                                    timestamp: {$signPackage["timestamp"]},
                                    nonceStr: '{$signPackage["nonceStr"]}',
                                    signature: '{$signPackage["signature"]}',
                                    jsApiList: [
                                        'chooseImage',
                                        'previewImage',
                                        'uploadImage',
                                        'downloadImage'
                                    ]
                                });
                            
                            
                                //微信图片上传
                                wx.ready(function () {
                                    // 5 图片接口
                                    // 5.1 拍照、本地选图
                                    var images = {
                                        localId: [],
                                        serverId: []
                                    };
                                    document.querySelector('#chooseImage').onclick = function () {
                                        $('#imageInfo').html('');
                                        wx.chooseImage({
                                            count: 1,//只一张
                                            success: function (res) {
                                                images.localId = res.localIds;
                                                //alert('已选择 ' + res.localIds.length + ' 张图片');
                                                $('#chooseImage').html('重新选择图片');
                                                //上传图片
                                                var i = 0, length = images.localId.length;
                                                images.serverId = [];
                                                function upload() {
                                                    wx.uploadImage({
                                                        localId: images.localId[i],
                                                        success: function (res) {
                                                            i++;
                                                            //alert('已上传：' + i + '/' + length);
                                                            images.serverId.push(res.serverId);
                                                            if (i < length) {
                                                                upload();
                                                            }
                                                            var imgUrl_array = images.serverId;
                                                            var newimgUrls = imgUrl_array.join(';');//数组使用;分隔为字符串
                                                            $('#idpic').val(newimgUrls);
                                                            $('#imageInfo').html('<div class=\"text-danger\">已上传图片,请保存</div>');
                                                        },
                                                        fail: function (res) {
                                                            alert(JSON.stringify(res));
                                                        }
                                                    });
                                                }
                            
                                                upload();
                                            }
                                        });
                                    };
                                });
                                wx.error(function (res) {
                                    //console.log(res.errMsg);
                                });
                            </script>
                            ";


        return $return_str;
    }
}

/*--------------------------------微信上传图片------------------------------------------------------*/


if (!function_exists('messageToWeixin')) {
    /**发送模板消息
     *SendTemplateMessage原名称，要全系统排查一遍
     *
     * @param       $name     模板标题
     * @param       $clientid 用户ID
     * @param       $depid    公司ID
     * @param array $data     数据
     *
     * @return mixed|string
     */
    function messageToWeixin($name, $clientid, $depid, $data = array())
    {

        global $dsql;

        if (!DEBUG_LEVEL_ISSENDMSG) return "调试模式不发送";
        $openId = GetClientOpenID($clientid);//获取 客户信息 openid
        //判断是否有微信号
        if ($openId == "") return "未获取到会员OPENID";

        //获取模板和签名
        $query = "SELECT template_id,url FROM `#@__interface_weixinmsg_template`    WHERE depid='$depid' and  `name`='$name' ";
        //dump($query);
        $row = $dsql->GetOne($query);
        $template_id = $row['template_id'];
        $url = $row['url'];//从模板获取Url点击地址
        /*    {{first.DATA}} 订单号：{{keyword1.DATA}} 金额：{{keyword2.DATA}} 商品名称：{{keyword3.DATA}} 购买日期：{{keyword4.DATA}} {{remark.DATA}}*/

        $template = array(
            'touser' => $openId,//OPENID
            'template_id' => $template_id,//模板ID
            'url' => $url,//点击消息的URL
            'topcolor' => "#000000"//顶部颜色
        );

        $template = array_merge($template, $data);
        //dump($template);
        $template = urldecode(json_encode($template));


        $appId = GetWeixinAppId($depid);
        $appSecret = GetWeixinAppSecret($depid);
        $ACCESS_TOKEN = Get_access_token($appId, $appSecret);

        //发送之前在数据库中 创建记录
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$ACCESS_TOKEN";
        $result = http_request_json($url, $template);
        $result = json_decode($result, true);


        //dump($result);
        if ($result['errcode'] == 0) {
            $result_str = "发送成功";
            global $dsql;
            $senddate = time();//当前时间
            $sql = "INSERT INTO `#@__interface_weixinmsg_log` (`depid`,`clientid`, `body`, `senddate`, `type`) VALUES ('$depid', '$clientid', '$template', '$senddate', '$name');";
            $dsql->ExecuteNoneQuery($sql);
        } else {
            require_once DWTINC . '/weixin/pay/log.php';
            //初始化日志
            $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '.log');
            $log = Log::Init($logHandler, 15);
            Log::DEBUG("query:" . json_encode($result));
            $result_str = "发送失败";
        }
        //发送之后，要把发送的结果，更新到数据库中
        return $result_str;
    }
}


if (!function_exists('SendTemplateMessage')) {
    /**发送模板消息的前导数据处理
     *
     * @param       $name               模板标题
     * @param       $clientid           用户ID
     * @param       $depid              公司ID
     * @param array $weixinMsgDataArray 数据
     *
     * @return mixed|string
     */
    function SendTemplateMessage($name, $clientid, $depid, $weixinMsgDataArray = array())
    {
        //dump($weixinMsgDataArray);
        $data = $return_info = "";
        if ($name == "旅游订单预订成功通知") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),
                        'color' => "#000000"
                    ),
                    'OrderID' => array(
                        'value' => urlencode($weixinMsgDataArray["ordernum"]),
                        'color' => "#000000"
                    ),
                    'PkgName' => array(
                        'value' => urlencode($weixinMsgDataArray["goodsname"]),
                        'color' => "#000000"
                    ),
                    'TakeOffDate' => array(
                        'value' => urlencode($weixinMsgDataArray["godate"]),
                        'color' => "#000000"
                    ),
                    'Remark' => array(
                        'value' => urlencode($weixinMsgDataArray["remark"]),
                        'color' => "#000000"
                    )
                )
            );
        }

        if ($name == "新订单生成通知") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),/*加商品类型 直通车会员卡 旅游线路*/
                        'color' => "#000000"
                    ),
                    'OrderId' => array(
                        'value' => urlencode($weixinMsgDataArray["OrderId"]),
                        'color' => "#000000"
                    ),
                    'ProductId' => array(
                        'value' => urlencode($weixinMsgDataArray["ProductId"]),
                        'color' => "#000000"
                    ),
                    'ProductName' => array(
                        'value' => urlencode($weixinMsgDataArray["ProductName"]),
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode("[直通车]感谢您的使用"),
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "会员充值通知") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["first"]),
                        'color' => "#000000"
                    ),
                    /*                    'accountType' => array(
                                            'value' => urlencode("会员姓名"),
                                            'color' => "#000000"
                                        ),
                                        'account' => array(
                                            'value' => urlencode("李小小"),
                                            'color' => "#000000"
                                        ),*/
                    'amount' => array(
                        'value' => urlencode($weixinMsgDataArray["amount"]),
                        'color' => "#000000"
                    ),
                    'result' => array(
                        'value' => urlencode("充值成功"),
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode(""),
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "返现到账通知") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),
                        'color' => "#000000"
                    ),
                    'order' => array(
                        'value' => urlencode($weixinMsgDataArray["order"]),
                        'color' => "#000000"
                    ),
                    'money' => array(
                        'value' => urlencode($weixinMsgDataArray["money"]),
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode(""),/*暂时不要内容*/
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "积分到帐提醒") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),
                        'color' => "#000000"
                    ),
                    'keyword1' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword1"]),
                        'color' => "#000000"
                    ),
                    'keyword2' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword2"]),/*可用积分余额*/
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode($weixinMsgDataArray["remark"]),
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "提现成功通知") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),
                        'color' => "#000000"
                    ),
                    'keyword1' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword1"]),
                        'color' => "#000000"
                    ),
                    'keyword2' => array(
                        'value' => urlencode("微信钱包"),/*账户*/
                        'color' => "#000000"
                    ),
                    'keyword3' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword3"]),/*付款时间*/
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode(""),/*暂时没用*/
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "车辆安排提醒") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["first"]),
                        'color' => "#000000"
                    ),
                    'keyword1' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword1"]),//订单号
                        'color' => "#000000"
                    ),
                    'keyword2' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword2"]),/*车辆牌号*/
                        'color' => "#000000"
                    ),
                    'keyword3' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword3"]),/*出发时间*/
                        'color' => "#000000"
                    ),
                    'keyword4' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword4"]),/*付款时间*/
                        'color' => "#000000"
                    ),
                    'keyword5' => array(
                        'value' => urlencode($weixinMsgDataArray["keyword5"]),/*电话*/
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode("请及时与服务人员联系，按时提车。"),/*暂时没用*/
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "行程变更通知") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),
                        'color' => "#000000"
                    ),
                    'keyword1' => array(
                        'value' => urlencode($weixinMsgDataArray["goodsname"]),//线路名称
                        'color' => "#000000"
                    ),
                    'keyword2' => array(
                        'value' => urlencode($weixinMsgDataArray["godate"]),/*日期*/
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode($weixinMsgDataArray["remark"]),/*通知信息内容*/
                        'color' => "#000000"
                    )
                )
            );
        }
        if ($name == "乘车卡续费提醒") {
            $data = array(
                'data' => array(
                    'first' => array(
                        'value' => urlencode($weixinMsgDataArray["frist"]),//姓名
                        'color' => "#000000"
                    ),
                    'name' => array(
                        'value' => urlencode($weixinMsgDataArray["name"]),//乘车卡号
                        'color' => "#000000"
                    ),
                    'expDate' => array(
                        'value' => urlencode($weixinMsgDataArray["expDate"]),/*到期日期*/
                        'color' => "#000000"
                    ),
                    'remark' => array(
                        'value' => urlencode($weixinMsgDataArray["remark"]),/*通知信息内容*/
                        'color' => "#000000"
                    )
                )
            );
        }

        if ($data != "") $return_info = messageToWeixin($name, $clientid, $depid, $data);
        return $return_info;
    }
}




