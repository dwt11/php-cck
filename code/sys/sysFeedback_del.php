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
require_once("../config.php");

if(empty($dopost))
{
    ShowMsg("你没指定任何参数！","javascript:;");
    exit();
}


else if($dopost=="del")
{
    $ids = explode('`',$ids);
    $dquery = "";
    foreach($ids as $id)
    {
        if($dquery=="")
        {
            $dquery .= " id='$id' ";
        }
        else
        {
            $dquery .= " Or id='$id' ";
        }
    }
    if($dquery!="") $dquery = " where ".$dquery;
    
	$sql="DELETE FROM #@__sys_feedback".$dquery;   //141130修复BUG
	//dump($sql);
	$dsql->ExecuteNoneQuery($sql);
    $ENV_GOBACK_URL=(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL");
    ShowMsg("删除成功！",$$ENV_GOBACK_URL);
    exit();
}
else
{
    ShowMsg("无法识别你的请求！","javascript:;");
    exit();
}