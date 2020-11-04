<?php
/**
 * 删除栏目
 *
 * @version        $Id: catalog_del.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');

require_once('catalogUnit.class.php');
$id = trim(preg_replace("#[^0-9]#", '', $id));







$questr="SELECT reid FROM `#@__archives_type` where  reid =".$id;
$rowarc = $dsql->GetOne($questr);
if(is_array($rowarc))
{
	ShowMsg("删除失败,请先删除子栏目！","-1");
	exit(); 
}


$ut = new TypeUnit();

if($ut->GetTotalArc($id)>0)
{
	ShowMsg("删除失败,请先此栏目中的文档！","-1");
	exit(); 
}


	if($GLOBAMOREDEP)
    { //将权限值从dep_plus中删除


		$questr="SELECT depid FROM `#@__emp_dep_plus` WHERE FIND_IN_SET('".$id."',archivesids) ";
		$rowarc = $dsql->GetOne($questr);
		//dump($questr);
		if(is_array($rowarc))
		{
			$depid=$rowarc["depid"];
			
			$questr="SELECT archivesids FROM `#@__emp_dep_plus` where  `depid` = '$depid' ";
			$rowarc = $dsql->GetOne($questr);
			$archivesids_array=explode(",",$rowarc["archivesids"]);

			
				  //将删除的RANK从DEP_PLUS中移除
				 if(array_search($id,$archivesids_array)!==false)
				 {
					  //dump($admintypeid_array);
					 unset($archivesids_array[array_search($id,$archivesids_array)]);//如果替换掉里面的 其他-1
					 $archivesids = join(',',$archivesids_array) ;   
					 $dsql->ExecuteNoneQuery("UPDATE `#@__emp_dep_plus` SET archivesids='$archivesids' WHERE depid='$depid'");
				}
			
		}

	}


$ut->DelType($id);
ShowMsg("成功删除一个栏目！","catalog.php");
exit();




