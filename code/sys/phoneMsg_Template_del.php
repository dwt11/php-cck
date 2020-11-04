<?php
/**
 * 删除部门
 *
 * @version        $Id: dep_del.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');


$sql = "DELETE FROM `#@__interface_phonemsg_template` WHERE id='$id' ;";
$dsql->ExecuteNoneQuery($sql);


$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
ShowMsg("删除成功！", $$ENV_GOBACK_URL);
