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

require_once(DWTINC.'/oxwindow.class.php');
$id = trim(preg_replace("#[^0-9]#", '', $id));







$questr="SELECT reid FROM #@__archives_type where  channeltype =".$id;
$rowarc = $dsql->GetOne($questr);
if(is_array($rowarc))
{
	ShowMsg("删除失败,请先删除引用此模型的分类！","-1");
	exit(); 
}

$addtable="";//数据表名称
$addcon="";//发布表单文件名称
$editcon="";//编辑表单文件名称
$mancon="";//列表文件名称

//行到相关表和文件的信息
$row = $dsql->GetOne("SELECT * FROM #@__archives_channeltype WHERE id='$id' ");
if(is_array($row))
{
	$addtable=$row['addtable'];//数据表名称
	$addcon=$row['addcon'];//发布表单文件名称
	$editcon=$row['editcon'];//编辑表单文件名称
	$mancon=$row['mancon'];//列表文件名称
}



//删除数据表
if($addtable!="")
{
	//如果此表存在就删除
	if($dsql->IsTable($addtable)){
		$dsql->ExecuteNoneQuery("DROP TABLE IF EXISTS $addtable;");
	}
}

//如果文件名称不是默认的文件,则删除相关文件
if($addcon!="archives_add.php")DeleteFile($addcon);
if($editcon!="archives_edit.php")DeleteFile($editcon);
if($mancon!="archives.php")DeleteFile($mancon);


//删除数据
$dsql->ExecuteNoneQuery("DELETE FROM #@__archives_channeltype WHERE id='$id'");



ShowMsg("成功删除一个模型！","channel.php");
exit();




