<?php
/**
 * 回收站
 *
 * @version        $Id: recycling.php 1 15:46 20日
 * @package
 * @copyright
 * @license
 * @link
 */
require_once('../config.php');
require_once(DWTINC.'/datalistcp.class.php');
if(empty($typeid))
{
    $typeid = '0';
    $whereSql = '';
}
if($typeid!=0)
{
    //require_once('channelunit.func.php');
    $whereSql = " AND arc.typeid IN (".GetArchiveSonIds($typeid).")";
}
$query = "SELECT arc.*,tp.typename FROM `#@__archives` AS arc
LEFT JOIN #@__archives_type AS tp ON arc.typeid = tp.id
WHERE arc.issend = '-2' $whereSql ORDER BY arc.id desc";
$dlist = new DataListCP();
$dlist->pageSize = 30;
$dlist->SetTemplet("recycling.htm");
$dlist->SetSource($query);
$dlist->display();