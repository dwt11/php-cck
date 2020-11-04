<?php
//这个是一期的接口,这里只跳转
require_once("include/config.php");
$dwtNowUrls = explode('?', $dwtNowUrl);
$s_scriptName_p = $dwtNowUrls[1];//当前地址参数
//跳转并传递参数
header("location:goods/goods_view.php?{$s_scriptName_p}");

//echo $s_scriptName_p;