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
$dep_id = trim(preg_replace("#[^0-9]#", '', $dep_id));
require_once('depunit.class.php');
$questr="SELECT dep_reid FROM `#@__emp_dep` where  dep_reid =".$dep_id;
$rowarc = $dsql->GetOne($questr);
if(is_array($rowarc))
{
	ShowMsg("删除失败,请先删除子部门！","-1");
	exit(); 
}

$ut = new DepUnit();
if($ut->GetOnlyTotalEmp($dep_id)>0)
{
	ShowMsg("删除失败,请先删除此部门中的员工！","-1");
	exit(); 
}
if($ut->GetOnlyTotalClient($dep_id)>0)
{
	ShowMsg("删除失败,请先删除此部门中的会员！","-1");
	exit();
}

$ut->DelDep($dep_id);
ShowMsg("成功删除一个部门！","dep.php");
exit();
