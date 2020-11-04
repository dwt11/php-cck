<?php
/**
 *   ID: sys_function_noListDir.php
 * User: dell
 * Date: 2016-03-07 8:43
 * 不显示在系统功能列表中的文件夹
 */


$GLOBALS['$noListDirArray'] = array();
$GLOBALS['$noListDirArray'][] = "css";
$GLOBALS['$noListDirArray'][] = "data";
$GLOBALS['$noListDirArray'][] = "images";
$GLOBALS['$noListDirArray'][] = "js";
$GLOBALS['$noListDirArray'][] = "uploads";
$GLOBALS['$noListDirArray'][] = "include";
$GLOBALS['$noListDirArray'][] = "web";
$GLOBALS['$noListDirArray'][] = "baseconfig";
$GLOBALS['$noListDirArray'][] = "inputdate";
$GLOBALS['$noListDirArray'][] = "install";
$GLOBALS['$noListDirArray'][] = "down";
$GLOBALS['$noListDirArray'][] = "ui";
$GLOBALS['$noListDirArray'][] = "basicui";
$GLOBALS['$noListDirArray'][] = "app";
$GLOBALS['$noListDirArray'][] = "phoneMsg";
$GLOBALS['$noListDirArray'][] = "weixin";
$GLOBALS['$noListDirArray'][] = "lyapp";
$GLOBALS['$noListDirArray'][] = "phpqrcode";


//公司管理员不显示的权限分配功能列表(意思就是只有管理员可以使用的功能,不能再继续分给下级)
//保存的是数据库中的ID大类ID值x_sys_function
//第一级  商品管理 会员管理  订单管理 这样的
//210系统,公司不可以再给下级分配 系统权限
//$NoDisplyArray_1=array("210");
$groupSETNoDisply_1 = "210";


// 只在包含有"售卡点"相关的权限和部门时才起作用
//一级目录 208商品管理
//$NoDisplyArray_1_skd=array("208","200");
$groupSETNoDisply_1_skd = "208,200,238,241,297,246,301,336,337,272,307,310,338,305,217,342";
