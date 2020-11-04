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

//权限值ID有可能出现小数位,所以不过滤了  140820
//$rank = trim(preg_replace("#[^0-9]#", '', $rank));


$questr = "SELECT usertype FROM `#@__sys_admin`
          LEFT JOIN #@__emp ON #@__emp.emp_id=#@__sys_admin.empid

 WHERE  `usertype` = '$rank'   AND #@__emp.emp_isdel=0";
$rowarc = $dsql->GetOne($questr);
//dump($questr);
if (is_array($rowarc)) {
    ShowMsg("删除失败,请先移除属于此权限组的登录用户！", "-1");
    exit();
}


$sql = "DELETE FROM `#@__sys_admintype` WHERE CONCAT(`rank`)='$rank' ;";
$dsql->ExecuteNoneQuery($sql);


ShowMsg("成功删除一个用户组!", "sysGroup.php");
exit();
