<?php
/**
 * 参数 列表
 *
 * @version        $Id: 2016年4月29日 14:46
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

require_once(DWTINC . "/datalistcp.class.php");
setcookie(GetFunMainName($dwtNowUrl) . "ENV_GOBACK_URL", $dwtNowUrl, time() + 3600, "/");
$sql = $where = "";

if (empty($depid)) $depid = "";
$whereSql = "where isdel='0' ";
if ($depid != "") $whereSql .= " and depid='$depid'' ";
$sql = "SELECT * FROM #@__interface_phoneMsg $whereSql   ORDER BY   depid asc ";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetTemplate("phoneMsg.htm");
$dlist->SetSource($sql);
$dlist->Display();

