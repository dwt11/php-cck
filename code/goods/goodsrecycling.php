<?php
/**
 * 内容列表
 * content_s_list.php、content_i_list.php、content_select_list.php
 * 均使用本文件作为实际处理代码，只是使用的模板不同，如有相关变动，只需改本文件及相关模板即可
 *
 * @version        $Id: goods.php 1 14:31 2010年7月12日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC . '/datalistcp.class.php');
require_once('catalog.class.php');
require_once DWTINC . '/enums.func.php';  //获取数据字典对应的值

$t1 = ExecTime();

$typeid = isset($typeid) ? intval($typeid) : 0;
$userid = isset($userid) ? intval($userid) : 0;


if (!isset($keyword)) $keyword = '';
if (!isset($dopost)) $dopost = '';


$maintable = '#@__goods';


setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");


$tl = new GoodsTypeUnit($typeid);
$positionname = $tl->GetPositionName();    //当前分类名称
$optionarr = $tl->GetGoodsTypeOptionS();  //搜索表单的分类值//GetOptionArray


$whereSql = " where status='-2'";
if ($keyword != '') {
    $title .= " 搜索\"" . $keyword . "\" ";
    $whereSql .= " AND ( goodsname LIKE '%$keyword%' or goods.description LIKE '%$keyword%' or  goodscode like '%" . $keyword . "%') ";
}
if ($typeid != 0) {
    $whereSql .= ' AND typeid IN (' . GetGoodsSonIds($typeid) . ')';    //搜索用的
}


$orderby = empty($orderby) ? 'pubdate' : preg_replace("#[^a-z0-9]#", "", $orderby);
$orderbyField = '' . $orderby . ' DESC';


//获得当前模型的扩展数据表名
$addtable = "#@__goods_addonfloor";


$query = "SELECT  goods.*
 FROM `$maintable` goods 
 $whereSql
 group  by goods.id
    ORDER BY   $orderbyField ";


//dump($query);
//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;

//GET参数
$dlist->SetParameter('typeid', $typeid);
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('orderby', $orderby);

//模板
if (empty($s_tmplets)) $s_tmplets = 'goodsrecycling.htm';
$dlist->SetTemplate($s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();

$t2 = ExecTime();
//echo $t2-$t1;





