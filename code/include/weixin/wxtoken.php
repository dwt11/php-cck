<?php

/*微信推送接收页面*/

require_once(dirname(__FILE__) . "/../../include/common.inc.php");
$questr = "SELECT TOKEN,msg,searchrx,menurx,guanzhu_return FROM `#@__interface_weixin` WHERE isdel=0 and id ='10' ";
$rowarc = $dsql->GetOne($questr);
$msg = $rowarc['msg'];//未搜索到内容时的回复
$searchrx = $rowarc['searchrx'];//搜索到时的条数
$menurx = $rowarc['menurx'];//菜单点击回复数//暂无用
$guanzhu_return = $rowarc['guanzhu_return'];//关注后的自动回复
$TOKEN = $rowarc['TOKEN'];
define("TOKEN", $TOKEN);

$x = strpos($msg, "}") + 1;//切割无匹配时的自动回复
$msg1 = substr($msg, 0, strrpos($msg, '{'));
$msg2 = substr($msg, $x);
define("msg1", $msg1);
define("msg2", $msg2);
define("searchrx", $searchrx);
define("menurx", $menurx);
define("guanzhu_return", $guanzhu_return);
define("top_dep_id", $DEP_TOP_ID);


$wechatObj = new wechatCallback();
$wechatObj->valid();

class wechatCallback
{
    private $items = '';
    private $articleCount = 0;//自动匹配到的条目 数量
    private $keyword = '';
    private $fromUsername = '';//微信回传回来的用户的OPENID
    private $eventKey = '';//微信回传回来的参数,这里设定的是上级用户的ID(client)


    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //if($this->checkSignature()){
        ob_clean();//必须加此句,否则 无法TOKEN验证通过
        echo $echoStr;

        $this->responseMsg();
        exit;
        //}
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $this->fromUsername = $fromUsername;//此处是获得的客户OPENid(点击获取)
        $this->eventKey = $postObj->EventKey;//此处是获得的客户上级ID
        $toUsername = $postObj->ToUserName;
        $RX_TYPE = trim($postObj->MsgType);
        $time = time();


        /* require_once DWTINC . '/weixin/pay/log.php';
         //初始化日志
         $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_erwm.log');
         $log = Log::Init($logHandler, 15);
         Log::DEBUG("\r\npostObj:" . json_encode($postObj));*/

        //exit();


        //返回微信 发给用户的模板
        //文字 模板
        $textTpl = "<xml>
							<ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
							<FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
							<CreateTime>" . $time . "</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
        //图片模板
        $picTpl = "<xml>
							<ToUserName><![CDATA[" . $fromUsername . "]]></ToUserName>
							<FromUserName><![CDATA[" . $toUsername . "]]></FromUserName>
							<CreateTime>" . $time . "</CreateTime>
							<MsgType><![CDATA[news]]></MsgType>
							<Content><![CDATA[]]></Content>
							<ArticleCount>%d</ArticleCount>
							<Articles>%s</Articles>
							<FuncFlag>1</FuncFlag>
							</xml>";

        if (!empty($postStr)) {
            switch ($RX_TYPE) {
                case "text":
                    //用户在微信中输入的消息
                    $this->keyword = strtolower($postObj->Content);//接收用户消息
                    $this->keyword = XSSClean(addslashes(Html2Text($this->keyword)));//170628增加安全检测
                    $messtype = 1;
                    break;
                case "event":
                    //事件
                    //用户点击菜单的,菜单内容   或关注事件
                    $this->keyword = $this->handleEvent($postObj);
                    $messtype = 0;
                    break;
                default:
                    $resultStr = "Unknow msg type: " . $RX_TYPE;
                    $messtype = 0;
                    break;
            }


            if ($this->keyword == 'hi' || $this->keyword == '您好' || $this->keyword == '你好' || $this->keyword == 'hello2bizuser') {
	    //???这里已经在微信数据表配置了,未做引用
                $contentStr = "请发送您想知道的内容！
若无法解决请拨打：12345678
或添加微信：12345678";//自定义欢迎回复;
                echo sprintf($textTpl, $contentStr);
            } else if (!empty($this->keyword)) {
                if ($messtype) {
                    $this->rx_search();
                    //搜索的返回搜索结果
                    if ($this->articleCount == 0) {
                        //如果没有匹配的内容 ,则返回自定义的提示内容
                        $contentStr = constant("msg1") . "{$this->keyword}" . constant("msg2");
                        echo sprintf($textTpl, $contentStr);
                    } else {
                        //返回商品和文档条目
                        /* require_once DWTINC . '/weixin/pay/log.php';
                         //初始化日志
                         $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_erwm.log');
                         $log = Log::Init($logHandler, 15);
                         Log::DEBUG("echoStr:" . $this->articleCount.$this->items);*/
                        echo sprintf($picTpl, $this->articleCount, $this->items);
                    }
                } else {
                    // $this->m_search();//用户点击菜单后的事件,没有用
                    //事件的返回事件结果

                    /*require_once DWTINC . '/weixin/pay/log.php';
                    //初始化日志
                    $logHandler = new CLogFileHandler(DWTPATH . "/data/debuglog0408/" . date('Y-m-d') . '_erwm.log');
                    $log = Log::Init($logHandler, 15);
                    Log::DEBUG("echoStr:" . $this->keyword);*/
                    echo sprintf($textTpl, $this->keyword);
                }

            }


        } else {
            echo "";
            exit;
        }
    }


    //用户扫描关注或关注后扫描 创建或更新用户推荐人
    private function clientInfo($EventKey_sponsorid, $OpenId)
    {
        //如果用户不存在则创建
        $clientid_asdfs = GetOPENID_INdate($OpenId);
        if (!$clientid_asdfs > 0) {


            $nickname = $sex = $city = $province = $country = $sex_temp = $headimgurl = "";

            //获取用户基本信息
            $userinfo_array=getWEIXIN_userinfo($OpenId,constant("top_dep_id"));
            if(isset($userinfo_array["nickname"]))$nickname=$userinfo_array["nickname"];
            if(isset($userinfo_array["city"]))$city=$userinfo_array["city"];
            if(isset($userinfo_array["province"]))$province=$userinfo_array["province"];
            if(isset($userinfo_array["country"]))$country=$userinfo_array["country"];
            if(isset($userinfo_array["headimgurl"]))$headimgurl=$userinfo_array["headimgurl"];
            if(isset($userinfo_array["sex_temp"]))$sex_temp=$userinfo_array["sex_temp"];

            //openid不存在,则创建用户
            //插入到客户扩展表
            $sponsorid = 0;
            if ($EventKey_sponsorid != "") $sponsorid = $EventKey_sponsorid;
            //dump($sponsorid);
            $clientid_asdfs = RegClient(
                $realname = "", $mobilephone = "", $mobilephone_check = "", $address = "", $tag = "", $description = "", $from = "微信",
                $idcard = "", $operatorid = "", $sponsorid,
                $pwd = "",
                $depid = constant("top_dep_id"), $openid = $OpenId, $AppId = "",
                $nickname = $nickname, $sex_temp = $sex_temp, $city = $city, $province = $province, $country = $country, $headimgurl = $headimgurl
            );
            CreateCoupon($clientid_asdfs);//生成优惠券
        }


        UPDATEclientSponsorid($clientid_asdfs, $EventKey_sponsorid);//更新用户的上级会员 ,要判断 是否符合更换的条件
        $sponsorid = getOneClientSponsorid($clientid_asdfs);//获取上级编号
        $sponsor_realname = "";
        if ($sponsorid > 0) $sponsor_realname = getOneCLientRealName($sponsorid);//获取上级姓名


        //获取推荐人姓名
        return $sponsor_realname;
    }

    private function handleEvent($object)
    {
        switch ($object->Event) {

            case "subscribe":
                //第一次扫描关注
                if (isset($object->FromUserName)) {
                    //EventKey这个是二维码中传递的参数(客户上级ID)
                    //$contentStr = "关注二维码场景 " . $object->EventKey;
                    $EventKey_sponsorid = str_replace("qrscene_", "", $object->EventKey);
                    $OpenId = $object->FromUserName;
                    $sponsor_realname = $this->clientInfo($EventKey_sponsorid, $OpenId);

                    $this->keyword = constant("guanzhu_return");//回复消息
                    if ($sponsor_realname != "") $this->keyword .= "
                    推荐人:" . $sponsor_realname;//回复消息
                }
                break;
            case "SCAN":
                //关注后扫描
                //$contentStr = "扫描 " . $object->EventKey;
                //------要实现统计分析，则需要扫描事件写入数据库，这里可以记录 EventKey及用户OpenID，扫描时间
                if (isset($object->FromUserName)) {
                    //EventKey这个是二维码中传递的参数(客户上级ID)
                    //$contentStr = "关注二维码场景 " . $object->EventKey;
                    $EventKey_sponsorid = $object->EventKey;
                    $OpenId = $object->FromUserName;
                    $sponsor_realname = $this->clientInfo($EventKey_sponsorid, $OpenId);
                    $this->keyword = constant("guanzhu_return");//回复消息
                    if ($sponsor_realname != "") $this->keyword .= "
                    推荐人:" . $sponsor_realname;//回复消息
                }
                break;
            case "CLICK":
                //点击菜单
                $this->keyword = $object->EventKey;
                break;
            default :
                $this->keyword = "未知事件: " . $object->Event;
                break;
        }
        return $this->keyword;
    }


    private function rx_search()
    {//根据用户回复的内容模糊搜索文章
        global $dsql;

        //获取商品

        $weixin_post = $dsql->SetQuery("SELECT id,goodsname,litpic FROM `#@__goods` WHERE goodsname LIKE '%" . $this->keyword . "%' AND `status`='0'  ORDER BY id DESC LIMIT 10");
        //按文章权重排序
        $items = '';
        $dsql->Execute();
        while ($weixin_post = $dsql->GetObject()) {
            $title = $weixin_post->goodsname;
            $excerpt = $weixin_post->goodsname;//获取摘要
            $thumb = $weixin_post->litpic;//获取缩略图;
            $link = '/lyapp/goods/goods_view.php?id=' . $weixin_post->id;
            $items = $items . $this->get_item($title, $excerpt, $thumb, $link);
            $this->articleCount++;
        }


        //获取 文章
        $weixin_post = $dsql->SetQuery("SELECT  `#@__archives`.id,`#@__archives`.title,`#@__archives`.litpic FROM `#@__archives`     WHERE title LIKE '%" . $this->keyword . "%'  AND issend > -1  ORDER BY id DESC LIMIT 10");
        //按文章权重排序
        $dsql->Execute();
        while ($weixin_post = $dsql->GetObject()) {
            $title = $weixin_post->title;
            $excerpt = $weixin_post->title;//获取摘要
            $thumb = $weixin_post->litpic;//获取缩略图;
            $link = '/lyapp/archives/archives_view.php?aid=' . $weixin_post->id;
            $items = $items . $this->get_item($title, $excerpt, $thumb, $link);
            $this->articleCount++;
        }

        if ($this->articleCount > constant("searchrx")) $this->articleCount = constant("searchrx");
        $this->items = $items;
    }


    /*private function m_search()
    {
        //暂时没用
        //菜单事件按栏目名精确搜索
        global $dsql;
        $weixin_posts = $dsql->SetQuery("Select  `#@__archives`.id,`#@__archives`.title,`#@__archives`.description,`#@__archives`.litpic,`#@__archives`.typeid,`#@__archives`.weight from `#@__archives` INNER JOIN `#@__arctype` ON `#@__archives`.typeid = `#@__arctype`.id where `#@__arctype`.typename = '" . $this->keyword . "' order by weight desc LIMIT 10");
        //按文章权重排序
        $items = '';
        $dsql->Execute();

        while ($weixin_post = $dsql->GetObject()) {
            //$title = $weixin_post->title.$this->fromUsername;//此处是获得的客户OPENid(点击获取)
            $title = $weixin_post->title;
            $excerpt = $weixin_post->description;//获取摘要
            $thumb = $weixin_post->litpic;//获取缩略图;
            $link = '/wap.php?action=article&id=' . $weixin_post->id;
            $items = $items . $this->get_item($title, $excerpt, $thumb, $link);
            $this->articleCount++;
        }

        if ($this->articleCount > menurx) $this->articleCount = menurx;
        $this->items = $items;
    }*/

    private function get_item($title, $description, $picUrl, $url)
    {
        if (!$description) $description = $title;
        return
            '
			<item>
			<Title><![CDATA[' . $title . ']]></Title>
			<Description><![CDATA[' . $description . ']]></Description>
			<PicUrl><![CDATA[http://' . $_SERVER['HTTP_HOST'] . $picUrl . ']]></PicUrl>
			<Url><![CDATA[http://' . $_SERVER['HTTP_HOST'] . $url . ']]></Url>
			</item>
			';
    }

    //验证签名
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = constant("TOKEN");
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}


function XSSClean($val)
{
    global $cfg_soft_lang;
    if ($cfg_soft_lang == 'gb2312') gb2utf8($val);
    if (is_array($val)) {
        while (list($key) = each($val)) {
            if (in_array($key, array('tags', 'body', 'DWT_fields', 'DWT_addonfields', 'dopost', 'introduce'))) continue;
            $val[$key] = XSSClean($val[$key]);
        }
        return $val;
    }
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    $val = str_replace("`", "‘", $val);
    $val = str_replace("'", "‘", $val);
    $val = str_replace("\"", "“", $val);
    $val = str_replace(",", "，", $val);
    $val = str_replace("(", "（", $val);
    $val = str_replace(")", "）", $val);

    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    if ($cfg_soft_lang == 'gb2312') utf82gb($val);
    return $val;
}

/*用户授权关注后,直接获取用户信息,无需再次授权*/
function getWEIXIN_userinfo($openid,$dep_id)
{
    //获取用户基本信息
    $return_array=array();
    $dep_appid = GetWeixinAppId($dep_id);
    $dep_secret = GetWeixinAppSecret($dep_id);
    $ACCESS_TOKEN = Get_access_token($dep_appid, $dep_secret);
    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ACCESS_TOKEN&openid=$openid";
    $handle = fopen($url, "rb");
    if ($handle) {

        $contents = "";
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        $json_array = json_decode($contents, TRUE);
        $return_array["nickname"] = XSSClean(addslashes(Html2Text($json_array['nickname'])));
        $sex = XSSClean($json_array['sex']);
        $return_array["city"] = XSSClean($json_array['city']);
        $return_array["province"] = XSSClean($json_array['province']);
        $return_array["country"] = XSSClean($json_array['country']);
        $return_array["headimgurl"] = $json_array['headimgurl'];
        $return_array["sex_temp"] = "未知";
        if ($sex == 1) $return_array["sex_temp"] = "男";
        if ($sex == 2) $return_array["sex_temp"] = "女";
    }
    return $return_array;

}