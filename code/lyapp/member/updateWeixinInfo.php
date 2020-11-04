<?php
require_once(dirname(__FILE__) . "/../include/config.php");

/*更新微信信息*/
//如果是在微信浏览器中，并且DEPID大于0,并且不是从login.php中登录过来的（用户在微信中使用网址直接访问时，不从微信校验登录，而从账户登录 ），则调用微信登录过程
$depid = $DEPID;
$questr11 = "SELECT photo,nickname  FROM #@__client_weixin WHERE clientid='$CLIENTID'    ";
$row11 = $dsql->GetOne($questr11);
$photo = $nickName = "";
if ($row11) {
    $photo = $row11["photo"];
    $nickName = $row11["nickname"];
}
if (IsWeixinBrowser() && $depid > 0 && $photo == "" && $nickName == "") {
    $dep_appid = GetWeixinAppId($depid);
    $dep_secret = GetWeixinAppSecret($depid);

    $questr11 = "SELECT openid  FROM #@__client_depinfos WHERE clientid='$CLIENTID'    ";
    $row22 = $dsql->GetOne($questr11);
    $OPENID =  "";
    if ($row22) {
        $OPENID = $row22["openid"];
    }


    $ACCESS_TOKEN = Get_access_token($dep_appid, $dep_secret);
    if ($ACCESS_TOKEN == "" || $OPENID == "") exit;
    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ACCESS_TOKEN&openid=$OPENID";
    $handle = fopen($url, "rb");
    if ($handle) {

        $contents = "";
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        $json_array = json_decode($contents, TRUE);
        $nickname = XSSClean(addslashes(Html2Text($json_array['nickname'])));
        $sex = XSSClean($json_array['sex']);
        $city = XSSClean($json_array['city']);
        $province = XSSClean($json_array['province']);
        $country = XSSClean($json_array['country']);
        $headimgurl = $json_array['headimgurl'];
        $sex_temp = "未知";
        if ($sex == 1) $sex_temp = "男";
        if ($sex == 2) $sex_temp = "女";

        //将微信 的内容绑定到手工的账户上
        $query = "UPDATE #@__client_weixin SET  nickname='$nickname',sex='$sex_temp',city='$city',province='$province',country='$country',photo='$headimgurl'     WHERE clientid='$CLIENTID' ";
        $dsql->ExecuteNoneQuery($query);
        $cfg_ml->fields["nickname"] = $nickname;
        $cfg_ml->fields["photo"] = $headimgurl;

    }


}







