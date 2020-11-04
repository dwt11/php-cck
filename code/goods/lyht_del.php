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


//查询表信息
$query = "SELECT goodsid FROM `#@__goods_addon_lycp`  WHERE lyhtid='$id' ";
$row = $dsql->GetOne($query);
if(isset($row["goodsid"])||$row["goodsid"]!=""){
    ShowMsg("有旅游商品使用此合同,删除失败!", "-1");
    exit();

}



$query = "DELETE FROM `#@__lyht` WHERE id='$id'";
if (!$dsql->ExecuteNoneQuery($query)) {
    ShowMsg("删除失败!", "-1");
    exit();


}
$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
ShowMsg("删除成功！", $$ENV_GOBACK_URL);

exit();




