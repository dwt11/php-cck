<?php
/**
 * 将旅游子订单的人员删除不显示在 预约管理列表
 *
 * @version        $Id: dep_del.php 1 14:31 2010年7月12日
 * @package        DwtX.Administrator

 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once('../config.php');

$id = trim(preg_replace("#[^0-9]#", '', $id));

if ($id == '') {
    echo "参数无效";
    exit();
}
$query = "UPDATE `#@__order_addon_lycp` SET `isdel`='1' WHERE (`id`='$id')";
$dsql->ExecuteNoneQuery($query);
echo "删除成功";
exit();





