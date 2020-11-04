<?php
/**
 * 微信参数 列表
 *
 * @version        $Id: weixin.php 2016年4月29日 14:46
 * @package
 * @copyright
 * @license
 * @link
 */
require_once("../config.php");

require_once(DWTINC . "/datalistcp.class.php");
require_once(DWTINC . "/common.func.php");
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");
$sql = $where = "";

if (empty($depid)) $depid = "";
if (empty($weixin_type)) $weixin_type = "";
$whereSql="where isdel='0' ";
if($depid!="")$whereSql.= " and depid='$depid'' ";
if($weixin_type!="")$whereSql.= " and  weixin_type='$weixin_type'' ";
$sql = "SELECT * FROM #@__interface_weixin $whereSql   ORDER BY   depid asc ,id aSC";

//dump($sql);
$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetParameter("depid", $depid);
$dlist->SetParameter("weixin_type", $weixin_type);
$dlist->SetTemplate("weixin.htm");
$dlist->SetSource($sql);
$dlist->Display();

