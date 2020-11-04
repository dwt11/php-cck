<?php
/**
 * 编辑日志
 *
 * @version        $Id: log_edit.php 1 8:48 13日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once(dirname(__FILE__) . "/../include/config.php");
CheckRank();
if (empty($id)) {
    showMsg("非法参数", "feedback.php");
    exit;
}

$query = "SELECT * FROM `#@__feedback`  WHERE  id='$id' and clientid='$CLIENTID'";
$row = $dsql->GetOne($query);
if (!is_array($row)) {
    ShowMsg("无权删除此信息!", "-1");
    exit();
}

$sql = "DELETE FROM #@__feedback where id=$id";
$dsql->ExecuteNoneQuery($sql);
ShowMsg("删除成功！", "feedback.php");
exit();
