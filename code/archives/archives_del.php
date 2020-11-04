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

if (empty($dopost)) {
    ShowMsg('对不起，你没指定运行参数！', '-1');
    exit();
}
$id = trim(preg_replace("#[^0-9]#", '', $id));

/*--------------------------
//删除文档到回收站
function delArchives(){ }
---------------------------*/
if ($dopost == "delArchives") {

    if (!empty($id) && empty($qstr)) {
        $qstr = $id;
    }
    if ($qstr == '') {
        ShowMsg("参数无效！", $ENV_GOBACK_URL);
        exit();
    }
    $qstrs = explode("`", $qstr);
    $okaids = Array();

    foreach ($qstrs as $id) {

        if (!isset($okaids[$id])) {
            DelArc($id);
            //dump($id);
        } else {
            $okaids[$id] = 1;
        }
    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功删除指定的文档！", $$ENV_GOBACK_URL);
    exit();

} /*-----------------------------
//还原文档
function RbReturnArchives(){ }
------------------------------*/
else if ($dopost == 'return') {

    if (!empty($id) && empty($qstr)) $qstr = $id;

    if ($qstr == '') {
        ShowMsg("参数无效！", "recycling.php");
        exit();
    }
    $qstrs = explode("`", $qstr);
    foreach ($qstrs as $id) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET issend='0' WHERE id='$id'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives_arctiny` SET `issend` = '0' WHERE id = '$id'; ");
    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功删除指定的文档！", $$ENV_GOBACK_URL);
    exit();
} /*-----------------------------
//清空文档
function RbClearArchives(){ }
------------------------------*/
else if ($dopost == 'clear') {

    if (!empty($id) && empty($qstr)) $qstr = $id;
    if ($qstr == '') {
        ShowMsg("参数无效！", "recycling.php");
        exit();
    }
    $qstrs = explode("`", $qstr);
    $okaids = Array();
    foreach ($qstrs as $id) {
        if (!isset($okaids[$id])) {
            clearArc($id);

        } else {
            $okaids[$id] = 1;
        }
    }
    $ENV_GOBACK_URL = (GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL");
    ShowMsg("成功删除指定的文档！", $$ENV_GOBACK_URL);
    exit();
}


/**
 *  清空文档信息
 *
 * @access    public
 *
 * @param     string $id 文档ID
 *
 * @return    string
 */
function clearArc($id)
{
    global $dsql;
    if (empty($id)) return;
    $id = preg_replace("#[^0-9]#i", '', $id);

    //查询表信息
    $query = "SELECT ch.maintable,ch.addtable,ch.nid FROM `#@__archives_arctiny` arc
                LEFT JOIN `#@__archives_type` tp ON tp.id=arc.typeid
              LEFT JOIN `#@__archives_channeltype` ch ON ch.id=arc.channelid WHERE arc.id='$id' ";
    //dump($query);

    $row = $dsql->GetOne($query);
    $nid = $row['nid'];
    $maintable = (trim($row['maintable']) == '' ? '#@__archives' : trim($row['maintable']));
    $addtable = trim($row['addtable']);
    //查询档案信息
    $arcQuery = "SELECT arc.*,tp.*,arc.id AS archivesid FROM `$maintable` arc LEFT JOIN `#@__archives_type` tp ON arc.typeid=tp.id WHERE arc.id='$id' ";
    $arcRow = $dsql->GetOne($arcQuery);


    if (!is_array($arcRow)) return FALSE;

    $query = "DELETE FROM `#@__archives_arctiny` WHERE id='$id' and issend='-2'";
    if ($dsql->ExecuteNoneQuery($query)) {
        //$dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE archivesid='$id' ");  //这句评论表弄好要启用??
        if ($addtable != '') {
            $dsql->ExecuteNoneQuery("Delete From `$addtable` WHERE archivesid='$id'");//2011.7.3 根据论坛反馈，修复删除文章时无法清除附加表中对应的数据 (by：织梦的鱼)
        }
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$id'  and issend='-2'");
    }

    return true;
}


/**
 *  删除文档信息到回收站
 *
 * @access    public
 *
 * @param     string $id 文档ID
 *
 * @return    string
 */
function DelArc($id)
{
    global $dsql;
    if (empty($id)) return;
    $id = preg_replace("#[^0-9]#i", '', $id);

    //查询表信息
    $query = "SELECT ch.maintable,ch.addtable,ch.nid FROM `#@__archives_arctiny` arc
                LEFT JOIN `#@__archives_type` tp ON tp.id=arc.typeid
              LEFT JOIN `#@__archives_channeltype` ch ON ch.id=arc.channelid WHERE arc.id='$id' ";
    //dump($query);

    $row = $dsql->GetOne($query);
    $nid = $row['nid'];
    $maintable = (trim($row['maintable']) == '' ? '#@__archives' : trim($row['maintable']));

    //查询档案信息
    $arcQuery = "SELECT arc.*,tp.*,arc.id AS archivesid FROM `$maintable` arc LEFT JOIN `#@__archives_type` tp ON arc.typeid=tp.id WHERE arc.id='$id' ";
    $arcRow = $dsql->GetOne($arcQuery);


    if (!is_array($arcRow)) return FALSE;

    $dsql->ExecuteNoneQuery("UPDATE `$maintable` SET issend='-2' WHERE id='$id' ");
    $dsql->ExecuteNoneQuery("UPDATE `#@__archives_arctiny` SET `issend` = '-2' WHERE id = '$id'; ");

    return true;
}
