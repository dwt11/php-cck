<?php
/**
 * 删除部门
 *
 * @version        $Id: dep_del.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once('../config.php');

$id = trim(preg_replace("#[^0-9]#", '', $id));

if ($id == '') {
    ShowMsg("参数无效！", $ENV_GOBACK_URL);
    exit();
}
$query = "DELETE FROM `#@__sys_goods_order` WHERE id='$id'";
if ($dsql->ExecuteNoneQuery($query)) {

    $query = "DELETE FROM `#@__sys_goods_orderdetails` WHERE orderId='$id'";
    $dsql->ExecuteNoneQuery($query);


    ShowMsg("成功删除指定的订单！", $ENV_GOBACK_URL);
    exit();
} else {
    ShowMsg("删除失败！", $ENV_GOBACK_URL);
    exit();

}




