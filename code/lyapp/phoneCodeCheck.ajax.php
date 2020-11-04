<?php
require_once("include/config.php");
if (empty($clientid)) $clientid = '';
if (empty($isyz)) $isyz = '';

$phoenISuse = ValidatePhoneISon($mobilephone, $clientid,$isyz);//新的手机号 是否已经使用
//dump($phoenISuse);
if ($phoenISuse == "手机号可用") {
    echo "true";
}else{
    echo "false";
}
exit;

