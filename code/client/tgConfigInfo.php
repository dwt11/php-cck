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
if ($dopost == "save") {


    $t1=str_replace("@","",$t1);
    $t2=str_replace("@","",$t2);
    $t3=str_replace("@","",$t3);
    $t4=str_replace("@","",$t4);
    $t5=str_replace("@","",$t5);
    $dsql->ExecuteNoneQuery("UPDATE `#@__tg_config` SET `t1`='$t1',`t2`='$t2',`t3`='$t3',`t4`='$t4',`t5`='$t5',`backpic`='$pic'  WHERE id='1' ");

    echo ("成功保存！");
    exit();
}
include DwtInclude('client/tgConfigInfo.htm');

