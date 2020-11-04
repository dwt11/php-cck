<?php
/**
 * 模型删除
 *
 * @version        $Id: channel_edit.php 1 14:49 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . "/dwttag.class.php");

if (empty($dopost)) $dopost = "";
$id = isset($id) && is_numeric($id) ? $id : 0;


if ($id < 5) {
    ShowMsg("系统模型不允许删除！", "channel.php");
    exit();
}

$myrow = $dsql->GetOne("SELECT addtable FROM `#@__sys_channeltype` WHERE id='$id'", MYSQL_ASSOC);
if (!is_array($myrow)) {
    ShowMsg('你所指定的模型信息不存在!', '-1');
    exit();
}

/*//检查频道的表是否独占数据表
$addtable = str_replace($cfg_dbprefix, '', str_replace('#@__', $cfg_dbprefix, $myrow['addtable']));
$row = $dsql->GetOne("SELECT COUNT(id) AS dd FROM `#@__channeltype` WHERE  addtable like '{$cfg_dbprefix}{$addtable}' OR addtable LIKE CONCAT('#','@','__','$addtable') ; ");
$isExclusive2 = ($row['dd'] > 1 ? 0 : 1);

//获取与频道关连的所有栏目id
$tids = '';
$dsql->Execute('qm', "SELECT id FROM `#@__arctype` WHERE channeltype='$id'");
while ($row = $dsql->GetArray('qm')) {
    $tids .= ($tids == '' ? $row['id'] : ',' . $row['id']);
}

//删除相关信息
if ($tids != '') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE typeid IN($tids); ");
    $dsql->ExecuteNoneQuery("DELETE FROM `{$myrow['maintable']}` WHERE typeid IN($tids); ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__spec` WHERE typeid IN ($tids); ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE typeid IN ($tids); ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctype` WHERE id IN ($tids); ");
}

//删除附加表或附加表内的信息
if ($isExclusive2 == 1) {
    $dsql->ExecuteNoneQuery("DROP TABLE IF EXISTS `{$cfg_dbprefix}{$addtable}`;");
} else {
    if ($tids != '' && $myrow['addtable'] != '') {
        $dsql->ExecuteNoneQuery("DELETE FROM `{$myrow['addtable']}` WHERE typeid IN ($tids); ");
    }
}

//删除频道配置信息
$dsql->ExecuteNoneQuery("DELETE FROM `#@__channeltype` WHERE id='$id' ");

//更新栏目缓存
UpDateCatCache($dsql);
ShowMsg("成功删除一个模型！", "mychannel_main.php");
exit();*/