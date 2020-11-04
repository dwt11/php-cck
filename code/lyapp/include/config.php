<?php
/**
 * @version        $Id: config.php 1 8:38 2010年7月9日
 * @package        DWTCMS.Member
 * @license        http://help.DWTcms.com/usersguide/license.html
 * @link           http://www.DWTcms.com
 */

//针对会员中心操作进行XSS过滤
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

$_GET = XSSClean($_GET);
$_POST = XSSClean($_POST);
$_REQUEST = XSSClean($_REQUEST);
$_COOKIE = XSSClean($_COOKIE);

require_once(dirname(__FILE__) . '/../../include/common.inc.php');
require_once(DWTINC . '/filter.inc.php');
require_once('memberlogin.class.php');
require_once(DWTINC . '/dwttemplate.class.php');
//系统配置参数,这里面有时区设置
//require_once(DEDEDATA . "/depconfig.cache.inc.php");//这个随后再启用

/*if(empty($did)) {
    showMsg("非法参数");
    exit;
}*/

//获得当前脚本名称，如果你的系统被禁用了$_SERVER变量，请自行更改这个选项
$dwtNowUrl = $s_scriptName = '';
$dwtNowUrl = GetCurUrl();//当前全地址
$dwtNowUrls = explode('?', $dwtNowUrl);
$s_scriptName = $dwtNowUrls[0];//当前地址 没参数
$gourl = empty($gourl) ? "" : RemoveXSS($gourl);
$code = empty($code) ? "" : RemoveXSS($code);//微信回传参数

//$sponsorid=0;
if (!empty($u)) {
    //上级ID不等于空，并且 不是当前登录用户
    PutCookie('DWTsponsorid', $u, 3600 * 24 * 7);
} /*else {
 //不清空推荐人ID 以免第一次打开系统时  在会员里注册微信会员时没有保存推荐人
    DropCookie('DWTsponsorid');
}*/
//if (GetCookie("DWTsponsorid") != "") $sponsorid = GetCookie("DWTsponsorid");
//echo ($sponsorid);


$DEPID = empty($did) ? $DEP_TOP_ID : RemoveXSS($did);//170207创建全局公司顶级ID，要用这个创建附件位置，微信 短信等的参数调用，如果没有获取到默认取17，
$DEPID = trim(preg_replace("#[^0-9]#", '', $DEPID));//161031修复，必须是数字

$cfg_ml = new MemberLogin($DEPID, $code);
$CLIENTID = $cfg_ml->M_ID;//全局公用变量

//判断优惠券是否未查看,存入COOKIES,到别的页面JS判断,然后显示红包页面
$query_coupon = "SELECT isview FROM #@__clientdata_coupon  WHERE isuse='0' AND clientid='$CLIENTID'";
$row_coupon = $dsql->getone($query_coupon);
if (isset($row_coupon["isview"]) && $row_coupon["isview"] == 0) {
    PutCookie('DWTis_coupon_view', "1",3600*24);
    //dump(GetCookie('DWTis_coupon_view'));
}
/**
 *  验证用户是否注册和登录
 *
 */
function CheckRank()
{
    global $dsql, $cfg_ml;
    $clientid = $cfg_ml->M_ID;
    $gourl = urlencode(GetCurUrl());

    if (empty($clientid)) {
        header("location:/lyapp/login.php?gourl=$gourl");
        //showMsg("请重新登录", "/lyapp/login.php?gourl=$gourl");
        exit;
    }


    $minfos = $dsql->GetOne("SELECT mobilephone_check FROM `#@__client` WHERE id='$clientid'; ");
    $mobilephone_check = $minfos["mobilephone_check"];
    if ($mobilephone_check == 0) {
        //判断是否手机验证
        header("location:/lyapp/phone.php?gourl=$gourl");
        exit();
    }
}



