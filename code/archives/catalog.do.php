<?php
/**
 * 栏目跳转操作
 *
 * @version        $Id: catalog.do.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');
if(empty($dopost))
{
    ShowMsg("对不起，请指定栏目参数！","catalog.php");
    exit();
}
$typeid = empty($typeid) ? 0 : intval($typeid);
$unittype = empty($unittype) ? 0 : intval($unittype);
$channelid = empty($channelid) ? 0 : intval($channelid);




if($dopost=="upRankAll")
{
    //检查权限许可
    //checkpurview('t_Edit');
    $row = $dsql->GetOne("SELECT id FROM #@__archives_type   ORDER BY   id DESC");
    if(is_array($row))
    {
        $maxID = $row['id'];
        for($i=1;$i<=$maxID;$i++)
        {
            if(isset(${'sortrank'.$i}))
            {
                $dsql->ExecuteNoneQuery("UPDATE #@__archives_type SET sortrank='".(${'sortrank'.$i})."' WHERE id='{$i}';");
            }
        }
    }
    //UpDateCatCache();
    ShowMsg("操作成功，正在返回...","catalog.php");
    exit();
}

/*-----------
获得子类的内容,catalog.php
function GetSunLists();
-----------*/
else if($dopost=="GetSunLists")
{
require_once("catalogUnit.class.php");
    AjaxHead();
    PutCookie('lastCid', $typeid, 3600*24, "/");
    $tu = new TypeUnit();
    $tu->dsql = $dsql;
    echo "    <table width='100%' border='0' cellspacing='0' cellpadding='0'>\r\n";
    $tu->LogicListAllSunType($typeid, "　");
    echo "    </table>\r\n";
    $tu->Close();
}


/*-----------
获得子类的内容,index_menu.php使用
function GetSunLists();
-----------*/
else if($dopost=="GetMenuSunLists")
{
    global $cataloglistToMenu;
	AjaxHead();
    PutCookie('lastCidMenu', $typeid, 3600*24, "/");
	require_once("catalog.inc.class.php");
	$cl = new CatalogInc();
    echo "    <table width='100%' border='0' cellspacing='0' cellpadding='0'>\r\n";
    $cl->LogicGetListToMenu($typeid, "　");
	echo $cataloglistToMenu;
    echo "    </table>\r\n";
    $cl->Close();
}
/*----------------
合并栏目
function unitCatalog() { }
-----------------*/
else if($dopost == 'unitCatalog')
{
//    //checkpurview('t_Move');
    require_once(DWTINC.'/oxwindow.class.php');
    require_once("catalogLinkOption.class.php");
    //require_once('channelunit.func.php');
    if(empty($nextjob))
    {
        $typeid = isset($typeid) ? intval($typeid) : 0;
        $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__archives_type` WHERE reid='$typeid' ");
        $tl = new TypeLink($typeid);
        $typename = $tl->TypeInfos['typename'];
        $reid = $tl->TypeInfos['reid'];
        $channelid = $tl->TypeInfos['channeltype'];
        if(!empty($row['dd']))
        {
            ShowMsg("栏目： $typename($typeid) 有子栏目，不能进行合并操作！", '-1');
            exit();
        }
        $typeOptions = $tl->GetArchiveOptionArray(0);
        $wintitle = '合并栏目';
        $wecome_info = "<a href='catalog.php'>栏目管理</a> &gt;&gt; 合并栏目";
        $win = new OxWindow();
        $win->Init('catalog.do.php', 'js/blank.js', 'POST');
        $win->AddHidden('dopost', 'unitCatalog');
        $win->AddHidden('typeid', $typeid);
        $win->AddHidden('channelid', $channelid);
        $win->AddHidden('nextjob', 'unitok');
        $win->AddTitle("合并栏目后，原栏目会被删除。");
        $win->AddItem('你选择的栏目是：', "<font color='red'>$typename($typeid)</font>");
        $win->AddItem('你希望合并到那个栏目？', "<select name='unittype'>\r\n{$typeOptions}\r\n</select>");
        $win->AddItem('注意事项：', '栏目不能有下级子栏目，只允许子级到更高级或同级或不同父级的情况。');
        $winform = $win->GetWindow('ok');
        $win->Display();
        exit();
    }
    else
    {
        if($typeid==$unittype)
        {
            ShowMsg("同一栏目无法合并,请后退重试！", '-1');
            exit();
        }
        if(IsParent($unittype, $typeid))
        {
            ShowMsg('不能从父类合并到子类！', 'catalog.php');
            exit();
        }
        $row = $dsql->GetOne("SELECT addtable FROM `#@__archives_channeltype` WHERE id='$channelid' ");
        $addtable = (empty($row['addtable']) ? '#@__addonarticle' : $row['addtable'] );
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives_arctiny` SET typeid='$unittype' WHERE typeid='$typeid' ");
        //$dsql->ExecuteNoneQuery("UPDATE `#@__feedback` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid='$unittype' WHERE typeid='$typeid' ");
        //$dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid2='$unittype' WHERE typeid2='$typeid' ");
        //$dsql->ExecuteNoneQuery("UPDATE `#@__addonspec` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `$addtable` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives_type` WHERE id='$typeid' ");
        //UpDateCatCache();
        ShowMsg('成功合并指定栏目！', 'catalog.php');
        exit();
    }
}
/*----------------
移动栏目
function moveCatalog() { }
-----------------*/
else if($dopost == 'moveCatalog')
{
    //checkpurview('t_Move');
    require_once(DWTINC.'/oxwindow.class.php');
    require_once("catalogLinkOption.class.php");
    //require_once('channelunit.func.php');
    if(empty($nextjob))
    {
        $tl = new TypeLink($typeid);
        $typename = $tl->TypeInfos['typename'];
        $reid = $tl->TypeInfos['reid'];
        $channelid = $tl->TypeInfos['channeltype'];
        $typeOptions = $tl->GetArchiveOptionArray(0);
        $wintitle = "移动栏目";
        $wecome_info = "<a href='catalog.php'>栏目管理</a> &gt;&gt; 移动栏目";
        $win = new OxWindow();
        $win->Init('catalog.do.php', 'js/blank.js', 'POST');
        $win->AddHidden('dopost', 'moveCatalog');
        $win->AddHidden('typeid', $typeid);
        $win->AddHidden('channelid', $channelid);
        $win->AddHidden('nextjob', 'unitok');
        $win->AddTitle("移动目录后，会成为目标目录的子目录。");
        $win->AddItem('你选择的栏目是：',"$typename($typeid)");
        $win->AddItem('你希望移动到那个栏目？',"<select name='movetype'>\r\n<option value='0'>移动为顶级栏目</option>\r\n$typeOptions\r\n</select>");
        $win->AddItem('注意事项：','不允许从父级移动到子级目录，只允许子级到更高级或同级或不同父级的情况。');
        $winform = $win->GetWindow('ok');
        $win->Display();
        exit();
    }
    else
    {
        if($typeid==$movetype)
        {
            ShowMsg('移对对象和目标位置相同！', 'catalog.php');
            exit();
        }
        if(IsParent($movetype, $typeid))
        {
            ShowMsg('不能从父类移动到子类！', 'catalog.php');
            exit();
        }
        $dsql->ExecuteNoneQuery(" UPDATE `#@__archives_type` SET reid='$movetype' WHERE id='$typeid' ");
        //UpDateCatCache();
        ShowMsg('成功移动目录！', 'catalog.php');
        exit();
    }
}