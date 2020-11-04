<?php
/**
 * 部门操作
 *
 * @version        $Id: dep.do.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');
if(empty($dopost))
{
    ShowMsg("对不起，请指定部门参数！","dep.php");
    exit();
}
$typeid = empty($typeid) ? 0 : intval($typeid);


/*--------------------------
//查看 部门包含的员工
function listEmp();
---------------------------*/
 if($dopost=="listEmp")
{
    if(empty($gurl)) $gurl = 'emp.php';
    header("location:{$gurl}?emp_dep={$did}");
    exit();
}

/*-----------
获得子类的内容-----菜单里用  暂无用
function GetSunListsMenu();
-----------
else if($dopost=="GetSunListsMenu")
{
    $userChannel = $GLOBALS['CUSERLOGIN']->getUserChannel();
    require_once("depunit.class.php");
    AjaxHead();
    PutCookie('lastCidMenu',$typeid,3600*24,"/");
    $tu = new depUnit($userChannel);
    $tu->LogicListAllSunType($typeid,"　");
}*/
/*-----------
获得子类的内容
function GetSunLists();
-----------*/
else if($dopost=="GetSunLists")
{
    require_once("depunit.class.php");
    AjaxHead();
    PutCookie('lastCid', $typeid, 3600*24, "/");
    $tu = new depUnit();
    $tu->dsql = $dsql;
    echo "    <table width='100%' border='0' cellspacing='0' cellpadding='0'>\r\n";
    $tu->LogicListAllSunDep($typeid, "　");
    echo "    </table>\r\n";
    $tu->Close();
}
