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
require_once(DWTINC . '/enums.func.php');

if (empty($dopost)) {
    ShowMsg("你没指定任何参数！", "javascript:;");
    exit();
}
$ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");

/*-----------------
删除组或子内容
function __del() { }
------------------*/
if ($dopost == 'del') {
    $arr = $dsql->GetOne("SELECT * FROM `#@__sys_stepselect` WHERE id='$id' ");
    if (!is_array($arr)) {
        ShowMsg("无法获取信息，不允许后续操作！", "sysStepSelect.php");
        exit();
    }
    if ($arr['issystem'] == 1) {
        ShowMsg("系统内置的组不能删除！", "sysStepSelect.php");
        exit();
    }
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_stepselect` WHERE id='$id'; ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` WHERE egroup='{$arr['egroup']}'; ");
    ShowMsg("成功删除一个组！", "sysStepSelect.php");
    exit();
} else if ($dopost == 'delenumAllSel') {
    $oldid = "";
    $ids = explode('`', $ids);
    $dquery = "";
    foreach ($ids as $id) {
        if ($dquery == "") {
            $dquery .= " id='$id' ";
        } else {
            $dquery .= " Or id='$id' ";
        }
        $oldid = $id;//用于获取原来的类别组名称
    }
    if ($dquery != "") $dquery = " where " . $dquery;

    //获取类别组名称
    $groups = array();
    $dsql->Execute('me', "SELECT egroup FROM `#@__sys_enum` WHERE id IN($oldid) GROUP BY egroup");
    while ($row = $dsql->GetArray('me')) {
        $groups[] = $row['egroup'];
    }

    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` " . $dquery);

    //更新缓存
    foreach ($groups as $egroup) {
        WriteEnumsCache($egroup);
    }

    ShowMsg("成功删除选中的子内容！", $$ENV_GOBACK_URL);
    exit();
} else if ($dopost == 'delenum') {
    $row = $dsql->GetOne("SELECT egroup FROM `#@__sys_enum` WHERE id = '$id' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` WHERE id='{$id}'; ");
    WriteEnumsCache($row['egroup']);
    ShowMsg("成功删除一个子内容！", $$ENV_GOBACK_URL);
    exit();
}