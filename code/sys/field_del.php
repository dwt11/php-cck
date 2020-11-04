<?php
/**
 * 自定义模型,字段编辑
 *
 * @version        $Id: field_edit.php 1 15:22 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/dwttag.class.php");
require_once("fieldsSqlCode.func.php");


$id = isset($id) && is_numeric($id) ? $id : 0;
/*$mysql_version = $dsql->GetVersion();*/


/*------------------
删除字段
function _DELETE()
-------------------*/
//模型信息,获取附加表的名称和一些参数
$row = $dsql->GetOne("SELECT fieldset,addtable FROM `#@__sys_channeltype` WHERE id='$id'");
$fieldset = $row['fieldset'];
$trueTable = $row['addtable'];
$dtp = new DwtTagParse();
$dtp->SetNameSpace("field", "<", ">");
$dtp->LoadSource($fieldset);

//检测旧数据类型，并替换为新配置
foreach ($dtp->CTags as $tagid => $ctag) {
    if (strtolower($ctag->GetName()) == strtolower($fname)) {
        $dtp->Assign($tagid, "#@Delete@#");
    }
}

$oksetting = addslashes($dtp->GetResultNP());
$dsql->ExecuteNoneQuery("UPDATE `#@__sys_channeltype` SET fieldset='$oksetting' WHERE id='$id' ");
$dsql->ExecuteNoneQuery("ALTER TABLE `$trueTable` DROP `$fname` ");
ShowMsg("成功删除一个字段！", "field.php?id={$id}&dopost=edit");
exit();
