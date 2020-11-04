<?php
/**
 * 删除
 *
 * @version        $Id: dep_del.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once('../config.php');

$id = trim(preg_replace("#[^0-9]#", '', $id));
if (empty($id)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}


//清空会员所有的信息





$dsql->ExecuteNoneQuery("DELETE FROM `#@__client_depinfos` WHERE clientid='$id';");
$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
ShowMsg("成功删除！", $$ENV_GOBACK_URL);
