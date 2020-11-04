<?php
/**
 * 系统配置
 *
 * @version        $Id: sysBaseConfigInfo.php 1 22:28 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

if (empty($dopost)) $dopost = "";


//保存配置的改动
if($dopost=="save")
{

    $jbnum_max_100=$jbnum_max*100;
    $jbnum_min_100=$jbnum_min*100;

        $dsql->ExecuteNoneQuery("UPDATE `#@__goods_coupon` SET `jbnum_max`='$jbnum_max_100',`jbnum_min`='$jbnum_min_100',`isuse`='$isuse' WHERE id='1' ");

    ShowMsg("成功保存！", "couponConfigInfo.php");
    exit();
}
include DwtInclude('goods/couponConfigInfo.htm');

