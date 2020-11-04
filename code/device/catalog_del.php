<?php
/**
 * 删除分类
 *
 * @version        $Id: catalog_del.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once('../config.php');
require_once('catalog.class.php');
$id = trim(preg_replace("#[^0-9]#", '', $id));

$questr="SELECT reid FROM `#@__device_type` where  reid ='$id'";
$rowarc = $dsql->GetOne($questr);
if(is_array($rowarc))
{
	ShowMsg("删除失败,请先删除子分类！","-1");
	exit(); 
}


$ut = new DeviceTypeUnit();
if($ut->GetTotalDevice($id)>0)
{
	ShowMsg("删除失败,请先此分类中的设备！","-1");
	exit(); 
}

$dsql->ExecuteNoneQuery("DELETE FROM `#@__device_type` WHERE id='$id'");
ShowMsg("成功删除一个分类！","catalog.php");
exit();




