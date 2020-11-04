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


$id = trim(preg_replace("#[^0-9]#", '', $id));


//????160818这里要检查一下 是否有用户在使用此 字段
/*$questr="SELECT empid FROM `#@__sys_admin` where  CONCAT(`empid`)='$aid' ";
$rowarc = $dsql->GetOne($questr);
if(is_array($rowarc))
{
	ShowMsg("删除失败,请先删除属于此员工的登录用户！","-1");
	exit(); 
}*/
$dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_goods` WHERE id='$id';");
$ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
ShowMsg("删除成功！",$$ENV_GOBACK_URL);
exit();
