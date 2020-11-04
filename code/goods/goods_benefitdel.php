<?php
/**
 * 删除分类
 *
 * @version        $Id: catalog_del.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once('../config.php');
 //$id = trim(preg_replace("#[^0-9]#", '', $id));


if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

$query = "UPDATE `#@__goods_benefit` SET `isdel`='1' WHERE ( id in($id))";
//dump($query);
$dsql->ExecuteNoneQuery($query);
ShowMsg("删除成功！", "goods_benefit.php?goodsid=$goodsid");
exit();




