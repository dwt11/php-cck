<?php

/**
 *   ID: wxJSJDK.PHP.php
 * User: dell
 * Date: 2016-06-24 15:03
 *
 * 微信JS-SDK PHP Demo JS接口安全域名
 *JS-SDK使用权限签名算法
 *
 *
 * 用于 自定义分享接口 jsapi_ticket 分享到朋友圈 分享给朋友 分享到QQ
 * 拍照 录音
 *
 *
 */
class wxJSSDK
{
    private $appId;
    private $appSecret;

    public function __construct($depid)
    {
        $this->appId = GetWeixinAppId($depid);
        $this->appSecret = GetWeixinAppSecret($depid);
    }

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            //"url" => $url,
            "signature" => $signature,
            //"rawString" => $string
        );
        return $signPackage;
        //return json_encode($signPackage);
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket()
    {
        $time = time();
        //如果上一次获取 没有失效  则直接返回全局的
//    echo $_SESSION[$appid . 'access_token_oldtime'];
        if (!empty($_SESSION[$this->appId . 'ticket']) && $_SESSION[$this->appId . 'ticket_time'] + 7000 > $time) {
            return $_SESSION[$this->appId . 'ticket'];
        }


        $ACCESS_TOKEN = Get_access_token($this->appId, $this->appSecret);
        if ($ACCESS_TOKEN != "" && $ACCESS_TOKEN != "false") {

            //dump($ACCESS_TOKEN);
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$ACCESS_TOKEN&type=jsapi";
            $json = http_request_json($url); //这个地方不能用file_get_contents
            $data = json_decode($json, true);
            //dump($data);
            if (isset($data['ticket'])) {
                //获取 新的 值
                $_SESSION[$this->appId . 'ticket'] = $data['ticket'];
                $_SESSION[$this->appId . 'ticket_time'] = $time;
                return $data['ticket'];
            } else {
                return "false";
            }
        }
    }
}