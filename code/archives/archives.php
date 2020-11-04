<?php
/**
 * 内容列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: archives.php 1 14:31 12日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");
require_once('catalogLinkOption.class.php');
require_once(DWTINC.'/datalistcp.class.php');
$t1 = ExecTime();

$typeid = isset($typeid) ? intval($typeid) : 0;//此处临时 加限制  只显示仪表的内容  随后修改
$channelid = isset($channelid) ? intval($channelid) : 0;
$userid = isset($userid) ? intval($userid) : 0;

if(!isset($keyword)) $keyword = '';
if(!isset($flag)) $flag = '';
if(!isset($issend)) $issend = '';  //是否审核
if(!isset($dopost)) $dopost = '';


$userCatalogSql = '';
$user_catalogs=array();
$user_catalog="";


$maintable = '#@__archives';
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");

$tl = new TypeLink($typeid);
$positionname = $tl->GetArchivePositionName();    //当前栏目名称
$optionarr = $tl->GetArchiveOptionArray($typeid);  //搜索表单的栏目值//GetOptionArray
$ispart = $tl->GetArchiveIspart();  //是否封面栏目  如果是封面栏目  则不输出添加

$whereSql = empty($channelid) ? " WHERE arc.channelid > 0  AND arc.issend > -2 " : " WHERE arc.channelid = '$channelid' AND arc.issend > -2 ";

$flagsArr = '';
$dsql->Execute('f', 'SELECT * FROM `#@__att`   ORDER BY   sortid ASC');
while($frow = $dsql->GetArray('f'))
{
    $flagsArr .= ($frow['att']==$flag ? "<option value='{$frow['att']}' selected>{$frow['attname']}</option>\r\n" : "<option value='{$frow['att']}'>{$frow['attname']}</option>\r\n");
}


if(!empty($mid))
{
    $whereSql .= " AND arc.userid = '$userid' ";
}
if($keyword != '')
{
    $whereSql .= " AND ( CONCAT(arc.title) LIKE '%$keyword%') ";
}
if($flag != '')
{
    $whereSql .= " AND FIND_IN_SET('$flag', arc.flag) ";
}
if($typeid != 0)
{
    $whereSql .= ' AND arc.typeid IN ('.GetArchiveSonIds($typeid).')';    //搜索用的
}
if($issend != '')
{
    $whereSql .= " AND arc.issend = '$issend' ";
}

$orderby = empty($orderby) ? 'id' : preg_replace("#[^a-z0-9]#", "", $orderby);
$orderbyField = 'arc.'.$orderby.' DESC';
if($orderby=="deptype")$orderbyField = 'arc.'.$orderby.' ASC';//如果用户选择按权限排序  则升序

$query = "SELECT  arc.id,arc.typeid,arc.flag,arc.issend,arc.channelid,arc.deptype,arc.click,arc.title,arc.color,arc.senddate,arc.userid 
FROM `$maintable` arc
$whereSql
  ORDER BY   id DESC ";

if(empty($f) || !preg_match("#form#", $f)) $f = 'form1.arcid1';
//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;

//GET参数
$dlist->SetParameter('typeid', $typeid);
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('flag', $flag);
$dlist->SetParameter('orderby', $orderby);
$dlist->SetParameter('issend', $issend);
$dlist->SetParameter('channelid', $channelid);

//模板
$s_tmplets = 'archives.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;



function getFeedbackNumb($archivesid){
    global $dsql;
    $query="SELECT COUNT(*) AS dd FROM `#@__archives_feedback` WHERE archivesid='$archivesid' AND ischeck='1' ";
    $row = $dsql->GetOne($query);

    $totalcount = (empty($row['dd']) ? 0 : $row['dd']);
    return $totalcount;
}