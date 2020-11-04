<?php
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
/*$rankInfo = GetClientType("rank", $CLIENTID);
$rankInfo_array = explode(",", $rankInfo);
if (!in_array("直通车", $rankInfo_array) && !in_array("合伙人", $rankInfo_array)) {
    ShowMsg("您必须先购买直通车或成为合伙人后，才可以进行推广!", "-1");
    exit();
}*/


//171104原任何人都可推广,现改为只有身份的人才可以
$rankInfo = GetClientType("rank", $CLIENTID);
if ($rankInfo == "") {
    ShowMsg("您必须先购买直通车或成为合伙人后，才可以进行推广!", "/lyapp/");//不能用-1,跳转 在微信中直接输出地址,无法跳转
    exit();
}


//二维码网址
//$qr="http://x.com/ly_carcard_show.php?sponsorid=$cfg_ml->M_ID";
//dump($cfg_ml->M_ID);
$tx_img_path = $cfg_ml->fields["photo"];
//$tx_img_path="http://wx.qlogo1111.cn/mmopen/ovZ9RJSRkBaPaPeKOPg4V9MdKkHp5cyvDMHHpydYXBIxxHOG6Nefe0hNTDEraEO7orBB70qDF7SGEGr4XZyrcO5eic4HwdQo4/0";
//dump($tx_img_path);
//echo "<img src='".$tx_img_path."'>";
//dump($cfg_ml);
//缓存微信头像
put_file_from_url_content($tx_img_path, "tx-" . $CLIENTID . $cfg_ml->fields["senddate"] . '.png', "../../" . getUploadFileAdd($DEPID) . "qr/");


function put_file_from_url_content($url, $saveName, $path)
{
    // 设置运行时间为无限制
    set_time_limit(0);

    $url = trim($url);
    $curl = curl_init();
    // 设置你需要抓取的URL
    curl_setopt($curl, CURLOPT_URL, $url);
    // 设置header
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);   //超时时间，超过时间跳过 只需要设置一个秒的数量就可以
    // 运行cURL，请求网页
    $file = curl_exec($curl);


    // 关闭URL请求
    curl_close($curl);
    // 将文件写入获得的数据
    if ($file) {
        $filename = $path . $saveName;
        $write = @fopen($filename, "w");
        if ($write == false) {
            return false;
        }
        if (fwrite($write, $file) == false) {
            return false;
        }
        if (fclose($write) == false) {
            return false;
        }
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <meta name="viewport" content="width=device-width,minimum-scale=1,user-scalable=no,maximum-scale=1,initial-scale=1">
    <title> 免费旅游直通车</title>
    <link href="../../ui/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../ui/css/style.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet" media="screen">
</head>

<body>
<div class="main">
    <div class="text-center  yellow-bg  font-bold"> 保存下面的图片到手机，然后发布到朋友圈或群聊中<br>领取金币推广奖励。</div>
    <img src="gd_weixin.php" style="max-width: 100%;">
</div>
<script src="../js/weixinHideOptionMenu.js"></script>
</body>
</html>
