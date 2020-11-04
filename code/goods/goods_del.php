<?php
/**
 * 删除
 *
 * @version        $Id: dep_del.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once('../config.php');


$id = trim(preg_replace("#[^0-9]#", '', $id));

$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

/*if($dopost=='clear')
{//彻底删除
	
	$dsql->ExecuteNoneQuery("DELETE FROM `#@__goods` WHERE id='$id' and status='-2';");
    //这里要获取一下附加 表 再删除??????????????170112
	ShowMsg("删除成功！",$$ENV_GOBACK_URL);
	exit();
}
if($dopost=='delGoods')
{//删除到回收站*/
	
	$dsql->ExecuteNoneQuery("UPDATE #@__goods SET status='-2' where id='$id';");
	ShowMsg("已经删除到回收站！",$$ENV_GOBACK_URL);
	exit();
/*}
if($dopost=='upstatus')
{//更新商品的状态
	$dsql->ExecuteNoneQuery("UPDATE #@__goods SET status='$status' and pubdate='".time()."' where id='$id';");
	if($status=="0")ShowMsg("成功更新商品状态为:正常！",$$ENV_GOBACK_URL);
	exit();
}*/
