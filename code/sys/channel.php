<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: channel.php 1 15:26 2010年7月20日
 * @package

 * @license
 * @link
 */
require_once("../config.php");
require_once(DWTINC.'/datalistcp.class.php');
setcookie(GetFunMainName($dwtNowUrl)."ENV_GOBACK_URL",$dwtNowUrl,time()+3600,"/");

$sql = "SELECT id,nid,typename,maintable,addtable,templist,tempadd,tempedit FROM `#@__sys_channeltype`   ORDER BY   id aSC";
$dlist = new DataListCP();
$dlist->SetTemplet("channel.htm");
$dlist->SetSource($sql);
$dlist->display();

