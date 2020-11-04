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


$emp_id = trim(preg_replace("#[^0-9]#", '', $emp_id));


//151028注销此段,不提示用户  直接将员工更新为删除掉  因为不要删除登录信息  登录信息是主要根数据
/*$questr="SELECT empid FROM `#@__sys_admin` where  CONCAT(`empid`)='$aid' ";
$rowarc = $dsql->GetOne($questr);
if(is_array($rowarc))
{
	ShowMsg("删除失败,请先删除属于此员工的登录用户！","-1");
	exit(); 
}*/

$dsql->ExecuteNoneQuery("UPDATE `#@__emp` SET emp_isdel=1 WHERE emp_id='$emp_id';");

$ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
ShowMsg("删除成功！",$$ENV_GOBACK_URL);
exit();
