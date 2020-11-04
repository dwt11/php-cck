<?php
/**
 * 微信菜单编辑
 *
 * @version        $Id: 20160504 09:17
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/request.class.php");


if (empty($dopost)) $dopost = '';


if (!isset($depid)) {
    ShowMsg("无效的运行参数", "-1");
    exit();
}


//dump("11",'9999');


/*从数据库获取微信菜单*/
$query = "SELECT * FROM `#@__interface_phonemsg_template` WHERE depid='$depid'   ORDER BY   id ASC";
$dsql->Execute('me', $query);
$template_array  = array();
while ($row = $dsql->getarray()) {
        $template_array[] = $row;
}
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
include DwtInclude('sys/phoneMsg_Template.htm');

