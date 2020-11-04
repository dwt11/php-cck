<?php
/**
 * 信息编辑
 *
 * @version        $Id: goods_edit.php 1 8:26 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");


$id = isset($id) && is_numeric($id) ? $id : 0;
if ($id == '') {
    ShowMsg("参数无效！", $$ENV_GOBACK_URL);
    exit();
}

/*--------------------------------
function save(){  }
-------------------------------*/


//更新数据库的SQL语句
$query = "UPDATE #@__client_depinfos SET isdel='0'   WHERE clientid='$id' ";
// dump($query);
if (!$dsql->ExecuteNoneQuery($query)) {
    ShowMsg('更新数据表时出错，请检查', -1);
    exit();
}


$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

ShowMsg("用户状态恢复成功！", $$ENV_GOBACK_URL);
exit;
