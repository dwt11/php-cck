<?php
require_once("include/config.php");


if (empty($dopost)) $dopost = '';


//AJAX 发送验证码过程  此过程 必须是UTF8格式
//$DEPID来自全局
//$tempid clientid来自调用页面传递
$return = SendPhoneMSG($mobilephone, $name, $clientid, $DEPID,$data=array());
echo $return;

