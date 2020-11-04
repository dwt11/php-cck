<?php
/**
 * 删除部门
 *
 * @version        $Id: dep_del.php 1 14:31 2010年7月12日
 * @package        DwtX.Administrator

 * @license        http://help.dwtx.com/usersguide/license.html
 * @link           http://www.dwtx.com
 */
require_once('../config.php');

$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}
$query = "UPDATE `#@__client_depinfos` SET `isdel`='1' WHERE (`clientid`='$id')";
$dsql->ExecuteNoneQuery($query);
ShowMsg("禁用成功！", $$ENV_GOBACK_URL);
exit();





